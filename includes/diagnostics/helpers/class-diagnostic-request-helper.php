<?php
/**
 * Diagnostic Request Helper
 *
 * Centralized helpers for making HTTP requests in diagnostics.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.6035.1402
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Request_Helper Class
 *
 * Provides shared utilities for safe HTTP requests.
 *
 * @since 1.6035.1402
 */
class Diagnostic_Request_Helper {

	/**
	 * Perform a HEAD request and return a structured result.
	 *
	 * @since  1.6035.1402
	 * @param  string $url  URL to request.
	 * @param  array  $args Optional. wp_remote_head args.
	 * @return array {
	 *     Result array.
	 *
	 *     @type bool        $success       Whether request succeeded.
	 *     @type int|null    $code          HTTP status code when available.
	 *     @type string|null $error_message Error message on failure.
	 *     @type string|null $error_code    Error code on failure.
	 *     @type array|null  $response      Response array on success.
	 * }
	 */
	public static function head_result( string $url, array $args = array() ): array {
		return self::request_result( 'HEAD', $url, $args );
	}

	/**
	 * Perform a POST request and return a structured result.
	 *
	 * @since  1.6035.1402
	 * @param  string $url  URL to request.
	 * @param  array  $args Optional. wp_remote_post args.
	 * @return array {
	 *     Result array.
	 *
	 *     @type bool        $success       Whether request succeeded.
	 *     @type int|null    $code          HTTP status code when available.
	 *     @type string|null $error_message Error message on failure.
	 *     @type string|null $error_code    Error code on failure.
	 *     @type array|null  $response      Response array on success.
	 * }
	 */
	public static function post_result( string $url, array $args = array() ): array {
		return self::request_result( 'POST', $url, $args );
	}

	/**
	 * Perform a HEAD request and return the response code.
	 *
	 * @since  1.6035.1402
	 * @param  string $url  URL to request.
	 * @param  array  $args Optional. wp_remote_head args.
	 * @return int|null Response code or null on failure.
	 */
	public static function head_response_code( string $url, array $args = array() ): ?int {
		$response = self::request( 'HEAD', $url, $args );
		if ( null === $response ) {
			return null;
		}

		return wp_remote_retrieve_response_code( $response );
	}

	/**
	 * Perform a POST request and return the response code.
	 *
	 * @since  1.6035.1402
	 * @param  string $url  URL to request.
	 * @param  array  $args Optional. wp_remote_post args.
	 * @return int|null Response code or null on failure.
	 */
	public static function post_response_code( string $url, array $args = array() ): ?int {
		$response = self::request( 'POST', $url, $args );
		if ( null === $response ) {
			return null;
		}

		return wp_remote_retrieve_response_code( $response );
	}

	/**
	 * Perform an HTTP request with standard defaults.
	 *
	 * @since  1.6035.1402
	 * @param  string $method HTTP method.
	 * @param  string $url    URL to request.
	 * @param  array  $args   Request args.
	 * @return array|null Response array or null on failure.
	 */
	private static function request( string $method, string $url, array $args = array() ): ?array {
		if ( ! function_exists( 'wp_remote_request' ) ) {
			return null;
		}

		$defaults = array(
			'timeout'   => 5,
			'sslverify' => false,
		);

		$args = wp_parse_args( $args, $defaults );

		switch ( strtoupper( $method ) ) {
			case 'HEAD':
				$response = wp_remote_head( $url, $args );
				break;
			case 'POST':
				$response = wp_remote_post( $url, $args );
				break;
			default:
				$args['method'] = $method;
				$response       = wp_remote_request( $url, $args );
		}

		if ( is_wp_error( $response ) ) {
			return null;
		}

		return $response;
	}

	/**
	 * Perform an HTTP request and return a structured result.
	 *
	 * @since  1.6035.1402
	 * @param  string $method HTTP method.
	 * @param  string $url    URL to request.
	 * @param  array  $args   Request args.
	 * @return array Result array.
	 */
	private static function request_result( string $method, string $url, array $args = array() ): array {
		if ( ! function_exists( 'wp_remote_request' ) ) {
			return array(
				'success'       => false,
				'code'          => null,
				'error_message' => __( 'HTTP requests are unavailable.', 'wpshadow' ),
				'error_code'    => 'http_unavailable',
				'response'      => null,
			);
		}

		$defaults = array(
			'timeout'   => 5,
			'sslverify' => false,
		);

		$args = wp_parse_args( $args, $defaults );

		switch ( strtoupper( $method ) ) {
			case 'HEAD':
				$response = wp_remote_head( $url, $args );
				break;
			case 'POST':
				$response = wp_remote_post( $url, $args );
				break;
			default:
				$args['method'] = $method;
				$response       = wp_remote_request( $url, $args );
		}

		if ( is_wp_error( $response ) ) {
			return array(
				'success'       => false,
				'code'          => null,
				'error_message' => $response->get_error_message(),
				'error_code'    => $response->get_error_code(),
				'response'      => null,
			);
		}

		return array(
			'success'       => true,
			'code'          => wp_remote_retrieve_response_code( $response ),
			'error_message' => null,
			'error_code'    => null,
			'response'      => $response,
		);
	}
}
