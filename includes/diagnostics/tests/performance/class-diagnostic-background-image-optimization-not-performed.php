<?php
/**
 * Background Image Optimization Not Performed Diagnostic
 *
 * Checks if background images are optimized.
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
 * Background Image Optimization Not Performed Diagnostic Class
 *
 * Detects unoptimized background images.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Background_Image_Optimization_Not_Performed extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'background-image-optimization-not-performed';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Background Image Optimization Not Performed';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if background images are optimized';

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
		// Check for background image optimization
		if ( ! has_filter( 'wp_head', 'optimize_background_images' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Background image optimization is not performed. Use CSS srcset and picture element for responsive background images, compress WebP formats, and use lazy loading for below-fold backgrounds.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/background-image-optimization-not-performed',
			);
		}

		return null;
	}
}
