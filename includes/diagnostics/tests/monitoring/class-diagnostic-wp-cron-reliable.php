<?php
/**
 * WP Cron Reliable Diagnostic
 *
 * Scheduled WordPress tasks (email sending, update checks, published
 * post scheduling, backup runs, etc.) depend on cron events firing on
 * time. When numerous events are significantly overdue it indicates that
 * the cron runner is blocked, misconfigured, or not running at all.
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
 * Diagnostic_Wp_Cron_Reliable Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Wp_Cron_Reliable extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'wp-cron-reliable';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'WP Cron Reliable';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks for a high volume of significantly overdue WP-Cron events, which indicates the cron runner is blocked or not executing reliably.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'monitoring';

	/**
	 * Minimum number of overdue events before a finding is raised.
	 * A few overdue events is normal; many suggests a systemic problem.
	 */
	private const OVERDUE_COUNT_THRESHOLD = 3;

	/**
	 * How many minutes past scheduled time constitutes "overdue".
	 */
	private const OVERDUE_MINUTES = 60;

	/**
	 * Run the diagnostic check.
	 *
	 * Reads the cron array and counts events whose scheduled timestamp is
	 * more than OVERDUE_MINUTES in the past. A healthy site will have zero
	 * or very few overdue events. A blocked cron runner causes them to
	 * accumulate rapidly.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		$cron_array = _get_cron_array();
		if ( empty( $cron_array ) ) {
			return null;
		}

		$now                 = time();
		$overdue_threshold   = $now - ( self::OVERDUE_MINUTES * MINUTE_IN_SECONDS );
		$overdue_count       = 0;
		$overdue_hooks       = array();

		foreach ( $cron_array as $timestamp => $hooks ) {
			if ( ! is_numeric( $timestamp ) ) {
				continue;
			}

			if ( (int) $timestamp > $overdue_threshold ) {
				continue; // Not yet overdue.
			}

			foreach ( array_keys( (array) $hooks ) as $hook ) {
				$overdue_count++;
				if ( count( $overdue_hooks ) < 10 ) {
					$overdue_hooks[] = array(
						'hook'       => $hook,
						'overdue_by' => human_time_diff( (int) $timestamp, $now ),
					);
				}
			}
		}

		if ( $overdue_count < self::OVERDUE_COUNT_THRESHOLD ) {
			return null;
		}

		$total_events = 0;
		foreach ( $cron_array as $hooks ) {
			$total_events += count( (array) $hooks );
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: overdue event count, 2: total event count */
				__( '%1$d of %2$d scheduled WP-Cron events are more than %3$d minutes overdue. Scheduled tasks including email sending, update checks, and backups may not be running as expected.', 'wpshadow' ),
				$overdue_count,
				$total_events,
				self::OVERDUE_MINUTES
			),
			'severity'     => $overdue_count >= 10 ? 'high' : 'medium',
			'threat_level' => $overdue_count >= 10 ? 65 : 45,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/wp-cron-reliable?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'overdue_count'  => $overdue_count,
				'total_events'   => $total_events,
				'overdue_events' => $overdue_hooks,
				'fix'            => __( 'Check whether a security plugin or server firewall is blocking requests to wp-cron.php. If DISABLE_WP_CRON is set, ensure the system cron job is active. Install a plugin such as WP Crontrol to inspect and manually trigger overdue events while you investigate the root cause.', 'wpshadow' ),
			),
		);
	}
}
