<?php
/**
 * REST API Endpoints Diagnostic
 *
 * Checks if REST API endpoints are properly configured and secure.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1300
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Endpoints Diagnostic Class
 *
 * Verifies that REST API endpoints are properly configured, secured,
 * and accessible only to authorized users.
 *
 * @since 1.6035.1300
 */
class Diagnostic_REST_API_Endpoints extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'rest-api-endpoints';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'REST API Endpoints';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if REST API endpoints are properly configured and secure';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'developer';

	/**
	 * Run the REST API endpoints diagnostic check.
	 *
	 * @since  1.6035.1300
	 * @return array|null Finding array if API issues detected, null otherwise.
	 */
	public static function check() {
		$issues   = array();
		$warnings = array();
		$stats    = array();

		// Check if REST API is enabled.
		if ( ! rest_api_enabled() ) {
			$issues[] = __( 'REST API is disabled globally', 'wpshadow' );
		}

		// Get REST API index.
		$rest_index = rest_get_server();
		
		if ( null === $rest_index ) {
			$warnings[] = __( 'REST server not properly initialized', 'wpshadow' );
			return null;
		}

		// Get registered routes.
		$routes = $rest_index->get_routes();
		$stats['total_routes'] = count( $routes );

		// Check for public endpoints without authentication.
		$public_endpoints = array();
		$protected_endpoints = array();

		foreach ( $routes as $route => $endpoint ) {
			if ( isset( $endpoint[0]['methods'] ) ) {
				// Check if endpoint requires authentication.
				$needs_auth = isset( $endpoint[0]['permission_callback'] ) &&
							  $endpoint[0]['permission_callback'] !== '__return_true';

				if ( in_array( 'GET', (array) $endpoint[0]['methods'], true ) ) {
					if ( ! $needs_auth ) {
						$public_endpoints[] = $route;
					} else {
						$protected_endpoints[] = $route;
					}
				}
			}
		}

		$stats['public_endpoints'] = count( $public_endpoints );
		$stats['protected_endpoints'] = count( $protected_endpoints );

		// Check for overly permissive endpoints.
		$exposed_sensitive_routes = array();
		$sensitive_keywords = array( 'users', 'comments', 'settings' );

		foreach ( $public_endpoints as $route ) {
			foreach ( $sensitive_keywords as $keyword ) {
				if ( strpos( $route, $keyword ) !== false ) {
					$exposed_sensitive_routes[] = $route;
				}
			}
		}

		if ( ! empty( $exposed_sensitive_routes ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of endpoints */
				__( '%d sensitive endpoints publicly accessible', 'wpshadow' ),
				count( $exposed_sensitive_routes )
			);
		}

		// Check REST API access methods.
		if ( get_rest_option( 'rest_api_enabled' ) === false ) {
			$warnings[] = __( 'REST API disabled via settings', 'wpshadow' );
		}

		// Check for custom endpoints.
		$custom_endpoints = 0;
		$core_endpoint_prefixes = array( '/wp/v2/', '/wp-site-health/v1/' );

		foreach ( $routes as $route => $endpoint ) {
			$is_core = false;
			foreach ( $core_endpoint_prefixes as $prefix ) {
				if ( strpos( $route, $prefix ) === 0 ) {
					$is_core = true;
					break;
				}
			}

			if ( ! $is_core && strpos( $route, '/wp/' ) === 0 ) {
				$custom_endpoints++;
			}
		}

		$stats['custom_endpoints'] = $custom_endpoints;

		if ( $custom_endpoints > 20 ) {
			$warnings[] = sprintf(
				/* translators: %d: number */
				__( 'High number of custom REST endpoints (%d)', 'wpshadow' ),
				$custom_endpoints
			);
		}

		// Check if REST API is properly versioned.
		$has_versioning = false;
		foreach ( array_keys( $routes ) as $route ) {
			if ( preg_match( '/\/v\d+\//', $route ) ) {
				$has_versioning = true;
				break;
			}
		}

		if ( ! $has_versioning && $custom_endpoints > 0 ) {
			$warnings[] = __( 'Custom endpoints should use versioning (e.g., /v1/)', 'wpshadow' );
		}

		// Check for proper rate limiting.
		$rate_limiting_plugins = array(
			'rest-api-rate-limit/rest-api-rate-limit.php',
		);

		$has_rate_limiting = false;
		foreach ( $rate_limiting_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_rate_limiting = true;
				break;
			}
		}

		if ( ! $has_rate_limiting ) {
			$warnings[] = __( 'No REST API rate limiting plugin detected', 'wpshadow' );
		}

		// Check for CORS configuration.
		if ( function_exists( 'rest_send_cors_headers' ) ) {
			$stats['cors_enabled'] = true;
		} else {
			$warnings[] = __( 'CORS headers not properly configured', 'wpshadow' );
		}

		// Check user endpoints security.
		$users_route_found = false;
		foreach ( $routes as $route => $endpoint ) {
			if ( strpos( $route, '/wp/v2/users' ) === 0 ) {
				$users_route_found = true;
				
				// Check if users endpoint requires authentication.
				if ( isset( $endpoint[0]['methods'] ) && 
					 in_array( 'GET', (array) $endpoint[0]['methods'], true ) ) {
					
					$needs_auth = isset( $endpoint[0]['permission_callback'] ) &&
								  $endpoint[0]['permission_callback'] !== '__return_true';
					
					if ( ! $needs_auth ) {
						$issues[] = __( 'Users REST endpoint is publicly accessible', 'wpshadow' );
					}
				}
			}
		}

		// Check settings endpoint.
		foreach ( $routes as $route => $endpoint ) {
			if ( strpos( $route, '/wp/v2/settings' ) === 0 ) {
				// Settings should always require authentication.
				if ( isset( $endpoint[0]['methods'] ) && 
					 in_array( 'GET', (array) $endpoint[0]['methods'], true ) ) {
					
					$needs_auth = isset( $endpoint[0]['permission_callback'] ) &&
								  $endpoint[0]['permission_callback'] !== '__return_true';
					
					if ( ! $needs_auth ) {
						$issues[] = __( 'Settings REST endpoint is publicly accessible', 'wpshadow' );
					}
				}
			}
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'REST API endpoints have critical security issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/rest-api-endpoints',
				'context'      => array(
					'stats'                      => $stats,
					'exposed_sensitive_routes'   => array_slice( $exposed_sensitive_routes, 0, 5 ),
					'public_endpoints_count'     => count( $public_endpoints ),
					'issues'                     => $issues,
					'warnings'                   => $warnings,
				),
			);
		}

		// If only warnings.
		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'REST API endpoints have recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/rest-api-endpoints',
				'context'      => array(
					'stats'                  => $stats,
					'public_endpoints_count' => count( $public_endpoints ),
					'warnings'               => $warnings,
				),
			);
		}

		return null; // REST API endpoints are properly configured.
	}
}
