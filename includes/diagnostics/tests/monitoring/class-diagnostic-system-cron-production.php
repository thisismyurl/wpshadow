<?php
/**
 * System Cron In Production Diagnostic
 *
 * WordPress WP-Cron runs on page load by default. On low-traffic sites
 * this means scheduled tasks may be skipped for hours. On high-traffic
 * sites it adds latency to every request. The optimal configuration for
 * production is to disable WP-Cron and instead use a real server cron
 * job that calls wp-cron.php on a fixed schedule.
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
 * Diagnostic_System_Cron_Production Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_System_Cron_Production extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'system-cron-production';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'System Cron In Production';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether WordPress WP-Cron is configured for reliable execution. The recommended production setup disables the built-in visitor-triggered cron in favour of a real server cron job.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'monitoring';

	/**
	 * Confidence level of this diagnostic.
	 *
	 * @var string
	 */
	protected static $confidence = 'standard';

	/**
	 * Number of minutes a cron event can be overdue before we consider it stuck.
	 */
	private const OVERDUE_THRESHOLD_MINUTES = 30;

	/**
	 * Run the diagnostic check.
	 *
	 * Two sub-checks:
	 *
	 * 1. If DISABLE_WP_CRON is not true, WordPress is using visitor-triggered
	 *    cron — reliable enough for shared hosting but unreliable for time-
	 *    sensitive tasks on low-traffic sites. Raises a low advisory.
	 *
	 * 2. If DISABLE_WP_CRON is true, the admin has opted into a system cron
	 *    setup. Validates by checking for overdue events (which would mean
	 *    the system cron is not running).
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		$wp_cron_disabled = defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON;

		if ( ! $wp_cron_disabled ) {
			// Using built-in visitor-triggered cron — give a low advisory.
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'WordPress is using its built-in visitor-triggered cron (DISABLE_WP_CRON is not set). Scheduled tasks will only run when a visitor loads the site, making them unreliable on low-traffic sites and adding latency on high-traffic sites.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 25,
				'kb_link'      => '',
				'details'      => array(
					'disable_wp_cron' => false,
					'fix'             => __( 'Add define(\'DISABLE_WP_CRON\', true); to wp-config.php, then add a server cron job that runs every minute: * * * * * wget -q -O - https://yoursite.com/wp-cron.php?doing_wp_cron > /dev/null 2>&1. This ensures tasks run on schedule regardless of traffic.', 'wpshadow' ),
				),
			);
		}

		// DISABLE_WP_CRON is true — check for overdue cron events as evidence
		// that the system cron job is not actually running.
		$cron_array = _get_cron_array();
		if ( empty( $cron_array ) ) {
			return null;
		}

		$now        = time();
		$threshold  = self::OVERDUE_THRESHOLD_MINUTES * MINUTE_IN_SECONDS;
		$overdue    = array();

		foreach ( $cron_array as $timestamp => $hooks ) {
			if ( ! is_numeric( $timestamp ) ) {
				continue;
			}
			$overdue_by = $now - (int) $timestamp;
			if ( $overdue_by > $threshold ) {
				foreach ( array_keys( (array) $hooks ) as $hook ) {
					$overdue[] = array(
						'hook'       => $hook,
						'overdue_by' => human_time_diff( (int) $timestamp, $now ),
					);
					if ( count( $overdue ) >= 5 ) {
						break 2;
					}
				}
			}
		}

		if ( ! empty( $overdue ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of overdue cron events */
					__( 'DISABLE_WP_CRON is set but %d scheduled tasks are significantly overdue, suggesting the server cron job is not running or is configured incorrectly.', 'wpshadow' ),
					count( $overdue )
				),
				'severity'     => 'high',
				'threat_level' => 65,
				'kb_link'      => '',
				'details'      => array(
					'disable_wp_cron' => true,
					'overdue_events'  => $overdue,
					'fix'             => __( 'Verify that the server cron job running wp-cron.php is active and executing without errors. Check the cron tab with crontab -l and test by running the command manually. Ensure the URL and credentials are correct.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
