<?php
/**
 * REST API Endpoint Configuration and Security
 *
 * Validates REST API endpoint configuration and security.
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
 * Diagnostic_REST_API_Configuration Class
 *
 * Checks REST API endpoint configuration and security issues.
 *
 * @since 1.2034.1615
 */
class Diagnostic_REST_API_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'rest-api-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'REST API Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates REST API endpoint configuration and security';

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
		// Pattern 1: REST API completely disabled
		$rest_enabled = get_option( 'rest_enabled', true );

		if ( false === $rest_enabled ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'REST API is disabled', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/rest-api-configuration',
				'details'      => array(
					'issue'                       => 'rest_api_disabled',
					'message'                     => __( 'WordPress REST API is globally disabled', 'wpshadow' ),
					'what_is_rest_api'            => __( 'REST API provides programmatic access to WordPress content and functionality', 'wpshadow' ),
					'features_requiring_rest_api' => array(
						'Block editor (Gutenberg)' => 'Core WordPress page/post editing',
						'Site Editor'              => 'Full Site Editing',
						'Mobile apps'              => 'WordPress mobile applications',
						'Third-party integrations' => 'Apps, services, plugins',
						'Headless WordPress'       => 'Decoupled frontend apps',
					),
					'impact_when_disabled'        => array(
						'Block editor unavailable' => 'Cannot use Gutenberg',
						'Mobile apps broken'       => 'Cannot manage site from mobile',
						'Third-party tools fail'   => 'Integrations stop working',
						'Modern features broken'   => 'Site Editor, block patterns',
					),
					'security_vs_functionality'   => __( 'REST API properly configured is more secure than disabling it', 'wpshadow' ),
					'why_disabling_is_risky'      => array(
						'Creates incompatibility',
						'Plugins expect REST API',
						'Future-proofing concerns',
						'Better to secure than disable',
					),
					'how_to_re_enable'            => array(
						'Edit wp-config.php',
						'Remove: define(\'REST_ENABLED\', false);',
						'Or via option: update_option(\'rest_enabled\', true);',
						'Re-enable specific endpoints if needed',
					),
					'secure_configuration'        => array(
						'Require authentication for sensitive endpoints',
						'Implement rate limiting',
						'Add CORS restrictions',
						'Monitor endpoint access',
					),
					'code_example'                => "// Re-enable REST API
if (defined('REST_ENABLED') && !REST_ENABLED) {
	// Either remove the define() from wp-config.php
	// Or use: update_option('rest_enabled', true);
}",
					'recommendation'              => __( 'Enable REST API and properly secure it instead of disabling', 'wpshadow' ),
				),
			);
		}

		// Pattern 2: REST API authentication issues
		$jwt_enabled = defined( 'JWT_AUTH_SECRET_KEY' ) || class_exists( 'WP_REST_Auth_Controller' );
		$basic_auth  = ! is_ssl() && defined( 'WP_USE_BASIC_AUTH' );

		if ( ! $jwt_enabled && ! wp_is_application_passwords_available() ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No secure REST API authentication method available', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/rest-api-configuration',
				'details'      => array(
					'issue'                       => 'no_secure_auth',
					'message'                     => __( 'No secure authentication method for REST API', 'wpshadow' ),
					'authentication_methods'      => array(
						'Application Passwords' => array(
							'status'   => wp_is_application_passwords_available() ? 'Available' : 'Not Available',
							'requires' => 'WordPress 5.6+, HTTPS',
							'security' => 'Tokens per application, not account password',
						),
						'JWT (JSON Web Tokens)' => array(
							'status'   => $jwt_enabled ? 'Available' : 'Not Available',
							'requires' => 'JWT auth plugin',
							'security' => 'Tokens with expiration',
						),
						'OAuth 2.0'             => array(
							'status'   => 'Available via plugins',
							'requires' => 'OAuth plugin',
							'security' => 'Industry standard, best for third-party',
						),
					),
					'application_passwords'       => array(
						'Best for'     => 'Native integrations, mobile apps',
						'Requirements' => 'HTTPS (SSL/TLS)',
						'How it works' => 'Generate per-app tokens, revoke independently',
						'Security'     => 'Each app gets unique password',
					),
					'why_authentication_critical' => array(
						'Prevents unauthorized access',
						'Protects sensitive endpoints',
						'Enables third-party integrations',
						'Audit trail for API usage',
					),
					'ssl_requirement'             => __( 'Application Passwords require HTTPS for security', 'wpshadow' ),
					'checking_https'              => sprintf( 'is_ssl(): %s', is_ssl() ? 'Yes' : 'No' ),
					'setup_application_passwords' => array(
						'1. Ensure site uses HTTPS',
						'2. User profile > Application Passwords',
						'3. Create new application token',
						'4. Use token in API calls',
					),
					'jwt_plugin_example'          => array(
						'wp-api-jwt-auth',
						'JWT Authentication for WP REST API',
						'WordPress JWT Auth',
					),
					'code_example'                => "// Using Application Password
\$response = wp_remote_get('https://yoursite.com/wp-json/wp/v2/posts', array(
	'headers' => array(
		'Authorization' => 'Basic ' . base64_encode('user:application_password'),
	),
));",
					'recommendation'              => __( 'Enable Application Passwords (HTTPS required) or JWT authentication', 'wpshadow' ),
				),
			);
		}

		// Pattern 3: REST API endpoints exposing sensitive user data
		$user_endpoint     = rest_get_route_for_post_type_items( 'user' );
		$exposed_user_data = false;

		if ( $user_endpoint ) {
			$user_response = wp_remote_get( rest_url( '/wp/v2/users' ), array( 'sslverify' => false ) );

			if ( 200 === wp_remote_retrieve_response_code( $user_response ) ) {
				$users = json_decode( wp_remote_retrieve_body( $user_response ), true );

				if ( is_array( $users ) && ! empty( $users ) ) {
					// Check if sensitive fields are exposed
					$user = array_shift( $users );
					if ( isset( $user['email'] ) || isset( $user['username'] ) ) {
						$exposed_user_data = true;
					}
				}
			}
		}

		if ( $exposed_user_data ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'REST API exposing sensitive user information', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/rest-api-configuration',
				'details'      => array(
					'issue'                     => 'sensitive_user_data_exposed',
					'message'                   => __( 'Unauthenticated access to user information', 'wpshadow' ),
					'exposed_information'       => array(
						'Email addresses',
						'Usernames',
						'User IDs',
						'Display names',
						'User URLs',
					),
					'security_risks'            => array(
						'User enumeration attacks',
						'Targeted phishing',
						'Credential guessing',
						'Privacy violation',
						'GDPR compliance issues',
					),
					'user_enumeration'          => __( 'Attackers can discover all usernames and emails', 'wpshadow' ),
					'secure_user_endpoint'      => array(
						'Restrict to authenticated users',
						'Hide email addresses',
						'Limit fields returned',
						'Implement rate limiting',
					),
					'how_to_restrict'           => "add_filter('rest_user_query_vars', function(\$args) {
	// Only allow authenticated users to query users
	if (!is_user_logged_in()) {
		return array();
	}
	return \$args;
});",
					'custom_response_filtering' => "add_filter('rest_prepare_user', function(\$response, \$user) {
	// Only expose minimal data to public
	if (!current_user_can('list_users')) {
		\$response->data = array(
			'id' => \$user->ID,
			'name' => \$user->display_name,
		);
	}
	return \$response;
}, 10, 2);",
					'complete_restriction'      => "// Completely disable user endpoint for non-admins
