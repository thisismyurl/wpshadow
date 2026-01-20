<?php
/**
 * Snooze and Dismissal Manager for Guardian System
 *
 * Handles snoozed and permanently dismissed issues.
 * Extends repository with dismissal tracking and snooze functionality.
 *
 * @package WPShadow
 * @subpackage Guardian\Managers
 * @since 1.2.6
 */

declare( strict_types=1 );

namespace WPShadow\Guardian;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Snooze/Dismissal Manager Class
 *
 * Manages issue snoozed and dismissed states with time-based expiration.
 * Persists to wp_options (site-specific) or wp_sitemeta (network-wide).
 *
 * @since 1.2.6
 */
class WPSHADOW_Snooze_Manager {

	/**
	 * Option key for snoozed issues (site option)
	 *
	 * @var string
	 */
	private const SNOOZE_KEY = 'wpshadow_snoozed_issues';

	/**
	 * Option key for permanently dismissed issues (site option)
	 *
	 * @var string
	 */
	private const DISMISS_KEY = 'wpshadow_dismissed_issues';

	/**
	 * Option key for dismissal history (site option)
	 *
	 * @var string
	 */
	private const HISTORY_KEY = 'wpshadow_dismissal_history';

	/**
	 * Snooze duration presets (in seconds)
	 *
	 * @var array
	 */
	private const SNOOZE_PRESETS = array(
		24   => 86400,      // 24 hours
		48   => 172800,     // 48 hours
		72   => 259200,     // 72 hours
		'week' => 604800,   // 7 days
		'month' => 2592000, // 30 days
	);

	/**
	 * Initialize snooze manager
	 *
	 * @return void
	 * @since 1.2.6
	 */
	public static function init(): void {
		// AJAX handlers
		add_action( 'wp_ajax_wpshadow_snooze_issue', array( __CLASS__, 'ajax_snooze_issue' ) );
		add_action( 'wp_ajax_wpshadow_dismiss_issue_permanent', array( __CLASS__, 'ajax_dismiss_issue_permanent' ) );
		add_action( 'wp_ajax_wpshadow_restore_issue', array( __CLASS__, 'ajax_restore_issue' ) );
		add_action( 'wp_ajax_wpshadow_get_dismissal_history', array( __CLASS__, 'ajax_get_dismissal_history' ) );

		// Cleanup expired snoozes
		if ( ! wp_next_scheduled( 'wpshadow_cleanup_snoozes' ) ) {
			wp_schedule_event( time(), 'hourly', 'wpshadow_cleanup_snoozes' );
		}
		add_action( 'wpshadow_cleanup_snoozes', array( __CLASS__, 'cleanup_expired_snoozes' ) );
	}

	/**
	 * Snooze an issue for a specified duration
	 *
	 * @param string $issue_id The issue ID to snooze
	 * @param int|string $duration Duration key (24, 48, 72, 'week', 'month') or seconds
	 * @param string $reason Optional reason for snoozing
	 * @return bool True on success, false on failure
	 * @since 1.2.6
	 */
	public static function snooze_issue( string $issue_id, $duration, string $reason = '' ): bool {
		if ( empty( $issue_id ) ) {
			return false;
		}

		// Convert duration to seconds
		$seconds = self::get_snooze_seconds( $duration );
		if ( $seconds <= 0 ) {
			return false;
		}

		// Get current snoozed issues
		$snoozed = self::get_snoozed_issues();

		// Add new snooze
		$expiration_time = time() + $seconds;
		$snoozed[ $issue_id ] = array(
			'expiration' => $expiration_time,
			'duration_label' => self::get_snooze_label( $duration ),
			'snoozed_at' => time(),
			'snoozed_by' => get_current_user_id(),
			'reason' => sanitize_text_field( $reason ),
		);

		// Save
		return update_option( self::SNOOZE_KEY, $snoozed );
	}

