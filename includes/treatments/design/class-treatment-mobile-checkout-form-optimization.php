<?php
/**
 * Mobile Checkout Form Optimization Treatment
 *
 * Validates WooCommerce/EDD checkout forms are optimized for mobile completion
 * with proper field ordering, minimal friction, and mobile-friendly patterns.
 *
 * @package    WPShadow
 * @subpackage Treatments\Mobile
 * @since      1.602.1220
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
 * @since 1.602.1220
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
	 * @since  1.602.1220
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Checkout_Form_Optimization' );
	}

	/**
	 * Check WooCommerce checkout optimization.
	 *
	 * @since  1.602.1220
	 * @return array Issues found.
	 */
	private static function check_woocommerce_checkout() {
		$issues = array();

		// Check guest checkout.
		$guest_checkout = get_option( 'woocommerce_enable_guest_checkout', 'yes' );
		if ( 'yes' !== $guest_checkout ) {
			$issues[] = array(
				'issue_type'  => 'forced_registration',
				'severity'    => 'high',
				'description' => 'Guest checkout disabled - forces account creation (23% abandonment increase)',
				'location'    => 'WooCommerce > Settings > Accounts & Privacy',
			);
		}

		// Check account creation requirement.
		$require_account = get_option( 'woocommerce_enable_signup_and_login_from_checkout', 'no' );
		$force_account   = get_option( 'woocommerce_enable_checkout_login_reminder', 'no' );

		// Check number of required fields.
		$checkout_fields = WC()->countries->get_address_fields( WC()->countries->get_base_country(), 'billing_' );
		$required_count  = 0;
		foreach ( $checkout_fields as $field ) {
			if ( isset( $field['required'] ) && $field['required'] ) {
				++$required_count;
			}
		}

		if ( $required_count > 8 ) {
			$issues[] = array(
				'issue_type'  => 'too_many_required_fields',
				'severity'    => 'medium',
				'description' => sprintf( 'Checkout has %d required fields (recommended: 7-8 max)', $required_count ),
				'location'    => 'WooCommerce checkout fields',
			);
		}

		// Check if address autocomplete is enabled.
		$autocomplete_enabled = self::check_address_autocomplete_plugin();
		if ( ! $autocomplete_enabled ) {
			$issues[] = array(
				'issue_type'  => 'no_address_autocomplete',
				'severity'    => 'medium',
				'description' => 'Address autocomplete not detected - users must manually type addresses',
				'location'    => 'Consider installing address autocomplete plugin',
			);
		}

		// Check mobile payment options.
		$mobile_payment = self::check_mobile_payment_options();
		if ( ! $mobile_payment ) {
			$issues[] = array(
				'issue_type'  => 'no_mobile_payment',
				'severity'    => 'medium',
				'description' => 'Apple Pay/Google Pay not detected - missing fast mobile payment options',
				'location'    => 'Consider enabling Stripe Payment Request buttons',
			);
		}

		// Check if multi-step checkout is too long.
		$checkout_html = self::capture_checkout_html();
		if ( ! empty( $checkout_html ) ) {
			// Count form fields.
			$field_count = substr_count( $checkout_html, '<input' ) + substr_count( $checkout_html, '<select' );
			if ( $field_count > 15 ) {
				$issues[] = array(
					'issue_type'  => 'checkout_too_long',
					'severity'    => 'high',
					'description' => sprintf( 'Checkout has %d form elements (recommended: <15 for mobile)', $field_count ),
					'location'    => 'Checkout page',
				);
			}
		}

		return $issues;
	}

	/**
	 * Check Easy Digital Downloads checkout.
	 *
	 * @since  1.602.1220
	 * @return array Issues found.
	 */
	private static function check_edd_checkout() {
		$issues = array();

		// Check guest checkout.
		$guest_disabled = edd_get_option( 'logged_in_only', false );
		if ( $guest_disabled ) {
			$issues[] = array(
				'issue_type'  => 'forced_registration',
				'severity'    => 'high',
				'description' => 'EDD checkout requires login - forces account creation',
				'location'    => 'Downloads > Settings > General',
			);
		}

		return $issues;
	}

	/**
	 * Check if address autocomplete plugin is active.
	 *
	 * @since  1.602.1220
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
	 * @since  1.602.1220
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
	 * @since  1.602.1220
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
