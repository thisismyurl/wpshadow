<?php

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPSHADOW_Notice_Manager {

	private const META_KEY = 'wpshadow_dismissed_notices';

	private const SUPPRESSION_DURATIONS = array(
		'info'    => 7 * DAY_IN_SECONDS,  
		'success' => 7 * DAY_IN_SECONDS,  
		'warning' => 3 * DAY_IN_SECONDS,  
		'error'   => 1 * DAY_IN_SECONDS,  
	);

	public static function init(): void {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_dismissal_script' ) );
		add_action( 'wp_ajax_wpshadow_dismiss_notice', array( __CLASS__, 'ajax_dismiss_notice' ) );
	}

	public static function enqueue_dismissal_script(): void {

	}

	public static function ajax_dismiss_notice(): void {
		check_ajax_referer( 'wpshadow_dismiss_notice', 'nonce' );

		$notice_key = isset( $_POST['notice_key'] ) ? sanitize_key( $_POST['notice_key'] ) : '';

		$duration = isset( $_POST['duration'] ) ? absint( $_POST['duration'] ) : 0;

		if ( empty( $notice_key ) ) {
			wp_send_json_error( array( 'message' => __( 'That notice doesn\'t exist.', 'wpshadow' ) ) );
		}

		if ( $duration > 0 ) {
			$user_id = get_current_user_id();
			$transient_key = $notice_key . '_dismissed_' . $user_id;
			$result = set_transient( $transient_key, time(), $duration );

			error_log( 'WPShadow: Setting transient ' . $transient_key . ' for ' . $duration . ' seconds. Result: ' . var_export( $result, true ) );
			wp_send_json_success( array( 'message' => __( 'Notice dismissed temporarily.', 'wpshadow' ) ) );
		}

		self::dismiss_notice( $notice_key );

		wp_send_json_success( array( 'message' => __( 'Notice dismissed.', 'wpshadow' ) ) );
	}

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

		$notice_type = self::extract_notice_type( $notice_key );
		$duration    = self::SUPPRESSION_DURATIONS[ $notice_type ] ?? self::SUPPRESSION_DURATIONS['info'];

		if ( ( $current_time - $dismissed_time ) > $duration ) {

			unset( $dismissed[ $notice_key ] );
			update_user_meta( $user_id, self::META_KEY, $dismissed );
			return false;
		}

		return true;
	}

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

	public static function clear_all_dismissed(): bool {
		$user_id = get_current_user_id();

		if ( ! $user_id ) {
			return false;
		}

		return delete_user_meta( $user_id, self::META_KEY );
	}

	public static function render_notice( string $notice_key, string $message, string $type = 'info', array $args = array() ): void {

		if ( ! empty( $args['capability'] ) && ! current_user_can( $args['capability'] ) ) {
			return;
		}

		if ( self::is_dismissed( $notice_key ) ) {
			return;
		}

		if ( ! in_array( $type, array( 'info', 'success', 'warning', 'error' ), true ) ) {
			$type = 'info';
		}

		printf(
			'<div class="notice notice-%s is-dismissible" data-notice-key="%s"><p>%s</p></div>',
			esc_attr( $type ),
			esc_attr( $notice_key ),
			$message 
		);
	}

	private static function extract_notice_type( string $notice_key ): string {
		$parts = explode( '_', $notice_key, 2 );

		if ( isset( $parts[0] ) && in_array( $parts[0], array( 'info', 'success', 'warning', 'error' ), true ) ) {
			return $parts[0];
		}

		return 'info';
	}
}
