<?php
/**
 * API-First Architecture Diagnostic
 *
 * Tests whether the site implements API-first architecture with public API availability.
 *
 * @since   1.6034.0200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * API-First Architecture Diagnostic Class
 *
 * API-first architecture enables headless implementations, third-party integrations,
 * and multi-platform experiences, providing flexibility and scalability.
 *
 * @since 1.6034.0200
 */
class Diagnostic_Api_First_Architecture extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'api-first-architecture';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'API-First Architecture';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site implements API-first architecture with public API availability';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'technical-architecture';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6034.0200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues    = array();
		$api_score = 0;
		$max_score = 7;

		// Check if REST API is enabled.
		$rest_enabled = self::check_rest_api_enabled();
		if ( ! $rest_enabled ) {
			$issues[] = __( 'WordPress REST API is disabled', 'wpshadow' );
		} else {
			++$api_score;
		}

		// Check for custom API endpoints.
		$custom_endpoints = self::count_custom_api_endpoints();
		if ( $custom_endpoints > 0 ) {
			++$api_score;
		} else {
			$issues[] = __( 'No custom API endpoints registered', 'wpshadow' );
		}

		// Check for API authentication.
		$has_authentication = self::check_api_authentication();
		if ( $has_authentication ) {
			++$api_score;
		} else {
			$issues[] = __( 'No API authentication method configured', 'wpshadow' );
		}

		// Check for API documentation.
		$has_documentation = self::check_api_documentation();
		if ( $has_documentation ) {
			++$api_score;
		} else {
			$issues[] = __( 'No API documentation available for developers', 'wpshadow' );
		}

		// Check for API versioning.
		$has_versioning = self::check_api_versioning();
		if ( $has_versioning ) {
			++$api_score;
		} else {
			$issues[] = __( 'API versioning not implemented', 'wpshadow' );
		}

		// Check for rate limiting.
		$has_rate_limiting = self::check_rate_limiting();
		if ( $has_rate_limiting ) {
			++$api_score;
		} else {
			$issues[] = __( 'No API rate limiting to prevent abuse', 'wpshadow' );
		}

		// Check for CORS configuration.
		$has_cors = self::check_cors_configuration();
		if ( $has_cors ) {
			++$api_score;
		} else {
			$issues[] = __( 'CORS not configured for cross-origin API requests', 'wpshadow' );
		}

		// Determine severity based on API implementation.
		$api_percentage = ( $api_score / $max_score ) * 100;

		if ( $api_percentage < 30 ) {
			// Minimal or no API implementation.
			$severity     = 'medium';
			$threat_level = 50;
		} elseif ( $api_percentage < 60 ) {
			// Basic API implementation.
			$severity     = 'low';
			$threat_level = 30;
		} else {
			// Good API implementation - no issue.
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: API implementation percentage */
				__( 'API implementation at %d%%. ', 'wpshadow' ),
				(int) $api_percentage
			) . implode( '. ', $issues );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/api-first-architecture',
			);
		}

		return null;
	}

	/**
	 * Check if REST API is enabled.
	 *
	 * @since  1.6034.0200
	 * @return bool True if REST API is enabled, false otherwise.
	 */
	private static function check_rest_api_enabled() {
		// Check if REST API is disabled by filter.
		$rest_enabled = apply_filters( 'rest_enabled', true );
		if ( ! $rest_enabled ) {
			return false;
		}

		// Check if REST API endpoints are accessible.
		$rest_url = rest_url();
		return ! empty( $rest_url );
	}

	/**
	 * Count custom API endpoints registered.
	 *
	 * @since  1.6034.0200
	 * @return int Number of custom endpoints.
	 */
	private static function count_custom_api_endpoints() {
		global $wp_rest_server;

		if ( ! isset( $wp_rest_server ) ) {
			rest_get_server();
		}

		$routes           = rest_get_server()->get_routes();
		$custom_endpoints = 0;

		// Count non-core endpoints.
		foreach ( $routes as $route => $handlers ) {
			// Skip core WordPress endpoints.
			if ( strpos( $route, '/wp/' ) === 0 || strpos( $route, '/wp-' ) === 0 ) {
				continue;
			}
			++$custom_endpoints;
		}

		return apply_filters( 'wpshadow_custom_api_endpoint_count', $custom_endpoints );
	}

	/**
	 * Check if API authentication is configured.
	 *
	 * @since  1.6034.0200
	 * @return bool True if authentication is configured, false otherwise.
	 */
	private static function check_api_authentication() {
		// Check for JWT authentication plugins.
		$auth_plugins = array(
			'jwt-authentication-for-wp-rest-api/jwt-auth.php' => 'JWT Authentication',
			'wp-rest-api-authentication/wp-rest-api-authentication.php' => 'WP REST API Authentication',
			'application-passwords/application-passwords.php' => 'Application Passwords',
		);

		foreach ( $auth_plugins as $plugin_path => $plugin_name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		// Check for Application Passwords (core in WP 5.6+).
		if ( function_exists( 'wp_is_application_passwords_available' ) && wp_is_application_passwords_available() ) {
			return true;
		}

		return apply_filters( 'wpshadow_has_api_authentication', false );
	}

	/**
	 * Check if API documentation exists.
	 *
	 * @since  1.6034.0200
	 * @return bool True if documentation exists, false otherwise.
	 */
	private static function check_api_documentation() {
		// Check for common API documentation pages.
		$doc_slugs = array( 'api-docs', 'api-documentation', 'developer-api', 'api' );
		foreach ( $doc_slugs as $slug ) {
			$page = get_page_by_path( $slug );
			if ( $page ) {
				return true;
			}
		}

		// Check for API documentation plugins.
		$doc_plugins = array(
			'wp-rest-api-documentation/wp-rest-api-documentation.php',
			'wp-api-swagger-ui/wp-api-swagger-ui.php',
		);

		foreach ( $doc_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_api_documentation', false );
	}

	/**
	 * Check if API versioning is implemented.
	 *
	 * @since  1.6034.0200
	 * @return bool True if versioning is implemented, false otherwise.
	 */
	private static function check_api_versioning() {
		$routes = rest_get_server()->get_routes();

		// Check if any custom routes use versioning (e.g., /v1/, /v2/).
		foreach ( $routes as $route => $handlers ) {
			if ( preg_match( '#/v\d+/#', $route ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_api_versioning', false );
	}

	/**
	 * Check if rate limiting is configured.
	 *
	 * @since  1.6034.0200
	 * @return bool True if rate limiting exists, false otherwise.
	 */
	private static function check_rate_limiting() {
		// Check for rate limiting plugins.
		$rate_limit_plugins = array(
			'wp-rest-api-rate-limit/wp-rest-api-rate-limit.php',
		);

		foreach ( $rate_limit_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		// Check if rate limiting filter is used.
		$has_filter = has_filter( 'rest_request_before_callbacks' );

		return apply_filters( 'wpshadow_has_api_rate_limiting', $has_filter );
	}

	/**
	 * Check if CORS is configured.
	 *
	 * @since  1.6034.0200
	 * @return bool True if CORS is configured, false otherwise.
	 */
	private static function check_cors_configuration() {
		// Check if CORS headers are being sent.
		$has_cors_filter = has_filter( 'rest_pre_serve_request' );

		return apply_filters( 'wpshadow_has_cors_configuration', $has_cors_filter );
	}
}
