<?php
/**
 * Security API Service Manager
 *
 * Handles interactions with external security APIs and connection testing.
 *
 * @package    WPShadow
 * @subpackage Core\Security
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Core\Security;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Security API Service Manager Class
 *
 * Provides unified interface for testing and managing security API integrations.
 *
 * @since 0.6093.1200
 */
class Security_API_Manager {

	/**
	 * Get decrypted API key for a service
	 *
	 * @since 0.6093.1200
	 * @param  string $service Service name (wpscan, hibp, etc).
	 * @return string Decrypted API key or empty string.
	 */
	public static function get_api_key( $service ) {
		$encrypted = get_option( "wpshadow_{$service}_api_key", '' );

		if ( empty( $encrypted ) ) {
			return '';
		}

		$decrypted = openssl_decrypt(
			$encrypted,
			'AES-256-CBC',
			wp_salt( 'auth' ),
			0,
			substr( wp_salt( 'secure_auth' ), 0, 16 )
		);

		return $decrypted ? $decrypted : '';
	}

	/**
	 * Save encrypted API key
	 *
	 * @since 0.6093.1200
	 * @param  string $service Service name.
	 * @param  string $api_key API key.
	 * @return bool True on success.
	 */
	public static function save_api_key( $service, $api_key ) {
		if ( empty( $service ) || empty( $api_key ) ) {
			return false;
		}

		$encrypted = openssl_encrypt(
			$api_key,
			'AES-256-CBC',
			wp_salt( 'auth' ),
			0,
			substr( wp_salt( 'secure_auth' ), 0, 16 )
		);

		return (bool) update_option( "wpshadow_{$service}_api_key", $encrypted, false );
	}

	/**
	 * Check if service is enabled
	 *
	 * @since 0.6093.1200
	 * @param  string $service Service name.
	 * @return bool True if enabled.
	 */
	public static function is_enabled( $service ) {
		return (bool) get_option( "wpshadow_{$service}_enabled", false );
	}

	/**
	 * Enable or disable a service
	 *
	 * @since 0.6093.1200
	 * @param  string $service Service name.
	 * @param  bool   $enabled Enable or disable.
	 * @return bool True on success.
	 */
	public static function set_enabled( $service, $enabled ) {
		return (bool) update_option( "wpshadow_{$service}_enabled", $enabled ? 1 : 0 );
	}

	/**
	 * Get service configuration
	 *
	 * @since 0.6093.1200
	 * @param  string $service Service name.
	 * @return array {
	 *     @type string $name Service display name.
	 *     @type string $description Service description.
	 *     @type bool   $enabled Is service enabled.
	 *     @type bool   $has_api_key Does service have API key configured.
	 *     @type string $rate_limit Rate limit description.
	 *     @type bool   $privacy_sensitive Is privacy sensitive.
	 * }
	 */
	public static function get_config( $service ) {
		$configs = self::get_all_configs();
		return isset( $configs[ $service ] ) ? $configs[ $service ] : array();
	}

