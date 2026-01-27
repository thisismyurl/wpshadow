<?php
/**
 * Diagnostic: Image EXIF Data Stripping
 *
 * Checks if WordPress strips EXIF data from uploaded images.
 * EXIF data can contain sensitive location, camera, and timestamp information.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Image_Exif_Stripping
 *
 * Monitors EXIF data handling in uploaded images.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Image_Exif_Stripping extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'image-exif-stripping';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Image EXIF Data Stripping';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if EXIF data is stripped from uploaded images';

	/**
	 * Check EXIF stripping status.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Check if GD or Imagick is available (required for EXIF stripping).
		$has_gd      = function_exists( 'imagecreatefromjpeg' );
		$has_imagick = extension_loaded( 'imagick' );

		if ( ! $has_gd && ! $has_imagick ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Neither GD nor Imagick is available. WordPress cannot strip EXIF data from uploaded images.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/image_exif_stripping',
				'meta'        => array(
					'has_gd'      => false,
					'has_imagick' => false,
				),
			);
		}

		// Check if a plugin is handling EXIF removal.
		$exif_plugins = array(
			'remove-exif-data/remove-exif-data.php',
			'exif-cleaner/exif-cleaner.php',
			'ewww-image-optimizer/ewww-image-optimizer.php',
			'imagify/imagify.php',
		);

		$has_exif_plugin = false;
		foreach ( $exif_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_exif_plugin = true;
				break;
			}
		}

		// Check if WordPress rotates images based on EXIF (WordPress 5.3+).
		$wp_auto_rotate = function_exists( 'wp_get_image_editor' );

		// WordPress 5.3+ auto-rotates but doesn't strip all EXIF by default.
		if ( ! $has_exif_plugin ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No EXIF stripping plugin detected. Consider using a plugin to remove sensitive metadata from uploaded images.', 'wpshadow' ),
				'severity'    => 'info',
				'threat_level' => 25,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/image_exif_stripping',
				'meta'        => array(
					'has_gd'           => $has_gd,
					'has_imagick'      => $has_imagick,
					'has_exif_plugin'  => false,
					'wp_auto_rotate'   => $wp_auto_rotate,
				),
			);
		}

		// EXIF handling is configured.
		return null;
	}
}
