<?php
/**
 * Diagnostic: REST API Endpoint Errors
 *
 * Checks if REST API index exposes expected namespaces and routes without errors.
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
 * Class Diagnostic_Rest_Api_Endpoint_Errors
 *
 * Tests REST API index for errors.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Rest_Api_Endpoint_Errors extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'rest-api-endpoint-errors';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'REST API Endpoint Errors';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks REST API index for errors and missing namespaces';

	/**
	 * Check REST API index for errors.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$index_url = rest_url();
		$response = wp_remote_get( $index_url );

		if ( is_wp_error( $response ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'REST API index could not be retrieved (request error). Endpoints may be failing.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/rest_api_endpoint_errors',
				'meta'        => array(
					'index_url'     => $index_url,
					'error_message' => $response->get_error_message(),
				),
			);
		}

		$status = wp_remote_retrieve_response_code( $response );
		$body   = wp_remote_retrieve_body( $response );
		$data   = json_decode( $body, true );

		if ( $status >= 400 || empty( $data ) || isset( $data['code'] ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'REST API index returned an error response. Endpoints may be failing or blocked.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/rest_api_endpoint_errors',
				'meta'        => array(
					'index_url'   => $index_url,
					'http_status' => $status,
					'api_error'   => isset( $data['code'] ) ? $data['code'] : '',
				),
			);
		}

		$namespaces = isset( $data['namespaces'] ) && is_array( $data['namespaces'] ) ? $data['namespaces'] : array();

		if ( ! in_array( 'wp/v2', $namespaces, true ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'REST API wp/v2 namespace is missing from the index. Core endpoints may be disabled or blocked.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/rest_api_endpoint_errors',
				'meta'        => array(
					'index_url'  => $index_url,
					'namespaces' => $namespaces,
				),
			);
		}

		return null;
	}
}
