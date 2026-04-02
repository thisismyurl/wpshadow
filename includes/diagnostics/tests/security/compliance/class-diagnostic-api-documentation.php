<?php
/**
 * API Documentation Diagnostic
 *
 * Checks if API documentation is current, accurate, and accessible.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tests
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * API Documentation Diagnostic Class
 *
 * Detects if REST API documentation is properly maintained
 * and accessible for enterprise integrations.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Api_Documentation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'api-documentation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'API Documentation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if API documentation is current and accurate';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'api-management';

	/**
	 * Primary persona
	 *
	 * @var string
	 */
	protected static $persona = 'enterprise-corp';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if REST API is enabled.
		$rest_enabled = get_option( 'rest_api_enabled', true );
		
		if ( ! $rest_enabled ) {
			$issues[] = __( 'REST API is disabled', 'wpshadow' );
			
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'REST API is disabled, no API documentation needed', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/api-documentation',
				'issues'       => $issues,
				'persona'      => self::$persona,
			);
		}

		// Get all registered REST routes.
		$server = rest_get_server();
		$routes = $server->get_routes();
		$route_count = count( $routes );

		// Check for popular API documentation plugins.
		$doc_plugins = array(
			'wp-rest-api-controller/wp-rest-api-controller.php' => 'WP REST API Controller',
			'rest-api-toolbox/rest-api-toolbox.php'             => 'REST API Toolbox',
			'swagger-ui/swagger-ui.php'                         => 'Swagger UI',
			'api-documentation/api-documentation.php'           => 'API Documentation',
			'wp-api-swagger-ui/wp-api-swagger-ui.php'           => 'WP API Swagger UI',
		);

		$has_doc_plugin = false;
		$active_doc_tools = array();

		foreach ( $doc_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$has_doc_plugin = true;
				$active_doc_tools[] = $plugin_name;
			}
		}

		// Check for custom API documentation setup.
		$custom_api_docs_url = get_option( 'wpshadow_api_docs_url', '' );
		$custom_api_docs_enabled = get_option( 'wpshadow_api_docs_enabled', false );

		if ( ! empty( $custom_api_docs_url ) || $custom_api_docs_enabled ) {
			$has_doc_plugin = true;
			$active_doc_tools[] = 'Custom API Documentation';
		}

		// Check for API documentation page.
		$api_doc_page = get_page_by_path( 'api-documentation' );
		if ( ! $api_doc_page ) {
			$api_doc_page = get_page_by_path( 'api-docs' );
		}

		$has_doc_page = ( null !== $api_doc_page );

		// Check documentation freshness.
		$doc_last_updated = get_option( 'wpshadow_api_docs_last_updated', 0 );
		$days_since_update = $doc_last_updated > 0 
			? ( time() - $doc_last_updated ) / DAY_IN_SECONDS 
			: 9999;

		// Check for API versioning.
		$has_versioning = false;
		foreach ( array_keys( $routes ) as $route ) {
			if ( preg_match( '#/v\d+/#', $route ) ) {
				$has_versioning = true;
				break;
			}
		}

		// Check for OpenAPI/Swagger spec.
		$has_openapi_spec = file_exists( ABSPATH . 'openapi.json' ) || 
		                    file_exists( ABSPATH . 'swagger.json' ) ||
		                    file_exists( WP_CONTENT_DIR . '/api-docs/openapi.json' );

		// Evaluate issues.
		if ( ! $has_doc_plugin && ! $has_doc_page && ! $has_openapi_spec ) {
			$issues[] = sprintf(
				/* translators: %d: number of API routes */
				__( 'No API documentation found for %d registered REST routes', 'wpshadow' ),
				$route_count
			);
		}

		if ( $has_doc_plugin && $days_since_update > 90 ) {
			$issues[] = sprintf(
				/* translators: %d: number of days */
				__( 'API documentation not updated in %d+ days', 'wpshadow' ),
				floor( $days_since_update )
			);
		}

		if ( $route_count > 50 && ! $has_versioning ) {
			$issues[] = __( 'Large API surface without versioning detected', 'wpshadow' );
		}

		if ( ! $has_openapi_spec && $route_count > 20 ) {
			$issues[] = __( 'No OpenAPI/Swagger specification file found', 'wpshadow' );
		}

		// Check for API authentication documentation.
		$auth_methods_documented = get_option( 'wpshadow_api_auth_documented', false );
		if ( ! $auth_methods_documented && $route_count > 10 ) {
			$issues[] = __( 'API authentication methods not documented', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$description = sprintf(
			/* translators: 1: number of routes 2: list of documentation tools */
			__( 'API documentation not fully configured. Found %1$d REST routes. %2$s', 'wpshadow' ),
			$route_count,
			! empty( $active_doc_tools ) 
				? sprintf( __( 'Active tools: %s', 'wpshadow' ), implode( ', ', $active_doc_tools ) )
				: __( 'No documentation tools detected.', 'wpshadow' )
		);

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => $description,
			'severity'     => 'medium',
			'threat_level' => 55,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/api-documentation',
			'issues'       => $issues,
			'persona'      => self::$persona,
			'context'      => array(
				'route_count'             => $route_count,
				'has_doc_plugin'          => $has_doc_plugin,
				'active_doc_tools'        => $active_doc_tools,
				'has_doc_page'            => $has_doc_page,
				'days_since_update'       => $days_since_update,
				'has_versioning'          => $has_versioning,
				'has_openapi_spec'        => $has_openapi_spec,
				'auth_methods_documented' => $auth_methods_documented,
			),
		);
	}
}
