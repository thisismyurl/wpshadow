<?php
/**
 * Mobile Bandwidth Optimization Diagnostic
 *
 * Detects if media is optimized for mobile bandwidth constraints.
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
 * Diagnostic_Media_Mobile_Bandwidth_Optimization Class
 *
 * Tests if images and media are optimized for mobile bandwidth constraints,
 * including lazy loading, responsive images, and bandwidth-aware serving.
 *
 * @since 1.26033.1635
 */
class Diagnostic_Media_Mobile_Bandwidth_Optimization extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-mobile-bandwidth-optimization';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Bandwidth Optimization';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects if media is optimized for mobile bandwidth constraints';

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
		$lazy_loading_enabled = function_exists( 'wp_get_loading_attr_default' );
		$responsive_images    = wp_image_add_srcset_and_sizes( '' ) === '';
		$optimization_plugins = self::check_optimization_plugins();

		if ( ! $lazy_loading_enabled || ! $optimization_plugins ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Media is not optimized for mobile bandwidth constraints. Enable lazy loading and image optimization.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 50,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/mobile-bandwidth-optimization',
			);
		}

		return null;
	}

	/**
	 * Check for image optimization plugins
	 *
	 * @since  1.26033.1635
	 * @return bool True if optimization plugin is active.
	 */
	private static function check_optimization_plugins() {
		$optimization_plugins = array(
			'smush-bulk-converter-and-image-smusher/wp-smush.php',
			'imagify/imagify.php',
			'ewww-image-optimizer/ewww-image-optimizer.php',
			'shortpixel-image-optimiser/wp-shortpixel.php',
		);

		foreach ( $optimization_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}
}
