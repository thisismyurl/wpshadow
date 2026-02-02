<?php
/**
 * Media API Rate Limiting Diagnostic
 *
 * Checks if rate limiting is configured for media API endpoints.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Media API Rate Limiting Diagnostic Class
 *
 * Verifies that REST API media endpoints have rate limiting
 * to prevent abuse and excessive resource consumption.
 *
 * @since 1.26033.0000
 */
class Diagnostic_Media_Api_Rate_Limiting extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-api-rate-limiting';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Media API Rate Limiting';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if rate limiting is configured for media API endpoints';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for rate limiting plugins.
		$rate_limit_plugins = array(
			'wp-rest-api-controller/wp-rest-api-controller.php',
			'disable-json-api/disable-json-api.php',
			'jwt-authentication-for-wp-rest-api/jwt-auth.php',
		);

		$has_rate_limiter = false;
		foreach ( $rate_limit_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_rate_limiter = true;
				break;
			}
		}

		if ( ! $has_rate_limiter ) {
			$issues[] = __( 'No rate limiting plugin detected for REST API', 'wpshadow' );
		}

		// Check for custom rate limiting filters.
		$has_custom_limiter = has_filter( 'rest_pre_dispatch' ) || has_filter( 'rest_request_before_callbacks' );
		if ( ! $has_custom_limiter && ! $has_rate_limiter ) {
			$issues[] = __( 'No custom rate limiting implementation detected', 'wpshadow' );
		}

		// Check if WordPress transients API is available for rate tracking.
		if ( ! function_exists( 'set_transient' ) || ! function_exists( 'get_transient' ) ) {
			$issues[] = __( 'Transients API not available for rate limit tracking', 'wpshadow' );
		}

		// Check if object caching is available for better performance.
		$has_object_cache = wp_using_ext_object_cache();
		if ( ! $has_object_cache ) {
			// Not critical but recommended.
			$issues[] = __( 'External object cache not available (recommended for efficient rate limiting)', 'wpshadow' );
		}

		// Check for IP detection capability.
		if ( empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$issues[] = __( 'Cannot detect client IP address for rate limiting', 'wpshadow' );
		}

		// Check if behind proxy with proper forwarding.
		if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) && empty( $_SERVER['HTTP_X_REAL_IP'] ) ) {
			// Might indicate proxy but no proper IP forwarding.
			$issues[] = __( 'Site appears to be behind proxy without proper IP forwarding configuration', 'wpshadow' );
		}

		// Check for REST API namespace protection.
		$server = rest_get_server();
		if ( $server ) {
			$namespaces = $server->get_namespaces();
			if ( in_array( 'wp/v2', $namespaces, true ) ) {
				// Core namespace exists - should be protected.
				$has_namespace_filter = has_filter( 'rest_namespace_index' );
				if ( ! $has_namespace_filter ) {
					// Not critical but good to have.
				}
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-api-rate-limiting',
			);
		}

		return null;
	}
}
