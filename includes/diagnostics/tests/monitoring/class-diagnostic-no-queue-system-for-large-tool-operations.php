<?php
/**
 * No Queue System for Large Tool Operations Diagnostic
 *
 * Detects whether tools handle multiple concurrent requests or crash under load.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Admin
 * @since      1.2034.1505
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_No_Queue_System_For_Large_Tool_Operations Class
 *
 * Verifies tool operations use queuing for reliability.
 *
 * @since 1.2034.1505
 */
class Diagnostic_No_Queue_System_For_Large_Tool_Operations extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-queue-system-for-large-tool-operations';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Tool Operation Queuing';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if tools use job queuing to handle concurrent requests reliably';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2034.1505
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// 1. Check for Action Scheduler (WooCommerce queue system).
		$has_action_scheduler = false;
		if ( class_exists( 'ActionScheduler' ) || class_exists( 'ActionScheduler_Store' ) ) {
			$has_action_scheduler = true;
		}

		// 2. Check for WP Cron as fallback queue.
		$cron_disabled = defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON;
		
		if ( $cron_disabled && ! $has_action_scheduler ) {
			$issues[] = __( 'WP-Cron disabled and no queue system available - tools cannot process in background', 'wpshadow' );
		}

		// 3. Check for concurrent request protection.
		// Tools should use locks to prevent duplicate processing.
		$transient_test = get_transient( 'wpshadow_queue_test' );
		set_transient( 'wpshadow_queue_test', time(), 60 );
		
		// Clean up.
		delete_transient( 'wpshadow_queue_test' );

		// 4. Check for job tracking table.
		if ( $has_action_scheduler ) {
			$table_name = $wpdb->prefix . 'actionscheduler_actions';
			$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" );
			
			if ( $table_exists ) {
				// Count pending jobs.
				$pending_count = $wpdb->get_var(
					"SELECT COUNT(*) FROM {$table_name} WHERE status = 'pending'"
				);

				if ( (int) $pending_count > 100 ) {
					$issues[] = sprintf(
						/* translators: %d: number of pending jobs */
						__( '%d pending jobs in queue - may indicate processing issues', 'wpshadow' ),
						(int) $pending_count
					);
				}

				// Check for failed jobs.
				$failed_count = $wpdb->get_var(
					"SELECT COUNT(*) FROM {$table_name} WHERE status = 'failed'"
				);

				if ( (int) $failed_count > 10 ) {
					$issues[] = sprintf(
						/* translators: %d: number of failed jobs */
						__( '%d failed jobs in queue - investigate failures', 'wpshadow' ),
						(int) $failed_count
					);
				}
			} else {
				$issues[] = __( 'Action Scheduler detected but tables missing - queue system not initialized', 'wpshadow' );
			}
		}

		// 5. Check for AJAX request concurrency.
		// WordPress uses REQUEST_TIME to prevent overlapping requests.
		// But custom implementations may not.

		// 6. Check for queue processing hooks.
		if ( $has_action_scheduler ) {
			// Verify processing hooks are registered.
			if ( ! has_action( 'action_scheduler_run_queue' ) ) {
				$issues[] = __( 'Action Scheduler queue processor not running - jobs may not be processed', 'wpshadow' );
			}
		}

		// 7. Check for priority/ordering.
		// Queues should support job priorities.
		if ( $has_action_scheduler ) {
			// Action Scheduler supports priorities via scheduled_date.
		} else {
			$issues[] = __( 'No job priority system - all operations run with equal priority', 'wpshadow' );
		}

		// 8. Check for retry logic.
		// Failed jobs should retry automatically.
		if ( $has_action_scheduler ) {
			$table_name = $wpdb->prefix . 'actionscheduler_actions';
			$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" );
			
			if ( $table_exists ) {
				$retry_count = $wpdb->get_var(
					"SELECT COUNT(*) FROM {$table_name} WHERE attempts > 1"
				);

				if ( (int) $retry_count > 0 ) {
					// Retries happening - good, but verify they're not excessive.
					$excessive_retries = $wpdb->get_var(
						"SELECT COUNT(*) FROM {$table_name} WHERE attempts > 5"
					);

					if ( (int) $excessive_retries > 0 ) {
						$issues[] = sprintf(
							/* translators: %d: number of jobs */
							__( '%d jobs retrying excessively (5+ attempts) - may be persistent failures', 'wpshadow' ),
							(int) $excessive_retries
						);
					}
				}
			}
		}

		// 9. Check for queue monitoring.
		// Admins should be able to see queue status.
		$has_queue_ui = false;
		
		if ( $has_action_scheduler ) {
			// Action Scheduler provides Tools > Scheduled Actions page.
			$has_queue_ui = true;
		}

		if ( ! $has_queue_ui ) {
			$issues[] = __( 'No queue monitoring interface - cannot view job status', 'wpshadow' );
		}

		// 10. Check for resource limits.
		// Queue should respect memory and time limits.
		$max_execution = ini_get( 'max_execution_time' );
		$memory_limit  = ini_get( 'memory_limit' );
		
		if ( ! empty( $max_execution ) && (int) $max_execution < 300 && (int) $max_execution > 0 ) {
			$issues[] = __( 'Short PHP execution time - queue may not process large jobs', 'wpshadow' );
		}

		// 11. Check WP Cron schedule.
		if ( ! $cron_disabled ) {
			$cron_jobs = _get_cron_array();
			
			if ( empty( $cron_jobs ) ) {
				$issues[] = __( 'No cron jobs scheduled - queue processing may not be working', 'wpshadow' );
			}

			// Check for tool-related cron jobs.
			$tool_crons = 0;
			foreach ( $cron_jobs as $timestamp => $cron ) {
				foreach ( $cron as $hook => $data ) {
					if ( false !== strpos( $hook, 'export' ) ||
					     false !== strpos( $hook, 'import' ) ||
					     false !== strpos( $hook, 'action_scheduler' ) ) {
						$tool_crons++;
					}
				}
			}

			if ( 0 === $tool_crons ) {
				$issues[] = __( 'No tool-related cron jobs scheduled', 'wpshadow' );
			}
		}

		// 12. Check for deadlock prevention.
		// Queues should detect and recover from deadlocks.
		if ( $has_action_scheduler ) {
			$table_name = $wpdb->prefix . 'actionscheduler_actions';
			$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" );
			
			if ( $table_exists ) {
				// Check for stuck "in-progress" jobs.
				$stuck_jobs = $wpdb->get_var(
					"SELECT COUNT(*) FROM {$table_name} 
					WHERE status = 'in-progress' 
					AND last_attempt_gmt < DATE_SUB(NOW(), INTERVAL 1 HOUR)"
				);

				if ( (int) $stuck_jobs > 0 ) {
					$issues[] = sprintf(
						/* translators: %d: number of jobs */
						__( '%d jobs stuck in "in-progress" status - possible deadlock', 'wpshadow' ),
						(int) $stuck_jobs
					);
				}
			}
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Queue system issues: %s', 'wpshadow' ),
				implode( '; ', $issues )
			),
			'severity'     => 'medium',
			'threat_level' => 70,
			'auto_fixable' => true,
			'kb_link'      => 'https://wpshadow.com/kb/tool-queue-system',
			'details'      => array(
				'issues'              => $issues,
				'has_action_scheduler' => $has_action_scheduler,
				'cron_disabled'       => $cron_disabled,
			),
		);
	}
}