	/**
	 * Get all service configurations
	 *
	 * @since 0.6093.1200
	 * @return array Service configurations.
	 */
	public static function get_all_configs() {
		return array(
			'wpscan' => array(
				'name' => __( 'WPScan Vulnerability Database', 'wpshadow' ),
				'description' => __( 'Check plugins for known security issues', 'wpshadow' ),
				'enabled' => self::is_enabled( 'wpscan' ),
				'has_api_key' => ! empty( self::get_api_key( 'wpscan' ) ),
				'rate_limit' => __( '25 requests/day', 'wpshadow' ),
				'privacy_sensitive' => false,
				'signup_url' => 'https://wpscan.com/register',
				'docs_url' => 'https://wpscan.com/api',
			),
			'hibp' => array(
				'name' => __( 'Have I Been Pwned', 'wpshadow' ),
				'description' => __( 'Check if admin emails were exposed in data breaches', 'wpshadow' ),
				'enabled' => self::is_enabled( 'hibp' ),
				'has_api_key' => false,
				'rate_limit' => __( 'Rate limited', 'wpshadow' ),
				'privacy_sensitive' => true,
				'signup_url' => 'https://haveibeenpwned.com',
				'docs_url' => 'https://haveibeenpwned.com/API/v3',
			),
			'abuseipdb' => array(
				'name' => __( 'AbuseIPDB', 'wpshadow' ),
				'description' => __( 'Check if server IP is on security blacklists', 'wpshadow' ),
				'enabled' => self::is_enabled( 'abuseipdb' ),
				'has_api_key' => ! empty( self::get_api_key( 'abuseipdb' ) ),
				'rate_limit' => __( '1,000 requests/day', 'wpshadow' ),
				'privacy_sensitive' => false,
				'signup_url' => 'https://www.abuseipdb.com/register',
				'docs_url' => 'https://docs.abuseipdb.com/',
			),
			'gsb' => array(
				'name' => __( 'Google Safe Browsing', 'wpshadow' ),
				'description' => __( 'Check external links for phishing and malware', 'wpshadow' ),
				'enabled' => self::is_enabled( 'gsb' ),
				'has_api_key' => ! empty( self::get_api_key( 'gsb' ) ),
				'rate_limit' => __( '10,000 requests/day', 'wpshadow' ),
				'privacy_sensitive' => false,
				'signup_url' => 'https://console.cloud.google.com/',
				'docs_url' => 'https://developers.google.com/safe-browsing',
			),
			'phishtank' => array(
				'name' => __( 'PhishTank', 'wpshadow' ),
				'description' => __( 'Community-verified phishing URL detection', 'wpshadow' ),
				'enabled' => self::is_enabled( 'phishtank' ),
				'has_api_key' => ! empty( self::get_api_key( 'phishtank' ) ),
				'rate_limit' => __( 'Unlimited', 'wpshadow' ),
				'privacy_sensitive' => false,
				'signup_url' => 'https://phishtank.org/register.php',
				'docs_url' => 'https://phishtank.org/api_documentation.php',
			),
		);
	}

	/**
	 * Get all enabled services
	 *
	 * @since 0.6093.1200
	 * @return array List of enabled service names.
	 */
	public static function get_enabled_services() {
		$services = array();
		$configs = self::get_all_configs();

		foreach ( $configs as $key => $config ) {
			if ( $config['enabled'] ) {
				$services[] = $key;
			}
		}

		return $services;
	}

	/**
	 * Cache API response
	 *
	 * @since 0.6093.1200
	 * @param  string $service Service name.
	 * @param  string $cache_key Cache key.
	 * @param  mixed  $data Data to cache.
	 * @param  int    $ttl Time to live in seconds (default 24 hours).
	 * @return bool True on success.
	 */
	public static function set_cache( $service, $cache_key, $data, $ttl = DAY_IN_SECONDS ) {
		$cache_key = "wpshadow_api_{$service}_{$cache_key}";
		return (bool) set_transient( $cache_key, $data, $ttl );
	}

	/**
	 * Get cached API response
	 *
	 * @since 0.6093.1200
	 * @param  string $service Service name.
	 * @param  string $cache_key Cache key.
	 * @return mixed Cached data or false if not found.
	 */
	public static function get_cache( $service, $cache_key ) {
		$cache_key = "wpshadow_api_{$service}_{$cache_key}";
		return get_transient( $cache_key );
	}

	/**
	 * Clear cache for a service
	 *
	 * @since 0.6093.1200
	 * @param  string $service Service name (optional, clears all if not specified).
	 */
	public static function clear_cache( $service = '' ) {
		global $wpdb;

		if ( empty( $service ) ) {
			// Clear all API caches
			$wpdb->query(
				$wpdb->prepare(
					"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
					'_transient_wpshadow_api_%'
				)
			);
		} else {
			// Clear specific service cache
			$wpdb->query(
				$wpdb->prepare(
					"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
					'_transient_wpshadow_api_' . $service . '_%'
				)
			);
		}
	}

	/**
	 * Log API call for audit trail
	 *
	 * @since 0.6093.1200
	 * @param  string $service Service name.
	 * @param  string $action Action performed.
	 * @param  array  $details Additional details.
	 */
	public static function log_call( $service, $action, $details = array() ) {
		if ( ! function_exists( 'WPShadow\Core\Activity_Logger' ) ) {
			return;
		}

		// Log to activity logger if available
		do_action( 'wpshadow_log_api_call', $service, $action, $details );
	}
}
