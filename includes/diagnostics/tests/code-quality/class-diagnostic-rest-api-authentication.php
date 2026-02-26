<?php
/**
 * REST API Authentication and Permissions
 *
 * Validates REST API authentication and permission implementations.
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
 * Diagnostic_REST_API_Authentication Class
 *
 * Checks REST API authentication and permission issues.
 *
 * @since 1.2034.1615
 */
class Diagnostic_REST_API_Authentication extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'rest-api-authentication';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'REST API Authentication';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates REST API authentication and permission implementations';

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
		// Pattern 1: REST API authentication bypasses via tokens
		$auth_plugins = array(
			'wp-api-jwt-auth',
			'jwt-authentication-for-wp-rest-api',
			'simple-jwt-authentication',
		);

		$jwt_active = false;
		foreach ( $auth_plugins as $plugin ) {
			if ( is_plugin_active( $plugin . '/' . $plugin . '.php' ) ) {
				$jwt_active = true;
				break;
			}
		}

		if ( ! $jwt_active && ! wp_is_application_passwords_available() ) {
			// Check if basic auth is attempted over HTTP
			if ( ! is_ssl() ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'No secure authentication method over HTTP', 'wpshadow' ),
					'severity'     => 'critical',
					'threat_level' => 95,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/rest-api-authentication',
					'details'      => array(
						'issue'                   => 'no_secure_auth_http',
						'message'                 => __( 'REST API used over HTTP without authentication protection', 'wpshadow' ),
						'critical_security_risk'  => __( 'Credentials transmitted in plaintext over HTTP', 'wpshadow' ),
						'attacks_enabled'         => array(
							'Man-in-the-middle (MITM)' => 'Credentials captured',
							'Session hijacking'        => 'Tokens intercepted',
							'Credential theft'         => 'Passwords exposed',
							'API request tampering'    => 'Requests modified in transit',
						),
						'http_vs_https'           => array(
							'HTTP'  => 'Plaintext transmission, insecure',
							'HTTPS' => 'Encrypted transmission, secure',
						),
						'impact'                  => __( 'Any network observer can capture authentication credentials', 'wpshadow' ),
						'ssl_requirement'         => __( 'SSL/TLS certificate required for secure REST API', 'wpshadow' ),
						'http_enforcement'        => array(
							'Redirect HTTP to HTTPS',
							'Use HSTS header',
							'Set Secure flag on cookies',
						),
						'htaccess_redirect'       => 'RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]',
						'wordpress_redirect'      => "// In wp-config.php or functions.php
if (!is_ssl() && !in_array(\$_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1'))) {
	wp_safe_remote_get('https://' . \$_SERVER['HTTP_HOST'] . \$_SERVER['REQUEST_URI']);
	exit;
}",
						'ssl_certificate_options' => array(
							'Let\'s Encrypt' => 'Free, auto-renews',
							'Comodo SSL'     => 'Affordable, established',
							'Cloudflare SSL' => 'Included with Cloudflare',
							'DigiCert'       => 'Premium, highly trusted',
						),
						'testing_ssl'             => 'Use https://www.ssllabs.com/ssltest/',
						'recommendation'          => __( 'Implement HTTPS/SSL and enable secure authentication method', 'wpshadow' ),
					),
				);
			}
		}

		// Pattern 2: Application passwords disabled or misconfigured
		if ( wp_is_application_passwords_available() ) {
			$app_pass_disabled = get_option( 'application_passwords_disabled', false );

			if ( $app_pass_disabled ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Application passwords disabled', 'wpshadow' ),
					'severity'     => 'medium',
					'threat_level' => 50,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/rest-api-authentication',
					'details'      => array(
						'issue'                   => 'app_passwords_disabled',
						'message'                 => __( 'Application passwords disabled for REST API access', 'wpshadow' ),
						'what_are_app_passwords'  => __( 'Per-app tokens that grant API access without sharing account password', 'wpshadow' ),
						'benefits'                => array(
							'Account password remains secret',
							'Generate unique token per app',
							'Revoke individual apps',
							'Better security posture',
						),
						'when_to_use'             => array(
							'Mobile app integration',
							'Third-party services',
							'CI/CD pipelines',
							'Automated tasks',
						),
						'why_disable_is_risky'    => array(
							'Forces using account passwords',
							'Cannot revoke individual apps',
							'Account compromise = full access loss',
							'No audit trail per app',
						),
						'enabling_app_passwords'  => array(
							'1. Ensure WordPress 5.6+',
							'2. Enable HTTPS',
							'3. Edit wp-config.php:',
							'   remove: define(\'WP_APPLICATION_PASSWORDS_REQUIRE_HTTPS\', false);',
							'4. Or go to Settings > Policies',
						),
						'code_to_enable'          => "// Make sure this is NOT in wp-config.php:
// define('WP_APPLICATION_PASSWORDS_DISABLE', true);

// If it is, either:
// 1. Remove the line, or
// 2. Change to: define('WP_APPLICATION_PASSWORDS_DISABLE', false);",
						'requiring_https'         => __( 'Application Passwords require HTTPS for security (enforced by WordPress)', 'wpshadow' ),
						'user_setup_process'      => array(
							'1. User goes to profile page',
							'2. Scrolls to Application Passwords',
							'3. Enters app name',
							'4. Clicks Generate Password',
							'5. Receives unique token',
						),
						'api_usage_example'       => "// Using Application Password in API call
\$app_password = 'abc1 def2 ghi3 jkl4 mno5 pqr6 stu7 vwx8';
\$user = 'admin';

\$response = wp_remote_get('https://yoursite.com/wp-json/wp/v2/posts', array(
	'headers' => array(
		'Authorization' => 'Basic ' . base64_encode(\$user . ':' . \$app_password),
	),
));",
						'security_best_practices' => array(
							'Use HTTPS always',
							'One password per app',
							'Revoke unused passwords',
							'Monitor access logs',
						),
						'recommendation'          => __( 'Enable Application Passwords for secure REST API access', 'wpshadow' ),
					),
				);
			}
		}

		// Pattern 3: REST API endpoints with overly permissive access
		global $wp_rest_server;

		if ( isset( $wp_rest_server ) ) {
			$routes            = $wp_rest_server->get_routes();
			$overly_permissive = array();

			foreach ( $routes as $route => $endpoints ) {
				foreach ( $endpoints as $endpoint ) {
					if ( isset( $endpoint['permission_callback'] ) ) {
						// Check for __return_true permission callbacks
						if ( '__return_true' === $endpoint['permission_callback'] || ( is_array( $endpoint['permission_callback'] ) && '__return_true' === end( $endpoint['permission_callback'] ) ) ) {
							// Check if route handles sensitive operations
							if ( in_array( strtoupper( $endpoint['methods'] ?? 'GET' ), array( 'POST', 'PUT', 'DELETE' ), true ) ) {
								$overly_permissive[] = array(
									'route'   => $route,
									'methods' => $endpoint['methods'] ?? 'unknown',
								);
							}
						}
					}
				}
			}

			if ( ! empty( $overly_permissive ) ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'REST API endpoints allowing public modifications', 'wpshadow' ),
					'severity'     => 'high',
					'threat_level' => 85,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/rest-api-authentication',
					'details'      => array(
						'issue'                      => 'overly_permissive_access',
						'overly_permissive_count'    => count( $overly_permissive ),
						'endpoints'                  => array_slice( $overly_permissive, 0, 10 ),
						'message'                    => sprintf(
							/* translators: %d: number of endpoints */
							__( '%d REST API endpoints allow unauthenticated modifications', 'wpshadow' ),
							count( $overly_permissive )
						),
						'security_risk'              => __( 'Anyone can modify your site without authentication', 'wpshadow' ),
						'modification_methods'       => array(
							'POST'      => 'Create new content',
							'PUT/PATCH' => 'Update existing content',
							'DELETE'    => 'Remove content',
						),
						'possible_attacks'           => array(
							'Spam posts'        => 'Flood site with content',
							'Data destruction'  => 'Delete all posts/pages',
							'Malware injection' => 'Insert malicious code',
							'Defacement'        => 'Change site content',
						),
						'permission_callback_rules'  => array(
							'GET requests'    => 'Can be public if data is non-sensitive',
							'POST/PUT/DELETE' => 'Should require authentication',
							'Admin endpoints' => 'Should require manage_options',
							'User data'       => 'Should require appropriate capability',
						),
						'secure_permission_examples' => "// Not recommended - anyone can modify
register_rest_route('my-plugin/v1', '/posts', array(
	'methods' => 'POST',
	'callback' => 'create_post',
	'permission_callback' => '__return_true', // ❌ Not recommended
));

// Better - only logged-in users
register_rest_route('my-plugin/v1', '/posts', array(
	'methods' => 'POST',
	'callback' => 'create_post',
	'permission_callback' => 'is_user_logged_in', // ✓ Better
));

// Strongest - only administrators
register_rest_route('my-plugin/v1', '/posts', array(
	'methods' => 'POST',
	'callback' => 'create_post',
	'permission_callback' => function() {
		return current_user_can('manage_options'); // ✓ BEST
	},
));",
						'read_only_allowed'          => __( '__return_true acceptable for public GET endpoints only', 'wpshadow' ),
						'checking_current_endpoints' => array(
							'1. Review all custom REST routes',
							'2. Find __return_true on state-changing operations',
							'3. Update permission_callback',
							'4. Test with anonymous user',
						),
						'recommendation'             => __( 'Require authentication for all state-changing REST API operations', 'wpshadow' ),
					),
				);
			}
		}

		// Pattern 4: REST API user enumeration via password reset
		$user_enumeration = wp_remote_get( rest_url( '/wp/v2/users/validate-username' ), array( 'sslverify' => false ) );

		if ( 200 === wp_remote_retrieve_response_code( $user_enumeration ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'REST API allows user enumeration', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/rest-api-authentication',
				'details'      => array(
					'issue'                     => 'user_enumeration',
					'message'                   => __( 'REST API leaks information about registered users', 'wpshadow' ),
					'enumeration_methods'       => array(
						'Username validation'   => '/wp/v2/users/validate-username',
						'User endpoint listing' => '/wp/v2/users',
						'Author queries'        => '/wp/v2/users?search=',
						'Post author fields'    => 'Author IDs in post data',
					),
					'attacker_uses'             => array(
						'Discover admin username',
						'Find valid usernames',
						'Build target list',
						'Credential stuffing attacks',
						'Phishing campaigns',
					),
					'privacy_implications'      => __( 'Reveals which email addresses/usernames are registered', 'wpshadow' ),
					'preventing_enumeration'    => array(
						'Restrict /wp/v2/users to authenticated',
						'Hide author endpoints',
						'Don\'t expose user IDs in posts',
						'Use generic error messages',
					),
					'disable_user_endpoint'     => "add_filter('rest_endpoints', function(\$endpoints) {
	if (!current_user_can('list_users')) {
		unset(\$endpoints['/wp/v2/users']);
		unset(\$endpoints['/wp/v2/users/(?P<id>[\\d]+)']);
	}
	return \$endpoints;
});",
					'hide_author_from_posts'    => "add_filter('rest_prepare_post', function(\$response, \$post) {
	// Don't expose author ID to public
	if (!current_user_can('edit_post', \$post->ID)) {
		unset(\$response->data['author']);
	}
	return \$response;
}, 10, 2);",
					'generic_errors'            => "// Return same response for valid/invalid users
