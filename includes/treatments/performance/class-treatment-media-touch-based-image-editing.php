<?php
/**
 * Media Touch-Based Image Editing Treatment
 *
 * Checks if touch-based image editing is properly supported.
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
 * Media Touch-Based Image Editing Treatment Class
 *
 * Verifies that WordPress image editor supports touch-based interactions
 * for cropping, rotating, and editing on mobile devices.
 *
 * @since 0.6093.1200
 */
class Treatment_Media_Touch_Based_Image_Editing extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-touch-based-image-editing';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Media Touch-Based Image Editing';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if touch-based image editing is properly supported';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Touch_Based_Image_Editing' );
	}
}
