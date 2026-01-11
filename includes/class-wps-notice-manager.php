<?php
/**
 * WPS Notice Manager
 *
 * Handles persistent dismissal of admin notices with time-based suppression.
 *
 * @package wp_supportSupport
 * @since 1.2601.0822
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * class WPS_Notice_Manager
 *
 * Manages admin notice dismissal with persistent storage and time-based expiration.
 */
class WPS_Notice_Manager {

	/**
	 * User meta key for storing dismissed notices.
	 *
	 * @var string
	 */
	private const META_KEY = 'WPS_dismissed_notices';

	/**
	 * Default suppression durations by notice type (in seconds).
	 *
	 * @var array<string, int>
	 */
	private const SUPPRESSION_DURATIONS = array(
		'info'    => 7 * DAY_IN_SECONDS,  // 7 days.
		'success' => 7 * DAY_IN_SECONDS,  // 7 days.
		'warning' => 3 * DAY_IN_SECONDS,  // 3 days.
		'error'   => 1 * DAY_IN_SECONDS,  // 1 day.
	);

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_dismissal_script' ) );
		add_action( 'wp_ajax_WPS_dismiss_notice', array( __CLASS__, 'ajax_dismiss_notice' ) );
	}

	/**
	 * Enqueue dismissal JavaScript.
	 *
	 * @return void
	 */
	public static function enqueue_dismissal_script(): void {
		wp_enqueue_script(
			'wps-notice-dismissal',
			wp_support_URL . 'assets/js/notice-dismissal.js',
			array( 'jquery' ),
			wp_support_VERSION,
			true
		);

		wp_localize_script(
			'wps-notice-dismissal',
			'wpsNotices',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'WPS_dismiss_notice' ),
			)
		);
	}

	/**
	 * AJAX handler for dismissing notices.
	 *
	 * @return void
	 */
	public static function ajax_dismiss_notice(): void {
		check_ajax_referer( 'WPS_dismiss_notice', 'nonce' );

		$notice_key = isset( $_POST['notice_key'] ) ? sanitize_key( $_POST['notice_key'] ) : '';

		if ( empty( $notice_key ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid notice key.', 'plugin-wp-support-thisismyurl' ) ) );
		}

		self::dismiss_notice( $notice_key );

		wp_send_json_success( array( 'message' => __( 'Notice dismissed.', 'plugin-wp-support-thisismyurl' ) ) );
	}

	/**
	 * Check if a notice has been dismissed and is still suppressed.
	 *
	 * @param string $notice_key Unique notice identifier.
	 * @return bool True if notice should be suppressed.
	 */
	public static function is_dismissed( string $notice_key ): bool {
		$user_id = get_current_user_id();

		if ( ! $user_id ) {
			return false;
		}

		$dismissed = get_user_meta( $user_id, self::META_KEY, true );

		if ( ! is_array( $dismissed ) || ! isset( $dismissed[ $notice_key ] ) ) {
			return false;
		}

		$dismissed_time = (int) $dismissed[ $notice_key ];
		$current_time   = time();

		// Extract notice type from key (format: type_specific_identifier).
		$notice_type = self::extract_notice_type( $notice_key );
		$duration    = self::SUPPRESSION_DURATIONS[ $notice_type ] ?? self::SUPPRESSION_DURATIONS['info'];

		// Check if suppression period has expired.
		if ( ( $current_time - $dismissed_time ) > $duration ) {
			// Expired, remove from dismissed list.
			unset( $dismissed[ $notice_key ] );
			update_user_meta( $user_id, self::META_KEY, $dismissed );
			return false;
		}

		return true;
	}

	/**
	 * Dismiss a notice for the current user.
	 *
	 * @param string $notice_key Unique notice identifier.
	 * @return bool True on success.
	 */
	public static function dismiss_notice( string $notice_key ): bool {
		$user_id = get_current_user_id();

		if ( ! $user_id ) {
			return false;
		}

		$dismissed = get_user_meta( $user_id, self::META_KEY, true );

		if ( ! is_array( $dismissed ) ) {
			$dismissed = array();
		}

		$dismissed[ $notice_key ] = time();

		return (bool) update_user_meta( $user_id, self::META_KEY, $dismissed );
	}

	/**
	 * Clear all dismissed notices for the current user.
	 *
	 * @return bool True on success.
	 */
	public static function clear_all_dismissed(): bool {
		$user_id = get_current_user_id();

		if ( ! $user_id ) {
			return false;
		}

		return delete_user_meta( $user_id, self::META_KEY );
	}

	/**
	 * Render a dismissible notice with persistent dismissal.
	 *
	 * @param string $notice_key Unique notice identifier (e.g., 'warning_vault_size', 'error_license_invalid').
	 * @param string $message Notice message (pre-escaped).
	 * @param string $type Notice type: info, success, warning, error.
	 * @param array  $args Optional. Additional arguments (e.g., 'capabilities').
	 * @return void
	 */
	public static function render_notice( string $notice_key, string $message, string $type = 'info', array $args = array() ): void {
		// Check capability if specified.
		if ( ! empty( $args['capability'] ) && ! current_user_can( $args['capability'] ) ) {
			return;
		}

		// Check if dismissed.
		if ( self::is_dismissed( $notice_key ) ) {
			return;
		}

		// Validate type.
		if ( ! in_array( $type, array( 'info', 'success', 'warning', 'error' ), true ) ) {
			$type = 'info';
		}

		printf(
			'<div class="notice notice-%s is-dismissible" data-notice-key="%s"><p>%s</p></div>',
			esc_attr( $type ),
			esc_attr( $notice_key ),
			$message // Already escaped by caller.
		);
	}

	/**
	 * Extract notice type from notice key.
	 *
	 * @param string $notice_key Notice key (format: type_identifier).
	 * @return string Notice type (info, success, warning, error).
	 */
	private static function extract_notice_type( string $notice_key ): string {
		$parts = explode( '_', $notice_key, 2 );

		if ( isset( $parts[0] ) && in_array( $parts[0], array( 'info', 'success', 'warning', 'error' ), true ) ) {
			return $parts[0];
		}

		return 'info';
	}
}



