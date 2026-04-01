<?php
/**
 * REST API Plugin Conflicts Diagnostic
 *
 * Detects conflicts between plugins affecting REST API functionality and endpoint registration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Plugin Conflicts Diagnostic Class
 *
 * Identifies REST API endpoint conflicts, namespace collisions, and authentication issues.
 *
 * @since 0.6093.1200
 */
class Diagnostic_REST_API_Plugin_Conflicts extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'rest-api-plugin-conflicts';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'REST API Plugin Conflicts';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects conflicts between plugins affecting REST API functionality';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'plugins';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues    = array();
		$conflicts = array();

		// Check if REST API is accessible.
		$rest_url = rest_url();
		$response = wp_remote_get( $rest_url );

		if ( is_wp_error( $response ) ) {
			$issues[] = sprintf(
				/* translators: %s: error message */
				__( 'REST API is not accessible: %s', 'wpshadow' ),
				$response->get_error_message()
			);
		}

		// Get REST API routes.
		$rest_server = rest_get_server();
		$routes      = $rest_server->get_routes();

		// Check for duplicate routes.
		$route_counts = array();
		foreach ( $routes as $route => $handlers ) {
			$route_counts[ $route ] = count( $handlers );
		}

		$duplicate_routes = array_filter( $route_counts, function( $count ) {
			return $count > 1;
		} );

		if ( ! empty( $duplicate_routes ) ) {
			$conflicts[] = sprintf(
				/* translators: %d: number of duplicate routes */
				__( 'Found %d REST API routes with multiple handlers (potential conflicts)', 'wpshadow' ),
				count( $duplicate_routes )
			);
		}

		// Check for common namespace conflicts.
		$namespaces = array();
		foreach ( $routes as $route => $handlers ) {
			if ( preg_match( '#^/([^/]+)/#', $route, $matches ) ) {
				$namespace = $matches[1];
				if ( ! isset( $namespaces[ $namespace ] ) ) {
					$namespaces[ $namespace ] = 0;
				}
				++$namespaces[ $namespace ];
			}
		}

		// Check for plugins using generic namespaces.
		$generic_namespaces = array( 'api', 'v1', 'v2', 'rest', 'custom' );
		foreach ( $generic_namespaces as $generic ) {
			if ( isset( $namespaces[ $generic ] ) ) {
				$conflicts[] = sprintf(
					/* translators: 1: namespace name, 2: number of routes */
					__( 'Generic namespace "%1$s" used (%2$d routes) - increases conflict risk', 'wpshadow' ),
					$generic,
					$namespaces[ $generic ]
				);
			}
		}

		// Check for REST API authentication conflicts.
		$auth_filters = array(
			'rest_authentication_errors',
			'rest_pre_dispatch',
			'rest_request_before_callbacks',
		);

		foreach ( $auth_filters as $filter ) {
			global $wp_filter;
			if ( isset( $wp_filter[ $filter ] ) && is_object( $wp_filter[ $filter ] ) ) {
				$callback_count = count( $wp_filter[ $filter ]->callbacks );
				if ( $callback_count > 5 ) {
					$conflicts[] = sprintf(
						/* translators: 1: filter name, 2: number of callbacks */
						__( 'Filter "%1$s" has %2$d callbacks - may cause authentication conflicts', 'wpshadow' ),
						$filter,
						$callback_count
					);
				}
			}
		}

		// Check for plugins known to cause REST API issues.
		$problematic_plugins = array(
			'wordfence/wordfence.php'     => 'Wordfence (firewall may block REST)',
			'sucuri-scanner/sucuri.php'   => 'Sucuri (may block REST endpoints)',
			'all-in-one-wp-security-and-firewall/wp-security.php' => 'AIOS (firewall rules)',
			'disable-json-api/disable-json-api.php' => 'Disable REST API',
			'disable-rest-api/disable-rest-api.php' => 'Disable REST API',
		);

		$active_plugins = get_option( 'active_plugins', array() );
		$found_problematic = array();
		foreach ( $active_plugins as $plugin ) {
			if ( isset( $problematic_plugins[ $plugin ] ) ) {
				$found_problematic[] = $problematic_plugins[ $plugin ];
			}
		}

		if ( ! empty( $found_problematic ) ) {
			$issues[] = sprintf(
				/* translators: %s: list of problematic plugins */
				__( 'Plugins known to affect REST API: %s', 'wpshadow' ),
				implode( ', ', $found_problematic )
			);
		}

		// Report findings.
		if ( ! empty( $conflicts ) || ! empty( $issues ) ) {
			$severity     = 'medium';
			$threat_level = 55;

			if ( count( $conflicts ) > 2 || ! empty( $found_problematic ) ) {
				$severity     = 'high';
				$threat_level = 75;
			}

			$description = __( 'REST API conflicts detected that may break plugin functionality', 'wpshadow' );

			$details = array(
				'total_routes'      => count( $routes ),
				'total_namespaces'  => count( $namespaces ),
			);

			if ( ! empty( $conflicts ) ) {
				$details['conflicts'] = $conflicts;
			}
			if ( ! empty( $issues ) ) {
				$details['issues'] = $issues;
			}
			if ( ! empty( $duplicate_routes ) ) {
				$details['duplicate_route_count'] = count( $duplicate_routes );
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/rest-api-plugin-conflicts?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => $details,
			);
		}

		return null;
	}
}
