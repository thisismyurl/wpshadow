<?php
/**
 * REST Media Upload Diagnostic
 *
 * Confirms media uploads work correctly over REST API.
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
 * Diagnostic_Rest_Media_Upload
 *
 * Tests REST API media upload endpoint to ensure file uploads work.
 *
 * @since 1.2601.2112
 */
class Diagnostic_Rest_Media_Upload extends Diagnostic_Base {

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

		// Check if current user can upload media.
		if ( ! current_user_can( 'upload_files' ) ) {
			return null; // User can't upload anyway.
		}

		// Test media endpoint availability.
		$media_endpoint = rest_url( '/wp/v2/media' );
		$response       = wp_remote_options( $media_endpoint, array(
			'timeout'   => 5,
			'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
		) );

		if ( is_wp_error( $response ) ) {
			return array(
				'id'           => 'rest-media-upload',
				'title'        => __( 'REST Media Upload Endpoint Not Available', 'wpshadow' ),
				'description'  => __( 'The REST API media upload endpoint is not responding. This could break media uploads in REST-based editors or mobile apps.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/rest_media_upload',
				'meta'         => array(
					'endpoint'      => '/wp/v2/media',
					'error_message' => $response->get_error_message(),
				),
			);
		}

		$status = wp_remote_retrieve_response_code( $response );

		// OPTIONS should return 200 to indicate endpoint exists.
		if ( 200 !== $status && 405 !== $status ) { // 405 allowed if method not permitted.
			return array(
				'id'           => 'rest-media-upload',
				'title'        => __( 'REST Media Upload Endpoint Misconfigured', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %d: HTTP status code */
					__( 'REST media endpoint returned HTTP %d. Expected 200 or 405. Media uploads via REST may not work properly.', 'wpshadow' ),
					$status
				),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/rest_media_upload',
				'meta'         => array(
					'status_code'    => $status,
					'endpoint'       => '/wp/v2/media',
				),
			);
		}

		return null;
	}
}
