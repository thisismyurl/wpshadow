<?php
/**
 * Plugin REST API Issues Diagnostic
 *
 * Detects plugins with broken REST API endpoints.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1630
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin REST API Issues Class
 *
 * Tests plugin REST API endpoints for errors and proper registration.
 * Broken endpoints indicate plugin issues or conflicts.
 *
 * @since 1.5029.1630
 */
class Diagnostic_Plugin_REST_API_Issues extends Diagnostic_Base {

	protected static $slug        = 'plugin-rest-api-issues';
	protected static $title       = 'Plugin REST API Issues';
	protected static $description = 'Detects plugins with broken REST API endpoints';
	protected static $family      = 'plugins';

	public static function check() {
		$cache_key = 'wpshadow_plugin_rest_api_issues';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		// Get REST API routes.
		$rest_server = rest_get_server();
		$routes = $rest_server->get_routes();

		if ( empty( $routes ) ) {
			set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
			return null;
		}

		$problematic_routes = array();

		foreach ( $routes as $route => $endpoints ) {
			// Skip core WordPress routes.
			if ( strpos( $route, '/wp/' ) === 0 || strpos( $route, '/oembed/' ) === 0 ) {
				continue;
			}

			// Test route with GET request.
			$test_url = rest_url( $route );
			$response = wp_remote_get( $test_url, array(
				'timeout' => 5,
				'sslverify' => false,
			) );

			if ( is_wp_error( $response ) ) {
				$problematic_routes[] = array(
					'route' => $route,
					'error' => $response->get_error_message(),
					'type'  => 'request_failed',
				);
				continue;
			}

			$status_code = wp_remote_retrieve_response_code( $response );

			// Check for server errors (500+).
			if ( $status_code >= 500 ) {
				$problematic_routes[] = array(
					'route'       => $route,
					'status_code' => $status_code,
					'type'        => 'server_error',
				);
			}

			// Limit checks to prevent long execution.
			if ( count( $problematic_routes ) >= 20 ) {
				break;
			}
		}

		if ( ! empty( $problematic_routes ) ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of broken endpoints */
					__( '%d plugin REST API endpoints are broken or returning errors. Review plugin health.', 'wpshadow' ),
					count( $problematic_routes )
				),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/plugins-rest-api-issues',
				'data'         => array(
					'problematic_routes' => $problematic_routes,
					'total_routes'       => count( $routes ),
				),
			);

			set_transient( $cache_key, $result, 12 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}
