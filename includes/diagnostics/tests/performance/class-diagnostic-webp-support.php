<?php
/**
 * WebP Support Diagnostic
 *
 * Checks if WebP image format support is enabled to reduce image file sizes.
 * WebP can reduce file sizes by 25-35% compared to JPEG/PNG.
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
 * WebP Support Diagnostic Class
 *
 * Verifies WebP image format support:
 * - Server support for WebP conversion
 * - ImageMagick or GD library availability
 * - WebP plugin installation and configuration
 * - Browser compatibility detection
 *
 * @since 1.6093.1200
 */
class Diagnostic_Webp_Support extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'webp-support';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'WebP Support';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if WebP image format is enabled for better compression';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		$webp_enabled        = false;
		$imagemagick_support = false;
		$gd_support          = false;
		$plugin_detected     = false;

		// Check ImageMagick support
		if ( extension_loaded( 'imagick' ) ) {
			$imagick           = new \Imagick();
			$imagemagick_support = in_array( 'WEBP', $imagick->queryFormats(), true );
		}

		// Check GD support
		if ( extension_loaded( 'gd' ) && function_exists( 'gd_info' ) ) {
			$gd_info      = gd_info();
			$gd_support   = isset( $gd_info['WebP Support'] ) ? (bool) $gd_info['WebP Support'] : false;
		}

		// Check for WebP plugins
		$webp_plugins = array(
			'ewww-image-optimizer/ewww-image-optimizer.php' => 'EWWW Image Optimizer',
			'imagify/imagify.php'                          => 'Imagify',
			'optimus/optimus.php'                          => 'Optimus',
			'wp-smush/wp-smush.php'                        => 'WP Smush',
			'shortpixel-image-optimiser/wp-shortpixel.php' => 'ShortPixel',
		);

		foreach ( $webp_plugins as $plugin_path => $plugin_name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$plugin_detected = true;
				$webp_enabled    = true;
				break;
			}
		}

		// Check if WebP conversion is available
		if ( ! $webp_enabled && ( $imagemagick_support || $gd_support ) ) {
			$webp_enabled = true;
		}

		if ( ! $webp_enabled ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'WebP support is not available. WebP images are 25-35%% smaller than JPEG/PNG with same quality.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/webp-support',
				'meta'          => array(
					'imagemagick_available' => $imagemagick_support,
					'gd_available'          => $gd_support,
					'plugin_installed'      => $plugin_detected,
					'recommendation'        => 'Install an image optimization plugin with WebP support (EWWW, Imagify, ShortPixel, or WP Smush)',
					'impact'                => 'Reduces total image size by 25-35%, improves LCP and page load time',
					'browser_support'       => '94% of browsers support WebP (as of 2026)',
			),
			);
		}

		return null;
	}
}
