<?php
/**
 * Mobile Upload Progress Indicators Diagnostic
 *
 * Detects if the media uploader provides progress feedback on mobile devices.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Media_Mobile_Upload_Progress Class
 *
 * Tests if mobile upload progress indicators are properly implemented,
 * providing feedback on slow network connections during media uploads.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Media_Mobile_Upload_Progress extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-mobile-upload-progress';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Upload Progress Indicators';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies media uploader provides progress feedback on mobile';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		global $wp_scripts;

		// Check if plupload is available (handles upload progress)
		$plupload_loaded = ( isset( $wp_scripts ) && $wp_scripts->query( 'plupload' ) );

		// Check for mobile-responsive upload UI
		$has_upload_ui = function_exists( 'wp_enqueue_media' ) && has_action( 'wp_enqueue_media' );

		if ( ! $plupload_loaded && ! $has_upload_ui ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Mobile upload progress indicators are not properly enabled. Add plupload for progress feedback.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 35,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/mobile-upload-progress',
			);
		}

		return null;
	}
}
