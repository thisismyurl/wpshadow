<?php
/**
 * Scheduled Task Execution Health Diagnostic
 *
 * Monitors WordPress cron jobs to ensure regular execution
 * and detect stuck or delayed scheduled tasks.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1045
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Scheduled Task Execution Health Class
 *
 * Validates WordPress cron system is executing tasks on schedule.
 * Detects stuck cron, missed tasks, and loopback issues.
 *
 * @since 1.5029.1045
 */
class Diagnostic_Scheduled_Task_Execution extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'scheduled-task-execution-health';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Scheduled Task Execution Health';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Monitors WordPress cron execution for stuck or delayed tasks';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'workflows';

	/**
	 * Run the diagnostic check.
	 *
	 * Analyzes WordPress cron system health using wp_get_schedules() and _get_cron_array().
	 * Detects stuck tasks, loopback issues, and execution delays.
	 *
	 * @since  1.5029.1045
	 * @return array|null Finding array if cron issues detected, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_scheduled_task_check';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$issues = array();

		// Check if cron is disabled.
		if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
			$issues[] = __( 'WordPress cron is disabled (DISABLE_WP_CRON)', 'wpshadow' );
		}

		// Get cron array using WordPress API (NO $wpdb).
		$cron_array = _get_cron_array();

		if ( empty( $cron_array ) || ! is_array( $cron_array ) ) {
			// No scheduled tasks at all - could be normal or could be an issue.
			set_transient( $cache_key, null, 6 * HOUR_IN_SECONDS );
			return null;
		}

		// Check for overdue tasks (more than 1 hour past scheduled time).
		$current_time = time();
		$overdue_tasks = 0;
		$overdue_details = array();

		foreach ( $cron_array as $timestamp => $cron_jobs ) {
			if ( $timestamp < ( $current_time - HOUR_IN_SECONDS ) ) {
				$overdue_tasks++;
				foreach ( $cron_jobs as $hook => $jobs ) {
					$overdue_details[] = array(
						'hook' => $hook,
						'scheduled' => $timestamp,
						'delay' => $current_time - $timestamp,
					);
				}
			}
		}

		if ( $overdue_tasks > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of overdue tasks */
				__( '%d scheduled tasks are overdue by more than 1 hour', 'wpshadow' ),
				$overdue_tasks
			);
		}

		// Test loopback request capability.
		$loopback_test = self::test_loopback_request();
		if ( is_wp_error( $loopback_test ) ) {
			$issues[] = sprintf(
				/* translators: %s: error message */
				__( 'Loopback request failed: %s', 'wpshadow' ),
				$loopback_test->get_error_message()
			);
		}

		// Check cron spawn timing.
		$doing_cron = get_transient( 'doing_cron' );
		if ( $doing_cron && ( $current_time - (int) $doing_cron > 600 ) ) {
			$issues[] = __( 'Cron spawn appears stuck (running for >10 minutes)', 'wpshadow' );
		}

		// If issues found, flag it.
		if ( ! empty( $issues ) ) {
			$threat_level = 50;
			if ( count( $issues ) >= 2 ) {
				$threat_level = 65;
			}
			if ( count( $issues ) >= 3 ) {
				$threat_level = 75;
			}

			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					__( 'WordPress scheduled tasks have %d health issues. Automated tasks may not be running.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => $threat_level > 60 ? 'high' : 'medium',
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/workflows-scheduled-tasks',
				'data'         => array(
					'issues'         => $issues,
					'overdue_count'  => $overdue_tasks,
					'overdue_tasks'  => array_slice( $overdue_details, 0, 10 ),
					'total_tasks'    => count( $cron_array ),
					'doing_cron'     => $doing_cron,
				),
			);

			set_transient( $cache_key, $result, 1 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 6 * HOUR_IN_SECONDS );
		return null;
	}

	/**
	 * Test if loopback requests work.
	 *
	 * @since  1.5029.1045
	 * @return true|\WP_Error True on success, WP_Error on failure.
	 */
	private static function test_loopback_request() {
		$url = home_url( '/' );
		$response = wp_remote_get( $url, array(
			'timeout' => 10,
			'sslverify' => false,
		) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( $response_code >= 400 ) {
			return new \WP_Error( 'loopback_failed', sprintf( 'HTTP %d', $response_code ) );
		}

		return true;
	}
}
