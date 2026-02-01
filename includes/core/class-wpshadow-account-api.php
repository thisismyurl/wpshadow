<?php
/**
 * WPShadow Account API Client
 *
 * Central API client for wpshadow.com account system.
 * Handles registration, authentication, and account management
 * for Guardian, Vault, Cloud Services, and Pro features.
 *
 * Philosophy: "Register, Don't Pay" (Commandment #3)
 * - Registration is FREE and creates a unified account
 * - Each service (Guardian, Vault, Cloud) has generous free tier
 * - One account across all WPShadow services
 * - No payment required until user needs more than free tier
 *
 * @package    WPShadow
 * @subpackage Core
 * @since      1.6032.0000
 */

declare(strict_types=1);

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPShadow Account API Class
 *
 * Centralized account management for all WPShadow cloud services.
 *
 * @since 1.6032.0000
 */
class WPShadow_Account_API {

	/**
	 * API Base URL
	 *
	 * @var string
	 */
	const API_BASE_URL = 'https://account.wpshadow.com/api/v1';

	/**
	 * API Version
	 *
	 * @var string
	 */
	const API_VERSION = '1.0';

	/**
	 * Check if account service is available.
	 *
	 * @since  1.6032.0000
	 * @return bool True if service is reachable.
	 */
	public static function is_available() {
		$cached = Cache_Manager::get( 'account_service_status', 'wpshadow_account' );
		if ( false !== $cached ) {
			return 'available' === $cached;
		}

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

		// Cache for 5 minutes.
		Cache_Manager::set(
			'account_service_status',
			$is_available ? 'available' : 'unavailable',
			'wpshadow_account',
			5 * MINUTE_IN_SECONDS
		);

		return $is_available;
	}

	/**
	 * Check if user is registered.
	 *
	 * @since  1.6032.0000
	 * @return bool True if registered.
	 */
	public static function is_registered() {
		$api_key = self::get_api_key();
		return ! empty( $api_key );
	}

	/**
	 * Get stored API key.
	 *
	 * @since  1.6032.0000
	 * @return string API key or empty string.
	 */
	public static function get_api_key() {
		return Settings_Registry::get( 'wpshadow_account_api_key', '' );
	}

	/**
	 * Store API key.
	 *
	 * @since  1.6032.0000
	 * @param  string $api_key API key to store.
	 * @return bool True on success.
	 */
	public static function set_api_key( $api_key ) {
		// Clear validation cache when key changes.
		Cache_Manager::delete( 'account_key_valid', 'wpshadow_account' );
		Cache_Manager::delete( 'account_info', 'wpshadow_account' );

		return Settings_Registry::set( 'wpshadow_account_api_key', sanitize_text_field( $api_key ) );
	}

