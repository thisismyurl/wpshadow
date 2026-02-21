<?php
/**
 * Mobile Checkout Optimization Treatment
 *
 * Checks if mobile users have fast checkout (<3 seconds).
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6035.1400
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Checkout Optimization Treatment Class
 *
 * Verifies that the checkout process is optimized for mobile users
 * and loads quickly on mobile devices.
 *
 * @since 1.6035.1400
 */
class Treatment_Mobile_Checkout_Optimization extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-checkout-optimization';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Checkout Optimization';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if mobile users have fast checkout (<3 seconds)';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ecommerce';

	/**
	 * Run the mobile checkout optimization treatment check.
	 *
	 * @since  1.6035.1400
	 * @return array|null Finding array if mobile checkout issues detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Checkout_Optimization' );
	}
}
