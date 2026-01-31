<?php
/**
 * Image Optimization Lossless Not Implemented Diagnostic
 *
 * Checks if lossless image optimization is used.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2351
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Image Optimization Lossless Not Implemented Diagnostic Class
 *
 * Detects missing lossless optimization.
 *
 * @since 1.2601.2351
 */
class Diagnostic_Image_Optimization_Lossless_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'image-optimization-lossless-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Image Optimization Lossless Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if lossless image optimization is implemented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2351
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for image optimization plugins
		$image_plugins = array(
			'imagify/imagify.php',
			'ewww-image-optimizer/ewww-image-optimizer.php',
			'shortpixel-image-optimiser/wp-shortpixel.php',
		);

		$image_active = false;
		foreach ( $image_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$image_active = true;
				break;
			}
		}

		if ( ! $image_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Lossless image optimization is not implemented. Use image optimization plugins to compress images without quality loss.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 35,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/image-optimization-lossless-not-implemented',
			);
		}

		return null;
	}
}
