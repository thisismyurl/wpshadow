<?php
/**
 * Guardian API Client
 *
 * Handles communication with WPShadow Guardian cloud service.
 * Phase 7: Guardian Launch - Plugin Integration
 *
 * @package    WPShadow
 * @subpackage Guardian
 * @since      1.6004.0300
 */

declare(strict_types=1);

namespace WPShadow\Guardian;

use WPShadow\Privacy\Consent_Preferences;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Guardian API Client Class
 *
 * Provides interface to WPShadow Guardian cloud scanning services.
 * Handles authentication, token management, and scan requests.
 *
 * @since 1.6004.0300
 */
class Guardian_API_Client {

	/**
	 * Guardian API base URL.
	 *
	 * @since 1.6004.0300
	 * @var string
	 */
	const API_BASE_URL = 'https://guardian.wpshadow.com/api/v1';

	/**
	 * API version.
	 *
	 * @since 1.6004.0300
	 * @var string
	 */
	const API_VERSION = '1.0';

	/**
	 * Check if Guardian is available.
	 *
	 * @since  1.6004.0300
	 * @return bool True if Guardian service is reachable.
	 */
	public static function is_available() {
		$status = \WPShadow\Core\Cache_Manager::get(
			'guardian_status',
			'wpshadow_guardian'
		);

		if ( false !== $status ) {
			return 'available' === $status;
		}

		// Check service status
		$response = wp_remote_get(
			self::API_BASE_URL . '/status',
			array(
				'timeout' => 5,
				'headers' => array(
					'User-Agent' => self::get_user_agent(),
				),
			)
		);

		$is_available = ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response );

		// Cache status for 5 minutes
		\WPShadow\Core\Cache_Manager::set(
			'guardian_status',
			$is_available ? 'available' : 'unavailable',
			5 * MINUTE_IN_SECONDS,
			'wpshadow_guardian'
			);

