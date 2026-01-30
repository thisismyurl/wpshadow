<?php
/**
 * WPShadow Cloud Service Connector
 *
 * Handles authentication and API communication with WPShadow Cloud Services.
 *
 * @package    WPShadow
 * @subpackage Integration
 * @since      1.26031.0000
 */

declare(strict_types=1);

namespace WPShadow\Integration\Cloud;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cloud Service Connector Class
 *
 * Manages API connections to WPShadow Cloud for external utilities.
 *
 * @since 1.26031.0000
 */
class Cloud_Service_Connector {

	/**
	 * API Base URL
	 *
	 * @var string
	 */
	const API_BASE = 'https://cloud.wpshadow.com/api/v1';

	/**
	 * Get API key.
	 *
	 * @since  1.26031.0000
	 * @return string|null API key or null if not registered.
	 */
	public static function get_api_key() {
		return get_option( 'wpshadow_cloud_api_key', null );
	}

	/**
	 * Check if site is registered with cloud services.
	 *
	 * @since  1.26031.0000
	 * @return bool True if registered.
	 */
	public static function is_registered() {
		$api_key = self::get_api_key();
		return ! empty( $api_key );
	}

	/**
	 * Register site with WPShadow Cloud.
	 *
	 * @since  1.26031.0000
	 * @param  string $email    User email address.
	 * @param  string $site_url Site URL.
	 * @return array {
	 *     Registration result.
	 *
	 *     @type bool   $success    Whether registration succeeded.
	 *     @type string $api_key    API key if successful.
	 *     @type string $message    Result message.
	 *     @type array  $free_tier  Free tier limits.
	 * }
	 */
	public static function register( $email, $site_url ) {
		$response = wp_remote_post(
			self::API_BASE . '/register',
			array(
				'timeout' => 30,
				'body'    => array(
					'email'       => sanitize_email( $email ),
					'site_url'    => esc_url_raw( $site_url ),
					'wp_version'  => get_bloginfo( 'version' ),
					'php_version' => PHP_VERSION,
					'plugin_version' => defined( 'WPSHADOW_VERSION' ) ? WPSHADOW_VERSION : '1.0.0',
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'message' => sprintf(
					/* translators: %s: error message */
					__( 'Registration failed: %s', 'wpshadow' ),
					$response->get_error_message()
				),
			);
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! empty( $body['success'] ) && ! empty( $body['api_key'] ) ) {
			update_option( 'wpshadow_cloud_api_key', $body['api_key'] );
			update_option( 'wpshadow_cloud_email', $email );
			update_option( 'wpshadow_cloud_registered_at', current_time( 'timestamp' ) );
			update_option( 'wpshadow_cloud_free_tier', $body['free_tier'] ?? array() );

			// Log registration
			\WPShadow\Core\Activity_Logger::log(
				'cloud_registered',
				array(
					'email'     => $email,
					'site_url'  => $site_url,
					'free_tier' => $body['free_tier'] ?? array(),
				)
			);

			return array(
				'success'   => true,
				'api_key'   => $body['api_key'],
				'message'   => __( 'Successfully registered with WPShadow Cloud!', 'wpshadow' ),
				'free_tier' => $body['free_tier'] ?? array(),
			);
		}

		return array(
			'success' => false,
			'message' => $body['message'] ?? __( 'Registration failed. Please try again.', 'wpshadow' ),
		);
	}

	/**
	 * Make API request to cloud service.
	 *
	 * @since  1.26031.0000
	 * @param  string $endpoint API endpoint (e.g., 'uptime/check').
	 * @param  array  $data     Request data.
	 * @param  string $method   HTTP method (GET, POST, PUT, DELETE).
	 * @return array {
	 *     API response.
	 *
	 *     @type bool  $success Whether request succeeded.
	 *     @type mixed $data    Response data if successful.
	 *     @type string $message Error message if failed.
	 * }
	 */
	public static function request( $endpoint, $data = array(), $method = 'POST' ) {
		$api_key = self::get_api_key();

		if ( ! $api_key ) {
			return array(
				'success' => false,
				'message' => __( 'Please register with WPShadow Cloud to use this feature.', 'wpshadow' ),
			);
		}

		$url = self::API_BASE . '/' . ltrim( $endpoint, '/' );

		$args = array(
			'method'  => $method,
			'timeout' => 30,
			'headers' => array(
				'X-WPShadow-API-Key' => $api_key,
				'Content-Type'       => 'application/json',
			),
		);

		if ( 'GET' === $method ) {
			$url = add_query_arg( $data, $url );
		} else {
			$args['body'] = wp_json_encode( $data );
		}

		$response = wp_remote_request( $url, $args );

		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'message' => sprintf(
					/* translators: %s: error message */
					__( 'API request failed: %s', 'wpshadow' ),
					$response->get_error_message()
				),
			);
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( 401 === $code ) {
			// API key invalid or expired
			return array(
				'success' => false,
				'message' => __( 'Your API key is invalid or expired. Please re-register.', 'wpshadow' ),
				'code'    => 401,
			);
		}

		if ( 429 === $code ) {
			// Rate limit exceeded
			return array(
				'success' => false,
				'message' => __( 'Free tier limit reached. Upgrade for more usage.', 'wpshadow' ),
				'code'    => 429,
				'data'    => $body,
			);
		}

		if ( $code >= 200 && $code < 300 ) {
			return array(
				'success' => true,
				'data'    => $body,
			);
		}

		return array(
			'success' => false,
			'message' => $body['message'] ?? __( 'API request failed.', 'wpshadow' ),
			'code'    => $code,
		);
	}

