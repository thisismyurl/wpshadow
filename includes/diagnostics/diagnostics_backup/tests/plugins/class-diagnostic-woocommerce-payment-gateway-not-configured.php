<?php
/**
 * WooCommerce Payment Gateway Not Configured Diagnostic
 *
 * Checks if payment gateways are configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce Payment Gateway Not Configured Diagnostic Class
 *
 * Detects missing payment gateway setup.
 *
 * @since 1.2601.2310
 */
class Diagnostic_WooCommerce_Payment_Gateway_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'woocommerce-payment-gateway-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'WooCommerce Payment Gateway Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if payment gateways are enabled';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'plugins';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if WooCommerce is active
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null;
		}

		// Check if any payment gateways are available
		$gateways = get_option( 'woocommerce_enabled_payment_gateways', array() );

		if ( empty( $gateways ) || ( is_array( $gateways ) && count( array_filter( $gateways ) ) === 0 ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'No payment gateways are configured or enabled in WooCommerce. Customers cannot complete purchases.', 'wpshadow' ),
				'severity'      => 'critical',
				'threat_level'  => 95,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/woocommerce-payment-gateway-not-configured',
			);
		}

		return null;
	}
}
