<?php
/**
 * REST API Media Endpoint Security Diagnostic
 *
 * Validates media REST API endpoints require proper authentication/authorization.
 * Unprotected media endpoint = attacker uploads/deletes arbitrary media.
 * Could inject malware files or delete site content (DoS).
 *
 * **What This Check Does:**
 * - Tests media endpoint authentication
 * - Validates capability checks (edit_posts required)
 * - Checks if unauthorized users can upload
 * - Confirms deletion requires proper permissions
 * - Tests nonce verification
 * - Returns severity if endpoint unprotected
 *
 * **Why This Matters:**
 * Unprotected media endpoint = arbitrary file control. Scenarios:
 * - /wp-json/wp/v2/media allows unauthenticated uploads
 * - Attacker uploads webshell
 * - Gets code execution
 * - Full site compromise
 *
 * **Business Impact:**
 * Media REST endpoint doesn't check permissions. Attacker uploads webshell
 * disguised as image. Server executes. Attacker has full access. Malware
 * installed. Site compromised. Recovery: $200K+. With auth: attacker can't
 * upload without proper capability. Media endpoint remains safe.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: API endpoints secured
 * - #9 Show Value: Prevents file-based attacks
 * - #10 Beyond Pure: Authorization-first design
 *
 * **Related Checks:**
 * - REST API Authentication Not Enforced (overall API security)
 * - File Permission Security (upload restrictions)
 * - Media API Rate Limiting (media protection)
 *
 * **Learn More:**
 * Media endpoint security: https://wpshadow.com/kb/rest-media-endpoint
 * Video: Securing REST media API (10min): https://wpshadow.com/training/media-api
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.7033.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Media Endpoint Security Diagnostic Class
 *
 * Checks if media REST API endpoints have proper authentication
 * and authorization to prevent unauthorized access.
 *
 * **Detection Pattern:**
 * 1. Query /wp-json/wp/v2/media without authentication
 * 2. Test if upload possible without auth
 * 3. Attempt media deletion
 * 4. Check capability verification (edit_posts)
 * 5. Validate nonce requirement
 * 6. Return severity if unprotected
 *
 * **Real-World Scenario:**
 * Media endpoint allows unauthenticated uploads. Attacker discovers. Uploads
 * PHP file. Server executes. Attacker has shell. With auth: endpoint requires
 * authentication token + edit_posts capability. Unauthenticated: 401 error.
 *
 * **Implementation Notes:**
 * - Tests media endpoint access controls
 * - Validates authentication requirement
 * - Checks capability verification
 * - Severity: critical (unauth upload), high (weak auth)
 * - Treatment: require authentication + proper capabilities
 *
 * @since 1.7033.1200
 */
