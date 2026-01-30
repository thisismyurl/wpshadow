<?php
/**
 * REST Post Creation Diagnostic
 *
 * Tests REST API post creation functionality.
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
 * Diagnostic_Rest_Post_Creation
 *
 * Tests REST API post creation to ensure editing via REST is functional.
 *
 * @since 1.2601.2112
 */
class Diagnostic_Rest_Post_Creation extends Diagnostic_Base {

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

		// Check if current user can create posts.
		if ( ! current_user_can( 'create_posts' ) ) {
			return null; // User can't create posts anyway.
		}

		// Test POST endpoint for post creation.
		$posts_endpoint = rest_url( '/wp/v2/posts' );
		$response       = wp_remote_post( $posts_endpoint, array(
			'timeout'   => 5,
			'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
			'headers'   => array(
				'Content-Type' => 'application/json',
			),
			'body'      => json_encode( array(
				'title'  => 'REST Test Post ' . wp_rand(),
				'status' => 'draft',
			) ),
		) );

		if ( is_wp_error( $response ) ) {
			return array(
				'id'           => 'rest-post-creation',
				'title'        => __( 'REST API Post Creation Failed', 'wpshadow' ),
				'description'  => __( 'Could not create a post via REST API. Editing posts in REST-based editors may not work. Check REST authentication and permissions.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/rest_post_creation',
				'meta'         => array(
					'error_message' => $response->get_error_message(),
					'endpoint'      => '/wp/v2/posts',
				),
			);
		}

		$status = wp_remote_retrieve_response_code( $response );

		// POST should return 201 (Created) or 200.
		if ( 201 !== $status && 200 !== $status ) {
			$body = wp_remote_retrieve_body( $response );
			return array(
				'id'           => 'rest-post-creation',
				'title'        => __( 'REST API Post Creation Misconfigured', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %d: HTTP status code */
					__( 'POST to REST posts endpoint returned HTTP %d instead of 201/200. Post creation via REST may not work.', 'wpshadow' ),
					$status
				),
				'severity'     => 'medium',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/rest_post_creation',
				'meta'         => array(
					'status_code' => $status,
					'endpoint'    => '/wp/v2/posts',
				),
			);
		}

		return null;
	}
}
