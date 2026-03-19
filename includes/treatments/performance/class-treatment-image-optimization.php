<?php
/**
 * Image Optimization Treatment
 *
 * Checks for unoptimized images that could impact Core Web Vitals,
 * particularly Largest Contentful Paint (LCP).
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
 * Image Optimization Treatment Class
 *
 * Analyzes images for optimization opportunities including:
 * - Oversized source images
 * - Missing responsive image variations
 * - High file size to dimensions ratio
 * - Unoptimized JPEG quality
 *
 * @since 1.6093.1200
 */
class Treatment_Image_Optimization extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'image-optimization';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Image Optimization';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for unoptimized images impacting Core Web Vitals';

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
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Image_Optimization' );
	}
}
