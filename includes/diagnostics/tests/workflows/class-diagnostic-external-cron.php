<?php
/**
 * External Cron Configured Diagnostic (Stub)
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
 * Diagnostic_External_Cron_Reviewed Class
 *
 * TODO: Implement full test logic and remediation guidance.
 */
class Diagnostic_External_Cron extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'external-cron';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'External Cron Configured';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether WordPress scheduled events are running on time, detecting signs that WP-Cron may be unreliable without a real external cron setup.';

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
	 * - Check for alternate cron endpoints, system cron integration, or hosting scheduler usage.
	 *
	 * TODO Fix Plan:
	 * - Configure reliable cron execution outside frontend traffic when possible.
	 * - Use WordPress hooks, filters, settings, DB fixes, PHP config, or accessible server settings.
	 * - Do not modify WordPress core files.
	 * - Ensure performance/security/success impact and align with WPShadow commandments.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		// If DISABLE_WP_CRON is true a server/system cron is handling execution — pass.
		if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
			return null;
		}

		// Check for significantly overdue scheduled events (> 15 min late).
		// This reveals whether WP-Cron is actually firing reliably under traffic.
		$cron_events = _get_cron_array();
		if ( empty( $cron_events ) || ! is_array( $cron_events ) ) {
			return null;
		}

		$now          = time();
		$overdue_jobs = array();

		foreach ( $cron_events as $timestamp => $hooks ) {
			if ( ! is_numeric( $timestamp ) ) {
				continue;
			}
			$late_by = $now - (int) $timestamp;
			if ( $late_by > 900 ) { // 15 minutes
				foreach ( array_keys( $hooks ) as $hook ) {
					$overdue_jobs[] = array(
						'hook'    => $hook,
						'late_by' => $late_by,
					);
				}
			}
		}

		if ( empty( $overdue_jobs ) ) {
			return null;
		}

		$count = count( $overdue_jobs );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of overdue cron events */
				_n(
					'%d scheduled event is overdue by more than 15 minutes. WP-Cron fires only when a page is loaded, so low-traffic sites may have tasks that never run on time. Configure a system cron job to call wp-cron.php on a fixed schedule.',
					'%d scheduled events are overdue by more than 15 minutes. WP-Cron fires only when a page is loaded, so low-traffic sites may have tasks that never run on time. Configure a system cron job to call wp-cron.php on a fixed schedule.',
					$count,
					'wpshadow'
				),
				$count
			),
			'severity'     => 'medium',
			'threat_level' => 35,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/external-cron',
			'details'      => array(
				'overdue_count' => $count,
				'overdue_jobs'  => array_slice( $overdue_jobs, 0, 10 ),
			),
		);
	}
}
