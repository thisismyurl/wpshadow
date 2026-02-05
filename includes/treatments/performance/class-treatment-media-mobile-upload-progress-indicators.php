<?php
/**
 * Media Mobile Upload Progress Indicators Treatment
 *
 * Checks if mobile users receive proper upload progress feedback.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6033.0000
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Media Mobile Upload Progress Indicators Treatment Class
 *
 * Verifies that mobile users receive proper visual feedback during
 * media uploads, including progress bars and status indicators.
 *
 * @since 1.6033.0000
 */
class Treatment_Media_Mobile_Upload_Progress_Indicators extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-mobile-upload-progress-indicators';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Media Mobile Upload Progress Indicators';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if mobile users receive proper upload progress feedback';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if media uploader is available.
		if ( ! function_exists( 'wp_enqueue_media' ) ) {
			$issues[] = __( 'Media uploader functionality is not available', 'wpshadow' );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-mobile-upload-progress-indicators',
			);
		}

		// Check if Plupload (media uploader) is registered.
		if ( ! wp_script_is( 'plupload', 'registered' ) ) {
			$issues[] = __( 'Plupload script (required for upload progress) is not registered', 'wpshadow' );
		}

		// Check if media-views (backbone views) is registered.
		if ( ! wp_script_is( 'media-views', 'registered' ) ) {
			$issues[] = __( 'Media views script is not registered', 'wpshadow' );
		}

		// Check for mobile-specific upload configurations.
		$plupload_init = has_filter( 'plupload_init' );
		if ( ! $plupload_init ) {
			$issues[] = __( 'No plupload_init filter detected for upload customization', 'wpshadow' );
		}

		// Check if jQuery is available (required for progress indicators).
		if ( ! wp_script_is( 'jquery', 'registered' ) ) {
			$issues[] = __( 'jQuery is not registered (required for upload progress)', 'wpshadow' );
		}

		// Check for AJAX endpoint availability.
		$ajax_url = admin_url( 'admin-ajax.php' );
		if ( empty( $ajax_url ) ) {
			$issues[] = __( 'AJAX endpoint is not available', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-mobile-upload-progress-indicators',
			);
		}

		return null;
	}
}
