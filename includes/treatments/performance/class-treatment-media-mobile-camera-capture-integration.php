<?php
/**
 * Media Mobile Camera Capture Integration Treatment
 *
 * Tests direct camera capture support in the media uploader
 * for mobile devices.
 *
 * @package    WPShadow
 * @subpackage Treatments\Tests
 * @since      1.6033.1625
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Media_Mobile_Camera_Capture_Integration Class
 *
 * Checks media uploader configuration for mobile capture support.
 *
 * @since 1.6033.1625
 */
class Treatment_Media_Mobile_Camera_Capture_Integration extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-mobile-camera-capture-integration';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Camera Capture Integration';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests mobile camera capture support in media uploader';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.1625
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		if ( ! function_exists( 'wp_enqueue_media' ) ) {
			$issues[] = __( 'Media uploader is unavailable; mobile camera capture cannot be used', 'wpshadow' );
		}

		if ( ! wp_script_is( 'media-views', 'registered' ) ) {
			$issues[] = __( 'Media uploader scripts are not registered; camera capture may not function', 'wpshadow' );
		}

		if ( ! has_filter( 'plupload_default_settings' ) ) {
			$issues[] = __( 'No Plupload settings filter detected; mobile capture settings may not be optimized', 'wpshadow' );
		}

		$mime_types = get_allowed_mime_types();
		if ( ! isset( $mime_types['jpg|jpeg|jpe'] ) && ! isset( $mime_types['png'] ) ) {
			$issues[] = __( 'Image MIME types are restricted; camera capture uploads may fail', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-mobile-camera-capture-integration',
			);
		}

		return null;
	}
}
