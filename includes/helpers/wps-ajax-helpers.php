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
