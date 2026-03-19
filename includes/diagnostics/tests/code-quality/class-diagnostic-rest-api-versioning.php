<?php
/**
 * REST API Versioning and Compatibility
 *
 * Validates REST API versioning and backward compatibility.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_REST_API_Versioning Class
 *
 * Checks REST API versioning and compatibility issues.
 *
 * @since 1.6093.1200
 */
class Diagnostic_REST_API_Versioning extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'rest-api-versioning';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'REST API Versioning';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates REST API versioning and backward compatibility';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'rest-api';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_rest_server;

		// Pattern 1: Custom endpoints without version in namespace
		$routes = array();
		if ( isset( $wp_rest_server ) ) {
			$routes = $wp_rest_server->get_routes();
		}

		$unversioned = array();
		foreach ( $routes as $route => $endpoints ) {
			if ( preg_match( '/^\/[a-z\-]+\//', $route ) && ! preg_match( '/\/v\d+/', $route ) ) {
				// Custom endpoint without version number
				if ( ! in_array( substr( $route, 0, 6 ), array( '/wp/v2', '/wp/v3', '/oembed' ), true ) ) {
					$unversioned[] = $route;
				}
			}
		}

		if ( ! empty( $unversioned ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Custom REST API endpoints without version numbers', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/rest-api-versioning',
				'details'      => array(
					'issue'                  => 'unversioned_endpoints',
					'unversioned_count'      => count( $unversioned ),
					'endpoints'              => array_slice( $unversioned, 0, 10 ),
					'message'                => sprintf(
						/* translators: %d: number of endpoints */
						__( '%d REST API endpoints lack version numbers', 'wpshadow' ),
						count( $unversioned )
					),
					'why_versioning_matters' => array(
						'Change APIs safely',
						'Maintain backward compatibility',
						'Support multiple clients',
						'Deprecate old versions',
					),
					'versioning_benefits'    => array(
						'v1 used by legacy clients' => 'Old apps continue working',
						'v2 with new structure'     => 'New apps get improved API',
						'Parallel versions'         => 'No breaking changes for existing users',
						'Deprecation timeline'      => 'Clear path to migration',
					),
					'semantic_versioning'    => array(
						'v1'     => 'Major version 1',
						'v2'     => 'Major version 2 (breaking changes)',
						'v1.1'   => 'Minor update (compatible)',
						'v1.1.1' => 'Patch (bug fix)',
					),
					'version_in_namespace'   => "register_rest_route('my-plugin/v1', '/items', array(
	// Namespace includes version number
	'methods' => 'GET',
	'callback' => 'get_items',
	'permission_callback' => '__return_true',
));

// Not this:
register_rest_route('my-plugin', '/items', ...); // ❌ No version",
					'deprecation_strategy'   => array(
						'1. Announce v1 deprecation',
						'2. Support v1 for 6-12 months',
						'3. Provide migration guide',
						'4. Remove v1 endpoint',
					),
					'deprecation_notice'     => "add_filter('rest_post_dispatch', function(\$response, \$server) {
	header('Deprecation: true');
	header('Sunset: ' . gmdate('r', strtotime('+6 months')));
	header('Link: <https://docs.example.com/migration>; rel=\"deprecation\"');
	
	return \$response;
}, 10, 2);",
					'api_documentation'      => __( 'Always document versioning strategy and migration paths', 'wpshadow' ),
					'client_awareness'       => __( 'Clients should be aware of API version they depend on', 'wpshadow' ),
					'testing_versions'       => array(
						'Test v1 with legacy clients',
						'Test v2 with new clients',
						'Verify backward compatibility',
						'Check deprecation headers',
					),
					'recommendation'         => __( 'Use version numbers in all custom REST API namespaces', 'wpshadow' ),
				),
			);
		}

		// Pattern 2: Deprecated WordPress REST API endpoints still in use
		$deprecated_endpoints = array(
			'/wp/v2/posts/(?P<id>[\\d]+)/revisions' => '5.0',
			'/wp/v2/media/(?P<id>[\\d]+)/post'      => '5.4',
		);

		$using_deprecated = array();
		foreach ( $deprecated_endpoints as $endpoint => $version ) {
			if ( isset( $routes[ $endpoint ] ) ) {
				$using_deprecated[] = array(
					'endpoint'         => $endpoint,
					'deprecated_since' => $version,
				);
			}
		}

		if ( ! empty( $using_deprecated ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Using deprecated REST API endpoints', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/rest-api-versioning',
				'details'      => array(
					'issue'                   => 'deprecated_endpoints',
					'deprecated_endpoints'    => $using_deprecated,
					'message'                 => sprintf(
						/* translators: %d: number of endpoints */
						__( '%d deprecated REST API endpoints detected', 'wpshadow' ),
						count( $using_deprecated )
					),
					'what_are_deprecated'     => __( 'Endpoints planned for removal in future WordPress versions', 'wpshadow' ),
					'risks'                   => array(
						'Future WordPress updates may break',
						'No security updates for deprecated code',
						'Performance not optimized',
						'Possible data loss',
					),
					'migration_required'      => __( 'Update code to use current endpoints before deprecation removal', 'wpshadow' ),
					'finding_usage'           => array(
						'1. Search codebase for endpoint URLs',
						'2. Check plugin integrations',
						'3. Review third-party API calls',
						'4. Update to current equivalents',
					),
					'common_deprecations'     => array(
						'Revisions endpoint'       => 'Changed structure in v2',
						'Post attachment endpoint' => 'Consolidated in newer versions',
						'Comment endpoints'        => 'Evolved in WordPress 5.1+',
					),
					'migration_planning'      => __( 'Schedule endpoint migration during next plugin/theme update', 'wpshadow' ),
					'testing_after_migration' => __( 'Test all API calls after updating endpoints', 'wpshadow' ),
					'recommendation'          => __( 'Update code to use non-deprecated REST API endpoints', 'wpshadow' ),
				),
			);
		}

		// Pattern 3: Missing REST API documentation
		$custom_routes = array();
		foreach ( $routes as $route => $endpoints ) {
			if ( ! in_array( substr( $route, 0, 6 ), array( '/wp/v2', '/wp/v3', '/oembed' ), true ) ) {
				$custom_routes[] = $route;
			}
		}

		if ( count( $custom_routes ) > 5 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Custom REST API endpoints may lack documentation', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/rest-api-versioning',
				'details'      => array(
					'issue'                     => 'undocumented_endpoints',
					'custom_endpoint_count'     => count( $custom_routes ),
					'message'                   => sprintf(
						/* translators: %d: number of custom endpoints */
						__( '%d custom REST API endpoints - documentation recommended', 'wpshadow' ),
						count( $custom_routes )
					),
					'why_documentation_matters' => array(
						'Developers need to know endpoints exist',
						'Clear usage examples',
						'Parameter documentation',
						'Error response documentation',
					),
					'documentation_includes'    => array(
						'Endpoint URL'            => '/my-plugin/v1/items',
						'Supported methods'       => 'GET, POST, PUT, DELETE',
						'Authentication required' => 'Yes/No, required capability',
						'Parameters'              => 'Type, required, description',
						'Response format'         => 'Example JSON response',
						'Errors'                  => 'Possible error codes and meanings',
					),
					'documentation_tools'       => array(
						'OpenAPI/Swagger'        => 'Industry standard',
						'README file'            => 'Simple markdown',
						'API documentation site' => 'Dedicated site like Postman',
						'Inline comments'        => 'In PHP code',
					),
					'adding_schema_to_endpoint' => "register_rest_route('my-plugin/v1', '/items', array(
	'methods' => 'GET',
	'callback' => 'get_items',
	'permission_callback' => '__return_true',
	'args' => array(
		'per_page' => array(
			'description' => 'Items per page',
			'type' => 'integer',
			'default' => 10,
		),
	),
	'schema' => array(
		'title' => 'Items',
		'description' => 'Collection of items',
		'type' => 'object',
		'properties' => array(
			'id' => array('type' => 'integer'),
			'title' => array('type' => 'string'),
		),
	),
));",
					'swagger_documentation'     => __( 'Auto-generate Swagger docs from schema definitions', 'wpshadow' ),
					'postman_collection'        => __( 'Export endpoints as Postman collection for testing', 'wpshadow' ),
					'readme_format'             => array(
						'# My Plugin REST API',
						'## Endpoints',
						'### GET /my-plugin/v1/items',
						'Description, parameters, response examples',
					),
					'recommendation'            => __( 'Document all custom REST API endpoints for developers', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
