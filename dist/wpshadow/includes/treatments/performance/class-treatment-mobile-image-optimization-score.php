<?php
/**
 * Mobile Image Optimization Score
 *
 * Comprehensive image size and format validation.
 *
 * @package    WPShadow
 * @subpackage Treatments\Performance
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Image Optimization Score
 *
 * Validates image formats (WEBP/AVIF), responsive srcset,
 * and image-to-page-weight ratio.
 *
 * @since 0.6093.1200
 */
class Treatment_Mobile_Image_Optimization_Score extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-image-optimization';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Image Optimization Score';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Comprehensive image size and format validation';

	/**
	 * The treatment family.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Image_Optimization_Score' );
	}
}