	/**
	 * Register new WPShadow account.
	 *
	 * Creates unified account that works across Guardian, Vault,
	 * Cloud Services, and Pro features. Free tier always.
	 *
	 * @since  1.6032.0000
	 * @param  string $email    User email address.
	 * @param  string $password User password (min 8 chars).
	 * @return array {
	 *     Registration result.
	 *
	 *     @type bool   $success Whether registration succeeded.
	 *     @type string $api_key API key if successful.
	 *     @type string $message Human-readable message.
	 *     @type array  $services Available services and free tier limits.
	 * }
	 */
	public static function register( $email, $password ) {
		// Validate inputs.
		if ( ! is_email( $email ) ) {
			return array(
				'success' => false,
				'message' => __( 'Invalid email address', 'wpshadow' ),
			);
		}

		if ( strlen( $password ) < 8 ) {
			return array(
				'success' => false,
				'message' => __( 'Password must be at least 8 characters', 'wpshadow' ),
			);
		}

		// Prepare site data.
		$site_data = array(
			'email'          => sanitize_email( $email ),
			'password'       => $password, // Sent over HTTPS, hashed on server.
			'site_url'       => esc_url_raw( site_url() ),
			'site_name'      => sanitize_text_field( get_bloginfo( 'name' ) ),
			'wp_version'     => get_bloginfo( 'version' ),
			'php_version'    => PHP_VERSION,
			'plugin_version' => defined( 'WPSHADOW_VERSION' ) ? WPSHADOW_VERSION : '1.0.0',
		);

		// Make registration request.
		$response = wp_remote_post(
			self::API_BASE_URL . '/register',
			array(
				'timeout' => 30,
				'headers' => array(
					'Content-Type' => 'application/json',
					'User-Agent'   => self::get_user_agent(),
				),
				'body'    => wp_json_encode( $site_data ),
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

		$body        = json_decode( wp_remote_retrieve_body( $response ), true );
		$status_code = wp_remote_retrieve_response_code( $response );

		if ( 201 === $status_code && ! empty( $body['success'] ) && ! empty( $body['api_key'] ) ) {
			// Store credentials.
			self::set_api_key( $body['api_key'] );
			Settings_Registry::set( 'wpshadow_account_email', $email );
			Settings_Registry::set( 'wpshadow_account_registered_at', current_time( 'timestamp' ) );

			// Store service status.
			if ( ! empty( $body['services'] ) ) {
				Settings_Registry::set( 'wpshadow_account_services', $body['services'] );
			}

			// Log activity.
			Activity_Logger::log(
				'wpshadow_account_registered',
				array(
					'email'    => $email,
					'site_url' => site_url(),
					'services' => $body['services'] ?? array(),
				)
			);

			return array(
				'success'  => true,
				'api_key'  => $body['api_key'],
				'message'  => __( 'Welcome to WPShadow! Your free account is ready.', 'wpshadow' ),
				'services' => $body['services'] ?? array(),
			);
		}

		return array(
			'success' => false,
			'message' => $body['message'] ?? __( 'Registration failed. Please try again.', 'wpshadow' ),
		);
	}

	/**
	 * Connect existing account with API key.
	 *
	 * For users who already have a WPShadow account.
	 *
	 * @since  1.6032.0000
	 * @param  string $api_key API key from account.wpshadow.com.
	 * @return array {
	 *     Connection result.
	 *
	 *     @type bool   $success Whether connection succeeded.
	 *     @type string $message Human-readable message.
	 *     @type array  $account Account information if successful.
	 * }
	 */
	public static function connect( $api_key ) {
		// Validate API key.
		$is_valid = self::validate_api_key( $api_key );

		if ( ! $is_valid ) {
			return array(
				'success' => false,
				'message' => __( 'Invalid API key', 'wpshadow' ),
			);
		}

		// Store API key.
		self::set_api_key( $api_key );

		// Fetch account information.
		$account_info = self::get_account_info( true );

		if ( is_wp_error( $account_info ) ) {
			return array(
				'success' => false,
				'message' => __( 'Failed to fetch account information', 'wpshadow' ),
			);
		}

		// Store email.
		if ( ! empty( $account_info['email'] ) ) {
			Settings_Registry::set( 'wpshadow_account_email', $account_info['email'] );
		}

		// Log activity.
		Activity_Logger::log(
			'wpshadow_account_connected',
			array(
				'site_url' => site_url(),
				'email'    => $account_info['email'] ?? '',
			)
		);

		return array(
			'success' => true,
			'message' => __( 'Account connected successfully!', 'wpshadow' ),
			'account' => $account_info,
		);
	}

	/**
	 * Disconnect account.
	 *
	 * Removes API key but keeps local data.
	 *
	 * @since  1.6032.0000
	 * @return array Result of disconnection.
	 */
	public static function disconnect() {
		Settings_Registry::set( 'wpshadow_account_api_key', '' );
		Settings_Registry::set( 'wpshadow_account_email', '' );

		// Clear all caches.
		Cache_Manager::delete( 'account_key_valid', 'wpshadow_account' );
		Cache_Manager::delete( 'account_info', 'wpshadow_account' );
		Cache_Manager::delete( 'account_service_status', 'wpshadow_account' );

		// Log activity.
		Activity_Logger::log(
			'wpshadow_account_disconnected',
			array( 'site_url' => site_url() )
		);

		return array(
			'success' => true,
			'message' => __( 'Account disconnected. Your local data is safe.', 'wpshadow' ),
		);
	}

	/**
	 * Validate API key with account service.
	 *
	 * @since  1.6032.0000
	 * @param  string $api_key API key to validate.
	 * @return bool True if valid.
	 */
	public static function validate_api_key( $api_key ) {
		// Check cache first.
		$cache_key = 'account_key_valid_' . md5( $api_key );
		$cached    = Cache_Manager::get( $cache_key, 'wpshadow_account' );

		if ( false !== $cached ) {
			return (bool) $cached;
		}

		$response = wp_remote_post(
			self::API_BASE_URL . '/validate',
			array(
				'timeout' => 15,
				'headers' => array(
					'Authorization' => 'Bearer ' . $api_key,
					'User-Agent'    => self::get_user_agent(),
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$body     = json_decode( wp_remote_retrieve_body( $response ), true );
		$is_valid = isset( $body['valid'] ) && $body['valid'];

		// Cache for 1 hour.
		Cache_Manager::set(
			$cache_key,
			$is_valid,
			'wpshadow_account',
			HOUR_IN_SECONDS
		);

		return $is_valid;
	}

	/**
	 * Get account information.
	 *
	 * @since  1.6032.0000
	 * @param  bool $force_refresh Force refresh from API.
	 * @return array|\WP_Error Account info or error.
	 */
	public static function get_account_info( $force_refresh = false ) {
		if ( ! $force_refresh ) {
			$cached = Cache_Manager::get( 'account_info', 'wpshadow_account' );
			if ( false !== $cached ) {
				return $cached;
			}
		}

		$api_key = self::get_api_key();
		if ( empty( $api_key ) ) {
			return new \WP_Error( 'no_api_key', __( 'No API key stored', 'wpshadow' ) );
		}

		$response = wp_remote_get(
			self::API_BASE_URL . '/account',
			array(
				'timeout' => 15,
				'headers' => array(
					'Authorization' => 'Bearer ' . $api_key,
					'User-Agent'    => self::get_user_agent(),
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( empty( $body['account'] ) ) {
			return new \WP_Error( 'invalid_response', __( 'Invalid API response', 'wpshadow' ) );
		}

		$account_info = $body['account'];

		// Cache for 15 minutes.
		Cache_Manager::set(
			'account_info',
			$account_info,
			'wpshadow_account',
			15 * MINUTE_IN_SECONDS
		);

		return $account_info;
	}

	/**
	 * Get service status and free tier limits.
	 *
	 * @since  1.6032.0000
	 * @return array {
	 *     Service information.
	 *
	 *     @type array $guardian Guardian status and free tier.
	 *     @type array $vault Vault status and free tier.
	 *     @type array $cloud Cloud Services status and free tier.
	 * }
	 */
	public static function get_services_status() {
		$account_info = self::get_account_info();

		if ( is_wp_error( $account_info ) ) {
			return self::get_default_service_limits();
		}

		return $account_info['services'] ?? self::get_default_service_limits();
	}

	/**
	 * Get default service limits (free tier).
	 *
	 * @since  1.6032.0000
	 * @return array Service limits.
	 */
	private static function get_default_service_limits() {
		return array(
			'guardian' => array(
				'tier'                => 'free',
				'tokens_per_month'    => 100,
				'tokens_current'      => 100,
				'tokens_reset_date'   => date( 'Y-m-d', strtotime( 'first day of next month' ) ),
				'scan_types'          => array( 'security', 'performance', 'seo', 'full' ),
				'email_notifications' => false,
			),
			'vault'    => array(
				'tier'            => 'free',
				'max_backups'     => 3,
				'retention_days'  => 7,
				'storage_limit'   => 1, // GB
				'storage_used'    => 0,
				'email_alerts'    => false,
			),
			'cloud'    => array(
				'tier'                  => 'free',
				'uptime_checks'         => 100,
				'ssl_checks'            => 100,
				'domain_checks'         => 100,
				'ai_scans_per_month'    => 50,
				'email_notifications'   => false,
			),
		);
	}

	/**
	 * Make authenticated API request.
	 *
	 * @since  1.6032.0000
	 * @param  string $endpoint API endpoint (e.g., '/account').
	 * @param  array  $args Request arguments.
	 * @return array|\WP_Error Response body or error.
	 */
	public static function api_request( $endpoint, $args = array() ) {
		$api_key = self::get_api_key();
		if ( empty( $api_key ) ) {
			return new \WP_Error( 'no_api_key', __( 'Not registered with WPShadow', 'wpshadow' ) );
		}

		$defaults = array(
			'timeout' => 30,
			'headers' => array(
				'Authorization' => 'Bearer ' . $api_key,
				'Content-Type'  => 'application/json',
				'User-Agent'    => self::get_user_agent(),
			),
		);

		$args = wp_parse_args( $args, $defaults );

		// Make request.
		$url      = self::API_BASE_URL . $endpoint;
		$method   = $args['method'] ?? 'GET';
		$response = wp_remote_request( $url, array_merge( $args, array( 'method' => $method ) ) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		return $body;
	}

	/**
	 * Get user agent string.
	 *
	 * @since  1.6032.0000
	 * @return string User agent.
	 */
	private static function get_user_agent() {
		return sprintf(
			'WPShadow/%s; %s',
			defined( 'WPSHADOW_VERSION' ) ? WPSHADOW_VERSION : '1.0.0',
			home_url()
		);
	}

	/**
	 * Sync account data across services.
	 *
	 * Updates Guardian, Vault, and Cloud Services with current account status.
	 *
	 * @since  1.6032.0000
	 * @return bool True if sync successful.
	 */
	public static function sync_services() {
		if ( ! self::is_registered() ) {
			return false;
		}

		$services = self::get_services_status();

		// Sync Guardian.
		if ( class_exists( '\WPShadow\Guardian\Guardian_API_Client' ) ) {
			// Guardian will use central account API key.
			$api_key = self::get_api_key();
			\WPShadow\Guardian\Guardian_API_Client::set_api_key( $api_key );
		}

		// Sync Vault.
		if ( class_exists( '\WPShadow\Vault\Vault_Manager' ) ) {
			Settings_Registry::set( 'vault_api_key', self::get_api_key() );
		}

		// Sync Cloud Services.
		if ( class_exists( '\WPShadow\Integration\Cloud\Cloud_Service_Connector' ) ) {
			update_option( 'wpshadow_cloud_api_key', self::get_api_key() );
		}

		// Log sync.
		Activity_Logger::log(
			'wpshadow_services_synced',
			array(
				'services' => array_keys( $services ),
			)
		);

		return true;
	}
}
