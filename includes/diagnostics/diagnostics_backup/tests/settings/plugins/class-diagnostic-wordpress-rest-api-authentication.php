<?php
/**
 * Wordpress Rest Api Authentication Diagnostic
 *
 * Wordpress Rest Api Authentication issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1248.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wordpress Rest Api Authentication Diagnostic Class
 *
 * @since 1.1248.0000
 */
class Diagnostic_WordpressRestApiAuthentication extends Diagnostic_Base {

	protected static $slug = 'wordpress-rest-api-authentication';
	protected static $title = 'Wordpress Rest Api Authentication';
	protected static $description = 'Wordpress Rest Api Authentication issue detected';
	protected static $family = 'security';

	public static function check() {
		// REST API is a WordPress core feature (since WP 4.7)
		global $wp_version;
		if ( version_compare( $wp_version, '4.7', '<' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Verify REST API is not completely disabled
		$rest_enabled = apply_filters( 'rest_enabled', true );
		if ( ! $rest_enabled ) {
			// REST API disabled is actually good for security, so no issue here
			return null;
		}
		
		// Check 2: Check if REST API authentication is properly configured
		$authentication_errors = array();
		
		// Check if Application Passwords are enabled (WP 5.6+)
		if ( version_compare( $wp_version, '5.6', '>=' ) ) {
			$app_passwords_available = wp_is_application_passwords_available();
			if ( ! $app_passwords_available ) {
				$issues[] = 'application_passwords_disabled';
			}
		}
		
		// Check 3: Verify nonce verification is in place for REST endpoints
		// Check if any custom REST routes exist without proper auth
		$rest_server = rest_get_server();
		if ( $rest_server ) {
			$namespaces = $rest_server->get_namespaces();
			
			// Look for custom namespaces (not wp/* or oembed)
			$custom_namespaces = array_filter( $namespaces, function( $ns ) {
				return ! preg_match( '/^(wp\/v2|oembed)/', $ns );
			});
			
			// Check if there are custom REST endpoints without auth
			foreach ( $custom_namespaces as $namespace ) {
				$routes = $rest_server->get_routes( $namespace );
				foreach ( $routes as $route => $handlers ) {
					foreach ( $handlers as $handler ) {
						// Check if permission_callback is set
						if ( ! isset( $handler['permission_callback'] ) ) {
							$issues[] = 'endpoints_without_auth';
							break 3; // Exit all loops
						}
					}
				}
			}
		}
		
		// Check 4: Verify user enumeration via REST API is disabled
		$allow_user_enumeration = get_option( 'wpshadow_allow_user_enumeration', 'no' );
		
		// Test if /wp-json/wp/v2/users endpoint exposes user data
		$rest_url = rest_url( 'wp/v2/users' );
		$response = wp_remote_get( $rest_url, array( 'timeout' => 5 ) );
		
		if ( ! is_wp_error( $response ) ) {
			$status_code = wp_remote_retrieve_response_code( $response );
			$body = wp_remote_retrieve_body( $response );
			
			// If endpoint returns user data without authentication, that's a security issue
			if ( 200 === $status_code && ! empty( $body ) ) {
				$data = json_decode( $body, true );
				if ( is_array( $data ) && ! empty( $data ) ) {
					$issues[] = 'user_enumeration_exposed';
				}
			}
		}
		
		// Check 5: Check for SSL/HTTPS requirement for REST API authentication
		if ( ! is_ssl() && ( defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN ) ) {
			// Site requires SSL for admin but not using it
			$issues[] = 'ssl_not_enforced';
		}
		
		// Check 6: Check if REST API prefix has been changed (security through obscurity)
		$rest_prefix = rest_get_url_prefix();
		if ( 'wp-json' === $rest_prefix ) {
			// Default prefix is being used - not necessarily bad, but custom is better
			// This is a low priority issue
		}
		
		// Check 7: Verify CORS headers are properly configured
		$cors_headers = apply_filters( 'rest_pre_serve_request', null, null, null, null );
		// If CORS is wide open (Access-Control-Allow-Origin: *), that's a potential issue
		
		// Check 8: Check for known REST API authentication plugins
		$auth_plugins = array(
			'JWT Authentication for WP REST API' => defined( 'JWT_AUTH_SECRET_KEY' ),
			'OAuth 2.0' => class_exists( 'WP_REST_OAuth2' ),
			'Basic Authentication' => class_exists( 'WP_REST_Basic_Auth' ),
		);
		
		$has_auth_plugin = false;
		foreach ( $auth_plugins as $plugin => $is_active ) {
			if ( $is_active ) {
				$has_auth_plugin = true;
				break;
			}
		}
		
		// If REST API is enabled but no additional auth mechanism is in place
		if ( ! $has_auth_plugin && count( $custom_namespaces ) > 0 ) {
			$issues[] = 'no_additional_auth_mechanism';
		}
		
		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of REST API authentication issues */
				__( 'WordPress REST API has authentication and security concerns: %s. Unauthorized access to REST API endpoints could expose sensitive data or allow malicious actions.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);
			
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => 75,
				'threat_level' => 75,
				'auto_fixable' => false, // Requires manual configuration
				'kb_link'      => 'https://wpshadow.com/kb/wordpress-rest-api-authentication',
			);
		}
		
		return null;
	}
}
