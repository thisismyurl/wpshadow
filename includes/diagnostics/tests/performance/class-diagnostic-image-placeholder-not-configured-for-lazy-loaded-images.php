<?php
/**
 * Image Placeholder Not Configured For Lazy Loaded Images Diagnostic
 *
 * Checks if image placeholders are configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2350
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Image Placeholder Not Configured For Lazy Loaded Images Diagnostic Class
 *
 * Detects missing image placeholders.
 *
 * @since 1.2601.2350
 */
class Diagnostic_Image_Placeholder_Not_Configured_For_Lazy_Loaded_Images extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'image-placeholder-not-configured-for-lazy-loaded-images';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Image Placeholder Not Configured For Lazy Loaded Images';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if image placeholders are configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2350
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for lazy loading plugin
		if ( ! is_plugin_active( 'lazy-load-images/lazy-load-images.php' ) && ! has_filter( 'wp_get_attachment_image_attributes' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Image placeholders are not configured for lazy-loaded images. Add placeholder images to improve perceived performance.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/image-placeholder-not-configured-for-lazy-loaded-images',
			);
		}

		return null;
	}
}
