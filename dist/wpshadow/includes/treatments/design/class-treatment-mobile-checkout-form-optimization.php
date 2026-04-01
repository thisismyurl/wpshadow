<?php
/**
 * Mobile Checkout Form Optimization Treatment
 *
 * Validates WooCommerce/EDD checkout forms are optimized for mobile completion
 * with proper field ordering, minimal friction, and mobile-friendly patterns.
 *
 * @package    WPShadow
 * @subpackage Treatments\Mobile
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Checkout Form Optimization Treatment Class
 *
 * Analyzes e-commerce checkout forms for mobile-specific issues that cause
 * abandonment. Checks field count, required fields, guest checkout, and more.
 *
 * @since 0.6093.1200
 */
class Treatment_Mobile_Checkout_Form_Optimization extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-checkout-form-optimization';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Checkout Form Optimization';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates checkout forms are optimized for mobile completion with minimal friction';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'mobile';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Checkout_Form_Optimization' );
	}

	/**
	 * Check if address autocomplete plugin is active.
	 *
	 * @since 0.6093.1200
	 * @return bool True if autocomplete detected.
	 */
	private static function check_address_autocomplete_plugin() {
		$active_plugins = get_option( 'active_plugins', array() );

		// Common address autocomplete plugins.
		$autocomplete_plugins = array(
			'woocommerce-address-autocomplete',
			'woo-address-autocomplete',
			'address-autocomplete-for-woocommerce',
		);

		foreach ( $autocomplete_plugins as $plugin ) {
			foreach ( $active_plugins as $active_plugin ) {
				if ( strpos( $active_plugin, $plugin ) !== false ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Check if mobile payment options are enabled.
	 *
	 * @since 0.6093.1200
	 * @return bool True if mobile payment detected.
	 */
	private static function check_mobile_payment_options() {
		// Check if Stripe is active with Payment Request enabled.
		$stripe_settings = get_option( 'woocommerce_stripe_settings', array() );
		if ( isset( $stripe_settings['payment_request'] ) && 'yes' === $stripe_settings['payment_request'] ) {
			return true;
		}

		// Check for PayPal.
		$paypal_settings = get_option( 'woocommerce_paypal_settings', array() );
		if ( isset( $paypal_settings['enabled'] ) && 'yes' === $paypal_settings['enabled'] ) {
			return true;
		}

		return false;
	}

	/**
	 * Capture checkout page HTML.
	 *
	 * @since 0.6093.1200
	 * @return string HTML content.
	 */
	private static function capture_checkout_html() {
		if ( ! function_exists( 'wc_get_checkout_url' ) ) {
			return '';
		}

		$response = wp_remote_get(
			wc_get_checkout_url(),
			array(
				'timeout'    => 10,
				'user-agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X)',
			)
		);

		if ( is_wp_error( $response ) ) {
			return '';
		}

		return wp_remote_retrieve_body( $response );
	}
}
