<?php
/**
 * Base AJAX Handler
 *
 * Abstract base class for AJAX handlers to eliminate security check duplication.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract AJAX Handler Base Class
 *
 * Provides common AJAX security patterns including nonce verification
 * and capability checking.
 */
abstract class AJAX_Handler_Base {
	/**
	 * Verify AJAX request with nonce and capability check.
	 *
	 * Sends JSON error and dies if verification fails.
	 *
	 * @param string $nonce_action The nonce action to verify.
	 * @param string $capability   The capability required (default: manage_options).
	 * @param string $nonce_field  The nonce field name (default: nonce).
	 * @return void Dies on failure, returns on success.
	 */
	protected static function verify_request( $nonce_action, $capability = 'manage_options', $nonce_field = 'nonce' ) {
		// Verify nonce
		check_ajax_referer( $nonce_action, $nonce_field );

		// Verify capability
		if ( ! current_user_can( $capability ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Insufficient permissions.', 'wpshadow' ),
				)
			);
		}
	}

	/**
	 * Sanitize and validate a POST parameter.
	 *
	 * @param string $key          The POST parameter key.
	 * @param string $type         The sanitization type (text, email, key, textarea, int, bool).
	 * @param mixed  $default      Default value if parameter is missing.
	 * @param bool   $required     Whether this parameter is required.
	 * @return mixed Sanitized value or error sent.
	 */
	protected static function get_post_param( $key, $type = 'text', $default = '', $required = false ) {
		if ( ! isset( $_POST[ $key ] ) ) {
			if ( $required ) {
				wp_send_json_error(
					array(
						'message' => sprintf(
						/* translators: %s: parameter name */
							__( 'Required parameter "%s" is missing.', 'wpshadow' ),
							$key
						),
					)
				);
			}
			return $default;
		}

		$value = wp_unslash( $_POST[ $key ] );

		switch ( $type ) {
			case 'email':
				return sanitize_email( $value );
			case 'key':
				return sanitize_key( $value );
			case 'textarea':
				return sanitize_textarea_field( $value );
			case 'int':
				return intval( $value );
			case 'bool':
				return rest_sanitize_boolean( $value );
			case 'url':
				return esc_url_raw( $value );
			case 'text':
			default:
				return sanitize_text_field( $value );
		}
	}

	/**
	 * Send standardized success response.
	 *
	 * @param array $data Additional data to include in response.
	 * @return void Dies after sending response.
	 */
	protected static function send_success( $data = array() ) {
		wp_send_json_success( $data );
	}

	/**
	 * Send standardized error response.
	 *
	 * @param string $message Error message.
	 * @param array  $data    Additional data to include in response.
	 * @return void Dies after sending response.
	 */
	protected static function send_error( $message, $data = array() ) {
		$data['message'] = $message;
		wp_send_json_error( $data );
	}
}