	/**
	 * Get usage statistics for current site.
	 *
	 * @since  1.26031.0000
	 * @return array Usage stats by service.
	 */
	public static function get_usage_stats() {
		$result = self::request( 'usage/stats', array(), 'GET' );

		if ( $result['success'] ) {
			return $result['data']['usage'] ?? array();
		}

		return array();
	}

	/**
	 * Get free tier limits.
	 *
	 * @since  1.26031.0000
	 * @return array Free tier limits by service.
	 */
	public static function get_free_tier_limits() {
		$cached = get_option( 'wpshadow_cloud_free_tier', array() );

		if ( ! empty( $cached ) ) {
			return $cached;
		}

		// Default limits if not fetched yet
		return array(
			'uptime_monitor'       => array( 'sites' => 1, 'interval' => 5 ),
			'ssl_monitor'          => array( 'sites' => 1, 'checks_per_day' => 1 ),
			'domain_monitor'       => array( 'domains' => 3 ),
			'ai_content_optimizer' => array( 'analyses_per_month' => 50 ),
			'ai_image_alt'         => array( 'images_per_month' => 100 ),
			'ai_spam_detection'    => array( 'checks_per_month' => 1000 ),
			'malware_scanner'      => array( 'scans_per_week' => 1 ),
			'blacklist_monitor'    => array( 'sites' => 1, 'checks_per_week' => 1 ),
			'ddos_detection'       => array( 'basic' => true ),
			'global_performance'   => array( 'locations' => 5, 'tests_per_day' => 3 ),
			'keyword_tracker'      => array( 'keywords' => 10 ),
			'broken_link_checker'  => array( 'urls_per_month' => 500 ),
			'ai_writing_assistant' => array( 'requests_per_day' => 10 ),
			'ai_translation'       => array( 'words_per_month' => 10000 ),
			'ai_chatbot'           => array( 'conversations_per_month' => 100 ),
		);
	}

	/**
	 * Deregister site from cloud services.
	 *
	 * @since  1.26031.0000
	 * @return array Result of deregistration.
	 */
	public static function deregister() {
		$result = self::request( 'deregister', array(), 'POST' );

		if ( $result['success'] || 401 === ( $result['code'] ?? 0 ) ) {
			// Clear local credentials even if API call failed
			delete_option( 'wpshadow_cloud_api_key' );
			delete_option( 'wpshadow_cloud_email' );
			delete_option( 'wpshadow_cloud_registered_at' );
			delete_option( 'wpshadow_cloud_free_tier' );

			\WPShadow\Core\Activity_Logger::log(
				'cloud_deregistered',
				array( 'timestamp' => current_time( 'timestamp' ) )
			);

			return array(
				'success' => true,
				'message' => __( 'Successfully deregistered from WPShadow Cloud.', 'wpshadow' ),
			);
		}

		return $result;
	}
}
