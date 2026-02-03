<?php
/**
 * PNG Compression Level Misconfigured Diagnostic
 *
 * Validates PNG compression level settings to ensure PNG files are
 * being optimized effectively for bandwidth and performance benefits.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.1500
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PNG Compression Misconfigured Diagnostic Class
 *
 * Checks PNG compression settings in image optimization plugins.
 * PNG compression typically provides 10-40% file size reduction for
 * graphics and transparent images.
 *
 * Based on EWWW Image Optimizer test suite patterns (test-optimize.php lines 200-250).
 *
 * @since 1.6033.1500
 */
class Diagnostic_Png_Compression_Misconfigured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'png-compression-misconfigured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'PNG Compression Settings Misconfigured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates PNG compression level settings for optimal file size reduction';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks PNG compression settings in active optimization plugins.
	 * PNG compression provides significant file size savings.
	 *
	 * @since  1.6033.1500
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if image optimizer plugin is active.
		$optimizer_config = self::get_optimizer_config();

		if ( ! $optimizer_config ) {
			// No optimizer plugin active, not relevant.
			return null;
		}

		$plugin = $optimizer_config['plugin'];
		$png_enabled = $optimizer_config['png_enabled'];
		$png_level = $optimizer_config['png_level'];

		// If PNG optimization is enabled and properly configured, no finding.
		if ( $png_enabled && $png_level > 0 ) {
			return null;
		}

		// Determine recommended setting based on plugin.
		$recommended_setting = 5; // Mid-range compression (good balance).

		return array(
			'id'                   => self::$slug,
			'title'                => self::$title,
			'description'          => sprintf(
				/* translators: %s: plugin name */
				__( 'PNG compression is disabled or set to 0 in %s. Enabling PNG optimization provides 10-40%% file size reduction for graphics and transparent images, improving page load speed.', 'wpshadow' ),
				$plugin
			),
			'severity'             => 'low',
			'threat_level'         => 20,
			'auto_fixable'         => true,
			'current_setting'      => $png_level,
			'recommended_setting'  => $recommended_setting,
			'potential_savings'    => '10-40% file size reduction',
			'plugin'               => $plugin,
			'kb_link'              => 'https://wpshadow.com/kb/png-compression-optimization',
		);
	}

	/**
	 * Get optimizer plugin configuration.
	 *
	 * @since  1.6033.1500
	 * @return array|null Configuration array or null if no optimizer active.
	 */
	private static function get_optimizer_config() {
		// Check EWWW Image Optimizer.
		if ( is_plugin_active( 'ewww-image-optimizer/ewww-image-optimizer.php' ) ) {
			$png_level = (int) get_option( 'ewww_image_optimizer_png_level', 0 );
			return array(
				'plugin'      => 'EWWW Image Optimizer',
				'png_enabled' => $png_level > 0,
				'png_level'   => $png_level,
			);
		}

		// Check ShortPixel.
		if ( is_plugin_active( 'shortpixel-image-optimiser/wp-shortpixel.php' ) ) {
			$compression_type = get_option( 'wp-shortpixel-compression', 0 );
			// ShortPixel uses: 0=Lossy, 1=Glossy, 2=Lossless.
			$png_enabled = $compression_type !== false;
			return array(
				'plugin'      => 'ShortPixel',
				'png_enabled' => $png_enabled,
				'png_level'   => $png_enabled ? 1 : 0,
			);
		}

		// Check Imagify.
		if ( is_plugin_active( 'imagify/imagify.php' ) ) {
			$optimization_level = get_option( 'imagify_optimization_level', 0 );
			// Imagify: 0=Normal, 1=Aggressive, 2=Ultra.
			$png_enabled = $optimization_level !== false;
			return array(
				'plugin'      => 'Imagify',
				'png_enabled' => $png_enabled,
				'png_level'   => $png_enabled ? 1 : 0,
			);
		}

		// Check TinyPNG/Compress JPEG & PNG.
		if ( is_plugin_active( 'tiny-compress-images/tiny-compress-images.php' ) ) {
			$api_key = get_option( 'tinypng_api_key', '' );
			$png_enabled = ! empty( $api_key );
			return array(
				'plugin'      => 'TinyPNG',
				'png_enabled' => $png_enabled,
				'png_level'   => $png_enabled ? 1 : 0,
			);
		}

		// Check Smush (WP Smush).
		if ( is_plugin_active( 'wp-smushit/wp-smush.php' ) ) {
			$smush_enabled = get_option( 'wp-smush-auto', false );
			return array(
				'plugin'      => 'WP Smush',
				'png_enabled' => (bool) $smush_enabled,
				'png_level'   => $smush_enabled ? 1 : 0,
			);
		}

		return null;
	}
}
