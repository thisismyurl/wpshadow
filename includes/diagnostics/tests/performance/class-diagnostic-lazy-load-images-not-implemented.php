<?php
/**
 * Lazy Load Images Not Implemented Diagnostic
 *
 * Checks if lazy loading is implemented.
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
 * Lazy Load Images Not Implemented Diagnostic Class
 *
 * Detects non-lazy-loaded images.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Lazy_Load_Images_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'lazy-load-images-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Lazy Load Images Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if lazy loading is implemented';

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
		// Check if lazy loading is enabled
		if ( ! has_filter( 'wp_get_attachment_image_attributes', 'wp_lazy_load_images' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Lazy load for images is not implemented. Enable lazy loading to defer off-screen image loading and improve page speed.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 35,
				'auto_fixable'  => true,
				'kb_link'       => 'https://wpshadow.com/kb/lazy-load-images-not-implemented',
			);
		}

		return null;
	}
}
