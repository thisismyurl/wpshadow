<?php
/**
 * REST Route Enumeration Diagnostic
 *
 * Checks if server blocks route discovery when REST is disabled.
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
 * Diagnostic_Rest_Route_Enumeration
 *
 * Verifies REST API route enumeration is properly restricted when disabled.
 *
 * @since 1.2601.2112
 */
class Diagnostic_Rest_Route_Enumeration extends Diagnostic_Base {

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

		// Check if REST API is disabled.
		if ( defined( 'REST_REQUEST' ) && ! REST_REQUEST ) {
			// REST is disabled, check if enumeration is blocked.
			$response = wp_remote_get( rest_url( '/' ), array(
				'timeout'   => 5,
				'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
			) );

			if ( is_wp_error( $response ) ) {
				return null; // Can't check, likely offline or misconfigured.
			}

			$status = wp_remote_retrieve_response_code( $response );
			$body   = wp_remote_retrieve_body( $response );

			// If REST is disabled but 200 with route list returned, enumeration is exposed.
			if ( 200 === $status && strpos( $body, 'index' ) !== false ) {
				return array(
					'id'           => 'rest-route-enumeration',
					'title'        => __( 'REST API Routes Enumerable When Disabled', 'wpshadow' ),
					'description'  => __( 'REST API route enumeration is enabled even though REST is disabled. This allows attackers to discover available endpoints. Consider disabling REST API entirely or restricting route visibility.', 'wpshadow' ),
					'severity'     => 'medium',
					'threat_level' => 25,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/rest_route_enumeration',
					'meta'         => array(
						'rest_enabled'  => defined( 'REST_REQUEST' ) ? REST_REQUEST : 'unknown',
						'routes_visible' => true,
					),
				);
			}
		}

		return null;
	}
}
