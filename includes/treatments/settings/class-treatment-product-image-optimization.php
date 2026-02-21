<?php
/**
 * Product Image Optimization Treatment
 *
 * Tests if product images are optimized for fast loading.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.7034.1220
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
 * Validates that product images are optimized with proper sizes,
 * compression, and lazy loading for fast catalog browsing.
 *
 * @since 1.7034.1220
 */
class Treatment_Product_Image_Optimization extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'product-image-optimization';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Product Image Optimization';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if product images are optimized for fast loading';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'ecommerce';

	/**
	 * Run the treatment check.
	 *
	 * Tests product image optimization including sizes, compression,
	 * format, and lazy loading configuration.
	 *
	 * @since  1.7034.1220
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Product_Image_Optimization' );
	}
}
