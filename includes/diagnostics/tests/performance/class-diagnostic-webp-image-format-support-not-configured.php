<?php
/**
 * WebP Image Format Support Not Configured Diagnostic
 *
 * Checks if WebP format is supported.
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
 * WebP Image Format Support Not Configured Diagnostic Class
 *
 * Detects missing WebP support.
 *
 * @since 1.2601.2352
 */
class Diagnostic_WebP_Image_Format_Support_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'webp-image-format-support-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'WebP Image Format Support Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if WebP format is supported';

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
		// Check if WebP support is configured
		if ( ! wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) ) && ! is_plugin_active( 'ewww-image-optimizer/ewww-image-optimizer.php' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'WebP image format is not supported. Enable WebP support to deliver smaller, faster-loading images to modern browsers.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/webp-image-format-support-not-configured',
			);
		}

		return null;
	}
}
