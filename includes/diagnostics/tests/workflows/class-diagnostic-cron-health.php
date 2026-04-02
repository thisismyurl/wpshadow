<?php
/**
 * Cron Health Reviewed Diagnostic (Stub)
 *
 * TODO stub mapped to the workflows gauge.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Cron_Health_Reviewed Class
 *
 * TODO: Implement full test logic and remediation guidance.
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
	 * TODO Test Plan:
	 * - Inspect cron option for overdue events or obvious scheduling failures.
	 *
	 * TODO Fix Plan:
	 * - Resolve missed cron execution and ensure recurring events run on time.
	 * - Use WordPress hooks, filters, settings, DB fixes, PHP config, or accessible server settings.
	 * - Do not modify WordPress core files.
	 * - Ensure performance/security/success impact and align with WPShadow commandments.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
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
				'kb_link'      => 'https://wpshadow.com/kb/cron-health',
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
				'kb_link'      => 'https://wpshadow.com/kb/cron-health',
				'details'      => array(
					'total_scheduled' => $total_hooks,
					'overdue_30m'     => $overdue_30m,
				),
			);
		}

		return null;
	}
}
