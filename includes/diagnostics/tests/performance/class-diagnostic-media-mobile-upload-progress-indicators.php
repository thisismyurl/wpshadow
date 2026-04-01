<?php
/**
 * Media Mobile Upload Progress Indicators Diagnostic
 *
 * Checks if mobile users receive proper upload progress feedback.
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
 * Media Mobile Upload Progress Indicators Diagnostic Class
 *
 * Verifies that mobile users receive proper visual feedback during
 * media uploads, including progress bars and status indicators.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Media_Mobile_Upload_Progress_Indicators extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-mobile-upload-progress-indicators';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Media Mobile Upload Progress Indicators';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if mobile users receive proper upload progress feedback';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
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
				'kb_link'      => 'https://wpshadow.com/kb/media-mobile-upload-progress-indicators?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
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
				'kb_link'      => 'https://wpshadow.com/kb/media-mobile-upload-progress-indicators?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
