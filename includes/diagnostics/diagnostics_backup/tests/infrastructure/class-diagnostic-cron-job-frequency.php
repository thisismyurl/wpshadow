<?php
/**
 * Diagnostic: Cron Job Frequency Checking
 *
 * Identifies cron jobs running more frequently than every minute.
 * Excessive frequency can cause server resource exhaustion.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Infrastructure
 * @since      1.2601.2200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Cron_Job_Frequency Class
 *
 * Audits WordPress scheduled cron jobs to identify any that are scheduled
 * to run more frequently than once per minute. Excessive frequency can
 * drain server resources and cause performance degradation.
 *
 * Uses WordPress's get_option( 'cron' ) to access the cron schedule
 * without database queries.
 *
 * @since 1.2601.2200
 */
class Diagnostic_Cron_Job_Frequency extends Diagnostic_Base {

	/**
	 * Diagnostic slug/identifier
	 *
	 * @var string
	 */
	protected static $slug = 'cron-job-frequency';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Excessive Cron Job Frequency';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies cron jobs scheduled to run too frequently (more than once per minute)';

	/**
	 * Family grouping
	 *
	 * @var string
	 */
	protected static $family = 'infrastructure';

	/**
	 * Family label
	 *
	 * @var string
	 */
	protected static $family_label = 'Infrastructure';

	/**
	 * Minimum safe interval in seconds (1 minute)
	 *
	 * @var int
	 */
	private const MIN_SAFE_INTERVAL = 60;

	/**
	 * Run the diagnostic check.
	 *
	 * Examines all registered cron jobs and identifies any scheduled
	 * to run more frequently than one minute apart.
	 *
	 * @since  1.2601.2200
	 * @return array|null Finding array if excessive crons found, null otherwise.
	 */
	public static function check() {
		// Get the cron schedule
		$crons = get_option( 'cron' );

		if ( ! is_array( $crons ) || empty( $crons ) ) {
			// No crons scheduled - this is fine
			return null;
		}

		// Find jobs with excessively short intervals
		$excessive_jobs = self::find_excessive_frequency_jobs( $crons );

		if ( empty( $excessive_jobs ) ) {
			// No excessive frequencies found
			return null;
		}

		// We found crons running too frequently
		$job_list = self::format_job_list( $excessive_jobs );
		$count = count( $excessive_jobs );

		$description = sprintf(
			/* translators: 1: number of jobs, 2: job list */
			__( '%1$d cron job(s) are scheduled to run more frequently than every minute: %2$s This can cause server resource exhaustion. Review whether these jobs need to run so frequently, or implement better rate limiting.', 'wpshadow' ),
			$count,
			$job_list
		);

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => $description,
			'severity'    => 'medium',
			'threat_level' => 45,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/infrastructure-cron-frequency',
			'family'      => self::$family,
			'meta'        => array(
				'excessive_job_count' => count( $excessive_jobs ),
				'jobs' => $excessive_jobs,
				'min_safe_interval' => self::MIN_SAFE_INTERVAL,
			),
		);
	}

	/**
	 * Find all cron jobs with excessively high frequency.
	 *
	 * @since  1.2601.2200
	 * @param  array $crons The cron schedule array from get_option( 'cron' ).
	 * @return array Array of excessive frequency jobs.
	 */
	private static function find_excessive_frequency_jobs( $crons ) {
		$excessive = array();

		foreach ( $crons as $timestamp => $cron_tasks ) {
			// Skip non-array entries
			if ( ! is_array( $cron_tasks ) ) {
				continue;
			}

			foreach ( $cron_tasks as $hook => $schedules ) {
				// Skip non-array entries
				if ( ! is_array( $schedules ) ) {
					continue;
				}

				foreach ( $schedules as $schedule_key => $schedule_data ) {
					// Skip non-array entries
					if ( ! is_array( $schedule_data ) ) {
						continue;
					}

					// Get the recurrence
					$recurrence = $schedule_data['schedule'] ?? null;

					if ( ! $recurrence ) {
						continue;
					}

					// Get the interval for this recurrence
					$interval = self::get_interval_for_schedule( $recurrence );

					// Check if interval is too short
					if ( $interval && $interval < self::MIN_SAFE_INTERVAL ) {
						$excessive[] = array(
							'hook'     => $hook,
							'schedule' => $recurrence,
							'interval' => $interval,
							'next_run' => wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp ),
						);
					}
				}
			}
		}

		return $excessive;
	}

	/**
	 * Get the interval in seconds for a cron schedule.
	 *
	 * Looks up the custom schedule or uses standard WordPress schedules.
	 *
	 * @since  1.2601.2200
	 * @param  string $schedule Schedule name/recurrence.
	 * @return int|null Interval in seconds, or null if not found.
	 */
	private static function get_interval_for_schedule( $schedule ) {
		// Standard WordPress schedules
		$wp_schedules = array(
			'hourly'      => HOUR_IN_SECONDS,
			'twicedaily'  => 12 * HOUR_IN_SECONDS,
			'daily'       => DAY_IN_SECONDS,
			'weekly'      => 7 * DAY_IN_SECONDS,
		);

		// Check standard schedules first
		if ( isset( $wp_schedules[ $schedule ] ) ) {
			return $wp_schedules[ $schedule ];
		}

		// Try to get custom schedules via wp_get_schedules()
		if ( function_exists( 'wp_get_schedules' ) ) {
			$schedules = wp_get_schedules();

			if ( isset( $schedules[ $schedule ]['interval'] ) ) {
				return absint( $schedules[ $schedule ]['interval'] );
			}
		}

		// Schedule not found - assume unknown
		return null;
	}

	/**
	 * Format excessive jobs into a readable list.
	 *
	 * @since  1.2601.2200
	 * @param  array $jobs Array of excessive frequency jobs.
	 * @return string Formatted job list.
	 */
	private static function format_job_list( $jobs ) {
		$items = array();

		foreach ( $jobs as $job ) {
			$items[] = sprintf(
				'%s (interval: %ds)',
				esc_html( $job['hook'] ),
				$job['interval']
			);
		}

		return implode( ', ', $items );
	}
}
