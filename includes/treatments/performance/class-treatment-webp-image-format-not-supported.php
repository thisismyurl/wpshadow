<?php
/**
 * WebP Image Format Not Supported Treatment
 *
 * Checks if WebP format is supported.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WebP Image Format Not Supported Treatment Class
 *
 * Detects missing WebP support.
 *
 * @since 1.6030.2352
 */
class Treatment_WebP_Image_Format_Not_Supported extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'webp-image-format-not-supported';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'WebP Image Format Not Supported';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if WebP format is supported';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.2352
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
