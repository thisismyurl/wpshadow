<?php
/**
 * Responsive Image Srcset Generation Treatment
 *
 * Validates srcset attribute generation for responsive images.
 * Tests multiple size variants and browser selection.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.7029.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Responsive Image Srcset Generation Treatment Class
 *
 * Checks if WordPress is properly generating srcset attributes
 * for responsive images to serve optimal sizes per device.
 *
 * @since 1.7029.1200
 */
class Treatment_Responsive_Image_Srcset_Generation extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'responsive-image-srcset-generation';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Responsive Image Srcset Generation';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates srcset attribute generation for responsive images';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * Tests if WordPress generates proper srcset attributes with
	 * multiple image size variants.
	 *
	 * @since  1.7029.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Responsive_Image_Srcset_Generation' );
	}
}
