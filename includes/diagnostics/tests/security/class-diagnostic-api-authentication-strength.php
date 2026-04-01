<?php
/**
 * API Authentication Strength Diagnostic
 *
 * Validates REST API authentication mechanisms and strength.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * API Authentication Strength Diagnostic
 *
 * Checks REST API authentication configuration and security.
 *
 * @since 0.6093.1200
 */
class Diagnostic_API_Authentication_Strength extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'api-authentication-strength';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'REST API Authentication Strength';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates REST API authentication mechanisms and strength';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$recommendations = array();

		// Check if REST API is enabled
		if ( get_option( 'rest_api_disabled' ) ) {
			// REST API disabled - this is actually good
			return null;
		}

		// Check for basic authentication plugins
		$basic_auth_plugins = array(
			'jwt-authentication-for-wp-rest-api/jwt-auth.php',
			'basic-authentication/plugin.php',
		);

		$active_plugins = get_option( 'active_plugins', array() );
		$has_auth_plugin = false;

		foreach ( $basic_auth_plugins as $plugin ) {
			if ( in_array( $plugin, $active_plugins, true ) ) {
				$has_auth_plugin = true;
				break;
			}
		}

		// Check REST API unauthenticated access
		$response = wp_remote_get( rest_url( '/wp/v2/users' ) );

		if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
			$issues[] = __( 'REST API allows unauthenticated access to user data', 'wpshadow' );
			$recommendations[] = __( 'Consider restricting REST API endpoints or requiring authentication', 'wpshadow' );
		}

		// Check if REST endpoints require capability checks
		global $wp_rest_server;

		$endpoints = array(
			'/wp/v2/posts',
			'/wp/v2/pages',
			'/wp/v2/users',
		);

		foreach ( $endpoints as $endpoint ) {
			$route = rest_get_route_for_post_type_items( 'post' );
			if ( '/wp/v2/posts' === $endpoint ) {
				// Check post endpoint permissions
				$rest_args = rest_get_collection_params();
				if ( empty( $rest_args['capability'] ) ) {
					$issues[] = sprintf(
						/* translators: %s: REST endpoint */
						__( 'Endpoint %s may have insufficient permission checks', 'wpshadow' ),
						$endpoint
					);
				}
			}
		}

		// Check for exposed sensitive parameters
		$rest_routes = $GLOBALS['wp_rest_server']->get_routes() ?? array();

		foreach ( $rest_routes as $route => $endpoints ) {
			// Check if debugging endpoints are exposed
			if ( strpos( $route, 'debug' ) !== false || strpos( $route, 'system-info' ) !== false ) {
				if ( ! is_user_logged_in() ) {
					$issues[] = sprintf(
						/* translators: %s: REST route */
						__( 'Debug endpoint %s is publicly accessible', 'wpshadow' ),
						$route
					);
				}
			}
		}

		// Check CORS headers
		$cors_headers = array(
			'Access-Control-Allow-Origin',
			'Access-Control-Allow-Credentials',
		);

		// Check if wildcard CORS is enabled
		$headers = headers_list();
		foreach ( $headers as $header ) {
			if ( strpos( $header, 'Access-Control-Allow-Origin: *' ) !== false ) {
				$issues[] = __( 'CORS is configured with wildcard (*) allowing all origins', 'wpshadow' );
				$recommendations[] = __( 'Configure CORS to only allow trusted domains', 'wpshadow' );
			}
		}

		// Check for JWT token security
		if ( $has_auth_plugin ) {
			$jwt_secret = get_option( 'jwt_auth_secret_key' );
			if ( empty( $jwt_secret ) ) {
				$issues[] = __( 'JWT authentication is enabled but secret key is not configured', 'wpshadow' );
				$recommendations[] = __( 'Generate and configure a strong JWT secret key', 'wpshadow' );
			}
		}

		// Check if HTTPS is enforced for API requests
		if ( ! is_ssl() ) {
			$issues[] = __( 'REST API is not served over HTTPS, credentials could be intercepted', 'wpshadow' );
			$recommendations[] = __( 'Enable SSL/TLS to protect API authentication credentials', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			$finding = array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'REST API has potential authentication security issues', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 75,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/api-authentication-strength?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'       => array(
					'why'            => __( 'Weak API auth = easy compromise. Real scenario: API uses basic auth over HTTP. Attacker sniffs network. Captures credentials. Impersonates user. Deletes all posts, steals data. Cost: $4M+ incident. With strong auth: OAuth 2.0 + HTTPS. Credentials never transmitted. Attack impossible.', 'wpshadow' ),
					'recommendation' => __( '1. Implement OAuth 2.0 or JWT authentication. 2. Use strong secret keys: 32+ characters, random. 3. Enforce HTTPS (TLS1.0) for all API requests. 4. Disable basic authentication over HTTP. 5. Implement token expiration: 15min access, 7day refresh. 6. Validate CORS: whitelist specific origins (not *). 7. Check JWT secret is strong (not default). 8. Implement rate limiting on auth endpoints. 9. Log authentication failures. 10. Rotate keys regularly (60-day cycle).', 'wpshadow' ),
				),
				'details'       => array(
					'issues'          => $issues,
					'recommendations' => $recommendations,
				),
			);
			$finding = Upgrade_Path_Helper::add_upgrade_path( $finding, 'security', 'api-auth', 'authentication-strength' );
			return $finding;
		}

		return null;
	}
}
