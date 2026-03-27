<?php
/**
 * Mobile Responsive Image Srcset Treatment
 *
 * Validates images use srcset for density variants.
 *
 * @since 1.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Responsive Image Srcset Treatment Class
 *
 * Validates that images use srcset with density variants (1x/2x/3x) and sizes
 * attribute for responsive image delivery, optimizing bandwidth.
 *
 * @since 1.6093.1200
 */
class Treatment_Mobile_Responsive_Image_Srcset extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-responsive-image-srcset';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Responsive Image Srcset';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validate images use srcset with density/size variants for responsive delivery';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Responsive_Image_Srcset' );
	}
}
