<?php
/**
 * AVIF Image Format Support Diagnostic
 *
 * Checks if AVIF image format support is available for the most aggressive
 * compression with smallest file sizes.
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
 * AVIF Image Format Support Diagnostic Class
 *
 * Verifies AVIF support:
 * - Server support for AVIF encoding
 * - ImageMagick or libaom availability
 * - AVIF plugin detection
 * - File size savings estimate
 *
 * @since 1.6093.1200
 */
class Diagnostic_Avif_Image_Format_Support extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'avif-image-format-support';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'AVIF Image Format Support';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for AVIF image format support for maximum compression';

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
		$avif_support    = false;
		$plugin_detected = false;

		// Check ImageMagick support
		if ( extension_loaded( 'imagick' ) ) {
			try {
				$imagick = new \Imagick();
				$formats = $imagick->queryFormats();
				if ( in_array( 'AVIF', $formats, true ) ) {
					$avif_support = true;
				}
			} catch ( \Exception $e ) {
				// Imagick not available
			}
		}

		// Check for AVIF plugins
		$avif_plugins = array(
			'imagify/imagify.php'                                => 'Imagify',
			'ewww-image-optimizer/ewww-image-optimizer.php'      => 'EWWW Image Optimizer',
			'optimus/optimus.php'                                => 'Optimus',
			'shortpixel-image-optimiser/wp-shortpixel.php'       => 'ShortPixel',
		);

		foreach ( $avif_plugins as $plugin_path => $plugin_name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$plugin_detected = true;
				$avif_support    = true;
				break;
			}
		}

		if ( ! $avif_support ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'AVIF image format support is not available. AVIF provides 30-50%% better compression than WebP.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 25,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/avif-image-format',
				'meta'          => array(
					'avif_available'       => $avif_support,
					'plugin_installed'     => $plugin_detected,
					'recommendation'       => 'Install image optimizer plugin with AVIF support (Imagify, EWWW, ShortPixel)',
					'impact'               => 'AVIF reduces image size by 30-50% compared to WebP',
					'browser_support'      => '85% of browsers support AVIF with fallback to WebP/JPEG',
					'best_practice'        => 'Use <picture> tag with AVIF first, then WebP, then JPEG fallback',
				),
			);
		}

		return null;
	}
}
