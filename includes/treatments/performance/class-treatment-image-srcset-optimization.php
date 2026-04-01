<?php
/**
 * Image Srcset Optimization Treatment
 *
 * Tests if images are optimized with srcset for responsive display on different devices.
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
 * Image Srcset Optimization Treatment Class
 *
 * Validates that images are served with srcset attributes for responsive
 * images, allowing browsers to select appropriate size for device/viewport.
 *
 * @since 0.6093.1200
 */
class Treatment_Image_Srcset_Optimization extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'image-srcset-optimization';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Image Srcset Optimization';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates images are served with srcset for responsive display';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * Tests if featured images and content images are generated with
	 * multiple sizes for responsive image srcset attribute.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Image_Srcset_Optimization' );
	}
}
