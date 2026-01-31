<?php
/**
 * Diagnostic: AJAX Endpoint Availability
 *
 * Tests if /wp-admin/admin-ajax.php endpoint is accessible and responding correctly.
 * Many WordPress plugins and themes rely on AJAX functionality for dynamic interactions.
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
 * Class Diagnostic_Ajax_Endpoint
 *
 * Verifies AJAX endpoint availability and functionality.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Ajax_Endpoint extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'ajax-endpoint-availability';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'AJAX Endpoint Availability';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Tests if admin-ajax.php endpoint responds correctly';

	/**
	 * Check if AJAX endpoint is available and responding.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$ajax_url = admin_url( 'admin-ajax.php' );

		// Test AJAX endpoint with a simple request.
		$response = wp_remote_get(
			add_query_arg( 'action', 'wpshadow_test_ajax', $ajax_url ),
			array(
				'timeout'   => 5,
				'sslverify' => false, // Allow self-signed certs in dev.
			)
		);

		// Check for HTTP errors.
		if ( is_wp_error( $response ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: Error message */
					__( 'AJAX endpoint is not accessible: %s', 'wpshadow' ),
					$response->get_error_message()
				),
				'severity'    => 'medium',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/api-ajax-endpoint',
				'meta'        => array(
					'ajax_url' => $ajax_url,
					'error'    => $response->get_error_message(),
				),
			);
		}

		$status_code = wp_remote_retrieve_response_code( $response );

		// AJAX endpoint should respond with 200, 302, or 400 (action not registered is expected).
		if ( ! in_array( $status_code, array( 200, 302, 400 ), true ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: HTTP status code */
					__( 'AJAX endpoint returned unexpected status code: %d', 'wpshadow' ),
					$status_code
				),
				'severity'    => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/api-ajax-endpoint',
				'meta'        => array(
					'ajax_url'    => $ajax_url,
					'status_code' => $status_code,
				),
			);
		}

		// AJAX endpoint is functional.
		return null;
	}
}
