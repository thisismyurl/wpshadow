<?php
/**
 * WebP / Modern Image Format Support Diagnostic
 *
 * Checks whether the server's image processing library (GD or ImageMagick)
 * supports the WebP format, enabling WordPress to generate and serve
 * smaller, next-generation image files.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Webp_Support Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Webp_Support extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'webp-support';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Modern Image Format Support';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether the server supports WebP image format via GD or ImageMagick, enabling WordPress 5.8+ to generate smaller next-generation image files during uploads.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

/**
 * Confidence level of this diagnostic.
 *
 * @var string
 */
protected static $confidence = 'standard';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests GD for imagewebp() support and Imagick for WEBP format availability.
	 * Returns null (healthy) if either library can handle WebP.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		$gd_webp      = function_exists( 'imagewebp' ) && function_exists( 'imagecreatefromwebp' );
		$imagick_webp = false;

		if ( class_exists( 'Imagick' ) ) {
			try {
				$formats      = \Imagick::queryFormats( 'WEBP' );
				$imagick_webp = ! empty( $formats );
			} catch ( \Exception $e ) {
				$imagick_webp = false;
			}
		}

		if ( $gd_webp || $imagick_webp ) {
			return null; // Server supports WebP.
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'The server\'s image processing library (GD or ImageMagick) does not support WebP format. WebP images are typically 25–35% smaller than equivalent JPEG/PNG files, improving page load times. Ask your host to enable WebP support in the GD or Imagick extension, or use an image optimisation plugin that handles conversion externally.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 30,
			'kb_link'      => 'https://wpshadow.com/kb/webp-support',
			'details'      => array(
				'gd_webp_support'      => $gd_webp,
				'imagick_webp_support' => $imagick_webp,
			),
		);
	}
}
