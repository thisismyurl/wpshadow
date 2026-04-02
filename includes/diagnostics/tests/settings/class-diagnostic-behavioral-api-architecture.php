<?php
/**
 * Behavioral API Architecture Diagnostic
 *
 * Checks if the site implements proper API architecture patterns including
 * RESTful design, versioning, documentation, rate limiting, and error handling.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Behavioral_API_Architecture Class
 *
 * Verifies that the site implements proper REST API architecture patterns
 * following WordPress and industry best practices.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Behavioral_API_Architecture extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'behavioral-api-architecture';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'API Architecture Patterns';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if the site implements proper REST API architecture patterns';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'developer';

	/**
	 * Run the API architecture diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if API architecture issues detected, null otherwise.
	 */
	public static function check() {
		// Skip if REST API is disabled.
		if ( ! function_exists( 'rest_get_server' ) ) {
			return null;
		}

		$issues = array();
		$stats  = array();
		$warnings = array();

		// Check API versioning.
		$versioning = self::check_api_versioning();
		$stats['has_versioning'] = $versioning['has_versioning'];
		$stats['custom_versions'] = $versioning['versions'];

		if ( ! $versioning['has_versioning'] && ! empty( $versioning['custom_routes'] ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of routes */
				__( '%d custom API routes lack versioning', 'wpshadow' ),
				count( $versioning['custom_routes'] )
			);
		}

		// Check error handling.
		$error_handling = self::check_error_handling();
		$stats['error_handlers'] = $error_handling['count'];

		if ( $error_handling['count'] === 0 ) {
			$warnings[] = __( 'No custom error handlers detected for REST API', 'wpshadow' );
		}

		// Check rate limiting.
		$rate_limiting = self::check_rate_limiting();
		$stats['has_rate_limiting'] = $rate_limiting;

		if ( ! $rate_limiting ) {
			$issues[] = __( 'No rate limiting configured for API endpoints', 'wpshadow' );
		}

		// Check authentication methods.
		$auth_methods = self::check_authentication_methods();
		$stats['auth_methods'] = $auth_methods;

		if ( empty( $auth_methods ) ) {
			$warnings[] = __( 'No custom authentication methods registered', 'wpshadow' );
		}

		// Check API documentation.
		$docs = self::check_api_documentation();
		$stats['has_documentation'] = $docs['has_docs'];

		if ( ! $docs['has_docs'] ) {
			$warnings[] = __( 'No API documentation detected', 'wpshadow' );
		}

		// Check CORS configuration.
		$cors = self::check_cors_configuration();
		$stats['cors_configured'] = $cors;

		// Check response formatting.
		$response_format = self::check_response_formatting();
		$stats['consistent_responses'] = $response_format;

		// Check endpoint organization.
		$organization = self::check_endpoint_organization();
		$stats['namespaces'] = $organization['namespaces'];
		$stats['total_routes'] = $organization['total_routes'];

		// If significant issues detected, return finding.
		if ( count( $issues ) >= 2 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your REST API architecture lacks important patterns like versioning and rate limiting. This can lead to breaking changes, abuse, and maintenance issues.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/api-architecture-best-practices',
				'details'      => array(
					'issues'         => $issues,
					'warnings'       => $warnings,
					'stats'          => $stats,
					'why_it_matters' => __( 'Poor API architecture causes breaking changes, security vulnerabilities, and maintenance nightmares. Well-designed APIs are versioned, documented, and protected.', 'wpshadow' ),
					'best_practices' => array(
						'versioning'      => __( 'Use /v1/ or /v2/ in routes for backward compatibility', 'wpshadow' ),
						'rate_limiting'   => __( 'Protect endpoints from abuse with rate limits', 'wpshadow' ),
						'authentication'  => __( 'Use JWT, OAuth, or API keys for secure access', 'wpshadow' ),
						'error_handling'  => __( 'Return consistent error codes and messages', 'wpshadow' ),
						'documentation'   => __( 'Document endpoints with examples', 'wpshadow' ),
					),
					'next_steps'     => array(
						__( 'Implement API versioning for custom routes', 'wpshadow' ),
						__( 'Add rate limiting plugin or custom implementation', 'wpshadow' ),
						__( 'Document API endpoints with OpenAPI/Swagger', 'wpshadow' ),
						__( 'Add custom error handlers for better debugging', 'wpshadow' ),
					),
				),
			);
		}

		return null;
	}

	/**
	 * Check API versioning.
	 *
	 * @since 1.6093.1200
	 * @return array Versioning information.
	 */
	private static function check_api_versioning(): array {
		$server = rest_get_server();
		$routes = $server->get_routes();

		$versions = array();
		$custom_routes = array();

		foreach ( array_keys( $routes ) as $route ) {
			// Check for version patterns.
			if ( preg_match( '#/(v\d+)/#', $route, $matches ) ) {
				$versions[] = $matches[1];
			}

			// Track custom (non-core) routes.
			if ( strpos( $route, '/wp/v2/' ) !== 0 && strpos( $route, '/wp-site-health/' ) !== 0 ) {
				$custom_routes[] = $route;
			}
		}

		$versions = array_unique( $versions );

		return array(
			'has_versioning' => ! empty( $versions ),
			'versions'       => $versions,
			'custom_routes'  => $custom_routes,
		);
	}

	/**
	 * Check error handling.
	 *
	 * @since 1.6093.1200
	 * @return array Error handling information.
	 */
	private static function check_error_handling(): array {
		$error_handlers = 0;

		// Check for custom error handlers.
		if ( has_filter( 'rest_request_before_callbacks' ) ) {
			$error_handlers++;
		}

		if ( has_filter( 'rest_request_after_callbacks' ) ) {
			$error_handlers++;
		}

		if ( has_filter( 'rest_authentication_errors' ) ) {
			$error_handlers++;
		}

		return array(
			'count' => $error_handlers,
		);
	}

	/**
	 * Check rate limiting.
	 *
	 * @since 1.6093.1200
	 * @return bool True if rate limiting detected.
	 */
	private static function check_rate_limiting(): bool {
		// Check for common rate limiting plugins.
		$rate_limit_plugins = array(
			'wp-rest-api-rate-limit/wp-rest-api-rate-limit.php',
			'jwt-authentication-for-wp-rest-api/jwt-auth.php',
		);

		foreach ( $rate_limit_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		// Check for custom rate limiting hooks.
		return has_filter( 'rest_pre_dispatch' ) || has_filter( 'rest_request_before_callbacks' );
	}

	/**
	 * Check authentication methods.
	 *
	 * @since 1.6093.1200
	 * @return array Detected authentication methods.
	 */
	private static function check_authentication_methods(): array {
		$methods = array();

		// Check for JWT.
		if ( is_plugin_active( 'jwt-authentication-for-wp-rest-api/jwt-auth.php' ) ) {
			$methods[] = 'JWT';
		}

		// Check for OAuth.
		if ( is_plugin_active( 'oauth2-provider/oauth2-provider.php' ) ) {
			$methods[] = 'OAuth2';
		}

		// Check for Application Passwords (WP 5.6+).
		if ( function_exists( 'wp_is_application_passwords_available' ) && wp_is_application_passwords_available() ) {
			$methods[] = 'Application Passwords';
		}

		// Check for custom auth handlers.
		if ( has_filter( 'determine_current_user' ) ) {
			$methods[] = 'Custom Authentication';
		}

		return $methods;
	}

	/**
	 * Check API documentation.
	 *
	 * @since 1.6093.1200
	 * @return array Documentation information.
	 */
	private static function check_api_documentation(): array {
		// Check for common API documentation plugins.
		$doc_plugins = array(
			'rest-api-documentation/rest-api-documentation.php',
			'swagger-api-documentation/swagger-api-documentation.php',
		);

		$has_docs = false;
		foreach ( $doc_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_docs = true;
				break;
			}
		}

		return array(
			'has_docs' => $has_docs,
		);
	}

	/**
	 * Check CORS configuration.
	 *
	 * @since 1.6093.1200
	 * @return bool True if CORS configured.
	 */
	private static function check_cors_configuration(): bool {
		return has_filter( 'rest_pre_serve_request' ) || has_filter( 'rest_api_init' );
	}

	/**
	 * Check response formatting consistency.
	 *
	 * @since 1.6093.1200
	 * @return bool True if consistent formatting detected.
	 */
	private static function check_response_formatting(): bool {
		// Check if custom response formatting is applied.
		return has_filter( 'rest_prepare_post' ) || has_filter( 'rest_pre_echo_response' );
	}

	/**
	 * Check endpoint organization.
	 *
	 * @since 1.6093.1200
	 * @return array Organization information.
	 */
	private static function check_endpoint_organization(): array {
		$server = rest_get_server();
		$routes = $server->get_routes();

		$namespaces = array();
		foreach ( array_keys( $routes ) as $route ) {
			if ( preg_match( '#^/([^/]+)/#', $route, $matches ) ) {
				$namespaces[] = $matches[1];
			}
		}

		$namespaces = array_unique( $namespaces );

		return array(
			'namespaces'   => count( $namespaces ),
			'total_routes' => count( $routes ),
		);
	}
}
