<?php
/**
 * Local Image Optimization Tools Missing Diagnostic
 *
 * Detects and validates installation status of local image optimization
 * tools (pngout, svgcleaner, jpegtran, gifsicle) that enable offline
 * image optimization without cloud APIs.
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
 * Local Optimization Tools Missing Diagnostic Class
 *
 * Checks for presence of local image optimization binaries (pngout,
 * svgcleaner, jpegtran, gifsicle). These tools enable faster offline
 * optimization without cloud API dependencies.
 *
 * Based on EWWW Image Optimizer test suite patterns.
 *
 * @since 1.6033.1500
 */
class Diagnostic_Local_Optimization_Tools_Missing extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'local-optimization-tools-missing';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Local Image Optimization Tools Missing';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects missing local image optimization binaries that improve performance';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Local optimization tools to check
	 *
	 * @var array<string, array{formats: array, description: string}>
	 */
	private static $tools = array(
		'pngout'      => array(
			'formats'     => array( 'PNG' ),
			'description' => 'PNG compression utility',
		),
		'svgcleaner'  => array(
			'formats'     => array( 'SVG' ),
			'description' => 'SVG optimization tool',
		),
		'jpegtran'    => array(
			'formats'     => array( 'JPEG' ),
			'description' => 'JPEG lossless transformation utility',
		),
		'gifsicle'    => array(
			'formats'     => array( 'GIF' ),
			'description' => 'GIF animation and optimization tool',
		),
		'optipng'     => array(
			'formats'     => array( 'PNG' ),
			'description' => 'PNG optimizer',
		),
		'pngquant'    => array(
			'formats'     => array( 'PNG' ),
			'description' => 'PNG lossy compression',
		),
		'cwebp'       => array(
			'formats'     => array( 'WebP' ),
			'description' => 'WebP converter',
		),
	);

	/**
	 * Run the diagnostic check.
	 *
	 * Checks for local image optimization tools. Missing tools result in
	 * slower cloud-based optimization or reduced functionality.
	 *
	 * @since  1.6033.1500
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if EWWW or similar optimization plugin is active.
		$has_optimizer = self::has_image_optimizer_plugin();

		// If no optimizer plugin, don't flag (not relevant).
		if ( ! $has_optimizer ) {
			return null;
		}

		// Check tool installation status.
		$tools_present = array();
		$tools_missing = array();

		foreach ( self::$tools as $tool => $info ) {
			if ( self::is_tool_installed( $tool ) ) {
				$tools_present[] = $tool;
			} else {
				$tools_missing[] = $tool;
			}
		}

		// If all tools present, no finding.
		if ( empty( $tools_missing ) ) {
			return null;
		}

		// If all tools missing, might be using cloud optimization only.
		$severity = count( $tools_missing ) >= 5 ? 'medium' : 'low';
		$threat_level = 20 + ( count( $tools_missing ) * 2 );

		return array(
			'id'            => self::$slug,
			'title'         => self::$title,
			'description'   => sprintf(
				/* translators: 1: number of missing tools, 2: comma-separated list of missing tool names */
				_n(
					'%1$d local image optimization tool is not installed: %2$s. Local optimization without this tool may fall back to slower cloud APIs.',
					'%1$d local image optimization tools are not installed: %2$s. Local optimization without these tools may fall back to slower cloud APIs.',
					count( $tools_missing ),
					'wpshadow'
				),
				count( $tools_missing ),
				implode( ', ', $tools_missing )
			),
			'severity'      => $severity,
			'threat_level'  => $threat_level,
			'auto_fixable'  => false,
			'tools_missing' => $tools_missing,
			'tools_present' => $tools_present,
			'kb_link'       => 'https://wpshadow.com/kb/local-image-optimization-tools',
		);
	}

	/**
	 * Check if image optimizer plugin is active.
	 *
	 * @since  1.6033.1500
	 * @return bool True if optimizer plugin detected.
	 */
	private static function has_image_optimizer_plugin() {
		$optimizer_plugins = array(
			'ewww-image-optimizer/ewww-image-optimizer.php',
			'tiny-compress-images/tiny-compress-images.php',
			'shortpixel-image-optimiser/wp-shortpixel.php',
			'imagify/imagify.php',
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
	 * Check if optimization tool is installed.
	 *
	 * @since  1.6033.1500
	 * @param  string $tool Tool name.
	 * @return bool True if tool is installed.
	 */
	private static function is_tool_installed( $tool ) {
		// Check common plugin directories (EWWW pattern).
		$plugin_dirs = array(
			WP_CONTENT_DIR . '/ewww/',
			WP_CONTENT_DIR . '/plugins/ewww-image-optimizer/binaries/',
		);

		foreach ( $plugin_dirs as $dir ) {
			if ( file_exists( $dir . $tool ) || file_exists( $dir . $tool . '.exe' ) ) {
				return true;
			}
		}

		// Check system PATH (safe shell execution).
		$command = sprintf( 'which %s 2>/dev/null || command -v %s 2>/dev/null', escapeshellarg( $tool ), escapeshellarg( $tool ) );

		// Execute safely.
		$output = array();
		$return_var = 0;
		exec( $command, $output, $return_var );

		// If command succeeded and returned path, tool is installed.
		return 0 === $return_var && ! empty( $output );
	}
}
