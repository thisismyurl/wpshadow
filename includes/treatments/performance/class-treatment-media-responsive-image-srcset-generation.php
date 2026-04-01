<?php
/**
 * Media Responsive Image Srcset Generation Treatment
 *
 * Checks if responsive image srcset attributes are properly generated.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Media Responsive Image Srcset Generation Treatment Class
 *
 * Verifies that WordPress is generating srcset and sizes attributes
 * for responsive images to serve appropriate sizes to different devices.
 *
 * @since 0.6093.1200
 */
class Treatment_Media_Responsive_Image_Srcset_Generation extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-responsive-image-srcset-generation';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Media Responsive Image Srcset Generation';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if responsive image srcset attributes are properly generated';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Responsive_Image_Srcset_Generation' );
	}
}
