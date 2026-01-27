<?php
/**
 * Diagnostic: Image Editor Availability
 *
 * Verifies WordPress image editor is functional and can process images.
 * The image editor is critical for thumbnails, uploads, and media management.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Infrastructure
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Image_Editor_Availability
 *
 * Tests WordPress image editor functionality.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Image_Editor_Availability extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'image-editor-availability';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Image Editor Availability';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Verifies WordPress image editor is functional';

	/**
	 * Check image editor availability.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Check if wp_get_image_editor function exists.
		if ( ! function_exists( 'wp_get_image_editor' ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'WordPress image editor function is not available. This is a core WordPress issue.', 'wpshadow' ),
				'severity'    => 'critical',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/image_editor_availability',
				'meta'        => array(
					'function_exists' => false,
				),
			);
		}

		// Get available image editor implementations.
		$implementations = apply_filters( 'wp_image_editors', array( 'WP_Image_Editor_Imagick', 'WP_Image_Editor_GD' ) );

		if ( empty( $implementations ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No image editor implementations are available. WordPress cannot process images.', 'wpshadow' ),
				'severity'    => 'critical',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/image_editor_availability',
				'meta'        => array(
					'implementations' => array(),
				),
			);
		}

		// Test if we can actually get an image editor instance.
		// Use WordPress logo as test image (always available).
		$test_image = ABSPATH . 'wp-admin/images/wordpress-logo.png';

		if ( ! file_exists( $test_image ) ) {
			// Try alternative test image.
			$test_image = ABSPATH . 'wp-includes/images/w-logo-blue.png';
		}

		if ( ! file_exists( $test_image ) ) {
			// Can't test without an image, but implementations exist.
			return null;
		}

		$editor = wp_get_image_editor( $test_image );

		if ( is_wp_error( $editor ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: Error message */
					__( 'Image editor failed to load: %s', 'wpshadow' ),
					$editor->get_error_message()
				),
				'severity'    => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/image_editor_availability',
				'meta'        => array(
					'error'           => $editor->get_error_message(),
					'implementations' => $implementations,
				),
			);
		}

		// Test if editor can perform basic operations.
		$load_result = $editor->load();

		if ( is_wp_error( $load_result ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: Error message */
					__( 'Image editor cannot load images: %s', 'wpshadow' ),
					$load_result->get_error_message()
				),
				'severity'    => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/image_editor_availability',
				'meta'        => array(
					'error'       => $load_result->get_error_message(),
					'editor_class' => get_class( $editor ),
				),
			);
		}

		// Image editor is fully functional.
		return null;
	}
}
