<?php
/**
 * REST API Data Validation and Sanitization
 *
 * Validates REST API request validation and response sanitization.
 *
 * @since   1.2034.1615
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_REST_API_Validation Class
 *
 * Checks REST API validation and sanitization issues.
 *
 * @since 1.2034.1615
 */
class Diagnostic_REST_API_Validation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'rest-api-validation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'REST API Validation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates REST API request validation and response sanitization';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'rest-api';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2034.1615
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_rest_server;

		// Pattern 1: Custom endpoints without parameter validation
		$routes = array();
		if ( isset( $wp_rest_server ) ) {
			$routes = $wp_rest_server->get_routes();
		}

		$invalid_endpoints = array();
		foreach ( $routes as $route => $endpoints ) {
			foreach ( $endpoints as $endpoint ) {
				if ( isset( $endpoint['callback'] ) && ! isset( $endpoint['args'] ) && in_array( strtoupper( $endpoint['methods'] ?? 'GET' ), array( 'POST', 'PUT', 'PATCH' ), true ) ) {
					$invalid_endpoints[] = $route;
				}
			}
		}

		if ( ! empty( $invalid_endpoints ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Custom REST API endpoints without parameter validation', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/rest-api-validation',
				'details'      => array(
					'issue' => 'no_parameter_validation',
					'invalid_count' => count( $invalid_endpoints ),
					'endpoints' => array_slice( $invalid_endpoints, 0, 10 ),
					'message' => sprintf(
						/* translators: %d: number of endpoints */
						__( '%d REST API endpoints lack parameter validation', 'wpshadow' ),
						count( $invalid_endpoints )
					),
					'security_risks' => array(
						'Invalid data accepted',
						'Type confusion attacks',
						'Injection attacks',
						'Unexpected behavior',
					),
					'validation_importance' => __( 'REST endpoints must validate all input parameters', 'wpshadow' ),
					'validation_types' => array(
						'Type checking' => 'Ensure parameter is correct type',
						'Required checking' => 'Verify required params present',
						'Format validation' => 'Email, URL format checks',
						'Range validation' => 'Min/max values',
						'Enum validation' => 'Only allowed values',
					),
					'secure_endpoint_pattern' => "register_rest_route('my-plugin/v1', '/items', array(
	'methods' => 'POST',
	'callback' => 'create_item',
	'permission_callback' => 'is_user_logged_in',
	'args' => array(
		'title' => array(
			'required' => true,
			'type' => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'validate_callback' => function(\$param) {
				return strlen(\$param) > 0 && strlen(\$param) <= 200;
			},
			'description' => 'Item title (1-200 chars)',
		),
		'category' => array(
			'required' => false,
			'type' => 'string',
			'enum' => array('news', 'update', 'bug-fix'),
			'description' => 'Item category',
		),
		'priority' => array(
			'required' => false,
			'type' => 'integer',
			'minimum' => 1,
			'maximum' => 10,
			'description' => 'Priority level (1-10)',
		),
	),
));",
					'common_validations' => array(
						'Text fields' => 'Min/max length, characters',
						'Numbers' => 'Min/max values, integer',
						'Emails' => 'Valid email format',
						'URLs' => 'Valid URL format',
						'Enums' => 'Specific allowed values',
						'Arrays' => 'Item types, count',
					),
					'sanitization_in_callback' => "function create_item(\$request) {
	\$params = \$request->get_json_params();
	
	// Validation already done by WordPress
	// But sanitize again for safety
	\$title = sanitize_text_field(\$params['title']);
	\$category = sanitize_key(\$params['category']);
	
	// Use sanitized data
	\$item_id = wp_insert_post(array(
		'post_type' => 'item',
		'post_title' => \$title,
		'meta_input' => array(
			'category' => \$category,
		),
	));
	
	return rest_ensure_response(array('id' => \$item_id));
}",
					'type_safety' => __( 'Validate types in args to prevent type confusion', 'wpshadow' ),
					'required_parameters' => __( 'Mark critical params as required', 'wpshadow' ),
					'range_checking' => __( 'Always validate min/max for numeric parameters', 'wpshadow' ),
					'enum_safety' => __( 'Use enum for parameters with specific allowed values', 'wpshadow' ),
					'recommendation' => __( 'Add parameter validation to all custom REST endpoints', 'wpshadow' ),
				),
			);
		}

		// Pattern 2: REST API responses exposing sensitive data
		$exposed_sensitive = false;

		foreach ( $routes as $route => $endpoints ) {
			foreach ( $endpoints as $endpoint ) {
				if ( isset( $endpoint['callback'] ) && is_callable( $endpoint['callback'] ) ) {
					// Check if endpoint returns post content without escaping
					$callback_source = serialize( $endpoint['callback'] );

					if ( preg_match( '/post_content|post_password|meta_value/', $callback_source ) ) {
						$exposed_sensitive = true;
						break 2;
					}
				}
			}
		}

		if ( $exposed_sensitive ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'REST API may expose sensitive data', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/rest-api-validation',
				'details'      => array(
					'issue' => 'sensitive_data_exposure',
					'message' => __( 'REST API responses may contain unescaped sensitive data', 'wpshadow' ),
					'sensitive_fields' => array(
						'post_password' => 'Page passwords',
						'meta_value' => 'Custom field data',
						'user_pass' => 'Password hashes',
						'email' => 'Email addresses',
						'phone' => 'Phone numbers',
					),
					'escaping_required' => __( 'All output must be escaped before sending in REST response', 'wpshadow' ),
					'escaping_methods' => array(
						'esc_html()' => 'HTML content',
						'esc_attr()' => 'HTML attributes',
						'esc_url()' => 'URLs',
						'esc_js()' => 'JavaScript strings',
						'wp_kses_post()' => 'Allowed HTML tags',
					),
					'secure_response_pattern' => "function get_items(\$request) {
	\$items = get_posts(array('post_type' => 'item'));
	
	\$response = array();
	foreach (\$items as \$item) {
		// NEVER expose sensitive fields
		\$response[] = array(
			'id' => \$item->ID,
			'title' => esc_html(\$item->post_title), // ESCAPE!
			'content' => wp_kses_post(\$item->post_content), // ESCAPE!
			// NOT: password, admin fields, etc.
		);
	}
	
	return rest_ensure_response(\$response);
}",
					'filtering_sensitive' => "add_filter('rest_prepare_post', function(\$response, \$post) {
	// Remove sensitive data from REST response
	unset(\$response->data['post_password']);
	
	// Only expose appropriate fields
	if (!current_user_can('edit_post', \$post->ID)) {
		// Public response
		\$response->data = array(
			'id' => \$response->data['id'],
			'title' => \$response->data['title'],
			'content' => \$response->data['content'],
		);
	}
	
	return \$response;
}, 10, 2);",
					'authentication_based_filtering' => __( 'Return different data based on user capabilities', 'wpshadow' ),
					'personally_identifiable_info' => __( 'Never expose PII (email, phone, SSN) without user consent', 'wpshadow' ),
					'gdpr_considerations' => __( 'Verify REST API responses comply with GDPR requirements', 'wpshadow' ),
					'testing_exposure' => array(
						'1. Call endpoint as anonymous user',
						'2. Inspect JSON response',
						'3. Check for sensitive fields',
						'4. Verify fields are escaped',
					),
					'recommendation' => __( 'Audit REST API responses and remove/escape sensitive data', 'wpshadow' ),
				),
			);
		}

		// Pattern 3: Missing input sanitization in REST callbacks
		return null;
	}
}
