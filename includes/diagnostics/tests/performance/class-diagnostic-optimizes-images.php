<?php
/**
 * Image Optimization Process Diagnostic
 *
 * Tests if images are properly optimized and compressed.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Image Optimization Process Diagnostic Class
 *
 * Verifies that an image optimization plugin or workflow is active.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Optimizes_Images extends Diagnostic_Base {

	protected static $slug = 'optimizes-images';
	protected static $title = 'Image Optimization Process';
	protected static $description = 'Tests if images are properly optimized and compressed';
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugins = array(
			'wp-smushit/wp-smush.php',
			'shortpixel-image-optimiser/wp-shortpixel.php',
			'imagify/imagify.php',
			'ewww-image-optimizer/ewww-image-optimizer.php',
			'optimole-wp/optimole-wp.php',
		);

		foreach ( $plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return null;
			}
		}

		$manual_flag = get_option( 'wpshadow_image_optimization_enabled' );
		if ( $manual_flag ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No image optimization detected. Compress images to improve load speed and user experience.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 35,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/image-optimization-process',
			'persona'      => 'publisher',
		);
	}
}
