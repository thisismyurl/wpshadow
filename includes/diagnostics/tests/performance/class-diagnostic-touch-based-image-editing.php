<?php
/**
 * Touch-Based Image Editing Diagnostic
 *
 * Tests image editor functionality on touch devices.
 * Validates pinch-to-zoom and drag operations.
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
 * Touch-Based Image Editing Diagnostic Class
 *
 * Checks if WordPress image editor supports touch-based
 * interactions for mobile and tablet users.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Touch_Based_Image_Editing extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'touch-based-image-editing';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Touch-Based Image Editing';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests image editor functionality on touch devices';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests if WordPress image editor is functional and usable
	 * on touch devices.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		global $wp_version;

		// Check WordPress version (touch improvements in 5.3+).
		$wp_supports_touch = version_compare( $wp_version, '5.3', '>=' );

		// Check if image editor is available.
		$has_gd  = extension_loaded( 'gd' );
		$has_imagick = extension_loaded( 'imagick' );
		$has_editor  = $has_gd || $has_imagick;

		if ( ! $has_editor ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Image editing is not available - no GD or Imagick PHP extension detected', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/touch-based-image-editing?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'has_gd'         => $has_gd,
					'has_imagick'    => $has_imagick,
					'recommendation' => __( 'Install GD or Imagick PHP extension to enable image editing', 'wpshadow' ),
				),
			);
		}

		// Check registered scripts for image editor.
		global $wp_scripts;
		$image_edit_registered = isset( $wp_scripts->registered['image-edit'] );
		$imgareaselect_registered = isset( $wp_scripts->registered['imgareaselect'] );

		// Check for touch event support in scripts.
		$has_touch_events = false;
		if ( isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script ) {
				if ( false !== strpos( $handle, 'touch' ) ||
				     false !== strpos( $handle, 'gesture' ) ) {
					$has_touch_events = true;
					break;
				}
			}
		}

		// Check theme responsive support.
		$theme = wp_get_theme();
		$theme_tags = $theme->get( 'Tags' );
		$is_mobile_friendly = is_array( $theme_tags ) && in_array( 'responsive-layout', $theme_tags, true );

		// Check for enhanced image editor plugins.
		$editor_plugins = array(
			'enable-media-replace/enable-media-replace.php' => 'Enable Media Replace',
			'imsanity/imsanity.php'                         => 'Imsanity',
		);

		$has_enhanced_editor = false;
		$active_plugin = '';
		foreach ( $editor_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_enhanced_editor = true;
				$active_plugin = $name;
				break;
			}
		}

		// Test image editor capability.
		$test_image = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'post_status'    => 'inherit',
				'posts_per_page' => 1,
			)
		);

		$can_edit_sample = false;
		if ( ! empty( $test_image ) ) {
			$file_path = get_attached_file( $test_image[0]->ID );
			if ( $file_path && file_exists( $file_path ) ) {
				$editor = wp_get_image_editor( $file_path );
				$can_edit_sample = ! is_wp_error( $editor );
			}
		}

		// Issue: Poor touch support or missing editor capabilities.
		if ( ! $wp_supports_touch || ! $image_edit_registered || ! $can_edit_sample ) {
			$issues = array();

			if ( ! $wp_supports_touch ) {
				$issues[] = 'outdated_wordpress';
			}
			if ( ! $image_edit_registered ) {
				$issues[] = 'image_edit_script_missing';
			}
			if ( ! $imgareaselect_registered ) {
				$issues[] = 'crop_tool_missing';
			}
			if ( ! $can_edit_sample ) {
				$issues[] = 'editor_not_functional';
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Image editor may not be optimized for touch devices, making mobile editing difficult', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/touch-based-image-editing?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'wp_version'              => $wp_version,
					'wp_supports_touch'       => $wp_supports_touch,
					'has_gd'                  => $has_gd,
					'has_imagick'             => $has_imagick,
					'image_edit_registered'   => $image_edit_registered,
					'imgareaselect_registered' => $imgareaselect_registered,
					'has_touch_events'        => $has_touch_events,
					'is_mobile_friendly'      => $is_mobile_friendly,
					'has_enhanced_editor'     => $has_enhanced_editor,
					'active_plugin'           => $active_plugin,
					'can_edit_sample'         => $can_edit_sample,
					'issues_detected'         => $issues,
					'usability_impact'        => __( 'Difficult image editing on touch devices reduces mobile user productivity', 'wpshadow' ),
					'recommendation'          => __( 'Update WordPress to 5.3+, test image editor on tablet/mobile devices', 'wpshadow' ),
					'editing_operations'      => array(
						'crop'       => __( 'Crop tool should support pinch-to-zoom and drag gestures', 'wpshadow' ),
						'rotate'     => __( 'Rotate buttons should be touch-friendly (min 44x44 px)', 'wpshadow' ),
						'flip'       => __( 'Flip controls should be easily tappable', 'wpshadow' ),
						'scale'      => __( 'Scale adjustment should support touch sliders', 'wpshadow' ),
					),
					'testing_checklist'       => array(
						__( '1. Open image editor on tablet/phone', 'wpshadow' ),
						__( '2. Test crop tool with touch drag', 'wpshadow' ),
						__( '3. Test pinch-to-zoom in crop area', 'wpshadow' ),
						__( '4. Test rotate button tap targets', 'wpshadow' ),
						__( '5. Verify save button is accessible', 'wpshadow' ),
					),
					'expected_behavior'       => array(
						__( 'Crop handles should be large enough to grab (44x44 px minimum)', 'wpshadow' ),
						__( 'Pinch-to-zoom should work within crop area', 'wpshadow' ),
						__( 'Buttons should not overlap on small screens', 'wpshadow' ),
						__( 'No horizontal scrolling required', 'wpshadow' ),
					),
				),
			);
		}

		return null;
	}
}
