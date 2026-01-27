<?php
/**
 * Diagnostic: REST API Authentication Test
 *
 * Tests REST API authentication mechanisms to ensure secure access.
 * Verifies that authentication (cookies, nonces, JWT) is properly configured.
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
 * Class Diagnostic_Rest_Api_Auth
 *
 * Validates REST API authentication functionality.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Rest_Api_Auth extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'rest-api-auth-test';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'REST API Authentication Test';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Tests REST API authentication mechanisms';

	/**
	 * Check REST API authentication configuration.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if cookie authentication is available.
		$cookie_auth_available = class_exists( 'WP_REST_Authentication_Cookies' );

		// Check if JWT authentication plugin is active.
		$jwt_auth_active = (
			is_plugin_active( 'jwt-authentication-for-wp-rest-api/jwt-auth.php' ) ||
			is_plugin_active( 'wp-api-jwt-auth/wp-api-jwt-auth.php' ) ||
			is_plugin_active( 'simple-jwt-authentication/simple-jwt-authentication.php' )
		);

		// Check if application passwords are enabled (WP 5.6+).
		$app_passwords_available = function_exists( 'wp_is_application_passwords_available' ) &&
									wp_is_application_passwords_available();

		// Check for custom authentication handlers.
		$custom_auth_handlers = apply_filters( 'rest_authentication_errors', null );
		$has_custom_auth      = is_wp_error( $custom_auth_handlers );

		// Test cookie authentication (if user is logged in).
		if ( is_user_logged_in() && $cookie_auth_available ) {
			$user_id = get_current_user_id();
			$request = new \WP_REST_Request( 'GET', '/wp/v2/users/me' );

			// Simulate authenticated request.
			$response = rest_do_request( $request );

			if ( is_wp_error( $response ) ) {
				$issues[] = __( 'Cookie authentication failed during test', 'wpshadow' );
			}
		}

		// Check if nonce verification works.
		$nonce_check_enabled = has_filter( 'rest_authentication_errors', 'rest_cookie_check_errors' );

		if ( ! $nonce_check_enabled ) {
			$issues[] = __( 'REST API nonce verification may not be enabled', 'wpshadow' );
		}

		// Report findings if multiple authentication methods are missing.
		if ( ! $cookie_auth_available && ! $jwt_auth_active && ! $app_passwords_available && ! $has_custom_auth ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No REST API authentication methods detected. Consider installing a JWT authentication plugin or enabling Application Passwords.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/api-rest-api-auth',
				'meta'        => array(
					'cookie_auth'      => $cookie_auth_available,
					'jwt_auth'         => $jwt_auth_active,
					'app_passwords'    => $app_passwords_available,
					'custom_auth'      => $has_custom_auth,
					'issues'           => $issues,
				),
			);
		}

		// Report minor issues if any detected.
		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: List of issues */
					__( 'REST API authentication issues detected: %s', 'wpshadow' ),
					implode( ', ', $issues )
				),
				'severity'    => 'info',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/api-rest-api-auth',
				'meta'        => array(
					'issues' => $issues,
				),
			);
		}

		// Authentication is properly configured.
		return null;
	}
}
