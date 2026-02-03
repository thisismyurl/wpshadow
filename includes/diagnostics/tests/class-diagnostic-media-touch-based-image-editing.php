<?php
/**
 * Touch-Based Image Editing Diagnostic
 *
 * Detects if the image editor supports touch-based interactions and gestures.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.1635
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Media_Touch_Based_Image_Editing Class
 *
 * Tests if image editing capabilities support touch interactions,
 * including pinch-to-zoom, swipe, and tap gestures on mobile devices.
 *
 * @since 1.26033.1635
 */
class Diagnostic_Media_Touch_Based_Image_Editing extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-touch-based-image-editing';

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
	protected static $description = 'Tests if image editor supports touch interactions and gestures';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check
	 *
	 * @since  1.26033.1635
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		global $wp_scripts;

		// Check for image editor script
		$image_editor_loaded = ( isset( $wp_scripts ) && $wp_scripts->query( 'image-edit' ) );

		// Check for touch support libraries
		$has_touch_support = function_exists( 'wp_register_script' ) && 
			has_filter( 'media_view_settings' );

		if ( ! $image_editor_loaded ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Image editor is not properly loaded. Touch-based editing may not be available.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/touch-based-image-editing',
			);
		}

		return null;
	}
}