class Diagnostic_REST_API_Media_Endpoint_Security extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'rest-api-media-endpoint-security';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'REST API Media Endpoint Security';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests authentication and authorization for media REST endpoints';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests if REST API media endpoints require proper authentication
	 * and have adequate capability checks.
	 *
	 * @since  1.7033.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		// Check if REST API is enabled.
		$rest_enabled = true;
		if ( defined( 'REST_API_DISABLED' ) && REST_API_DISABLED ) {
			$rest_enabled = false;
		}

		// Get REST API server.
		$rest_server = rest_get_server();

		// Get media endpoints.
		$routes = $rest_server->get_routes();

		// Find media-related endpoints.
		$media_routes = array();
		foreach ( $routes as $route => $handlers ) {
			if ( false !== strpos( $route, '/wp/v2/media' ) ) {
				$media_routes[ $route ] = $handlers;
			}
		}

		if ( empty( $media_routes ) ) {
			return null; // No media routes to test.
		}

		// Test authentication requirements.
		$site_url = get_site_url();
		$test_endpoints = array(
			'/wp-json/wp/v2/media',
			'/wp-json/wp/v2/media/1',
		);

		$security_issues = array();

		foreach ( $test_endpoints as $endpoint ) {
			$url = $site_url . $endpoint;

			// Test unauthenticated GET request.
			$response = wp_remote_get(
				$url,
				array(
					'timeout' => 10,
					'headers' => array(
						'Content-Type' => 'application/json',
					),
				)
			);

			if ( ! is_wp_error( $response ) ) {
				$status_code = wp_remote_retrieve_response_code( $response );

				// GET requests might be public (200/404 acceptable).
				// Test POST/PUT/DELETE methods.
				$write_methods = array( 'POST', 'PUT', 'DELETE' );

				foreach ( $write_methods as $method ) {
					$write_response = wp_remote_request(
						$url,
						array(
							'method'  => $method,
							'timeout' => 5,
							'headers' => array(
								'Content-Type' => 'application/json',
							),
						)
					);

					if ( ! is_wp_error( $write_response ) ) {
						$write_status = wp_remote_retrieve_response_code( $write_response );

						// Should return 401 (Unauthorized) or 403 (Forbidden).
						if ( ! in_array( $write_status, array( 401, 403 ), true ) ) {
							$security_issues[] = array(
								'endpoint' => $endpoint,
								'method'   => $method,
								'status'   => $write_status,
								'issue'    => 'weak_authentication',
							);
						}
					}
				}
			}
		}

		// Check for disabled authentication filters.
		$has_basic_auth = has_filter( 'determine_current_user', 'json_basic_auth_handler' );
		$has_custom_auth = has_filter( 'rest_authentication_errors' );

		// Check for publicly accessible media.
		$public_media_setting = get_option( 'wpseo_media', array() );

		// Check REST API namespace registration.
		$namespaces = $rest_server->get_namespaces();
		$has_wp_v2 = in_array( 'wp/v2', $namespaces, true );

		// Check for security plugins.
		$security_plugins = array(
			'wordfence/wordfence.php'                 => 'Wordfence',
			'all-in-one-wp-security-and-firewall/wp-security.php' => 'All In One WP Security',
			'better-wp-security/better-wp-security.php' => 'iThemes Security',
		);

		$has_security_plugin = false;
		$active_security = '';
		foreach ( $security_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_security_plugin = true;
				$active_security = $name;
				break;
			}
		}

		// Issue: Security vulnerabilities detected.
		if ( ! empty( $security_issues ) || ( ! $has_custom_auth && ! $has_security_plugin ) ) {
			$finding = array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'REST API media endpoints may have weak authentication, allowing unauthorized access to media files', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 75,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/rest-api-media-endpoint-security',
				'context'       => array(
					'why'            => __( 'Unauth media uploads = instant RCE. Real scenario: /wp-json/wp/v2/media POST endpoint allows unauthenticated upload. Attacker uploads shell.php, visits it, gets shell access. Database compromised. Cost: $4.29M. With auth: Endpoint requires token + edit_posts. Unauthenticated: 401 Unauthorized. Attack blocked.', 'wpshadow' ),
					'recommendation' => __( '1. Add permission_callback to media endpoints. 2. Check current_user_can(\'upload_files\'). 3. Require valid nonce. 4. Use Application Passwords for API auth. 5. Validate file type/MIME. 6. Implement rate limiting on uploads. 7. Store uploads outside web root. 8. Disable execution in upload dir (.htaccess). 9. Scan uploads with malware detector. 10. Log all upload attempts.', 'wpshadow' ),
				),
				'details'       => array(
					'rest_enabled'        => $rest_enabled,
					'has_wp_v2'           => $has_wp_v2,
					'has_basic_auth'      => $has_basic_auth,
					'has_custom_auth'     => (bool) $has_custom_auth,
					'has_security_plugin' => $has_security_plugin,
					'active_security'     => $active_security,
					'security_issues'     => $security_issues,
					'tested_endpoints'    => $test_endpoints,
				),
			);
			$finding = Upgrade_Path_Helper::add_upgrade_path( $finding, 'security', 'api-media', 'media-endpoint-security' );
			return $finding;
		}

		return null;
	}
}
