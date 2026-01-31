<?php
/**
 * Featured Image Size Optimization Not Configured Diagnostic
 *
 * Checks if featured images are optimized sizes.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2315
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Featured Image Size Optimization Not Configured Diagnostic Class
 *
 * Detects missing featured image optimization.
 *
 * @since 1.2601.2315
 */
class Diagnostic_Featured_Image_Size_Optimization_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'featured-image-size-optimization-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Featured Image Size Optimization Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if featured image sizes are optimized';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2315
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if featured image sizes are responsive
		$image_sizes = wp_get_registered_image_subsizes();

		if ( empty( $image_sizes ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'No custom image sizes are registered. Register responsive image sizes to improve performance.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/featured-image-size-optimization-not-configured',
			);
		}

		return null;
	}
}
