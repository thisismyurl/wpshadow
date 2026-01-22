<?php declare(strict_types=1);
/**
 * WooCommerce Payment Gateway Security Diagnostic
 *
 * Philosophy: Payment security - PCI compliance
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check WooCommerce payment security.
 */
class Diagnostic_Woocommerce_Payment_Security {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		if ( ! function_exists( 'is_woocommerce' ) ) {
			return null;
		}
		
		$gateways = get_option( 'woocommerce_enabled_payment_gateways', array() );
		
		if ( empty( $gateways ) ) {
			return null;
		}
		
		// Check if using outdated or insecure gateways
		foreach ( $gateways as $gateway ) {
			if ( preg_match( '/paypal|stripe|braintree|authorize/', $gateway ) ) {
				return null; // Using secure gateway
			}
		}
		
		return array(
			'id'          => 'woocommerce-payment-security',
			'title'       => 'WooCommerce Using Insecure Payment Gateway',
			'description' => 'Payment gateway is not PCI DSS compliant or outdated. Storing credit cards insecurely exposes customer data. Use certified gateways (Stripe, PayPal, Braintree).',
			'severity'    => 'critical',
			'category'    => 'security',
			'kb_link'     => 'https://wpshadow.com/kb/pci-compliant-payments/',
			'training_link' => 'https://wpshadow.com/training/payment-security/',
			'auto_fixable' => false,
			'threat_level' => 90,
		);
	}
}
