<?php
/**
 * Mobile Upload Progress Indicators Treatment
 *
 * Tests upload progress display on mobile devices.
 * Validates touch-friendly UI elements and feedback.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.7029.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Upload Progress Indicators Treatment Class
 *
 * Checks if upload progress is properly displayed for mobile users
 * with touch-friendly UI elements.
 *
 * @since 1.7029.1200
 */
class Treatment_Mobile_Upload_Progress_Indicators extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-upload-progress-indicators';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Upload Progress Indicators';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests upload progress display on mobile devices';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * Tests if WordPress provides adequate upload progress feedback
	 * for mobile users.
	 *
	 * @since  1.7029.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		global $wp_version;

		// Check WordPress version (improved in 5.3+).
		$wp_supports_mobile_ui = version_compare( $wp_version, '5.3', '>=' );

		// Check if plupload is enqueued (WordPress uploader).
		global $wp_scripts;
		$plupload_registered = isset( $wp_scripts->registered['plupload'] );
		$plupload_enqueued   = wp_script_is( 'plupload', 'enqueued' );

		// Check if wp-plupload is registered (handles progress).
		$wp_plupload_registered = isset( $wp_scripts->registered['wp-plupload'] );

		// Check for custom upload progress handlers.
		$has_custom_progress = false;
		if ( isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script ) {
				if ( false !== strpos( $handle, 'upload' ) && false !== strpos( $handle, 'progress' ) ) {
					$has_custom_progress = true;
					break;
				}
			}
		}

		// Check theme support for custom uploads.
		$theme = wp_get_theme();
		$theme_tags = $theme->get( 'Tags' );
		$is_mobile_friendly = is_array( $theme_tags ) && in_array( 'responsive-layout', $theme_tags, true );

		// Check for upload enhancing plugins.
		$upload_plugins = array(
			'filebird/filebird.php'                   => 'FileBird',
			'media-library-assistant/index.php'       => 'Media Library Assistant',
			'enhanced-media-library/enhanced-media-library.php' => 'Enhanced Media Library',
		);

		$has_upload_enhancement = false;
		$active_plugin = '';
		foreach ( $upload_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_upload_enhancement = true;
				$active_plugin = $name;
				break;
			}
		}

		// Check for AJAX upload support.
		$ajax_upload_enabled = defined( 'DOING_AJAX' ) || wp_doing_ajax();

		// Check max upload size (mobile connections may timeout on large files).
		$max_upload_size = wp_max_upload_size();
		$max_upload_mb   = $max_upload_size / ( 1024 * 1024 );

		// Check PHP settings.
		$post_max_size     = ini_get( 'post_max_size' );
		$upload_max_filesize = ini_get( 'upload_max_filesize' );

		// Issue: Missing progress indicators or poor mobile support.
		if ( ! $wp_supports_mobile_ui || ! $plupload_registered || 2 > $max_upload_mb ) {
			$issues = array();

			if ( ! $wp_supports_mobile_ui ) {
				$issues[] = 'outdated_wordpress';
			}
			if ( ! $plupload_registered ) {
				$issues[] = 'missing_plupload';
			}
			if ( 2 > $max_upload_mb ) {
				$issues[] = 'low_upload_limit';
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Mobile upload progress indicators may not work properly, leaving users uncertain about upload status', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-upload-progress-indicators',
				'details'      => array(
					'wp_version'            => $wp_version,
					'wp_supports_mobile_ui' => $wp_supports_mobile_ui,
					'plupload_registered'   => $plupload_registered,
					'plupload_enqueued'     => $plupload_enqueued,
					'wp_plupload_registered' => $wp_plupload_registered,
					'has_custom_progress'   => $has_custom_progress,
					'is_mobile_friendly'    => $is_mobile_friendly,
					'has_upload_enhancement' => $has_upload_enhancement,
					'active_plugin'         => $active_plugin,
					'max_upload_size'       => size_format( $max_upload_size ),
					'max_upload_mb'         => round( $max_upload_mb, 2 ),
					'post_max_size'         => $post_max_size,
					'upload_max_filesize'   => $upload_max_filesize,
					'issues_detected'       => $issues,
					'usability_impact'      => __( 'Without clear progress indicators, mobile users may abandon uploads or upload duplicates', 'wpshadow' ),
					'recommendation'        => __( 'Update WordPress to 5.3+, ensure plupload is enabled, and test upload feedback on mobile devices', 'wpshadow' ),
					'testing_steps'         => array(
						__( '1. Access admin on mobile device', 'wpshadow' ),
						__( '2. Go to Media → Add New', 'wpshadow' ),
						__( '3. Select a large image (1-5 MB)', 'wpshadow' ),
						__( '4. Verify progress bar displays', 'wpshadow' ),
						__( '5. Verify completion message shows', 'wpshadow' ),
					),
					'expected_behavior'     => array(
						__( 'Progress bar should be visible and update in real-time', 'wpshadow' ),
						__( 'Progress percentage should display (0-100%)', 'wpshadow' ),
						__( 'Cancel button should be touch-friendly', 'wpshadow' ),
						__( 'Success/error messages should be clear', 'wpshadow' ),
					),
				),
			);
		}

		return null;
	}
}