if (!get_user_by('login', \$username)) {
	return new WP_Error('invalid_credentials', 'Invalid username or password');
}",
					'password_reset_protection' => __( 'Implement additional authentication for password reset', 'wpshadow' ),
					'rate_limiting'             => __( 'Aggressively rate limit enumeration attempts', 'wpshadow' ),
					'recommendation'            => __( 'Restrict user enumeration endpoints and hide user information from unauthenticated requests', 'wpshadow' ),
				),
			);
		}

		// Pattern 5: Missing CORS headers for cross-origin requests
		if ( ! headers_sent() ) {
			$origin = get_http_header( 'Origin' );

			if ( $origin && ! get_http_header( 'Access-Control-Allow-Origin' ) ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'REST API CORS headers not properly configured', 'wpshadow' ),
					'severity'     => 'low',
					'threat_level' => 35,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/rest-api-authentication',
					'details'      => array(
						'issue'               => 'missing_cors_headers',
						'message'             => __( 'Cross-origin requests may be blocked', 'wpshadow' ),
						'what_is_cors'        => __( 'Browser security feature restricting cross-origin requests', 'wpshadow' ),
						'when_needed'         => array(
							'Frontend on different domain',
							'Mobile app calling backend',
							'Third-party integrations',
							'Microservices architecture',
						),
						'cors_headers'        => array(
							'Access-Control-Allow-Origin'  => 'Which origins allowed',
							'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE',
							'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
							'Access-Control-Allow-Credentials' => 'Include cookies/auth',
							'Access-Control-Max-Age'       => 'Preflight cache time',
						),
						'without_cors'        => array(
							'Requests fail silently',
							'Browser blocks response',
							'API works but frontend cannot access',
							'Development and production differ',
						),
						'adding_cors_headers' => "add_action('rest_api_init', function() {
	header('Access-Control-Allow-Origin: https://trusted-app.com');
	header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
	header('Access-Control-Allow-Headers: Content-Type, Authorization');
	header('Access-Control-Allow-Credentials: true');
	header('Access-Control-Max-Age: 86400');
});",
						'handling_preflight'  => __( 'OPTIONS requests sent by browser before actual request', 'wpshadow' ),
						'trusted_origins'     => array(
							'Your frontend domain',
							'Mobile app identifier',
							'Third-party partner domains',
							'Development domain',
						),
						'wildcard_caution'    => __( 'Never use * with credentials - only specific domains', 'wpshadow' ),
						'recommendation'      => __( 'Configure CORS headers for legitimate cross-origin requests', 'wpshadow' ),
					),
				);
			}
		}

		// Pattern 6: REST API credentials in URLs or logs
		global $wpdb;

		$api_logs = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->posts} WHERE post_content LIKE %s LIMIT 10",
				'%Authorization%Basic%'
			)
		);

		if ( ! empty( $api_logs ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'REST API credentials detected in content/logs', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 95,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/rest-api-authentication',
				'details'      => array(
					'issue'                  => 'credentials_exposed',
					'message'                => __( 'API credentials found in site content or logs', 'wpshadow' ),
					'security_risk'          => __( 'Anyone with access can use these credentials', 'wpshadow' ),
					'exposed_credentials'    => array(
						'API tokens',
						'Basic auth credentials',
						'JWT tokens',
						'Application passwords',
					),
					'where_credentials_leak' => array(
						'Database backups',
						'Server logs',
						'Git repositories',
						'Error messages',
						'JavaScript console',
					),
					'immediate_actions'      => array(
						'1. Find all exposed credentials',
						'2. Note down what was exposed',
						'3. Revoke those credentials immediately',
						'4. Generate new credentials',
						'5. Check logs for unauthorized access',
						'6. Audit what was accessed',
					),
					'preventing_exposure'    => array(
						'Never hardcode credentials',
						'Use environment variables',
						'Store in wp-config.php',
						'Use password managers',
						'Rotate credentials regularly',
					),
					'secure_patterns'        => "// WRONG - Credential in code
\$api_key = 'sk-12345abcde';

// RIGHT - Use environment variable
\$api_key = getenv('API_KEY');

// BETTER - Use wp-config.php
define('MY_API_KEY', getenv('API_KEY'));

// ACCESS
\$key = MY_API_KEY;",
					'log_monitoring'         => __( 'Monitor server and PHP logs for credential exposure', 'wpshadow' ),
					'git_protection'         => __( 'Use .gitignore to prevent committing credentials', 'wpshadow' ),
					'secrets_scanner'        => __( 'Consider secret scanning tools for repositories', 'wpshadow' ),
					'recommendation'         => __( 'Find and remove exposed credentials, regenerate new ones', 'wpshadow' ),
				),
			);
		}

		return null; // No issues found
	}
}
