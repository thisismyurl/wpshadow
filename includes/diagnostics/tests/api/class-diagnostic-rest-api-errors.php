<?php
/**
 * Diagnostic: REST API Endpoint Errors
 *
 * Scans core REST API endpoints for errors or missing handlers.
 * Broken endpoints can indicate plugin conflicts or server configuration issues.
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
 * Class Diagnostic_Rest_Api_Errors
 *
 * Scans REST API endpoints for errors and reports issues.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Rest_Api_Errors extends Diagnostic_Base {

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
	protected static $description = 'Scans REST API endpoints for errors or missing handlers';

	/**
	 * Core REST API endpoints to test.
	 *
	 * @var array
	 */
	private static $core_endpoints = array(
		'/wp/v2/posts',
		'/wp/v2/pages',
		'/wp/v2/media',
		'/wp/v2/users',
		'/wp/v2/comments',
		'/wp/v2/taxonomies',
		'/wp/v2/categories',
		'/wp/v2/tags',
		'/wp/v2/settings',
	);

	/**
	 * Check REST API endpoints for errors.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$errors       = array();
		$rest_url     = rest_url();
		$error_count  = 0;

		foreach ( self::$core_endpoints as $endpoint ) {
			$url      = $rest_url . ltrim( $endpoint, '/' );
			$response = wp_remote_get(
				$url,
				array(
					'timeout'   => 5,
					'sslverify' => false,
				)
			);

			// Check for HTTP errors.
			if ( is_wp_error( $response ) ) {
				$errors[ $endpoint ] = $response->get_error_message();
				++$error_count;
				continue;
			}

			$status_code = wp_remote_retrieve_response_code( $response );

			// Endpoints should respond with 200 (OK) or 401 (Unauthorized, but functional).
			if ( ! in_array( $status_code, array( 200, 401 ), true ) ) {
				$errors[ $endpoint ] = sprintf(
					/* translators: %d: HTTP status code */
					__( 'Returned status code %d', 'wpshadow' ),
					$status_code
				);
				++$error_count;
			}
		}

		// Report if multiple endpoints have errors.
		if ( $error_count >= 3 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: Number of endpoints with errors */
					_n(
						'%d REST API endpoint returned errors',
						'%d REST API endpoints returned errors',
						$error_count,
						'wpshadow'
					),
					$error_count
				),
				'severity'    => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/api-rest-api-errors',
				'meta'        => array(
					'error_count' => $error_count,
					'errors'      => $errors,
				),
			);
		}

		// Report if 1-2 endpoints have issues (lower severity).
		if ( $error_count > 0 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: Number of endpoints with errors */
					_n(
						'%d REST API endpoint returned errors',
						'%d REST API endpoints returned errors',
						$error_count,
						'wpshadow'
					),
					$error_count
				),
				'severity'    => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/api-rest-api-errors',
				'meta'        => array(
					'error_count' => $error_count,
					'errors'      => $errors,
				),
			);
		}

		// All endpoints are functional.
		return null;
	}
}
