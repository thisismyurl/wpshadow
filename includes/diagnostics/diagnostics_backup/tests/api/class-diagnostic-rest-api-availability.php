<?php
/**
 * Diagnostic: REST API Availability Check
 *
 * Verifies that the WordPress REST API is accessible and responding correctly.
 * Modern WordPress features (Gutenberg, block editor) and many plugins depend on REST API.
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
 * Class Diagnostic_Rest_Api_Availability
 *
 * Tests REST API endpoint availability and basic functionality.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Rest_Api_Availability extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'rest-api-availability';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'REST API Availability Check';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Verifies WordPress REST API is accessible and responding';

	/**
	 * Check if REST API is available and functional.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$rest_url = rest_url();

		// Test REST API root endpoint.
		$response = wp_remote_get(
			$rest_url,
			array(
				'timeout'   => 5,
				'sslverify' => false,
			)
		);

		// Check for HTTP errors.
		if ( is_wp_error( $response ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: Error message */
					__( 'REST API is not accessible: %s', 'wpshadow' ),
					$response->get_error_message()
				),
				'severity'    => 'medium',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/api-rest-api-availability',
				'meta'        => array(
					'rest_url' => $rest_url,
					'error'    => $response->get_error_message(),
				),
			);
		}

		$status_code = wp_remote_retrieve_response_code( $response );

		// REST API should respond with 200.
		if ( 200 !== $status_code ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: HTTP status code */
					__( 'REST API returned unexpected status code: %d', 'wpshadow' ),
					$status_code
				),
				'severity'    => 'medium',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/api-rest-api-availability',
				'meta'        => array(
					'rest_url'    => $rest_url,
					'status_code' => $status_code,
				),
			);
		}

		// Verify response is valid JSON.
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'REST API returned invalid JSON response', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/api-rest-api-availability',
				'meta'        => array(
					'rest_url'   => $rest_url,
					'json_error' => json_last_error_msg(),
				),
			);
		}

		// Verify essential properties exist in response.
		if ( ! isset( $data['name'] ) || ! isset( $data['namespaces'] ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'REST API response is missing expected properties', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/api-rest-api-availability',
				'meta'        => array(
					'rest_url' => $rest_url,
					'response' => $data,
				),
			);
		}

		// REST API is functional.
		return null;
	}
}
