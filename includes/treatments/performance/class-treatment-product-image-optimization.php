<?php
/**
 * Product Image Optimization Treatment
 *
 * Checks if product images are compressed and cached.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6035.1415
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Product Image Optimization Treatment Class
 *
 * Verifies that product images are properly optimized, compressed,
 * and cached for fast delivery.
 *
 * @since 1.6035.1415
 */
class Treatment_Product_Image_Optimization extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'product-image-optimization';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Product Image Optimization';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if product images are compressed and cached';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the product image optimization treatment check.
	 *
	 * @since  1.6035.1415
	 * @return array|null Finding array if image optimization issues detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Product_Image_Optimization' );
	}
}
