<?php
/**
 * Animated GIF Resizing (AGR) Support Missing Diagnostic
 *
 * Detects when gifsicle (required for animated GIF optimization) is missing,
 * preventing proper resizing and optimization of animated GIF files.
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
 * AGR Support Missing Diagnostic Class
 *
 * Checks for gifsicle availability to enable animated GIF resizing (AGR).
 * AGR provides significant file size reduction for animated GIFs.
 *
 * Based on EWWW Image Optimizer AGR test suite (test-agr.php lines 28-33).
 *
 * @since 1.6033.1500
 */
class Diagnostic_Agr_Support_Missing extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'agr-support-missing';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Animated GIF Resizing (AGR) Support Missing';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for gifsicle availability to enable animated GIF optimization';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks if gifsicle is available for animated GIF optimization.
	 *
	 * @since  1.6033.1500
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if an optimizer plugin is active.
		if ( ! self::has_optimizer_plugin() ) {
			// Not relevant if no optimizer plugin.
			return null;
		}

		// Check if gifsicle is available.
		$gifsicle_available = self::is_gifsicle_available();

		if ( $gifsicle_available ) {
			// Gifsicle is available, no finding.
			return null;
		}

		return array(
			'id'                 => self::$slug,
			'title'              => self::$title,
			'description'        => __( 'gifsicle is not installed on this server. Without gifsicle, animated GIFs cannot be properly resized or optimized, which can result in unnecessarily large file sizes and slower page loads. Installing gifsicle enables 40-70% file size reduction for animated GIFs.', 'wpshadow' ),
			'severity'           => 'low',
			'threat_level'       => 25,
			'auto_fixable'       => false,
			'required_tool'      => 'gifsicle',
			'installation_guide' => 'https://wpshadow.com/kb/install-gifsicle',
			'expected_benefits'  => '40-70% animated GIF file size reduction',
			'kb_link'            => 'https://wpshadow.com/kb/animated-gif-optimization',
		);
	}

	/**
	 * Check if an optimizer plugin is active.
	 *
	 * @since  1.6033.1500
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
	 * Check if gifsicle is available.
	 *
	 * @since  1.6033.1500
	 * @return bool True if gifsicle is available.
	 */
	private static function is_gifsicle_available() {
		// Check in plugin directories first.
		$plugin_paths = array(
			WP_PLUGIN_DIR . '/ewww-image-optimizer/binaries/gifsicle',
			WP_PLUGIN_DIR . '/ewww-image-optimizer-cloud/binaries/gifsicle',
		);

		foreach ( $plugin_paths as $path ) {
			if ( file_exists( $path ) && is_executable( $path ) ) {
				return true;
			}
		}

		// Check system PATH.
		$tool_path = 'gifsicle';

		// Safe execution with error suppression.
		$output = array();
		$return_var = 0;
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_exec
		@exec( 'which ' . escapeshellarg( $tool_path ) . ' 2>/dev/null', $output, $return_var );

		if ( 0 === $return_var && ! empty( $output ) ) {
			return true;
		}

		// Try command -v as fallback.
		$output = array();
		$return_var = 0;
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_exec
		@exec( 'command -v ' . escapeshellarg( $tool_path ) . ' 2>/dev/null', $output, $return_var );

		return 0 === $return_var && ! empty( $output );
	}
}
