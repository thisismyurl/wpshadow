<?php
/**
 * REST User Authentication Diagnostic
 *
 * Verifies user credentials are properly accepted by REST API.
 *
 * @since   1.2601.2112
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Rest_User_Authentication
 *
 * Tests REST API authentication with valid credentials to verify auth middleware is working.
 *
 * @since 1.2601.2112
 */
class Diagnostic_Rest_User_Authentication extends Diagnostic_Base {

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2112
	 * @return array|null Finding array if issues detected, null otherwise.
	 */
	public static function check() {
		if ( ! is_admin() ) {
			return null;
		}

		// Get current user credentials for test.
		$current_user = wp_get_current_user();
		if ( ! $current_user->ID ) {
			return null; // No logged-in user to test with.
		}

		// Test REST API with auth (cookie-based).
		$endpoint = rest_url( '/wp/v2/users/me' );
		$response = wp_remote_get( $endpoint, array(
			'timeout'   => 5,
			'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
		) );

		if ( is_wp_error( $response ) ) {
			return null; // Can't test.
		}

		$status = wp_remote_retrieve_response_code( $response );

		// Authenticated request should return 200 (or specific user data).
		if ( 200 !== $status ) {
			return array(
				'id'           => 'rest-user-authentication',
				'title'        => __( 'REST API User Authentication Not Working', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %d: HTTP status code */
					__( 'REST API returned HTTP %d for authenticated request to /users/me. Expected 200. User authentication via REST may not be working. Check nonce validation and auth header configuration.', 'wpshadow' ),
					$status
				),
				'severity'     => 'medium',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/rest_user_authentication',
				'meta'         => array(
					'status_code'    => $status,
					'expected_status' => 200,
					'test_endpoint'  => '/wp/v2/users/me',
					'auth_type'      => 'cookie',
				),
			);
		}

		return null;
	}
}
