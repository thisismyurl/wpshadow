<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: API Rate Limiting
 *
 * Checks if REST API rate limiting is configured to prevent abuse.
 * Unrestricted API access enables DoS and brute force attacks.
 *
 * @since 1.2.0
 */
class Test_Api_Rate_Limiting extends Diagnostic_Base {


	/**
	 * Check API rate limiting configuration
	 *
	 * @return array|null Diagnostic array if issues found, null if all good
	 */
	public static function check(): ?array {
		$rate_limiting = self::check_rate_limiting();

		if ( ! $rate_limiting['has_concerns'] ) {
			return null;
		}

		return array(
			'threat_level'  => $rate_limiting['threat_level'],
			'threat_color'  => 'orange',
			'passed'        => false,
			'issue'         => $rate_limiting['issue'],
			'metadata'      => $rate_limiting,
			'kb_link'       => 'https://wpshadow.com/kb/rest-api-rate-limiting/',
			'training_link' => 'https://wpshadow.com/training/wordpress-api-security/',
		);
	}

	/**
	 * Guardian Sub-Test: REST API rate limit headers
	 *
	 * @return array Test result
	 */
	public static function test_rate_limit_headers(): array {
		$has_rate_limiting = apply_filters( 'rest_throttle_check_limit', false );

		return array(
			'test_name'     => 'Rate Limit Headers',
			'rate_limiting' => $has_rate_limiting,
			'passed'        => $has_rate_limiting,
			'description'   => $has_rate_limiting ? 'Rate limiting headers enabled' : 'No rate limiting headers configured',
		);
	}

	/**
	 * Guardian Sub-Test: Requests per second limit
	 *
	 * @return array Test result
	 */
	public static function test_requests_per_second(): array {
		// Check if rate limiting plugin is active
		$active_plugins = get_plugins();

		$rate_limiting_plugins = array(
			'rate-limiting/rate-limiting.php' => 'Rate Limiting',
			'wordfence/wordfence.php'         => 'Wordfence Security',
			'sucuri-scanner/sucuri.php'       => 'Sucuri Security',
		);

		$has_plugin = false;
		foreach ( $rate_limiting_plugins as $plugin_file => $plugin_name ) {
			if ( isset( $active_plugins[ $plugin_file ] ) ) {
				$has_plugin = true;
				break;
			}
		}

		return array(
			'test_name'             => 'Requests Per Second Limit',
			'has_rate_limit_plugin' => $has_plugin,
			'passed'                => $has_plugin,
			'description'           => $has_plugin ? 'Rate limiting plugin active' : 'Consider installing rate limiting plugin',
		);
	}

	/**
	 * Guardian Sub-Test: API authentication requirements
	 *
	 * @return array Test result
	 */
	public static function test_api_authentication(): array {
		$rest_endpoints = array();

		// Get registered REST routes
		$routes = rest_get_server()->get_routes();

		$unauthenticated_count = 0;
		foreach ( $routes as $route => $route_data ) {
			foreach ( $route_data as $endpoint ) {
				$callback = $endpoint['callback'] ?? null;
				$methods  = $endpoint['methods'] ?? array();

				if ( isset( $methods['GET'] ) && ! isset( $endpoint['permission_callback'] ) ) {
					++$unauthenticated_count;
				}
			}
		}

		$status = 'normal';
		if ( $unauthenticated_count > 10 ) {
			$status = 'warning';
		}

		return array(
			'test_name'              => 'API Authentication',
			'unauthenticated_routes' => $unauthenticated_count,
			'status'                 => $status,
			'passed'                 => $status === 'normal',
			'description'            => sprintf( '%d unauthenticated API routes', $unauthenticated_count ),
		);
	}

	/**
	 * Guardian Sub-Test: User enumeration protection
	 *
	 * @return array Test result
	 */
	public static function test_user_enumeration(): array {
		// Test if user enumeration is possible via REST API
		$can_enumerate = false;

		$response = wp_remote_get(
			rest_url( 'wp/v2/users' ),
			array(
				'timeout' => 5,
			)
		);

		if ( ! is_wp_error( $response ) ) {
			$code = wp_remote_retrieve_response_code( $response );
			$body = wp_remote_retrieve_body( $response );

			if ( 200 === $code && strpos( $body, '"id"' ) !== false ) {
				$can_enumerate = true;
			}
		}

		return array(
			'test_name'     => 'User Enumeration Protection',
			'can_enumerate' => $can_enumerate,
			'passed'        => ! $can_enumerate,
			'description'   => $can_enumerate ? 'Users can be enumerated via REST API' : 'User enumeration is protected',
		);
	}

	/**
	 * Check rate limiting configuration
	 *
	 * @return array Rate limiting analysis
	 */
	private static function check_rate_limiting(): array {
		$concerns     = array();
		$threat_level = 0;

		// Check if rate limiting headers are enabled
		$has_rate_limiting = apply_filters( 'rest_throttle_check_limit', false );

		if ( ! $has_rate_limiting ) {
			$concerns[]   = 'Rate limiting headers not configured';
			$threat_level = 30;
		}

		// Check for rate limiting plugin
		$active_plugins           = get_plugins();
		$has_rate_limiting_plugin = false;

		$rate_limiting_plugins = array(
			'rate-limiting/rate-limiting.php',
			'wordfence/wordfence.php',
			'sucuri-scanner/sucuri.php',
		);

		foreach ( $rate_limiting_plugins as $plugin_file ) {
			if ( isset( $active_plugins[ $plugin_file ] ) ) {
				$has_rate_limiting_plugin = true;
				break;
			}
		}

		if ( ! $has_rate_limiting_plugin ) {
			$concerns[]   = 'No rate limiting plugin installed';
			$threat_level = max( $threat_level, 20 );
		}

		// Test user enumeration
		$response = wp_remote_get(
			rest_url( 'wp/v2/users' ),
			array(
				'timeout' => 5,
			)
		);

		$can_enumerate = false;
		if ( ! is_wp_error( $response ) ) {
			$code = wp_remote_retrieve_response_code( $response );
			if ( 200 === $code ) {
				$can_enumerate = true;
				$concerns[]    = 'Users can be enumerated via REST API';
				$threat_level  = max( $threat_level, 45 );
			}
		}

		return array(
			'has_concerns'          => ! empty( $concerns ),
			'threat_level'          => $threat_level,
			'issue'                 => ! empty( $concerns ) ? implode( ', ', $concerns ) : 'API rate limiting is configured',
			'rate_limiting_headers' => $has_rate_limiting,
			'rate_limiting_plugin'  => $has_rate_limiting_plugin,
			'user_enumeration'      => $can_enumerate,
		);
	}

	/**
	 * Get diagnostic name
	 *
	 * @return string
	 */
	public static function get_name(): string {
		return 'API Rate Limiting';
	}

	/**
	 * Get diagnostic description
	 *
	 * @return string
	 */
	public static function get_description(): string {
		return 'Checks if REST API rate limiting is configured to prevent abuse';
	}

	/**
	 * Get diagnostic category
	 *
	 * @return string
	 */
	public static function get_category(): string {
		return 'Security';
	}
}
