<?php
/**
 * Diagnostic: REST Batch Endpoint
 *
 * Checks if the REST API batch endpoint is available.
 * The batch endpoint enables efficient bulk REST requests.
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
 * Class Diagnostic_Rest_Batch_Endpoint
 *
 * Tests REST API batch endpoint availability.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Rest_Batch_Endpoint extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'rest-batch-endpoint';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'REST Batch Endpoint';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if the REST API batch endpoint is available';

	/**
	 * Check REST batch endpoint.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$batch_url = rest_url( 'wp/v2/batch' );
		$response = wp_remote_post( $batch_url, array( 'timeout' => 5 ) );

		if ( is_wp_error( $response ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'REST batch endpoint could not be reached. Batch requests may be blocked by the server or a security plugin.', 'wpshadow' ),
				'severity'    => 'info',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/rest_batch_endpoint',
				'meta'        => array(
					'batch_url'     => $batch_url,
					'error_message' => $response->get_error_message(),
				),
			);
		}

		$status = wp_remote_retrieve_response_code( $response );

		if ( 404 === $status || 400 === $status ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'REST batch endpoint returned 400/404. It may be disabled or unsupported by this WordPress version or server configuration.', 'wpshadow' ),
				'severity'    => 'info',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/rest_batch_endpoint',
				'meta'        => array(
					'batch_url'   => $batch_url,
					'http_status' => $status,
				),
			);
		}

		return null;
	}
}
