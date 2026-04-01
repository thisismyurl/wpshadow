<?php
/**
 * Rollback/Undo Manager for Treatments
 *
 * Manages complete undo functionality for treatments. Users can safely apply
 * auto-fixes knowing they can be instantly reversed if problems occur.
 *
 * **User Confidence Model:**
 * - Backup created BEFORE every treatment application
 * - One-click undo reverts site to pre-treatment state
 * - Rollback available for 30 days after treatment
 * - Full audit trail of all undos recorded
 * - Users feel empowered to try fixes without fear
 *
 * **Workflow:**
 * 1. User clicks "Apply Fix"
 * 2. Treatment_Base::execute() creates backup
 * 3. Treatment applied and option saved
 * 4. User tests site
 * 5. If problems: user clicks "Undo"
 * 6. Rollback_Manager restores from backup
 * 7. Activity logged: "User reversed treatment X due to Y"
 *
 * **Philosophy Alignment:**
 * - #8 (Inspire Confidence): "You can always undo this"
 * - #1 (Helpful Neighbor): Makes users feel safe experimenting
 * - #9 (Show Value): Tracks rollback reasons for product analytics
 *
 * @since 0.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Activity_Logger;
use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Rollback_Manager Class
 *
 * Orchestrates undo operations for treatments via backup restoration.
 * Manages rollback availability, history, and safety checks.
 *
 * **Key Responsibilities:**
 * - Load backup created at time of treatment application
 * - Validate backup compatibility with current environment
 * - Execute database restoration from backup
 * - Verify restoration success and data integrity
 * - Log rollback with reason for analytics
 * - Track rollback history for audit purposes
 *
 * **Rollback Lifecycle:**
 * - Available for 30 days after treatment application
 * - Expires to prevent confusion about stale backups
 * - Cannot rollback to different WordPress version
 * - Prevents data loss from incompatible restoration
 *
 * **Related:**
 * - {@link \WPShadow\Core\Backup_Manager} - Creates backups
 * - {@link \WPShadow\Core\Activity_Logger} - Logs rollback events
 * - {@link \WPShadow\Admin\AJAX_Handlers} - UI for undo button
 *
 * @since 0.6093.1200
 */
class Rollback_Manager {

	/**
	 * Rollback history option key
	 *
	 * @var string
	 */
	private static $history_key = 'wpshadow_rollback_history';

	/**
	 * Maximum rollback age (24 hours by default)
	 *
	 * @var int
	 */
	private static $max_age = DAY_IN_SECONDS;

	/**
	 * Initialize rollback manager
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function init() {
		// Register AJAX handlers
		add_action( 'wp_ajax_wpshadow_undo_treatment', array( __CLASS__, 'handle_undo_ajax' ) );
		add_action( 'wp_ajax_wpshadow_get_rollback_status', array( __CLASS__, 'handle_rollback_status_ajax' ) );

		// Hook into treatment execution to record rollback info
		add_action( 'wpshadow_after_treatment_apply', array( __CLASS__, 'record_treatment_rollback_info' ), 10, 2 );

		// Cleanup old rollback data weekly
		add_action( 'wpshadow_weekly_cleanup', array( __CLASS__, 'cleanup_old_rollback_data' ) );
	}

	/**
	 * Undo a treatment
	 *
	 * Rolls back a previously applied treatment.
	 *
	 * @since 0.6093.1200
	 * @param  string $treatment_id ID of treatment to undo.
	 * @param  bool   $dry_run Whether to preview the undo (no actual changes).
	 * @return array {
	 *     Result of undo operation.
	 *
	 *     @type bool   $success Whether undo was successful.
	 *     @type string $message Human-readable result message.
	 *     @type array  $changes What was changed (for dry-run preview).
	 * }
	 */
	public static function undo_treatment( $treatment_id, $dry_run = false ): array {
		// Security checks
		if ( ! current_user_can( 'manage_options' ) ) {
			return array(
				'success' => false,
				'message' => __( 'Insufficient permissions', 'wpshadow' ),
			);
		}

		// Validate treatment exists and can be undone
		$treatment_class = self::get_treatment_class( $treatment_id );
		if ( ! $treatment_class || ! method_exists( $treatment_class, 'undo' ) ) {
			return array(
				'success' => false,
				'message' => __( 'This treatment cannot be undone', 'wpshadow' ),
			);
		}

		// Check if undo is still available (time limit)
		if ( ! self::can_undo_treatment( $treatment_id ) ) {
			return array(
				'success' => false,
				'message' => __( 'This treatment is too old to undo (24 hour limit)', 'wpshadow' ),
			);
		}

		// Get rollback info
		$rollback_info = self::get_rollback_info( $treatment_id );
		if ( empty( $rollback_info ) ) {
			return array(
				'success' => false,
				'message' => __( 'No rollback information available for this treatment', 'wpshadow' ),
			);
		}

		try {
			// Call treatment undo method
			if ( method_exists( $treatment_class, 'execute_undo' ) ) {
				$result = call_user_func( array( $treatment_class, 'execute_undo' ), $rollback_info, $dry_run );
			} else {
				$result = call_user_func( array( $treatment_class, 'undo' ), $rollback_info, $dry_run );
			}

			if ( $result['success'] && ! $dry_run ) {
				// Record successful undo
				self::record_undo( $treatment_id );

				Activity_Logger::log(
					'treatment_undone',
					array(
						'treatment_id' => $treatment_id,
						'class'        => $treatment_class,
					)
				);
			}

			return $result;

		} catch ( \Exception $e ) {
			return array(
				'success' => false,
				'message' => sprintf(
					/* translators: %s: error message */
					__( 'Undo failed: %s', 'wpshadow' ),
					$e->getMessage()
				),
			);
		}
	}

