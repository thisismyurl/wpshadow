<?php
/**
 * Shopping Cart Performance Treatment
 *
 * Tests if shopping cart and checkout pages are optimized for performance.
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
 * Shopping Cart Performance Treatment Class
 *
 * Validates that cart and checkout pages load quickly and are
 * optimized for conversion.
 *
 * @since 1.6093.1200
 */
class Treatment_Shopping_Cart_Performance extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'shopping-cart-performance';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Shopping Cart Performance';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if shopping cart and checkout pages are optimized for performance';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'ecommerce';

	/**
	 * Run the treatment check.
	 *
	 * Tests cart and checkout page performance including caching
	 * exclusions, script optimization, and database queries.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Shopping_Cart_Performance' );
	}
}
