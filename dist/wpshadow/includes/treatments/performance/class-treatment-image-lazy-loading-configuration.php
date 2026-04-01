<?php
/**
 * Image Lazy Loading Configuration Treatment
 *
 * Tests if lazy loading is properly configured for images on the frontend.
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
 * Image Lazy Loading Configuration Treatment Class
 *
 * Validates that lazy loading is enabled for images to improve page load
 * performance by deferring offscreen image loads.
 *
 * @since 0.6093.1200
 */
class Treatment_Image_Lazy_Loading_Configuration extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'image-lazy-loading-configuration';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Image Lazy Loading Configuration';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if lazy loading is properly configured for frontend images';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * Tests if WordPress has lazy loading enabled and if images
	 * are configured with loading="lazy" attribute.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Image_Lazy_Loading_Configuration' );
	}
}
