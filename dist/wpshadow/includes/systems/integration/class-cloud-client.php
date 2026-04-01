<?php
declare(strict_types=1);

namespace WPShadow\Cloud;

/**
 * Cloud API Client
 *
 * Low-level HTTP client for authenticated cloud service communication.
 * Handles request/response, retries, and error handling.
 *
 * Philosophy: Centralized API communication ensures consistent security,
 * logging, and error handling across all cloud features.
 *
 * Security: All requests must include API token (stored in wp_options).
 * No API token is ever exposed in logs or error messages.
 *
 * @since 0.6093.1200
 */
class Cloud_Client {

	private const API_BASE        = 'https://api.wpshadow.com/v1';
	private const REQUEST_TIMEOUT = 10;
	private const MAX_RETRIES     = 3;

	/**
	 * Send authenticated request to cloud API
	 *
	 * @param string $method HTTP method (GET, POST, PUT, DELETE)
	 * @param string $endpoint API endpoint (e.g., '/register', '/scans')
	 * @param array  $data Request body data
	 * @param array  $headers Additional headers (Authorization added automatically)
	 *
	 * @return array Response data or error array ['error' => message]
	 */
	public static function request(
		string $method,
		string $endpoint,
		array $data = array(),
		array $headers = array()
	): array {
		if ( ! \WPShadow\Core\External_Request_Guard::is_allowed( 'cloud_api' ) ) {
			return array(
				'error' => \WPShadow\Core\External_Request_Guard::get_denied_message( __( 'Cloud requests', 'wpshadow' ) ),
			);
		}

		// Verify site is registered
		$token = get_option( 'wpshadow_cloud_token' );
		if ( ! $token && $endpoint !== '/register' ) {
			return array( 'error' => 'Site not registered with cloud service' );
		}

		// Build request
		$url = self::API_BASE . $endpoint;

		// Add authentication headers
		$headers['Content-Type'] = 'application/json';
		if ( $token ) {
			$headers['Authorization'] = 'Bearer ' . sanitize_text_field( $token );
		}
		if ( $site_id = get_option( 'wpshadow_site_id' ) ) {
			$headers['X-Site-ID'] = sanitize_text_field( $site_id );
		}

		// Add user agent
		$headers['User-Agent'] = 'WPShadow/' . get_option( 'wpshadow_version', '1.0' );

		// Prepare request args
		$args = array(
			'method'      => $method,
			'headers'     => $headers,
			'timeout'     => self::REQUEST_TIMEOUT,
			'sslverify'   => true,
			'redirection' => 5,
		);

		// Add body for POST/PUT
		if ( ! empty( $data ) && in_array( $method, array( 'POST', 'PUT' ), true ) ) {
			$args['body'] = wp_json_encode( $data );
		}

		// Execute with retry logic
		return self::send_with_retry( $url, $args );
	}

	/**
	 * Send request with exponential backoff retry logic
	 *
	 * Retries up to 3 times with exponential backoff:
	 * - Attempt 1: immediate
	 * - Attempt 2: 2 second delay
	 * - Attempt 3: 4 second delay
	 *
	 * @param string $url Full URL
	 * @param array  $args Request arguments
	 *
	 * @return array Response data or error
	 */
	private static function send_with_retry( string $url, array $args ): array {
		$response = null;

		for ( $attempt = 1; $attempt <= self::MAX_RETRIES; $attempt++ ) {
			// Backoff delay before retry (not before first attempt)
			if ( $attempt > 1 ) {
				sleep( 2 ** ( $attempt - 2 ) ); // 2, 4 seconds
			}

			// Send request
			$response = wp_remote_request( esc_url_raw( $url ), $args );

			// Check for network error
			if ( is_wp_error( $response ) ) {
				// Retry on network errors
				if ( $attempt < self::MAX_RETRIES ) {
					continue;
				}

				// Final attempt failed
				self::log_error( 'network_error', $response->get_error_message() );
				return array( 'error' => 'Network connection failed' );
			}

			// Check HTTP status
			$status = wp_remote_retrieve_response_code( $response );

			// Success
			if ( $status >= 200 && $status < 300 ) {
				return self::parse_response( $response );
			}

			// Server error (retry)
			if ( $status >= 500 && $status < 600 && $attempt < self::MAX_RETRIES ) {
				continue;
			}

			// Client error or final server error
			return self::handle_error_response( $response, $status );
		}

		// Should not reach here
		return array( 'error' => 'Request failed after retries' );
	}

	/**
	 * Parse successful response
	 *
	 * @param mixed $response WordPress HTTP response
	 *
	 * @return array Parsed response body
	 */
	private static function parse_response( $response ): array {
		$body = wp_remote_retrieve_body( $response );

		if ( empty( $body ) ) {
			return array( 'success' => true );
		}

		$decoded = json_decode( $body, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			self::log_error( 'json_decode_error', json_last_error_msg() );
			return array( 'error' => 'Invalid response format' );
		}

		return $decoded ?? array();
	}

	/**
	 * Handle error responses
	 *
	 * @param mixed $response WordPress HTTP response
	 * @param int   $status HTTP status code
	 *
	 * @return array Error array
	 */
	private static function handle_error_response( $response, int $status ): array {
		$body    = wp_remote_retrieve_body( $response );
		$decoded = json_decode( $body, true );

		$error_message = $decoded['error'] ?? $decoded['message'] ?? "HTTP {$status}";

		self::log_error( 'api_error', $error_message, $status );

		return array( 'error' => $error_message );
	}

	/**
	 * Log API errors securely
	 * Never log sensitive data like tokens
	 *
	 * @param string $error_type Type of error (network, json, api)
	 * @param string $message Error message
	 * @param int    $status HTTP status code (optional)
	 */
	private static function log_error(
		string $error_type,
		string $message,
		int $status = 0
	): void {
		// Only log in debug mode
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			return;
		}

		// Build safe log entry (no sensitive data)
		$log_entry = sprintf(
			'[WPShadow Cloud] %s: %s%s',
			ucfirst( str_replace( '_', ' ', $error_type ) ),
			$message,
			$status > 0 ? " (HTTP {$status})" : ''
		);

		// Log to WordPress debug log
		error_log( $log_entry );
	}

	/**
	 * Health check: Verify API connectivity
	 *
	 * @return bool True if API is reachable
	 */
	public static function health_check(): bool {
		$response = wp_remote_head(
			self::API_BASE . '/health',
			array(
				'timeout'   => 5,
				'sslverify' => true,
			)
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$status = wp_remote_retrieve_response_code( $response );
		return $status >= 200 && $status < 300;
	}
}
