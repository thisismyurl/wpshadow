<?php
/**
 * Upload Progress Screen Reader Announcements Diagnostic
 *
 * Tests ARIA live regions for upload progress.
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
 * Upload Progress Screen Reader Announcements Diagnostic Class
 *
 * Verifies that upload progress is announced to screen readers
 * using ARIA live regions and status updates.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Upload_Progress_Screen_Reader_Announcements extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'upload-progress-screen-reader-announcements';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Upload Progress Screen Reader Announcements';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests ARIA live regions for upload progress';

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

		// Check if wp-a11y script is registered (handles announcements).
		if ( ! wp_script_is( 'wp-a11y', 'registered' ) ) {
			$issues[] = __( 'WordPress accessibility helper script (wp-a11y) is not registered', 'wpshadow' );
		}

		// Check if media library is available.
		if ( ! function_exists( 'wp_enqueue_media' ) ) {
			$issues[] = __( 'Media library functionality is not available', 'wpshadow' );
		}

		// Check if plupload is registered (handles uploads).
		if ( ! wp_script_is( 'plupload', 'registered' ) ) {
			$issues[] = __( 'Plupload script is not registered', 'wpshadow' );
		}

		// Check if media-views is registered (contains upload UI).
		if ( ! wp_script_is( 'media-views', 'registered' ) ) {
			$issues[] = __( 'Media views script is not registered', 'wpshadow' );
		}

		// Check for upload progress filters.
		$has_progress_filter = has_filter( 'plupload_init' );
		if ( ! $has_progress_filter ) {
			$issues[] = __( 'No plupload_init filter detected for upload progress customization', 'wpshadow' );
		}

		// Check if wp-a11y.speak() is available.
		global $wp_scripts;
		if ( $wp_scripts && $wp_scripts->query( 'wp-a11y', 'registered' ) ) {
			// Check version to ensure speak() function exists.
			$a11y_version = $wp_scripts->registered['wp-a11y']->ver ?? '';
			if ( version_compare( $a11y_version, '4.6', '<' ) ) {
				$issues[] = __( 'WordPress accessibility script version is too old for ARIA announcements', 'wpshadow' );
			}
		}

		// Check for ARIA live region support in theme.
		$theme_support = get_theme_support( 'html5' );
		if ( empty( $theme_support ) ) {
			// Theme may not support modern HTML5/ARIA.
			$issues[] = __( 'Theme does not declare HTML5 support', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/upload-progress-screen-reader-announcements?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
