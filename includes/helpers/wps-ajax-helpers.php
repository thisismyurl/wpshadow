<?php
/**
 * AJAX Response Helpers
 *
 * Standardized AJAX response functions to ensure consistent JSON responses
 * across the plugin.
 *
 * @package WPS\CoreSupport
 * @since 1.2601.73003
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Send standardized AJAX success response.
 *
 * @param array $data Optional data to include in response.
 * @return void
 */
function wps_ajax_success( array $data = array() ): void {
	wp_send_json_success( $data );
}

/**
 * Send standardized AJAX error response.
 *
 * @param string $message Error message.
 * @param int    $code    HTTP status code (default: 400).
 * @return void
 */
function wps_ajax_error( string $message, int $code = 400 ): void {
	wp_send_json_error( array( 'message' => $message ), $code );
}

/**
 * Send permission denied error response.
 *
 * @return void
 */
function wps_ajax_permission_denied(): void {
	wps_ajax_error( __( 'Insufficient permissions', 'plugin-wp-support-thisismyurl' ), 403 );
}

/**
 * Send authentication required error response.
 *
 * @return void
 */
function wps_ajax_auth_required(): void {
	wps_ajax_error( __( 'You must be logged in to perform this action.', 'plugin-wp-support-thisismyurl' ), 401 );
}

/**
 * Send invalid request error response.
 *
 * @param string $field Optional field name that is invalid.
 * @return void
 */
function wps_ajax_invalid_request( string $field = '' ): void {
	$message = $field
		? sprintf( __( 'Invalid or missing field: %s', 'plugin-wp-support-thisismyurl' ), $field )
		: __( 'Invalid request', 'plugin-wp-support-thisismyurl' );

	wps_ajax_error( $message, 400 );
}

/**
 * Send not found error response.
 *
 * @param string $resource Optional resource name that was not found.
 * @return void
 */
function wps_ajax_not_found( string $resource = '' ): void {
	$message = $resource
		? sprintf( __( '%s not found', 'plugin-wp-support-thisismyurl' ), $resource )
		: __( 'Resource not found', 'plugin-wp-support-thisismyurl' );

	wps_ajax_error( $message, 404 );
}

/**
 * Verify AJAX request with nonce and capability check.
 *
 * Performs both nonce verification and capability check in one call.
 * Sends JSON error and exits if either check fails.
 *
 * @param string $nonce_action Nonce action name.
 * @param string $capability   Required capability (default: 'manage_options').
 * @param string $nonce_key    Nonce POST key (default: 'nonce').
 * @return void Sends JSON error and exits if checks fail.
 */
function wps_verify_ajax_request( string $nonce_action, string $capability = 'manage_options', string $nonce_key = 'nonce' ): void {
	check_ajax_referer( $nonce_action, $nonce_key );

	if ( ! current_user_can( $capability ) ) {
		wps_ajax_permission_denied();
	}
}

/**
 * Verify nonce from admin form submission.
 *
 * Checks both nonce and capability for admin page form submissions.
 * Calls wp_die() if either check fails.
 *
 * @param string $nonce_action Nonce action name.
 * @param string $nonce_key    Nonce key in $_POST or $_GET.
 * @param string $capability   Required capability (default: 'manage_options').
 * @return void Dies with error message if checks fail.
 */
function wps_verify_admin_request( string $nonce_action, string $nonce_key, string $capability = 'manage_options' ): void {
	$nonce = isset( $_POST[ $nonce_key ] ) ? wp_unslash( $_POST[ $nonce_key ] ) : ( isset( $_GET[ $nonce_key ] ) ? wp_unslash( $_GET[ $nonce_key ] ) : '' );

	if ( ! is_string( $nonce ) || ! wp_verify_nonce( $nonce, $nonce_action ) ) {
		wp_die( esc_html__( 'Security check failed. Please try again.', 'plugin-wp-support-thisismyurl' ) );
	}

	if ( ! current_user_can( $capability ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to perform this action.', 'plugin-wp-support-thisismyurl' ) );
	}
}

/**
 * Verify REST API request with capability check.
 *
 * @param \WP_REST_Request $request    REST request object.
 * @param string           $capability Required capability (default: 'manage_options').
 * @return true|\WP_Error True if verified, WP_Error if failed.
 */
function wps_verify_rest_request( \WP_REST_Request $request, string $capability = 'manage_options' ) {
	if ( ! current_user_can( $capability ) ) {
		return new \WP_Error(
			'rest_forbidden',
			__( 'You do not have sufficient permissions to perform this action.', 'plugin-wp-support-thisismyurl' ),
			array( 'status' => 403 )
		);
	}

	return true;
}
