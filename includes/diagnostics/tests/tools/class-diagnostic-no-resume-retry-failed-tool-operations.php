<?php
/**
 * No Resume or Retry Failed Tool Operations Diagnostic
 *
 * Detects whether failed tool operations can resume from interruption point
 * versus starting over.
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
 * No Resume or Retry Failed Tool Operations Diagnostic Class
 *
 * Checks for resume capability in tool operations.
 *
 * @since 1.2601.2148
 */
class Diagnostic_No_Resume_Retry_Failed_Tool_Operations extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-resume-retry-failed-tool-operations';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Resume or Retry for Failed Tool Operations';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects lack of resume/retry capability for interrupted operations';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'tools';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;
		
		$issues = array();

		// Check for checkpoint/resume options.
		$checkpoint_option = get_option( 'wpshadow_operation_checkpoint' );
		
		if ( false === $checkpoint_option ) {
			$issues[] = __( 'No checkpoint system configured (operations cannot resume)', 'wpshadow' );
		}

		// Check for operation state tracking.
		$operation_state = get_option( 'wpshadow_operation_state' );
		
		if ( false === $operation_state ) {
			$issues[] = __( 'No operation state tracking (resume impossible)', 'wpshadow' );
		}

		// Check for partial import cleanup.
		$partial_imports = $wpdb->get_var(
			"SELECT COUNT(*) 
			FROM {$wpdb->options} 
			WHERE option_name LIKE '_transient_import_%' 
			AND option_value LIKE '%\"status\":\"failed\"%'"
		);

		if ( $partial_imports > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of failed imports */
				__( '%d failed import transients (no resume capability)', 'wpshadow' ),
				$partial_imports
			);
		}

		// Check for retry mechanism.
		$has_retry_filter = has_filter( 'wpshadow_retry_failed_operation' );
		
		if ( ! $has_retry_filter ) {
			$issues[] = __( 'No retry filter registered (failed operations cannot retry)', 'wpshadow' );
		}

		// Check for background processing queue.
		$background_queue = get_option( 'wpshadow_background_queue' );
		
		if ( false === $background_queue ) {
			$issues[] = __( 'No background queue (long operations blocking)', 'wpshadow' );
		}

		// Check max_execution_time.
		$max_execution = (int) ini_get( 'max_execution_time' );
		
		if ( $max_execution > 0 && $max_execution < 300 ) {
			$issues[] = sprintf(
				/* translators: %d: execution time */
				__( 'max_execution_time %ds too low (operations will timeout)', 'wpshadow' ),
				$max_execution
			);
		}

		// Check for cron job handling retries.
		$cron_jobs = _get_cron_array();
		$has_retry_cron = false;
		
		if ( is_array( $cron_jobs ) ) {
			foreach ( $cron_jobs as $timestamp => $cron ) {
				foreach ( $cron as $hook => $events ) {
					if ( strpos( $hook, 'retry' ) !== false || strpos( $hook, 'resume' ) !== false ) {
						$has_retry_cron = true;
						break 2;
					}
				}
			}
		}

		if ( ! $has_retry_cron ) {
			$issues[] = __( 'No retry/resume cron jobs scheduled', 'wpshadow' );
		}

		// Check for session storage.
		$session_handler = ini_get( 'session.save_handler' );
		
		if ( 'files' === $session_handler ) {
			$issues[] = __( 'File-based sessions (may lose state on server restart)', 'wpshadow' );
		}

		// Check for database transaction support.
		$engine = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ENGINE 
				FROM information_schema.TABLES 
				WHERE TABLE_SCHEMA = %s 
				AND TABLE_NAME = %s",
				DB_NAME,
				$wpdb->posts
			)
		);

		if ( 'MyISAM' === $engine ) {
			$issues[] = __( 'MyISAM tables (no transaction rollback support)', 'wpshadow' );
		}

		// Check for progress persistence.
		$progress_data = get_option( 'wpshadow_operation_progress' );
		
		if ( false === $progress_data ) {
			$issues[] = __( 'No progress tracking (cannot determine resume point)', 'wpshadow' );
		}

		// Check for error recovery actions.
		$recovery_actions = $GLOBALS['wp_filter']['wpshadow_operation_error'] ?? null;
		
		if ( ! $recovery_actions || count( $recovery_actions->callbacks ) === 0 ) {
			$issues[] = __( 'No error recovery hooks registered', 'wpshadow' );
		}

		// Check for partial data cleanup.
		$orphaned_meta = $wpdb->get_var(
			"SELECT COUNT(*) 
			FROM {$wpdb->postmeta} 
			WHERE meta_key LIKE '_wpshadow_temp_%'"
		);

		if ( $orphaned_meta > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of orphaned records */
				__( '%d orphaned temporary meta records (incomplete cleanup)', 'wpshadow' ),
				$orphaned_meta
			);
		}

		// Check for operation lock management.
		$active_locks = $wpdb->get_var(
			"SELECT COUNT(*) 
			FROM {$wpdb->options} 
			WHERE option_name LIKE '_transient_wpshadow_lock_%'"
		);

		if ( $active_locks > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of locks */
				__( '%d active operation locks (may indicate stuck processes)', 'wpshadow' ),
				$active_locks
			);
		}

		// Check for timeout handling.
		$timeout_handler = has_filter( 'wpshadow_operation_timeout' );
		
		if ( ! $timeout_handler ) {
			$issues[] = __( 'No timeout handling (operations cannot recover from timeouts)', 'wpshadow' );
		}

		// Check for automatic retry backoff.
		$retry_config = get_option( 'wpshadow_retry_config' );
		
		if ( false === $retry_config ) {
			$issues[] = __( 'No retry configuration (no exponential backoff)', 'wpshadow' );
		}

		// Check for memory exhaustion handling.
		$memory_limit = wp_convert_hr_to_bytes( ini_get( 'memory_limit' ) );
		
		if ( $memory_limit > 0 && $memory_limit < 134217728 ) {
			$issues[] = sprintf(
				/* translators: %s: memory limit */
				__( 'memory_limit %s too low (operations may exhaust memory)', 'wpshadow' ),
				size_format( $memory_limit )
			);
		}

		// Check for atomic operations support.
		$supports_transactions = $wpdb->get_var( "SELECT @@have_query_cache" );
		
		// Check for WP_CRON reliability.
		if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
			if ( ! $has_retry_cron ) {
				$issues[] = __( 'WP_CRON disabled and no alternative retry mechanism', 'wpshadow' );
			}
		}

		// Check for failed operation history.
		$failed_operations = get_option( 'wpshadow_failed_operations_log' );
		
		if ( false !== $failed_operations && is_array( $failed_operations ) ) {
			$recent_failures = array_filter(
				$failed_operations,
				function( $op ) {
					return isset( $op['timestamp'] ) && $op['timestamp'] > ( time() - 86400 );
				}
			);

			if ( count( $recent_failures ) > 10 ) {
				$issues[] = sprintf(
					/* translators: %d: number of failures */
					__( '%d operations failed in last 24 hours', 'wpshadow' ),
					count( $recent_failures )
				);
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/no-resume-retry-failed-tool-operations',
			);
		}

		return null;
	}
}