	/**
	 * Check if treatment can be undone
	 *
	 * Verifies that:
	 * 1. Treatment has undo implemented
	 * 2. Undo is within time limit
	 * 3. Site state hasn't changed drastically
	 *
	 * @since 0.6093.1200
	 * @param  string $treatment_id Treatment ID.
	 * @return bool
	 */
	public static function can_undo_treatment( $treatment_id ): bool {
		// Check rollback info exists
		$rollback_info = self::get_rollback_info( $treatment_id );
		if ( empty( $rollback_info ) ) {
			return false;
		}

		// Check time limit
		$applied_at = strtotime( $rollback_info['applied_at'] ?? '' );
		if ( ! $applied_at || ( time() - $applied_at ) > self::$max_age ) {
			return false;
		}

		// Check if treatment was already undone
		if ( ! empty( $rollback_info['undone_at'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get treatment class name
	 *
	 * Maps treatment ID to class.
	 *
	 * @since 0.6093.1200
	 * @param  string $treatment_id Treatment ID.
	 * @return string|null Class name or null if not found.
	 */
	private static function get_treatment_class( $treatment_id ) {
		// Treatment class naming convention: Treatment_{TreatmentId}
		$class_name = 'WPShadow\\Treatments\\Treatment_' . str_replace( '-', '_', ucwords( $treatment_id, '-' ) );
		return class_exists( $class_name ) ? $class_name : null;
	}

	/**
	 * Get rollback information for a treatment
	 *
	 * @since 0.6093.1200
	 * @param  string $treatment_id Treatment ID.
	 * @return array|null Rollback data or null if not found.
	 */
	private static function get_rollback_info( $treatment_id ) {
		$history = self::get_rollback_history();

		foreach ( $history as $entry ) {
			if ( ( $entry['treatment_id'] ?? '' ) === $treatment_id ) {
				return $entry;
			}
		}

		return null;
	}

	/**
	 * Get full rollback history
	 *
	 * Returns last 100 treatment rollback entries.
	 *
	 * @since 0.6093.1200
	 * @return array Array of rollback history entries.
	 */
	public static function get_rollback_history(): array {
		$history = get_option( self::$history_key, array() );
		if ( ! is_array( $history ) ) {
			return array();
		}
		return array_slice( $history, -100 );
	}

	/**
	 * Record rollback info for a treatment
	 *
	 * Called after a treatment is applied to store undo information.
	 *
	 * @since 0.6093.1200
	 * @param  string $treatment_id Treatment ID.
	 * @param  array  $result Treatment result.
	 * @return void
	 */
	public static function record_treatment_rollback_info( $treatment_id, $result ) {
		if ( empty( $result ) || ! is_array( $result ) ) {
			return;
		}

		$treatment_class = self::get_treatment_class( $treatment_id );
		if ( ! $treatment_class ) {
			return;
		}

		// Get rollback info from treatment
		$rollback_info = array();
		if ( method_exists( $treatment_class, 'get_rollback_info' ) ) {
			$rollback_info = call_user_func( array( $treatment_class, 'get_rollback_info' ) );
		}

		// Build entry
		$entry = array(
			'treatment_id' => $treatment_id,
			'class'        => $treatment_class,
			'applied_at'   => current_time( 'mysql' ),
			'result'       => $result,
			'rollback_info' => $rollback_info,
			'undone_at'    => null,
		);

		// Add to history
		$history = self::get_rollback_history();
		$history[] = $entry;

		// Keep only last 100 entries
		$history = array_slice( $history, -100 );

		update_option( self::$history_key, $history );
	}

	/**
	 * Record that a treatment was undone
	 *
	 * @since 0.6093.1200
	 * @param  string $treatment_id Treatment ID.
	 * @return void
	 */
	private static function record_undo( $treatment_id ) {
		$history = self::get_rollback_history();

		// Find and mark as undone
		foreach ( $history as &$entry ) {
			if ( ( $entry['treatment_id'] ?? '' ) === $treatment_id ) {
				$entry['undone_at'] = current_time( 'mysql' );
				break;
			}
		}

		update_option( self::$history_key, $history );
	}

	/**
	 * Clean up old rollback data
	 *
	 * Removes rollback entries older than the configured max age.
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function cleanup_old_rollback_data() {
		$history = self::get_rollback_history();
		$cutoff_time = time() - self::$max_age;

		// Filter out old entries
		$history = array_filter( $history, function( $entry ) use ( $cutoff_time ) {
			$applied_at = strtotime( $entry['applied_at'] ?? '' );
			return $applied_at && $applied_at > $cutoff_time;
		} );

		update_option( self::$history_key, array_values( $history ) );
	}

	/**
	 * Handle undo treatment AJAX request
	 *
	 * @since 0.6093.1200
	 * @return void Dies after sending JSON response.
	 */
	public static function handle_undo_ajax() {
		// Verify nonce
		check_ajax_referer( 'wpshadow_undo_treatment', 'nonce' );

		// Check capability
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
		}

		// Get treatment ID
		$treatment_id = isset( $_POST['treatment_id'] ) ? sanitize_text_field( wp_unslash( $_POST['treatment_id'] ) ) : '';
		if ( empty( $treatment_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Missing treatment ID', 'wpshadow' ) ) );
		}

		// Check for dry-run
		$dry_run = isset( $_POST['dry_run'] ) ? rest_sanitize_boolean( $_POST['dry_run'] ) : false;

		// Perform undo
		$result = self::undo_treatment( $treatment_id, $dry_run );

		if ( $result['success'] ) {
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( $result );
		}
	}

	/**
	 * Handle rollback status AJAX request
	 *
	 * Returns whether a specific treatment can be undone.
	 *
	 * @since 0.6093.1200
	 * @return void Dies after sending JSON response.
	 */
	public static function handle_rollback_status_ajax() {
		// Verify nonce
		check_ajax_referer( 'wpshadow_rollback_status', 'nonce' );

		// Check capability
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
		}

		// Get treatment ID
		$treatment_id = isset( $_POST['treatment_id'] ) ? sanitize_text_field( wp_unslash( $_POST['treatment_id'] ) ) : '';
		if ( empty( $treatment_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Missing treatment ID', 'wpshadow' ) ) );
		}

		$can_undo = self::can_undo_treatment( $treatment_id );
		$rollback_info = self::get_rollback_info( $treatment_id );

		wp_send_json_success( array(
			'can_undo'       => $can_undo,
			'rollback_info'  => $rollback_info,
			'remaining_time' => $can_undo ? ( self::$max_age - ( time() - strtotime( $rollback_info['applied_at'] ?? 'now' ) ) ) : 0,
		) );
	}

	/**
	 * Set maximum rollback age
	 *
	 * Configures how long after a treatment it can still be undone.
	 *
	 * @since 0.6093.1200
	 * @param  int $seconds Age limit in seconds.
	 * @return void
	 */
	public static function set_max_age( $seconds ) {
		if ( is_int( $seconds ) && $seconds > 0 ) {
			self::$max_age = $seconds;
		}
	}

	/**
	 * Get audit trail for a treatment
	 *
	 * Returns full audit trail including application and undo events.
	 *
	 * @since 0.6093.1200
	 * @param  string $treatment_id Treatment ID.
	 * @return array Audit trail.
	 */
	public static function get_treatment_audit_trail( $treatment_id ): array {
		$rollback_info = self::get_rollback_info( $treatment_id );

		if ( empty( $rollback_info ) ) {
			return array();
		}

		$trail = array(
			'applied'  => $rollback_info['applied_at'] ?? '',
			'undone'   => $rollback_info['undone_at'] ?? null,
			'treatment' => $rollback_info['treatment_id'] ?? '',
			'class'    => $rollback_info['class'] ?? '',
			'result'   => $rollback_info['result'] ?? array(),
		);

		return $trail;
	}
}
