<?php
/**
 * Tool Database Lock Conflicts
 *
 * Checks for database lock conflict handling in tool operations.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tools
 * @since      1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Tool_Database_Lock_Conflicts Class
 *
 * Validates database lock handling in tool operations.
 *
 * @since 1.6030.2148
 */
class Diagnostic_Tool_Database_Lock_Conflicts extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'tool-database-lock-conflicts';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Tool Database Lock Handling';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates database lock conflict handling in tool operations';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'tools';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests lock conflict mechanisms.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// 1. Check for lock detection
		if ( ! self::detects_locks() ) {
			$issues[] = __( 'No detection of database locks', 'wpshadow' );
		}

		// 2. Check for lock wait handling
		if ( ! self::handles_lock_waits() ) {
			$issues[] = __( 'No handling of lock wait timeouts', 'wpshadow' );
		}

		// 3. Check for deadlock recovery
		if ( ! self::recovers_from_deadlocks() ) {
			$issues[] = __( 'No recovery mechanism for deadlocks', 'wpshadow' );
		}

		// 4. Check for lock minimization
		if ( ! self::minimizes_lock_duration() ) {
			$issues[] = __( 'Long locks not minimized', 'wpshadow' );
		}

		// 5. Check for lock conflict reporting
		if ( ! self::reports_lock_conflicts() ) {
			$issues[] = __( 'Lock conflicts not reported or logged', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of lock handling issues */
					__( '%d lock handling issues found', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 60,
				'auto_fixable' => true,
				'details'      => $issues,
				'kb_link'      => 'https://wpshadow.com/kb/tool-database-locks',
				'recommendations' => array(
					__( 'Implement lock detection for database operations', 'wpshadow' ),
					__( 'Handle lock wait timeouts gracefully', 'wpshadow' ),
					__( 'Implement deadlock recovery mechanisms', 'wpshadow' ),
					__( 'Minimize lock duration on database', 'wpshadow' ),
					__( 'Log and report lock conflicts for analysis', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Check for lock detection.
	 *
	 * @since  1.6030.2148
	 * @return bool True if locks detected.
	 */
	private static function detects_locks() {
		// Check for lock detection filter
		if ( has_filter( 'wpshadow_detect_database_locks' ) ) {
			return true;
		}

		// Check for SHOW PROCESSLIST analysis
		if ( has_filter( 'wpshadow_analyze_processlist' ) ) {
			return true;
		}

		// Check for lock status option
		$lock_info = get_option( 'wpshadow_database_lock_info' );
		if ( ! empty( $lock_info ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check for lock wait handling.
	 *
	 * @since  1.6030.2148
	 * @return bool True if wait handled.
	 */
	private static function handles_lock_waits() {
		// Check for timeout handling
		if ( has_filter( 'wpshadow_handle_lock_timeout' ) ) {
			return true;
		}

		// Check for retry mechanism
		if ( has_filter( 'wpshadow_retry_on_lock' ) ) {
			return true;
		}

		// Check for innodb_lock_wait_timeout setting
		global $wpdb;

		$lock_wait = $wpdb->get_var( "SELECT @@innodb_lock_wait_timeout" );
		if ( $lock_wait && intval( $lock_wait ) > 0 ) {
			return true;
		}

		return false;
	}

	/**
	 * Check for deadlock recovery.
	 *
	 * @since  1.6030.2148
	 * @return bool True if recovery implemented.
	 */
	private static function recovers_from_deadlocks() {
		// Check for deadlock detection
		if ( has_filter( 'wpshadow_detect_deadlock' ) ) {
			return true;
		}

		// Check for recovery action
		if ( has_action( 'wpshadow_recover_from_deadlock' ) ) {
			return true;
		}

		// Check for transaction rollback
		if ( has_filter( 'wpshadow_rollback_on_deadlock' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check for lock minimization.
	 *
	 * @since  1.6030.2148
	 * @return bool True if locks minimized.
	 */
	private static function minimizes_lock_duration() {
		// Check for row-level locking
		if ( has_filter( 'wpshadow_use_row_locks' ) ) {
			return true;
		}

		// Check for query optimization
		if ( has_filter( 'wpshadow_optimize_lock_scope' ) ) {
			return true;
		}

		// Check for batching to reduce lock time
		if ( has_filter( 'wpshadow_batch_operations_for_locks' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check for lock conflict reporting.
	 *
	 * @since  1.6030.2148
	 * @return bool True if conflicts reported.
	 */
	private static function reports_lock_conflicts() {
		// Check for lock conflict logging
		if ( has_filter( 'wpshadow_log_lock_conflicts' ) ) {
			return true;
		}

		// Check for admin notification
		if ( has_action( 'wpshadow_notify_lock_conflict' ) ) {
			return true;
		}

		return false;
	}
}
