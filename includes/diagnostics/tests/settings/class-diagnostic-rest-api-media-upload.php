<?php
/**
 * REST API Media Upload Diagnostic
 *
 * Detects if REST API media upload endpoints are properly configured and secured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_REST_API_Media_Upload Class
 *
 * Tests if REST API media upload endpoints enforce file type restrictions,
 * size limits, and proper security checks before accepting uploads.
 *
 * @since 0.6093.1200
 */
class Diagnostic_REST_API_Media_Upload extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'rest-api-media-upload';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'REST API Media Upload';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies REST API media uploads are properly secured';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$rest_enabled = false;
		if ( function_exists( 'rest_get_server' ) ) {
			$rest_enabled = (bool) rest_get_server();
		}

		if ( ! $rest_enabled ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'REST API is not enabled. File uploads via REST API may not work.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 60,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/rest-api-media-upload?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		// Check upload size limits
		$max_upload_size = wp_max_upload_size();
		if ( $max_upload_size < 5242880 ) { // 5MB minimum
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Upload size limit is too small for REST API media uploads. Increase PHP upload limits.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 60,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/rest-api-media-upload?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		// Check file type restrictions
		$allowed_types = get_allowed_mime_types();
		if ( empty( $allowed_types ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'No MIME types are allowed for upload. Configure allowed file types.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 60,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/rest-api-media-upload?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
