<?php
/**
 * REST API CORS Leak Diagnostic
 *
 * Detects overbroad CORS policies exposing the REST API.
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
 * Diagnostic_Rest_Api_Cors_Leak
 *
 * Checks REST API responses for overly permissive CORS headers.
 *
 * @since 1.2601.2112
 */
class Diagnostic_Rest_Api_Cors_Leak extends Diagnostic_Base {

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

		$rest_url = rest_url( '/' );
		$response = wp_remote_head( $rest_url, array(
			'timeout'   => 5,
			'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
		) );

		if ( is_wp_error( $response ) ) {
			return null; // Unable to inspect.
		}

		$headers = wp_remote_retrieve_headers( $response );
		$cors    = $headers['access-control-allow-origin'] ?? '';

		if ( empty( $cors ) ) {
			return null; // No CORS header, not leaking.
		}

		$site_host = wp_parse_url( home_url(), PHP_URL_HOST );
		$leaky     = '*' === $cors;

		if ( ! $leaky && ! empty( $site_host ) ) {
			$header_host = wp_parse_url( $cors, PHP_URL_HOST );
			if ( ! empty( $header_host ) && $header_host !== $site_host ) {
				$leaky = true;
			}
		}

		if ( $leaky ) {
			return array(
				'id'           => 'rest-api-cors-leak',
				'title'        => __( 'REST API CORS Policy Is Overly Permissive', 'wpshadow' ),
				'description'  => __( 'The REST API is returning an Access-Control-Allow-Origin header that allows any origin or a non-site origin. This can expose API responses to other domains. Restrict CORS to your site domain.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/rest_api_cors_leak',
				'meta'         => array(
					'cors_header' => $cors,
					'site_host'   => $site_host,
				),
			);
		}

		return null;
	}
}
