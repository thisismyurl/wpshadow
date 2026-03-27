<?php
/**
 * Mobile Background Image Performance Treatment
 *
 * Validates background images are optimized for mobile.
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
 * Mobile Background Image Performance Treatment Class
 *
 * Validates that background images are optimized for mobile with appropriate
 * image sizes and media queries to reduce bandwidth.
 *
 * @since 1.6093.1200
 */
class Treatment_Mobile_Background_Image_Performance extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-background-image-performance';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Background Image Performance';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validate background images are optimized for mobile with media queries';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Background_Image_Performance' );
	}
}
