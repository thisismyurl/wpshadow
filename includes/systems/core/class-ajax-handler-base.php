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
	 * Now includes rate limiting for protection against brute force attacks.
	 *
	 * @since  1.6035.0948 Added rate limiting.
	 * @param string $nonce_action The nonce action to verify.
	 * @param string $capability   The capability required (default: manage_options).
	 * @param string $nonce_field  The nonce field name (default: nonce).
	 * @return void Dies on failure, returns on success.
	 */
	protected static function verify_request( $nonce_action, $capability = 'manage_options', $nonce_field = 'nonce' ) {
		// Rate limit check (if enabled)
		if ( class_exists( 'WPShadow\Core\Rate_Limiter' ) ) {
			$user_id    = get_current_user_id();
			$ip_address = self::get_client_ip();
			$action     = isset( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : '';

			if ( ! Rate_Limiter::check_rate_limit( $action, $user_id, $ip_address ) ) {
				wp_send_json_error(
					array(
						'message'      => Rate_Limiter::get_rate_limit_message( $action, $user_id, $ip_address ),
						'rate_limited' => true,
					),
					429 // HTTP 429 Too Many Requests
				);
			}
		}

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
	 * Verify admin request (GET-based) with nonce and capability check.
	 *
	 * Uses check_admin_referer instead of check_ajax_referer for GET requests.
	 * Dies with wp_die on failure (not JSON response).
	 *
	 * @param string $nonce_action The nonce action to verify.
	 * @param string $capability   The capability required (default: manage_options).
	 * @param string $nonce_field  The nonce field name (default: _wpnonce).
	 * @return void Dies on failure, returns on success.
	 */
	protected static function verify_admin_request( $nonce_action, $capability = 'manage_options', $nonce_field = '_wpnonce' ) {
		// Verify nonce (admin referer for GET requests)
		check_admin_referer( $nonce_action, $nonce_field );

		// Verify capability
		if ( ! current_user_can( $capability ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'wpshadow' ), 403 );
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
	 * Sanitize and validate a GET parameter.
	 *
	 * @param string $key          The GET parameter key.
	 * @param string $type         The sanitization type (text, email, key, textarea, int, bool).
	 * @param mixed  $default      Default value if parameter is missing.
	 * @param bool   $required     Whether this parameter is required.
	 * @return mixed Sanitized value or dies if required and missing.
	 */
	protected static function get_get_param( $key, $type = 'text', $default = '', $required = false ) {
		if ( ! isset( $_GET[ $key ] ) ) {
			if ( $required ) {
				wp_die(
					sprintf(
						/* translators: %s: parameter name */
						esc_html__( 'Required parameter "%s" is missing.', 'wpshadow' ),
						esc_html( $key )
					),
					400
				);
			}
			return $default;
		}

		$value = wp_unslash( $_GET[ $key ] );

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
	 * Ensures user-facing messages are friendly and do not expose technical
	 * details. Technical errors are logged server-side for debugging.
	 *
	 * @since  1.8035.1200
	 * @param string|\WP_Error $message Error message or WP_Error instance.
	 * @param array           $data    Additional data to include in response.
	 * @return void Dies after sending response.
	 */
	protected static function send_error( $message, $data = array() ) {
		$raw_message = $message;
		$friendly_message = self::format_error_message( $message );

		if ( $friendly_message !== $raw_message && class_exists( 'WPShadow\Core\Error_Handler' ) ) {
			$action = isset( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : '';
			Error_Handler::log_error(
				'AJAX error message sanitized',
				array(
					'action'  => $action,
					'message' => is_wp_error( $raw_message ) ? $raw_message->get_error_message() : (string) $raw_message,
				)
			);
		}

		$data['message'] = $friendly_message;
		wp_send_json_error( $data );
	}

	/**
	 * Format error message for user-friendly display.
	 *
	 * Converts technical errors into plain-language guidance.
	 *
	 * @since  1.8035.1200
	 * @param  string|\WP_Error $message Error message or WP_Error instance.
	 * @return string User-friendly message.
	 */
	protected static function format_error_message( $message ) : string {
		if ( is_wp_error( $message ) ) {
			$message = $message->get_error_message();
		}

		$message = wp_strip_all_tags( (string) $message );
		$message = trim( $message );

		if ( '' === $message ) {
			return __( 'We couldn\'t complete that request right now. Please try again in a moment.', 'wpshadow' );
		}

		$technical_patterns = array(
			'/\bSQL\b/i',
			'/\bwpdb\b/i',
			'/\bmysqli?\b/i',
			'/\bFatal error\b/i',
			'/\bWarning\b/i',
			'/\bNotice\b/i',
			'/\bException\b/i',
			'/\bStack trace\b/i',
			'/\bon line\b/i',
			'/\.php\b/i',
			'/\bUndefined\b/i',
			'/\bCall to\b/i',
			'/\bbacktrace\b/i',
			'/\bREST\b/i',
			'/\bcURL\b/i',
			'/\bHTTP \d{3}\b/i',
			'/\bpermission denied\b/i',
			'/\bfile not found\b/i',
		);

		foreach ( $technical_patterns as $pattern ) {
			if ( preg_match( $pattern, $message ) ) {
				return __( 'We couldn\'t complete that request right now. Please try again in a moment.', 'wpshadow' );
			}
		}

		// Keep friendly short messages as-is.
		if ( strlen( $message ) > 180 ) {
			return __( 'We couldn\'t complete that request right now. Please try again in a moment.', 'wpshadow' );
		}

		return $message;
	}

	/**
	 * Get client IP address.
	 *
	 * Handles proxies and CloudFlare properly.
	 *
	 * @since  1.6035.0948
	 * @return string IP address.
	 */
	protected static function get_client_ip(): string {
		$ip_keys = array(
			'HTTP_CF_CONNECTING_IP', // CloudFlare
			'HTTP_X_FORWARDED_FOR',  // Proxy
			'HTTP_X_REAL_IP',        // Nginx proxy
			'REMOTE_ADDR',           // Direct connection
		);

		foreach ( $ip_keys as $key ) {
			if ( ! empty( $_SERVER[ $key ] ) ) {
				$ip = sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) );
				
				// X-Forwarded-For can contain multiple IPs
				if ( false !== strpos( $ip, ',' ) ) {
					$ips = explode( ',', $ip );
					$ip  = trim( $ips[0] );
				}
				
				if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
					return $ip;
				}
			}
		}

		return '0.0.0.0'; // Fallback
	}

	/**
	 * Get the WordPress AJAX action name from the class name.
	 *
	 * Convention: Dismiss_Finding_Handler -> wpshadow_dismiss_finding
	 *
	 * @since  1.7035.1200
	 * @return string AJAX action name.
	 */
	protected static function get_action() {
		$class_name = get_called_class();
		
		// Get just the class name (remove namespace).
		$parts      = explode( '\\', $class_name );
		$short_name = end( $parts );
		
		// Remove _Handler suffix if present.
		$short_name = preg_replace( '/_Handler$/i', '', $short_name );
		
		// Convert from PascalCase/Snake_Case to snake_case.
		$action = strtolower( preg_replace( '/(?<!^)[A-Z]/', '_$0', $short_name ) );
		
		// Add wpshadow prefix.
		return 'wpshadow_' . $action;
	}

	/**
	 * Auto-register this handler with WordPress AJAX hooks.
	 *
	 * Uses convention-based naming to derive the action from the class name.
	 *
	 * @since 1.7035.1200
	 * @return void
	 */
	public static function register() {
		$action = static::get_action();
		add_action( 'wp_ajax_' . $action, array( get_called_class(), 'handle' ) );
	}
}
