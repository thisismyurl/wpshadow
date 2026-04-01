<?php
/**
 * Responsive Image Srcset Generation Treatment
 *
 * Detects if responsive images with srcset are properly generated for all sizes.
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
 * Treatment_Media_Responsive_Image_Srcset Class
 *
 * Tests if responsive image srcset generation is enabled and properly
 * generating multiple image sizes for responsive delivery.
 *
 * @since 0.6093.1200
 */
class Treatment_Media_Responsive_Image_Srcset extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-responsive-image-srcset';

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
	protected static $description = 'Verifies responsive images with srcset are properly generated';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Responsive_Image_Srcset' );
	}
}
