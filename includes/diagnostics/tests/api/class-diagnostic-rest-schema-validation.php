<?php
/**
 * Diagnostic: REST Schema Validation
 *
 * Validates that REST endpoints have proper schema definitions.
 * Schema improves developer experience and provides self-documentation.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\API
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Rest_Schema_Validation
 *
 * Checks REST endpoints for proper schema definitions.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Rest_Schema_Validation extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'rest-schema-validation';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'REST Schema Validation';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Validates REST endpoints have proper schema definitions';

	/**
	 * Check REST API schema definitions.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$rest_server = rest_get_server();

		if ( ! $rest_server ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'REST API server could not be initialized.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/api-rest-schema-validation',
				'meta'        => array(
					'server_available' => false,
				),
			);
		}

		$routes = $rest_server->get_routes();
		$missing_schema = array();
		$total_routes = 0;

		// Check core namespaces for schema.
		$core_namespaces = array( 'wp/v2', 'oembed/1.0' );

		foreach ( $routes as $route => $handlers ) {
			// Only check core routes.
			foreach ( $core_namespaces as $namespace ) {
				if ( strpos( $route, '/' . $namespace ) === 0 ) {
					++$total_routes;

					foreach ( $handlers as $handler ) {
						// Check if schema callback exists.
						if ( empty( $handler['schema'] ) || ! is_callable( $handler['schema'] ) ) {
							$missing_schema[] = $route;
							break; // Only record route once.
						}
					}
					break;
				}
			}
		}

		$missing_count = count( $missing_schema );

		// Report if significant number of routes missing schema.
		if ( $missing_count > 5 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: Number of routes missing schema */
					_n(
						'%d REST API route is missing a schema definition',
						'%d REST API routes are missing schema definitions',
						$missing_count,
						'wpshadow'
					),
					$missing_count
				),
				'severity'    => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/api-rest-schema-validation',
				'meta'        => array(
					'missing_count'  => $missing_count,
					'total_routes'   => $total_routes,
					'missing_routes' => array_slice( $missing_schema, 0, 10 ), // First 10 only.
				),
			);
		}

		// Report if 1-5 routes missing schema (informational).
		if ( $missing_count > 0 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: Number of routes missing schema */
					_n(
						'%d REST API route is missing a schema definition',
						'%d REST API routes are missing schema definitions',
						$missing_count,
						'wpshadow'
					),
					$missing_count
				),
				'severity'    => 'info',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/api-rest-schema-validation',
				'meta'        => array(
					'missing_count'  => $missing_count,
					'total_routes'   => $total_routes,
					'missing_routes' => $missing_schema,
				),
			);
		}

		// All routes have proper schema.
		return null;
	}
}
