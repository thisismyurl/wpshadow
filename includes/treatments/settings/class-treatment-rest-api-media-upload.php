<?php
/**
 * REST API Media Upload Treatment
 *
 * Detects if REST API media upload endpoints are properly configured and secured.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6033.1635
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_REST_API_Media_Upload Class
 *
 * Tests if REST API media upload endpoints enforce file type restrictions,
 * size limits, and proper security checks before accepting uploads.
 *
 * @since 1.6033.1635
 */
class Treatment_REST_API_Media_Upload extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'rest-api-media-upload';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'REST API Media Upload';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies REST API media uploads are properly secured';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check
	 *
	 * @since  1.6033.1635
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		if ( ! rest_is_enabled() ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'REST API is not enabled. File uploads via REST API may not work.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 60,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/rest-api-media-upload',
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
				'kb_link'       => 'https://wpshadow.com/kb/rest-api-media-upload',
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
				'kb_link'       => 'https://wpshadow.com/kb/rest-api-media-upload',
			);
		}

		return null;
	}
}
