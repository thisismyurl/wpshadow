<?php
/**
 * Image Optimization Plugin Not Active Diagnostic
 *
 * Checks if image optimization is enabled.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Image Optimization Plugin Not Active Diagnostic Class
 *
 * Detects inactive image optimization.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Image_Optimization_Plugin_Not_Active extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'image-optimization-plugin-not-active';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Image Optimization Plugin Not Active';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if image optimization is enabled';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for image optimization plugin
		if ( ! is_plugin_active( 'imagify/imagify.php' ) && ! is_plugin_active( 'shortpixel-image-optimiser/wp-shortpixel.php' ) && ! is_plugin_active( 'ewww-image-optimizer/ewww-image-optimizer.php' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Image optimization plugin is not active. Use Imagify, ShortPixel, or EWWW Image Optimizer to reduce image file sizes without quality loss.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 35,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/image-optimization-plugin-not-active',
			);
		}

		return null;
	}
}
