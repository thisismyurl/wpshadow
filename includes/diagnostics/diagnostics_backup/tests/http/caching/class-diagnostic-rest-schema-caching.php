<?php
/**
 * REST Schema Caching Diagnostic
 *
 * Detects if REST API schema is properly cached.
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
 * Diagnostic_Rest_Schema_Caching
 *
 * Checks REST API schema caching headers for proper performance optimization.
 *
 * @since 1.2601.2112
 */
class Diagnostic_Rest_Schema_Caching extends Diagnostic_Base {

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

		// Test REST schema endpoint caching.
		$schema_url = rest_url( '/' );
		$response   = wp_remote_head( $schema_url, array(
			'timeout'   => 5,
			'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
		) );

		if ( is_wp_error( $response ) ) {
			return null; // Can't test.
		}

		$headers = wp_remote_retrieve_headers( $response );
		$cache_control = $headers['cache-control'] ?? '';
		$etag         = $headers['etag'] ?? '';

		// Schema should have cache headers to reduce repeated schema requests.
		if ( empty( $cache_control ) && empty( $etag ) ) {
			return array(
				'id'           => 'rest-schema-caching',
				'title'        => __( 'REST Schema Not Cached', 'wpshadow' ),
				'description'  => __( 'REST API schema endpoint is missing cache headers (Cache-Control or ETag). This means clients request the full schema on every call, increasing server load. Add caching headers to improve performance.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/rest_schema_caching',
				'meta'         => array(
					'has_cache_control' => ! empty( $cache_control ),
					'has_etag'         => ! empty( $etag ),
				),
			);
		}

		return null;
	}
}
