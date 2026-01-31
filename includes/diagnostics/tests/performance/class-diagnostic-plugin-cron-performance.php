<?php
/**
 * Plugin Cron Performance Diagnostic
 *
 * Detects plugins with problematic scheduled tasks.
 *
 * @since   1.4031.1939
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Plugin_Cron_Performance Class
 *
 * Identifies scheduled tasks that impact site performance.
 */
class Diagnostic_Plugin_Cron_Performance extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-cron-performance';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Cron Performance';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for plugin cron jobs that may impact performance';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.4031.1939
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$cron_concerns = array();

		// Get WordPress cron schedule
		$crons = _get_cron_array();

		if ( empty( $crons ) ) {
			return null;
		}

		$cron_count = count( $crons );
		$recurring  = 0;
		$overdue    = 0;

		$now = time();

		foreach ( $crons as $timestamp => $cron_jobs ) {
			// Check for overdue crons
			if ( $timestamp < $now ) {
				$overdue += count( $cron_jobs );
			}

			foreach ( $cron_jobs as $cron_name => $details ) {
				// Check for recurring (not one-time)
				if ( ! empty( $details[0]['recurrence'] ) && $details[0]['recurrence'] !== 'onetime' ) {
					$recurring++;
				}
			}
		}

		// Too many crons can slow down site
		if ( $cron_count > 50 ) {
			$cron_concerns[] = sprintf(
				/* translators: %d: cron job count */
				__( '%d scheduled cron jobs. Each executed on page load adds latency.', 'wpshadow' ),
				$cron_count
			);
		}

		// Overdue crons can spike CPU/database
		if ( $overdue > 10 ) {
			$cron_concerns[] = sprintf(
				/* translators: %d: overdue job count */
				__( '%d cron jobs are overdue. They\'ll execute on next page load, blocking the page.', 'wpshadow' ),
				$overdue
			);
		}

		// Too many recurring crons
		if ( $recurring > 30 ) {
			$cron_concerns[] = sprintf(
				/* translators: %d: recurring cron count */
				__( '%d recurring cron jobs. More than 30 will cause frequent performance spikes.', 'wpshadow' ),
				$recurring
			);
		}

		// Check for plugins running crons too frequently
		$very_frequent = 0;
		foreach ( $crons as $timestamp => $cron_jobs ) {
			foreach ( $cron_jobs as $details ) {
				if ( ! empty( $details[0]['interval'] ) ) {
					if ( $details[0]['interval'] < 3600 ) { // Less than 1 hour
						$very_frequent++;
					}
				}
			}
		}

		if ( $very_frequent > 5 ) {
			$cron_concerns[] = sprintf(
				/* translators: %d: high frequency cron count */
				__( '%d crons run more than hourly. This causes database strain.', 'wpshadow' ),
				$very_frequent
			);
		}

		if ( ! empty( $cron_concerns ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $cron_concerns ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'details'      => array(
					'total_crons'           => $cron_count,
					'overdue_crons'         => $overdue,
					'recurring_crons'       => $recurring,
					'very_frequent_crons'   => $very_frequent,
				),
				'kb_link'      => 'https://wpshadow.com/kb/plugin-cron-performance',
			);
		}

		return null;
	}
}
