<?php
/**
 * Media Touch-Based Image Editing Diagnostic
 *
 * Checks if touch-based image editing is properly supported.
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
 * Media Touch-Based Image Editing Diagnostic Class
 *
 * Verifies that WordPress image editor supports touch-based interactions
 * for cropping, rotating, and editing on mobile devices.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Media_Touch_Based_Image_Editing extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-touch-based-image-editing';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Media Touch-Based Image Editing';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if touch-based image editing is properly supported';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if image editor is available.
		if ( ! function_exists( 'wp_image_editor' ) ) {
			$issues[] = __( 'WordPress image editor is not available', 'wpshadow' );
		}

		// Check if imagesLoaded script is registered (used by image editor).
		if ( ! wp_script_is( 'imagesloaded', 'registered' ) ) {
			$issues[] = __( 'imagesLoaded library is not registered', 'wpshadow' );
		}

		// Check if image-edit script is registered.
		if ( ! wp_script_is( 'image-edit', 'registered' ) ) {
			$issues[] = __( 'Image edit script is not registered', 'wpshadow' );
		}

		// Check for jQuery Touch Punch (enables touch events for jQuery UI).
		$touch_punch_registered = wp_script_is( 'jquery-touch-punch', 'registered' );
		if ( ! $touch_punch_registered ) {
			$issues[] = __( 'jQuery Touch Punch is not registered (required for touch-based dragging)', 'wpshadow' );
		}

		// Check if jcrop is registered (cropping library).
		if ( ! wp_script_is( 'jcrop', 'registered' ) ) {
			$issues[] = __( 'Jcrop library is not registered', 'wpshadow' );
		}

		// Check if GD or ImageMagick is available for server-side editing.
		$editors = wp_image_editor_supports();
		if ( empty( $editors ) ) {
			$issues[] = __( 'No image editor library (GD or ImageMagick) is available', 'wpshadow' );
		}

		// Check for wp_ajax_image_editor AJAX handler.
		$has_image_editor_ajax = has_action( 'wp_ajax_image-editor' );
		if ( ! $has_image_editor_ajax ) {
			$issues[] = __( 'Image editor AJAX handler is not registered', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-touch-based-image-editing',
			);
		}

		return null;
	}
}
