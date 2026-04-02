<?php
/**
 * Cron Health Reviewed Diagnostic
 *
 * Inspects the WordPress cron array to detect events that are significantly
 * overdue (more than 30 minutes late), and flags when the total number of
 * registered scheduled events exceeds a healthy threshold (cron bloat).
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Cron_Health Class
 *
 * Iterates the _get_cron_array() output to count overdue events (> 30 min)
 * and total registered hooks. Returns a medium finding for overdue events or
 * a low finding for excessive queue size, or null when healthy.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Cron_Health extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'cron-health';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Cron Health';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether WordPress scheduled events are running on time and that the cron queue has not grown excessively large due to scheduling failures or plugin cron bloat.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'workflows';

	/**
	 * Run the diagnostic check.
	 *
	 * Reads the WordPress cron array, counts total registered hooks and how many
	 * are more than 30 minutes overdue. Returns a medium-severity finding for
	 * overdue events, a low-severity finding for cron queue bloat (> 100 hooks),
	 * or null when the cron queue is healthy.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array when cron issues are detected, null when healthy.
	 */
	public static function check() {
		$cron_events = _get_cron_array();
		if ( empty( $cron_events ) || ! is_array( $cron_events ) ) {
			return null;
		}

		$now          = time();
		$total_hooks  = 0;
		$overdue_30m  = 0;

		foreach ( $cron_events as $timestamp => $hooks ) {
			if ( ! is_numeric( $timestamp ) || ! is_array( $hooks ) ) {
				continue;
			}
			$total_hooks += count( $hooks );
			if ( ( $now - (int) $timestamp ) > 1800 ) {
				$overdue_30m += count( $hooks );
			}
		}

		// Flag if there are events more than 30 minutes overdue (threshold higher
		// than external-cron which uses 15 min, to surface only persistently missed jobs).
		if ( $overdue_30m > 0 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of overdue events */
					_n(
						'%d scheduled event is more than 30 minutes overdue. This indicates WP-Cron is not running reliably — either because the site receives insufficient traffic to trigger it, or because a previous cron run crashed leaving a stale lock.',
						'%d scheduled events are more than 30 minutes overdue. This indicates WP-Cron is not running reliably — either because the site receives insufficient traffic to trigger it, or because a previous cron run crashed leaving a stale lock.',
						$overdue_30m,
						'wpshadow'
					),
					$overdue_30m
				),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/cron-health?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'total_scheduled' => $total_hooks,
					'overdue_30m'     => $overdue_30m,
				),
			);
		}

		// Flag if there is an unusually large number of scheduled events (cron bloat).
		if ( $total_hooks > 100 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of scheduled events */
					__( '%d scheduled events are registered. An excessive number of cron events indicates a plugin is creating recurring jobs without cleaning up, which slows the site because the full cron array is loaded on every request.', 'wpshadow' ),
					$total_hooks
				),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/cron-health?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'total_scheduled' => $total_hooks,
					'overdue_30m'     => $overdue_30m,
				),
			);
		}

		return null;
	}
}
