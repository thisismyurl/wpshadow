<?php
/**
 * Image Resizing Configuration Missing Treatment
 *
 * Detects when WordPress image resizing is not properly configured,
 * leading to unnecessarily large images being served to users.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Image Resizing Misconfigured Treatment Class
 *
 * Checks if WordPress image resizing is properly configured to generate
 * appropriately-sized images for different contexts (thumbnails, medium, large).
 * Proper resizing prevents serving full-resolution images where smaller
 * versions would suffice.
 *
 * Based on EWWW Image Optimizer resizing validation patterns.
 *
 * @since 1.6093.1200
 */
class Treatment_Image_Resizing_Misconfigured extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'image-resizing-misconfigured';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Image Resizing Configuration Missing or Misconfigured';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates WordPress image resizing settings for optimal responsive image delivery';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * Checks if WordPress image sizes are properly configured.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Image_Resizing_Misconfigured' );
	}
}
