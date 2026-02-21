<?php
/**
 * Image Aspect Ratio Preservation Treatment
 *
 * Checks if images define proper aspect ratio to prevent Cumulative Layout Shift
 * during image loading.
 *
 * @since   1.6033.2097
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Image Aspect Ratio Preservation Treatment Class
 *
 * Verifies aspect ratio implementation:
 * - Width and height attributes
 * - CSS aspect-ratio support
 * - Container size definition
 * - CLS prevention
 *
 * @since 1.6033.2097
 */
class Treatment_Image_Aspect_Ratio_Preservation extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'image-aspect-ratio-preservation';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Image Aspect Ratio Preservation';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for proper image aspect ratio definition to prevent CLS';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.2097
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Image_Aspect_Ratio_Preservation' );
	}
}