		return $is_available;
	}

	/**
	 * Check if user is connected to Guardian.
	 *
	 * @since  1.6004.0300
	 * @return bool True if API key exists and is valid.
	 */
	public static function is_connected() {
		$api_key = self::get_api_key();

		if ( empty( $api_key ) ) {
			return false;
		}

		// Check if key is still valid (cached)
		$validation = \WPShadow\Core\Cache_Manager::get(
			'guardian_key_valid',
			'wpshadow_guardian'
		);

		if ( false !== $validation ) {
			return (bool) $validation;
		}

		// Validate key with API
		$is_valid = self::validate_api_key( $api_key );

		// Cache validation for 1 hour
		\WPShadow\Core\Cache_Manager::set(
			'guardian_key_valid',
			$is_valid,
			HOUR_IN_SECONDS,
			'wpshadow_guardian'
			);

		return $is_valid;
	}

	/**
	 * Get stored API key.
	 *
	 * @since  1.6004.0300
	 * @return string API key or empty string.
	 */
	public static function get_api_key() {
		return get_option( 'wpshadow_guardian_api_key', '' );
	}

	/**
	 * Store API key.
	 *
	 * @since  1.6004.0300
	 * @param  string $api_key API key to store.
	 * @return bool True on success.
	 */
	public static function set_api_key( $api_key ) {
		// Clear validation cache when key changes
		\WPShadow\Core\Cache_Manager::delete(
			'guardian_key_valid',
			'wpshadow_guardian'
		);

		return update_option( 'wpshadow_guardian_api_key', sanitize_text_field( $api_key ) );
	}

	/**
	 * Disconnect from Guardian (remove API key).
	 *
	 * @since  1.6004.0300
	 * @return bool True on success.
	 */
	public static function disconnect() {
		delete_option( 'wpshadow_guardian_api_key' );
		\WPShadow\Core\Cache_Manager::delete( 'guardian_key_valid', 'wpshadow_guardian' );
		\WPShadow\Core\Cache_Manager::delete( 'guardian_account_info', 'wpshadow_guardian' );

		// Log activity
		if ( class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
			\WPShadow\Core\Activity_Logger::log(
				'guardian_disconnected',
				'Disconnected from Guardian cloud service'
			);
		}

		return true;
	}

	/**
	 * Validate API key with Guardian service.
	 *
	 * @since  1.6004.0300
	 * @param  string $api_key API key to validate.
	 * @return bool True if valid.
	 */
	public static function validate_api_key( $api_key ) {
		$response = self::api_request(
			'/account/validate',
			array(
				'method'  => 'POST',
				'body'    => array( 'api_key' => $api_key ),
				'timeout' => 10,
			)
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		return isset( $response['valid'] ) && $response['valid'];
	}

	/**
	 * Get account information from Guardian.
	 *
	 * @since  1.6004.0300
	 * @return array|WP_Error Account info or error.
	 */
	public static function get_account_info() {
		if ( ! self::is_connected() ) {
			return new \WP_Error( 'not_connected', __( 'Not connected to Guardian', 'wpshadow' ) );
		}

		// Check cache first
		$cached = \WPShadow\Core\Cache_Manager::get(
			'guardian_account_info',
			'wpshadow_guardian'
		);
		if ( false !== $cached ) {
			return $cached;
		}

		$response = self::api_request( '/account/info' );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		// Cache for 15 minutes
		\WPShadow\Core\Cache_Manager::set(
			'guardian_account_info',
			$response,
			15 * MINUTE_IN_SECONDS,
			'wpshadow_guardian'
			);

		return $response;
	}

	/**
	 * Get token balance.
	 *
	 * @since  1.6004.0300
	 * @return int|WP_Error Token balance or error.
	 */
	public static function get_token_balance() {
		$account = self::get_account_info();

		if ( is_wp_error( $account ) ) {
			return $account;
		}

		return isset( $account['token_balance'] ) ? (int) $account['token_balance'] : 0;
	}

	/**
	 * Request a Guardian scan.
	 *
	 * @since  1.6004.0300
	 * @param  string $scan_type Type of scan: 'security', 'performance', 'seo', 'full'.
	 * @param  array  $options Scan options.
	 * @return array|WP_Error Scan result or error.
	 */
	public static function request_scan( $scan_type, $options = array() ) {
		if ( ! self::is_connected() ) {
			return new \WP_Error( 'not_connected', __( 'Not connected to Guardian. Please connect your account first.', 'wpshadow' ) );
		}

		// Check if user has consented to external services
		$user_id = get_current_user_id();
		$prefs   = Consent_Preferences::get_preferences( $user_id );

		if ( ! $prefs['anonymized_telemetry'] ) {
			return new \WP_Error(
				'consent_required',
				__( 'Guardian scans require sending site data to our cloud service. Please enable "Anonymous Usage Data" in Privacy Settings.', 'wpshadow' )
			);
		}

		// Prepare site data
		$site_data = self::prepare_site_data( $scan_type );

		$response = self::api_request(
			'/scans/request',
			array(
				'method'  => 'POST',
				'body'    => array(
					'scan_type' => $scan_type,
					'site_data' => $site_data,
					'options'   => $options,
				),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		// Store scan ID for tracking
		if ( isset( $response['scan_id'] ) ) {
			self::store_scan_record( $response['scan_id'], $scan_type );
		}

		// Clear token balance cache
		\WPShadow\Core\Cache_Manager::delete(
			'guardian_account_info',
			'wpshadow_guardian'
		);

		// Log activity
		if ( class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
			\WPShadow\Core\Activity_Logger::log(
				'guardian_scan_requested',
				sprintf( 'Guardian %s scan requested', $scan_type ),
				'',
				array(
					'scan_type' => $scan_type,
					'scan_id'   => $response['scan_id'] ?? '',
				)
			);
		}

		return $response;
	}

	/**
	 * Get scan results.
	 *
	 * @since  1.6004.0300
	 * @param  string $scan_id Scan ID.
	 * @return array|WP_Error Scan results or error.
	 */
	public static function get_scan_results( $scan_id ) {
		if ( ! self::is_connected() ) {
			return new \WP_Error( 'not_connected', __( 'Not connected to Guardian', 'wpshadow' ) );
		}

		$response = self::api_request( '/scans/' . sanitize_text_field( $scan_id ) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		return $response;
	}

	/**
	 * Make API request to Guardian.
	 *
	 * @since  1.6004.0300
	 * @param  string $endpoint API endpoint (without base URL).
	 * @param  array  $args Request arguments.
	 * @return array|WP_Error Response data or error.
	 */
	private static function api_request( $endpoint, $args = array() ) {
		$api_key = self::get_api_key();
		$url     = self::API_BASE_URL . $endpoint;

		$default_args = array(
			'timeout' => 15,
			'headers' => array(
				'Authorization' => 'Bearer ' . $api_key,
				'Content-Type'  => 'application/json',
				'User-Agent'    => self::get_user_agent(),
				'X-WPShadow-Version' => WPSHADOW_VERSION,
			),
		);

		$args = wp_parse_args( $args, $default_args );

		// Convert body to JSON if it's an array
		if ( isset( $args['body'] ) && is_array( $args['body'] ) ) {
			$args['body'] = wp_json_encode( $args['body'] );
		}

		$response = wp_remote_request( $url, $args );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		// Handle errors
		if ( $code >= 400 ) {
			$message = isset( $data['message'] ) ? $data['message'] : __( 'Unknown API error', 'wpshadow' );
			return new \WP_Error( 'api_error', $message, array( 'status' => $code ) );
		}

		return $data;
	}

	/**
	 * Prepare site data for scanning.
	 *
	 * Only sends non-sensitive information needed for analysis.
	 *
	 * @since  1.6004.0300
	 * @param  string $scan_type Type of scan.
	 * @return array Site data.
	 */
	private static function prepare_site_data( $scan_type ) {
		global $wp_version;

		$data = array(
			'wp_version'   => $wp_version,
			'php_version'  => PHP_VERSION,
			'site_url'     => wp_hash( home_url() ), // Hashed for privacy
			'active_theme' => get_option( 'stylesheet' ),
			'scan_type'    => $scan_type,
		);

		// Add plugins list (name and version only, no file paths)
		if ( in_array( $scan_type, array( 'security', 'full' ), true ) ) {
			$plugins = array();
			foreach ( get_plugins() as $plugin_file => $plugin_data ) {
				$plugins[] = array(
					'name'    => $plugin_data['Name'],
					'version' => $plugin_data['Version'],
					'active'  => is_plugin_active( $plugin_file ),
				);
			}
			$data['plugins'] = $plugins;
		}

		// Add performance metrics
		if ( in_array( $scan_type, array( 'performance', 'full' ), true ) ) {
			$data['performance'] = array(
				'memory_limit'    => WP_MEMORY_LIMIT,
				'max_memory_used' => WP_MAX_MEMORY_LIMIT,
			);
		}

		return $data;
	}

	/**
	 * Store scan record in database.
	 *
	 * @since  1.6004.0300
	 * @param  string $scan_id Scan ID.
	 * @param  string $scan_type Scan type.
	 * @return void
	 */
	private static function store_scan_record( $scan_id, $scan_type ) {
		$scans   = get_option( 'wpshadow_guardian_scans', array() );
		$scans[] = array(
			'scan_id'   => $scan_id,
			'scan_type' => $scan_type,
			'requested' => current_time( 'mysql' ),
			'status'    => 'pending',
		);

		// Keep only last 50 scans
		$scans = array_slice( $scans, -50 );

		update_option( 'wpshadow_guardian_scans', $scans );
	}

	/**
	 * Get user agent string.
	 *
	 * @since  1.6004.0300
	 * @return string User agent.
	 */
	private static function get_user_agent() {
		return sprintf(
			'WPShadow/%s; %s',
			WPSHADOW_VERSION,
			home_url()
		);
	}

	/**
	 * Get recent scans.
	 *
	 * @since  1.6004.0300
	 * @param  int $limit Number of scans to return.
	 * @return array Recent scans.
	 */
	public static function get_recent_scans( $limit = 10 ) {
		$scans = get_option( 'wpshadow_guardian_scans', array() );
		return array_slice( array_reverse( $scans ), 0, $limit );
	}

	/**
	 * Get pricing information.
	 *
	 * @since  1.6004.0300
	 * @return array Pricing tiers.
	 */
	public static function get_pricing() {
		return array(
			'free' => array(
				'name'   => __( 'Free Tier', 'wpshadow' ),
				'tokens' => 100,
				'price'  => 0,
				'period' => 'month',
			),
			'starter' => array(
				'name'   => __( 'Starter Pack', 'wpshadow' ),
				'tokens' => 500,
				'price'  => 20,
				'period' => 'one-time',
			),
			'pro' => array(
				'name'   => __( 'Pro Pack', 'wpshadow' ),
				'tokens' => 2000,
				'price'  => 60,
				'period' => 'one-time',
			),
			'unlimited' => array(
				'name'   => __( 'Guardian Pro', 'wpshadow' ),
				'tokens' => 'unlimited',
				'price'  => 19,
				'period' => 'month',
			),
		);
	}
}
