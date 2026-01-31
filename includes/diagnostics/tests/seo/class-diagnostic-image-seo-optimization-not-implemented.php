<?php
/**
 * Image SEO Optimization Not Implemented Diagnostic
 *
 * Checks if image SEO is optimized.
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
 * Image SEO Optimization Not Implemented Diagnostic Class
 *
 * Detects unoptimized image SEO.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Image_SEO_Optimization_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'image-seo-optimization-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Image SEO Optimization Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if image SEO is optimized';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for image alt text optimization
		if ( ! has_filter( 'wp_get_attachment_image_attributes', 'wp_add_image_alt_text' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Image SEO optimization is not implemented. Add descriptive alt text and file names to images for better search visibility.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/image-seo-optimization-not-implemented',
			);
		}

		return null;
	}
}
