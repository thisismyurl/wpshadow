<?php
/**
 * Shipping & Delivery Information Clarity Treatment
 *
 * Checks if shipping costs and delivery information are clear before checkout.
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
 * Shipping & Delivery Information Clarity Treatment Class
 *
 * 60% abandon carts due to unexpected shipping costs. Showing shipping
 * information upfront reduces abandonment.
 *
 * @since 1.6093.1200
 */
class Treatment_Shipping_Delivery_Information extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'shipping-delivery-information';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Shipping & Delivery Information Clarity';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if shipping costs and delivery timeframes are clear before checkout';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'conversion';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Shipping_Delivery_Information' );
	}
}