add_filter('rest_endpoints', function(\$endpoints) {
	if (!current_user_can('list_users')) {
		unset(\$endpoints['/wp/v2/users']);
		unset(\$endpoints['/wp/v2/users/(?P<id>[\\d]+)']);
	}
	return \$endpoints;
});",
					'authentication_callback'   => "add_filter('rest_user_collection_params', function(\$params) {
	\$params['context']['default'] = 'view';
	return \$params;
});",
					'rate_limiting'             => __( 'Implement rate limiting on /wp/v2/users endpoint', 'wpshadow' ),
					'monitoring'                => __( 'Monitor REST API logs for user enumeration attempts', 'wpshadow' ),
					'recommendation'            => __( 'Restrict user endpoint to authenticated users with appropriate capabilities', 'wpshadow' ),
				),
			);
		}

		// Pattern 4: REST API missing rate limiting
		$rate_limit_enabled = defined( 'REST_API_RATE_LIMIT' ) || function_exists( 'rest_api_init' );

		if ( ! $rate_limit_enabled ) {
			$request_count = get_transient( 'rest_api_requests_' . get_client_ip() );

			if ( false !== $request_count && $request_count > 100 ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'REST API rate limiting not configured', 'wpshadow' ),
					'severity'     => 'medium',
					'threat_level' => 55,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/rest-api-configuration',
					'details'      => array(
						'issue'                      => 'no_rate_limiting',
						'message'                    => __( 'REST API endpoints lack rate limiting protection', 'wpshadow' ),
						'what_is_rate_limiting'      => __( 'Limiting number of requests per IP per time period', 'wpshadow' ),
						'attacks_prevented'          => array(
							'Brute force attacks',
							'Credential stuffing',
							'Denial of service (DoS)',
							'API scraping',
							'Enumeration attacks',
						),
						'recommended_limits'         => array(
							'Public endpoints'        => '60 requests/minute',
							'Authenticated endpoints' => '300 requests/minute',
							'Admin endpoints'         => '600 requests/minute',
							'Login endpoint'          => '5 requests/minute',
						),
						'rate_limit_benefits'        => array(
							'Prevents abuse',
							'Protects from DoS',
							'Fair resource allocation',
							'API security hardening',
						),
						'implementing_rate_limiting' => "add_action('rest_api_init', function() {
	\$ip = get_client_ip();
	\$key = 'rest_limit_' . \$ip . '_' . date('H');
	\$count = get_transient(\$key);
	
	if (false === \$count) {
		set_transient(\$key, 1, HOUR_IN_SECONDS);
	} else {
		if (\$count >= 60) {
			wp_send_json_error('Rate limit exceeded', 429);
		}
		set_transient(\$key, \$count + 1, HOUR_IN_SECONDS);
	}
});",
						'plugin_solutions'           => array(
							'WP REST API Rate Limiting'    => 'Dedicated rate limit plugin',
							'Rate Limiting for WP REST API' => 'Alternative solution',
							'Server-level via ModSecurity' => 'Web server protection',
						),
						'ip_header_consideration'    => __( 'Ensure correct IP detection behind proxies/CDN', 'wpshadow' ),
						'cloudflare_integration'     => __( 'Can implement rate limiting at Cloudflare level', 'wpshadow' ),
						'monitoring_high_traffic'    => __( 'Monitor for legitimate high-traffic patterns', 'wpshadow' ),
						'recommendation'             => __( 'Implement REST API rate limiting to protect against abuse', 'wpshadow' ),
					),
				);
			}
		}

		// Pattern 5: REST API CORS misconfiguration
		$cors_headers = wp_get_server_protocol() . ' 200 OK';
		$allow_origin = get_http_header( 'Access-Control-Allow-Origin' );

		if ( '*' === $allow_origin || 'true' === get_option( 'rest_enable_all_origins', false ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'REST API CORS allowing all origins', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/rest-api-configuration',
				'details'      => array(
					'issue'                      => 'cors_misconfiguration',
					'message'                    => __( 'CORS headers allow requests from any origin', 'wpshadow' ),
					'what_is_cors'               => __( 'Cross-Origin Resource Sharing - controls which sites can access API', 'wpshadow' ),
					'security_risks'             => array(
						'Unauthorized sites access API',
						'Sensitive data exposure',
						'Credential theft',
						'Malicious actions',
					),
					'allow_origin_star_problem'  => __( 'Access-Control-Allow-Origin: * allows any site to call API', 'wpshadow' ),
					'correct_cors_setup'         => array(
						'Whitelist specific domains',
						'Never use wildcard (*)',
						'Include HTTPS requirement',
						'Specify allowed methods',
					),
					'secure_cors_example'        => "add_action('rest_api_init', function() {
	// Only allow specific trusted domain
	\$allowed_origin = 'https://trusted-site.com';
	\$current_origin = get_http_header('Origin');
	
	if (\$current_origin === \$allowed_origin) {
		header('Access-Control-Allow-Origin: ' . \$allowed_origin);
		header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
		header('Access-Control-Allow-Credentials: true');
	}
});",
					'wordpress_rest_oembed_cors' => __( 'WordPress enables CORS for oEmbed endpoints (acceptable)', 'wpshadow' ),
					'checking_current_settings'  => "// Check current CORS configuration
\$origin = get_http_header('Access-Control-Allow-Origin');
echo 'Current Allow-Origin: ' . \$origin;",
					'trusted_domains'            => array(
						'Your frontend domain'     => 'https://yoursite.com',
						'Mobile app domain'        => 'app://yourdomain',
						'Third-party integrations' => 'https://trusted-partner.com',
					),
					'methods_to_restrict'        => array(
						'GET'               => 'Safe for read-only',
						'POST, PUT, DELETE' => 'Restrict to authenticated',
						'OPTIONS'           => 'Preflight requests',
					),
					'recommendation'             => __( 'Configure CORS to allow only trusted, specific origins', 'wpshadow' ),
				),
			);
		}

		// Pattern 6: REST API custom endpoints lacking security
		global $wp_rest_server;

		if ( isset( $wp_rest_server ) ) {
			$routes             = $wp_rest_server->get_routes();
			$custom_endpoints   = 0;
			$insecure_endpoints = array();

			foreach ( $routes as $route => $endpoints ) {
				if ( ! in_array( substr( $route, 0, 6 ), array( '/wp/v2', '/wp/v3', '/oembed' ), true ) ) {
					++$custom_endpoints;

					// Check if endpoint requires authentication
					foreach ( $endpoints as $endpoint ) {
						if ( isset( $endpoint['callback'] ) && ! isset( $endpoint['permission_callback'] ) ) {
							$insecure_endpoints[] = $route;
						}
					}
				}
			}

			if ( count( $insecure_endpoints ) > 0 ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Custom REST API endpoints without permission checks', 'wpshadow' ),
					'severity'     => 'high',
					'threat_level' => 85,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/rest-api-configuration',
					'details'      => array(
						'issue'                       => 'insecure_custom_endpoints',
						'insecure_count'              => count( $insecure_endpoints ),
						'insecure_endpoints'          => array_slice( $insecure_endpoints, 0, 20 ),
						'message'                     => sprintf(
							/* translators: %d: number of endpoints */
							__( '%d custom REST API endpoints missing permission checks', 'wpshadow' ),
							count( $insecure_endpoints )
						),
						'security_requirement'        => __( 'Every REST endpoint must have permission_callback', 'wpshadow' ),
						'permission_callback_purpose' => __( 'Controls who can access the endpoint', 'wpshadow' ),
						'required_for_all_endpoints'  => array(
							'Public endpoints',
							'Authenticated endpoints',
							'Admin-only endpoints',
							'Custom role endpoints',
						),
						'common_permission_callbacks' => array(
							'__return_true'     => 'Anyone (use with caution)',
							'is_user_logged_in' => 'Logged-in users only',
							'current_user_can( "manage_options" )' => 'Administrators only',
							'function() { return current_user_can( "edit_posts" ); }' => 'Custom check',
						),
						'secure_endpoint_example'     => "register_rest_route('my-plugin/v1', '/custom-action', array(
	'methods' => 'POST',
	'callback' => 'my_plugin_callback',
	'permission_callback' => function() {
		return current_user_can('manage_options');
	},
	'args' => array(
		'param' => array(
			'type' => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'required' => true,
		),
	),
));

function my_plugin_callback(\$request) {
	// Verify nonce if needed
	// Sanitize parameters
	// Execute action
	return rest_ensure_response(array('success' => true));
}",
						'nonce_verification'          => __( 'Consider adding nonce verification for state-changing operations', 'wpshadow' ),
						'parameter_validation'        => __( 'Always validate and sanitize request parameters', 'wpshadow' ),
						'error_messages'              => __( 'Never reveal implementation details in error messages', 'wpshadow' ),
						'audit_custom_endpoints'      => array(
							'1. List all custom endpoints',
							'2. Check each has permission_callback',
							'3. Verify callback logic is correct',
							'4. Test with different user roles',
						),
						'recommendation'              => __( 'Add permission_callback to all custom REST endpoints', 'wpshadow' ),
					),
				);
			}
		}

		return null; // No issues found
	}
}
