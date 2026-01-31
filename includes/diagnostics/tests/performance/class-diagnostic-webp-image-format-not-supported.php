<?php
/**
 * WebP Image Format Not Supported Diagnostic
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
 * WebP Image Format Not Supported Diagnostic Class
 *
 * Detects missing WebP support.
 *
 * @since 1.2601.2352
 */
class Diagnostic_WebP_Image_Format_Not_Supported extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'webp-image-format-not-supported';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'WebP Image Format Not Supported';

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
		// Check if WebP is supported in GD library
		if ( extension_loaded( 'gd' ) && ! function_exists( 'imagewebp' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'WebP image format is not supported by your server. Enable WebP support in GD library to serve modern image formats and reduce file sizes.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/webp-image-format-not-supported',
			);
		}

		return null;
	}
}
