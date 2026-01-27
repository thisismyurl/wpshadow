<?php
/**
 * REST Unauthorized Error Diagnostic
 *
 * Confirms correct 401/403 errors are returned on unauthorized requests.
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
 * Diagnostic_Rest_Unauthorized_Error
 *
 * Tests REST API error responses for proper 401/403 status codes on auth failures.
 *
 * @since 1.2601.2112
 */
class Diagnostic_Rest_Unauthorized_Error extends Diagnostic_Base {

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

		// Test protected endpoint without auth.
		$protected_endpoint = rest_url( '/wp/v2/users/me' );
		$response           = wp_remote_get( $protected_endpoint, array(
			'timeout'   => 5,
			'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
			// Don't include auth headers.
		) );

		if ( is_wp_error( $response ) ) {
			return null; // Can't test.
		}

		$status = wp_remote_retrieve_response_code( $response );

		// Should return 401 (Unauthorized) or 403 (Forbidden).
		if ( 401 !== $status && 403 !== $status ) {
			return array(
				'id'           => 'rest-unauthorized-error',
				'title'        => __( 'REST API Not Properly Rejecting Unauthorized Requests', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %d: HTTP status code */
					__( 'REST API returned HTTP %d for unauthorized request instead of 401/403. This may indicate authentication middleware is not working. Make sure REST authentication is properly configured.', 'wpshadow' ),
					$status
				),
				'severity'     => 'medium',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/rest_unauthorized_error',
				'meta'         => array(
					'status_code'      => $status,
					'expected_codes'   => array( 401, 403 ),
					'test_endpoint'    => '/wp/v2/users/me',
				),
			);
		}

		return null;
	}
}
