<?php
/**
 * Diagnostic: Cron Schedules Stuck
 *
 * Detects if WordPress cron jobs are stuck or running excessively long (>1 hour).
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Cron_Schedules_Stuck
 *
 * Identifies cron jobs that have been running for an unusually long time,
 * which can indicate stuck processes consuming resources and blocking other scheduled tasks.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Cron_Schedules_Stuck extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'cron-schedules-stuck';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Cron Schedules Stuck';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Detect if WordPress cron jobs are stuck or running excessively long';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks for cron events that have been running longer than 1 hour,
	 * which likely indicates a stuck process.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issues detected, null otherwise.
	 */
	public static function check() {
		$crons = _get_cron_array();
		
		if ( empty( $crons ) || ! is_array( $crons ) ) {
			return null;
		}

		$current_time = time();
		$stuck_crons = array();
		$threshold = 3600; // 1 hour in seconds

		foreach ( $crons as $timestamp => $cron ) {
			if ( ! is_array( $cron ) ) {
				continue;
			}

			// Check if this cron is scheduled to have run
			if ( $timestamp > $current_time ) {
				continue;
			}

			$time_overdue = $current_time - $timestamp;

			// If the cron is more than 1 hour overdue, it's likely stuck
			if ( $time_overdue > $threshold ) {
				foreach ( $cron as $hook => $hook_data ) {
					if ( ! is_array( $hook_data ) ) {
						continue;
					}

					foreach ( $hook_data as $event ) {
						$stuck_crons[] = array(
							'hook' => $hook,
							'scheduled_time' => $timestamp,
							'overdue_by' => $time_overdue,
							'overdue_human' => human_time_diff( $timestamp, $current_time ),
						);
					}
				}
			}
		}

		if ( empty( $stuck_crons ) ) {
			return null;
		}

		$stuck_count = count( $stuck_crons );
		$hooks_list = array_unique( array_column( $stuck_crons, 'hook' ) );
		
		$description = sprintf(
			/* translators: %d: number of stuck cron jobs */
			_n(
				'Found %d stuck cron job that has been pending for over an hour. This can consume resources and prevent other scheduled tasks from running.',
				'Found %d stuck cron jobs that have been pending for over an hour. These can consume resources and prevent other scheduled tasks from running.',
				$stuck_count,
				'wpshadow'
			),
			$stuck_count
		) . ' ' . sprintf(
			/* translators: %s: comma-separated list of cron hook names */
			__( 'Affected hooks: %s', 'wpshadow' ),
			esc_html( implode( ', ', $hooks_list ) )
		);

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => $description,
			'severity'    => 'medium',
			'threat_level' => 50,
			'auto_fixable' => true,
			'kb_link'     => 'https://wpshadow.com/kb/cron-cron-schedules-stuck',
			'meta'        => array(
				'stuck_crons' => $stuck_crons,
				'stuck_count' => $stuck_count,
				'hooks'       => $hooks_list,
			),
		);
	}
}
