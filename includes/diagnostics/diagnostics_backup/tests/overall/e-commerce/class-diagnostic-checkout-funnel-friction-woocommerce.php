<?php
/**
 * Diagnostic: Checkout Funnel Friction Analysis (WooCommerce)
 *
 * Identifies friction points in WooCommerce checkout causing abandonment.
 * Each additional checkout field reduces conversion 5-10%.
 * 30% of users abandon if forced to create account.
 * Small improvements yield significant revenue increases.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Ecommerce
 * @since      1.26028.1900
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Checkout_Funnel_Friction_Woocommerce
 *
 * Tests WooCommerce checkout friction points.
 *
 * @since 1.26028.1900
 */
class Diagnostic_Checkout_Funnel_Friction_Woocommerce extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'checkout-funnel-friction-woocommerce';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Checkout Funnel Friction Analysis (WooCommerce)';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Identifies friction points in WooCommerce checkout causing abandonment';

	/**
	 * Diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'e-commerce';

	/**
	 * Check WooCommerce checkout friction.
	 *
	 * @since  1.26028.1900
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Check if WooCommerce is active.
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null;
		}

		$friction_points = array();
		$severity = 'medium';
		$threat_level = 50;

		// Check guest checkout.
		$guest_checkout_enabled = 'yes' === get_option( 'woocommerce_enable_guest_checkout', 'yes' );

		if ( ! $guest_checkout_enabled ) {
			$friction_points[] = __( 'Guest checkout disabled - forces account creation (30% abandonment)', 'wpshadow' );
			$severity = 'critical';
			$threat_level = 80;
		}

		// Check account creation requirement.
		$account_creation_required = 'yes' === get_option( 'woocommerce_enable_signup_and_login_from_checkout', 'no' );

		if ( $account_creation_required && ! $guest_checkout_enabled ) {
			$friction_points[] = __( 'Account creation mandatory at checkout', 'wpshadow' );
			$severity = 'critical';
			$threat_level = max( $threat_level, 80 );
		}

		// Count checkout fields.
		$checkout_fields = self::count_checkout_fields();

		if ( $checkout_fields > 15 ) {
			$friction_points[] = sprintf(
				/* translators: %d: Number of checkout fields */
				__( '%d checkout fields (each additional field reduces conversion 5-10%%)', 'wpshadow' ),
				$checkout_fields
			);
			$threat_level = max( $threat_level, 70 );
			if ( 'medium' === $severity ) {
				$severity = 'high';
			}
		}

		// Check payment methods.
		$payment_gateways = self::get_enabled_payment_gateways();

		if ( count( $payment_gateways ) < 2 ) {
			$friction_points[] = __( 'Only one payment method available (limits customer options)', 'wpshadow' );
			$threat_level = max( $threat_level, 60 );
		}

		// Check for shipping calculator on cart.
		$cart_shipping_calc = 'yes' === get_option( 'woocommerce_enable_shipping_calc', 'yes' );

		if ( ! $cart_shipping_calc ) {
			$friction_points[] = __( 'No shipping calculator on cart (customers can\'t see total cost before checkout)', 'wpshadow' );
			$threat_level = max( $threat_level, 55 );
		}

		// Check coupon field.
		$coupons_enabled = 'yes' === get_option( 'woocommerce_enable_coupons', 'yes' );

		if ( ! $coupons_enabled ) {
			// This is actually good for conversion - one less distraction.
			// Don't flag as friction point.
		}

		// Check for terms and conditions.
		$terms_page_id = wc_get_page_id( 'terms' );

		if ( $terms_page_id < 0 ) {
			$friction_points[] = __( 'No terms and conditions page (may reduce trust)', 'wpshadow' );
			$threat_level = max( $threat_level, 45 );
		}

		// If significant friction detected, return finding.
		if ( ! empty( $friction_points ) && count( $friction_points ) >= 2 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: Number of friction points, 2: List of friction points */
					__( 'Detected %1$d checkout friction point(s): %2$s. Reducing friction can significantly increase conversion rates and revenue.', 'wpshadow' ),
					count( $friction_points ),
					implode( '; ', $friction_points )
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/checkout-funnel-friction-woocommerce',
				'meta'         => array(
					'friction_points'           => $friction_points,
					'guest_checkout_enabled'    => $guest_checkout_enabled,
					'checkout_fields_count'     => $checkout_fields,
					'payment_gateways_count'    => count( $payment_gateways ),
					'payment_gateways'          => $payment_gateways,
					'recommendation'            => 'Enable guest checkout and minimize required fields',
				),
			);
		}

		// Checkout is reasonably optimized.
		return null;
	}

	/**
	 * Count total checkout fields.
	 *
	 * @since  1.26028.1900
	 * @return int Number of checkout fields.
	 */
	private static function count_checkout_fields() {
		// Get WooCommerce checkout fields.
		$checkout = WC()->checkout();

		if ( ! $checkout ) {
			return 0;
		}

		$fields = $checkout->get_checkout_fields();
		$count = 0;

		// Count all fields across billing, shipping, and account sections.
		foreach ( $fields as $field_group => $field_list ) {
			if ( is_array( $field_list ) ) {
				foreach ( $field_list as $field_key => $field_data ) {
					// Only count required fields for a more accurate friction assessment.
					if ( isset( $field_data['required'] ) && $field_data['required'] ) {
						$count++;
					}
				}
			}
		}

		return $count;
	}

	/**
	 * Get enabled payment gateways.
	 *
	 * @since  1.26028.1900
	 * @return array List of enabled gateway titles.
	 */
	private static function get_enabled_payment_gateways() {
		$gateways = WC()->payment_gateways->get_available_payment_gateways();
		$enabled_gateways = array();

		foreach ( $gateways as $gateway_id => $gateway ) {
			if ( 'yes' === $gateway->enabled ) {
				$enabled_gateways[] = $gateway->get_title();
			}
		}

		return $enabled_gateways;
	}
}