	/**
	 * Permanently dismiss an issue
	 *
	 * @param string $issue_id The issue ID to dismiss
	 * @param string $reason Optional reason for dismissal
	 * @return bool True on success, false on failure
	 * @since 1.2.6
	 */
	public static function dismiss_issue( string $issue_id, string $reason = '' ): bool {
		if ( empty( $issue_id ) ) {
			return false;
		}

		// Get current dismissed issues
		$dismissed = self::get_dismissed_issues();

		// Add dismissal record
		$dismissed[ $issue_id ] = array(
			'dismissed_at' => time(),
			'dismissed_by' => get_current_user_id(),
			'reason' => sanitize_text_field( $reason ),
		);

		// Save dismissal
		$saved = update_option( self::DISMISS_KEY, $dismissed );

		// Log to history
		if ( $saved ) {
			self::add_history_entry( $issue_id, 'permanent_dismiss', $reason );
		}

		return $saved;
	}

	/**
	 * Restore a dismissed issue (only admins can do this)
	 *
	 * @param string $issue_id The issue ID to restore
	 * @param string $reason Optional reason for restoration
	 * @return bool True on success, false on failure
	 * @since 1.2.6
	 */
	public static function restore_issue( string $issue_id, string $reason = '' ): bool {
		if ( empty( $issue_id ) || ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		// Get current dismissed issues
		$dismissed = self::get_dismissed_issues();

		// Check if issue is dismissed
		if ( ! isset( $dismissed[ $issue_id ] ) ) {
			return false;
		}

		// Remove from dismissed
		unset( $dismissed[ $issue_id ] );

		// Save
		$saved = update_option( self::DISMISS_KEY, $dismissed );

		// Log to history
		if ( $saved ) {
			self::add_history_entry( $issue_id, 'restored', $reason );
		}

		return $saved;
	}

	/**
	 * Check if an issue is currently snoozed
	 *
	 * @param string $issue_id The issue ID to check
	 * @return bool True if snoozed and not expired, false otherwise
	 * @since 1.2.6
	 */
	public static function is_snoozed( string $issue_id ): bool {
		$snoozed = self::get_snoozed_issues();

		if ( ! isset( $snoozed[ $issue_id ] ) ) {
			return false;
		}

		// Check if snooze has expired
		$expiration = $snoozed[ $issue_id ]['expiration'] ?? 0;
		if ( $expiration <= time() ) {
			// Snooze expired, remove it
			unset( $snoozed[ $issue_id ] );
			update_option( self::SNOOZE_KEY, $snoozed );
			return false;
		}

		return true;
	}

	/**
	 * Check if an issue is permanently dismissed
	 *
	 * @param string $issue_id The issue ID to check
	 * @return bool True if dismissed, false otherwise
	 * @since 1.2.6
	 */
	public static function is_dismissed( string $issue_id ): bool {
		$dismissed = self::get_dismissed_issues();
		return isset( $dismissed[ $issue_id ] );
	}

	/**
	 * Get snooze info for an issue
	 *
	 * @param string $issue_id The issue ID
	 * @return array|null Array with expiration, duration_label, etc., or null if not snoozed
	 * @since 1.2.6
	 */
	public static function get_snooze_info( string $issue_id ): ?array {
		$snoozed = self::get_snoozed_issues();

		if ( ! isset( $snoozed[ $issue_id ] ) ) {
			return null;
		}

		$snooze = $snoozed[ $issue_id ];

		// Check expiration
		if ( $snooze['expiration'] <= time() ) {
			unset( $snoozed[ $issue_id ] );
			update_option( self::SNOOZE_KEY, $snoozed );
			return null;
		}

		return $snooze;
	}

	/**
	 * Get all snoozed issues
	 *
	 * @return array Array of snoozed issue IDs with snooze details
	 * @since 1.2.6
	 */
	public static function get_snoozed_issues(): array {
		$snoozed = get_option( self::SNOOZE_KEY, array() );

		if ( ! is_array( $snoozed ) ) {
			$snoozed = array();
		}

		return $snoozed;
	}

	/**
	 * Get all dismissed issues
	 *
	 * @return array Array of dismissed issue IDs with dismissal details
	 * @since 1.2.6
	 */
	public static function get_dismissed_issues(): array {
		$dismissed = get_option( self::DISMISS_KEY, array() );

		if ( ! is_array( $dismissed ) ) {
			$dismissed = array();
		}

		return $dismissed;
	}

	/**
	 * Get dismissal history
	 *
	 * @param int $limit Maximum number of entries to return
	 * @return array Array of history entries sorted by date (newest first)
	 * @since 1.2.6
	 */
	public static function get_dismissal_history( int $limit = 50 ): array {
		$history = get_option( self::HISTORY_KEY, array() );

		if ( ! is_array( $history ) ) {
			$history = array();
		}

		// Sort by date descending (newest first)
		usort( $history, function ( $a, $b ) {
			return ( $b['timestamp'] ?? 0 ) <=> ( $a['timestamp'] ?? 0 );
		} );

		// Limit results
		return array_slice( $history, 0, $limit );
	}

	/**
	 * Filter issues array to exclude snoozed and dismissed
	 *
	 * @param array $issues Array of issues to filter
	 * @return array Filtered issues (without snoozed/dismissed)
	 * @since 1.2.6
	 */
	public static function filter_issues( array $issues ): array {
		$snoozed = self::get_snoozed_issues();
		$dismissed = self::get_dismissed_issues();

		return array_filter( $issues, function ( $issue ) use ( $snoozed, $dismissed ) {
			$issue_id = $issue['id'] ?? $issue['issue_id'] ?? '';
			return ! isset( $snoozed[ $issue_id ] ) && ! isset( $dismissed[ $issue_id ] );
		} );
	}

	/**
	 * Get snooze time remaining for an issue (in seconds)
	 *
	 * @param string $issue_id The issue ID
	 * @return int Seconds remaining, or 0 if not snoozed
	 * @since 1.2.6
	 */
	public static function get_snooze_remaining( string $issue_id ): int {
		$snooze_info = self::get_snooze_info( $issue_id );

		if ( null === $snooze_info ) {
			return 0;
		}

		$remaining = $snooze_info['expiration'] - time();
		return max( 0, $remaining );
	}

	/**
	 * Convert snooze duration to human-readable format
	 *
	 * @param string $issue_id The issue ID
	 * @return string Human-readable time remaining (e.g., "2 hours remaining")
	 * @since 1.2.6
	 */
	public static function get_snooze_display_text( string $issue_id ): string {
		$remaining = self::get_snooze_remaining( $issue_id );

		if ( $remaining <= 0 ) {
			return '';
		}

		if ( $remaining < 60 ) {
			return sprintf( '%d %s', $remaining, _n( 'second', 'seconds', $remaining, 'wpshadow' ) );
		}

		if ( $remaining < 3600 ) {
			$minutes = (int) ceil( $remaining / 60 );
			return sprintf( '%d %s', $minutes, _n( 'minute', 'minutes', $minutes, 'wpshadow' ) );
		}

		if ( $remaining < 86400 ) {
			$hours = (int) ceil( $remaining / 3600 );
			return sprintf( '%d %s', $hours, _n( 'hour', 'hours', $hours, 'wpshadow' ) );
		}

		$days = (int) ceil( $remaining / 86400 );
		return sprintf( '%d %s', $days, _n( 'day', 'days', $days, 'wpshadow' ) );
	}

	/**
	 * AJAX handler: Snooze an issue
	 *
	 * @return void
	 * @since 1.2.6
	 */
	public static function ajax_snooze_issue(): void {
		check_ajax_referer( 'wpshadow-reports', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
		}

		$issue_id = isset( $_POST['issue_id'] ) ? sanitize_text_field( wp_unslash( $_POST['issue_id'] ) ) : '';
		$duration = isset( $_POST['duration'] ) ? sanitize_text_field( wp_unslash( $_POST['duration'] ) ) : '24';
		$reason = isset( $_POST['reason'] ) ? sanitize_text_field( wp_unslash( $_POST['reason'] ) ) : '';

		if ( empty( $issue_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Issue ID required', 'wpshadow' ) ) );
		}

		$result = self::snooze_issue( $issue_id, $duration, $reason );

		if ( ! $result ) {
			wp_send_json_error( array( 'message' => __( 'Failed to snooze issue', 'wpshadow' ) ) );
		}

		// Get snooze info for response
		$snooze_info = self::get_snooze_info( $issue_id );

		wp_send_json_success( array(
			'message' => __( 'Issue snoozed successfully', 'wpshadow' ),
			'snooze_info' => $snooze_info,
			'duration_label' => self::get_snooze_label( $duration ),
			'snooze_remaining' => self::get_snooze_display_text( $issue_id ),
		) );
	}

	/**
	 * AJAX handler: Permanently dismiss an issue
	 *
	 * @return void
	 * @since 1.2.6
	 */
	public static function ajax_dismiss_issue_permanent(): void {
		check_ajax_referer( 'wpshadow-reports', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
		}

		$issue_id = isset( $_POST['issue_id'] ) ? sanitize_text_field( wp_unslash( $_POST['issue_id'] ) ) : '';
		$reason = isset( $_POST['reason'] ) ? sanitize_text_field( wp_unslash( $_POST['reason'] ) ) : '';

		if ( empty( $issue_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Issue ID required', 'wpshadow' ) ) );
		}

		$result = self::dismiss_issue( $issue_id, $reason );

		if ( ! $result ) {
			wp_send_json_error( array( 'message' => __( 'Failed to dismiss issue', 'wpshadow' ) ) );
		}

		wp_send_json_success( array(
			'message' => __( 'Issue dismissed permanently', 'wpshadow' ),
		) );
	}

	/**
	 * AJAX handler: Restore a dismissed issue
	 *
	 * @return void
	 * @since 1.2.6
	 */
	public static function ajax_restore_issue(): void {
		check_ajax_referer( 'wpshadow-reports', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
		}

		$issue_id = isset( $_POST['issue_id'] ) ? sanitize_text_field( wp_unslash( $_POST['issue_id'] ) ) : '';
		$reason = isset( $_POST['reason'] ) ? sanitize_text_field( wp_unslash( $_POST['reason'] ) ) : '';

		if ( empty( $issue_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Issue ID required', 'wpshadow' ) ) );
		}

		$result = self::restore_issue( $issue_id, $reason );

		if ( ! $result ) {
			wp_send_json_error( array( 'message' => __( 'Failed to restore issue', 'wpshadow' ) ) );
		}

		wp_send_json_success( array(
			'message' => __( 'Issue restored', 'wpshadow' ),
		) );
	}

	/**
	 * AJAX handler: Get dismissal history
	 *
	 * @return void
	 * @since 1.2.6
	 */
	public static function ajax_get_dismissal_history(): void {
		check_ajax_referer( 'wpshadow-reports', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
		}

		$limit = isset( $_POST['limit'] ) ? (int) $_POST['limit'] : 50;
		$history = self::get_dismissal_history( $limit );

		// Format history entries
		$formatted_history = array_map( function ( $entry ) {
			return array(
				'issue_id' => $entry['issue_id'] ?? '',
				'action' => $entry['action'] ?? 'unknown',
				'timestamp' => $entry['timestamp'] ?? 0,
				'user_id' => $entry['user_id'] ?? 0,
				'user_name' => get_userdata( $entry['user_id'] ?? 0 )->display_name ?? __( 'Unknown', 'wpshadow' ),
				'reason' => $entry['reason'] ?? '',
				'time_ago' => self::get_time_ago( $entry['timestamp'] ?? 0 ),
			);
		}, $history );

		wp_send_json_success( array(
			'history' => $formatted_history,
			'count' => count( $history ),
		) );
	}

	/**
	 * Clean up expired snoozes (hourly cron job)
	 *
	 * @return void
	 * @since 1.2.6
	 */
	public static function cleanup_expired_snoozes(): void {
		$snoozed = self::get_snoozed_issues();
		$current_time = time();
		$modified = false;

		foreach ( $snoozed as $issue_id => $snooze_info ) {
			$expiration = $snooze_info['expiration'] ?? 0;
			if ( $expiration <= $current_time ) {
				unset( $snoozed[ $issue_id ] );
				$modified = true;

				// Log to history
				self::add_history_entry( $issue_id, 'snooze_expired' );
			}
		}

		if ( $modified ) {
			update_option( self::SNOOZE_KEY, $snoozed );
		}
	}

	/**
	 * Clear all snoozes and dismissals (admin only)
	 *
	 * @return bool True on success
	 * @since 1.2.6
	 */
	public static function clear_all(): bool {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		delete_option( self::SNOOZE_KEY );
		delete_option( self::DISMISS_KEY );

		self::add_history_entry( '', 'all_cleared', 'Admin cleared all snoozes and dismissals' );

		return true;
	}

	/**
	 * Convert duration to seconds
	 *
	 * @param int|string $duration Duration preset key or seconds
	 * @return int Seconds (0 if invalid)
	 * @since 1.2.6
	 */
	private static function get_snooze_seconds( $duration ): int {
		if ( is_numeric( $duration ) ) {
			$seconds = (int) $duration;
			return $seconds > 0 ? $seconds : 0;
		}

		return self::SNOOZE_PRESETS[ (string) $duration ] ?? 0;
	}

	/**
	 * Get human-readable label for snooze duration
	 *
	 * @param int|string $duration Duration preset key or seconds
	 * @return string Human-readable label (e.g., "24 hours")
	 * @since 1.2.6
	 */
	private static function get_snooze_label( $duration ): string {
		$labels = array(
			24    => __( '24 hours', 'wpshadow' ),
			48    => __( '48 hours', 'wpshadow' ),
			72    => __( '72 hours', 'wpshadow' ),
			'week' => __( '1 week', 'wpshadow' ),
			'month' => __( '1 month', 'wpshadow' ),
		);

		if ( isset( $labels[ $duration ] ) ) {
			return $labels[ $duration ];
		}

		if ( is_numeric( $duration ) ) {
			return sprintf( __( '%d seconds', 'wpshadow' ), (int) $duration );
		}

		return __( 'Custom', 'wpshadow' );
	}

	/**
	 * Add entry to dismissal history
	 *
	 * @param string $issue_id The issue ID (empty for system-wide events)
	 * @param string $action The action performed
	 * @param string $reason Optional reason
	 * @return void
	 * @since 1.2.6
	 */
	private static function add_history_entry( string $issue_id, string $action, string $reason = '' ): void {
		$history = get_option( self::HISTORY_KEY, array() );

		if ( ! is_array( $history ) ) {
			$history = array();
		}

		// Add new entry
		$history[] = array(
			'issue_id' => $issue_id,
			'action' => $action,
			'timestamp' => time(),
			'user_id' => get_current_user_id(),
			'reason' => sanitize_text_field( $reason ),
		);

		// Keep only last 1000 entries
		if ( count( $history ) > 1000 ) {
			$history = array_slice( $history, -1000 );
		}

		update_option( self::HISTORY_KEY, $history );
	}

	/**
	 * Convert timestamp to "time ago" format
	 *
	 * @param int $timestamp Unix timestamp
	 * @return string Human-readable time ago (e.g., "2 hours ago")
	 * @since 1.2.6
	 */
	private static function get_time_ago( int $timestamp ): string {
		$seconds = time() - $timestamp;

		if ( $seconds < 60 ) {
			return __( 'Just now', 'wpshadow' );
		}

		if ( $seconds < 3600 ) {
			$minutes = (int) ( $seconds / 60 );
			return sprintf( _n( '%d minute ago', '%d minutes ago', $minutes, 'wpshadow' ), $minutes );
		}

		if ( $seconds < 86400 ) {
			$hours = (int) ( $seconds / 3600 );
			return sprintf( _n( '%d hour ago', '%d hours ago', $hours, 'wpshadow' ), $hours );
		}

		$days = (int) ( $seconds / 86400 );
		return sprintf( _n( '%d day ago', '%d days ago', $days, 'wpshadow' ), $days );
	}
}
