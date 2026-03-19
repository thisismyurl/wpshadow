<?php
/**
 * WebP Format Conversion Support Missing Diagnostic
 *
 * Detects when WebP image format support is unavailable, preventing
 * bandwidth savings of 20-35% compared to JPEG/PNG formats.
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
 * WebP Format Support Missing Diagnostic Class
 *
 * Checks for WebP format conversion capability through cwebp binary
 * or image library support (GD, ImageMagick). WebP provides significant
 * bandwidth savings over traditional formats.
 *
 * Based on EWWW Image Optimizer WebP conversion patterns.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Webp_Conversion_Support_Missing extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'webp-conversion-support-missing';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'WebP Format Conversion Support Missing';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for WebP format support to reduce image bandwidth by 20-35%';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks if WebP conversion is available through various methods.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if an optimizer plugin is active.
		if ( ! self::has_optimizer_plugin() ) {
			// Not relevant if no optimizer plugin.
			return null;
		}

		// Check WebP support availability.
		$webp_support = self::get_webp_support();

		if ( $webp_support['available'] ) {
			// WebP is available, no finding.
			return null;
		}

		return array(
			'id'                  => self::$slug,
			'title'               => self::$title,
			'description'         => __( 'WebP image format support is not available on this server. WebP provides 20-35% smaller file sizes compared to JPEG/PNG with similar quality, significantly improving page load speed and reducing bandwidth costs. Install cwebp or enable WebP support in GD/ImageMagick to enable this feature.', 'wpshadow' ),
			'severity'            => 'medium',
			'threat_level'        => 35,
			'auto_fixable'        => false,
			'webp_methods'        => $webp_support['methods'],
			'cwebp_available'     => $webp_support['cwebp'],
			'gd_webp_support'     => $webp_support['gd_webp'],
			'imagick_webp_support' => $webp_support['imagick_webp'],
			'installation_guide'  => 'https://wpshadow.com/kb/enable-webp-support',
			'expected_benefits'   => '20-35% bandwidth reduction, faster page loads, lower hosting costs',
			'kb_link'             => 'https://wpshadow.com/kb/webp-image-format',
		);
	}

	/**
	 * Check if an optimizer plugin is active.
	 *
	 * @since 1.6093.1200
	 * @return bool True if optimizer plugin is active.
	 */
	private static function has_optimizer_plugin() {
		$optimizer_plugins = array(
			'ewww-image-optimizer/ewww-image-optimizer.php',
			'shortpixel-image-optimiser/wp-shortpixel.php',
			'imagify/imagify.php',
			'tiny-compress-images/tiny-compress-images.php',
			'wp-smushit/wp-smush.php',
			'optimole-wp/optimole-wp.php',
		);

		foreach ( $optimizer_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get WebP support status.
	 *
	 * @since 1.6093.1200
	 * @return array {
	 *     WebP support information.
	 *
	 *     @type bool  $available    Whether WebP conversion is available.
	 *     @type array $methods      Available WebP conversion methods.
	 *     @type bool  $cwebp        Whether cwebp binary is available.
	 *     @type bool  $gd_webp      Whether GD has WebP support.
	 *     @type bool  $imagick_webp Whether ImageMagick has WebP support.
	 * }
	 */
	private static function get_webp_support() {
		$cwebp_available = self::is_cwebp_available();
		$gd_webp = self::has_gd_webp_support();
		$imagick_webp = self::has_imagick_webp_support();

		$methods = array();
		if ( $cwebp_available ) {
			$methods[] = 'cwebp';
		}
		if ( $gd_webp ) {
			$methods[] = 'GD';
		}
		if ( $imagick_webp ) {
			$methods[] = 'ImageMagick';
		}

		return array(
			'available'    => ! empty( $methods ),
			'methods'      => $methods,
			'cwebp'        => $cwebp_available,
			'gd_webp'      => $gd_webp,
			'imagick_webp' => $imagick_webp,
		);
	}

	/**
	 * Check if cwebp binary is available.
	 *
	 * @since 1.6093.1200
	 * @return bool True if cwebp is available.
	 */
	private static function is_cwebp_available() {
		// Check in plugin directories first.
		$plugin_paths = array(
			WP_PLUGIN_DIR . '/ewww-image-optimizer/binaries/cwebp',
			WP_PLUGIN_DIR . '/ewww-image-optimizer-cloud/binaries/cwebp',
		);

		foreach ( $plugin_paths as $path ) {
			if ( file_exists( $path ) && is_executable( $path ) ) {
				return true;
			}
		}

		// Check system PATH.
		$output = array();
		$return_var = 0;
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_exec
		@exec( 'which cwebp 2>/dev/null', $output, $return_var );

		if ( 0 === $return_var && ! empty( $output ) ) {
			return true;
		}

		// Try command -v as fallback.
		$output = array();
		$return_var = 0;
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_exec
		@exec( 'command -v cwebp 2>/dev/null', $output, $return_var );

		return 0 === $return_var && ! empty( $output );
	}

	/**
	 * Check if GD has WebP support.
	 *
	 * @since 1.6093.1200
	 * @return bool True if GD supports WebP.
	 */
	private static function has_gd_webp_support() {
		if ( ! function_exists( 'gd_info' ) ) {
			return false;
		}

		$gd_info = gd_info();
		return isset( $gd_info['WebP Support'] ) && $gd_info['WebP Support'];
	}

	/**
	 * Check if ImageMagick has WebP support.
	 *
	 * @since 1.6093.1200
	 * @return bool True if ImageMagick supports WebP.
	 */
	private static function has_imagick_webp_support() {
		if ( ! extension_loaded( 'imagick' ) || ! class_exists( 'Imagick' ) ) {
			return false;
		}

		try {
			$imagick = new \Imagick();
			$formats = $imagick->queryFormats( 'WEBP' );
			return in_array( 'WEBP', $formats, true );
		} catch ( \Exception $e ) {
			return false;
		}
	}
}
