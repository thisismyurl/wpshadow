<?php
/**
 * Mobile Checkout Form Optimization Diagnostic
 *
 * Validates WooCommerce/EDD checkout forms are optimized for mobile completion
 * with proper field ordering, minimal friction, and mobile-friendly patterns.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Mobile
 * @since      1.602.1220
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Checkout Form Optimization Diagnostic Class
 *
 * Analyzes e-commerce checkout forms for mobile-specific issues that cause
 * abandonment. Checks field count, required fields, guest checkout, and more.
 *
 * @since 1.602.1220
 */
class Diagnostic_Mobile_Checkout_Form_Optimization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-checkout-form-optimization';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Checkout Form Optimization';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates checkout forms are optimized for mobile completion with minimal friction';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'mobile';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.602.1220
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Only relevant if WooCommerce or EDD active.
		if ( ! function_exists( 'WC' ) && ! function_exists( 'edd_get_option' ) ) {
			return null;
		}

		$issues = array();

		// Check WooCommerce checkout.
		if ( function_exists( 'WC' ) ) {
			$wc_issues = self::check_woocommerce_checkout();
			$issues = array_merge( $issues, $wc_issues );
		}

		// Check EDD checkout.
		if ( function_exists( 'edd_get_option' ) ) {
			$edd_issues = self::check_edd_checkout();
			$issues = array_merge( $issues, $edd_issues );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$issue_count    = count( $issues );
		$threat_level   = min( 85, 60 + ( $issue_count * 5 ) );
		$severity       = $threat_level >= 75 ? 'high' : 'medium';
		$auto_fixable   = false;

		$description = sprintf(
			/* translators: %d: number of optimization issues */
			__( 'Found %d mobile checkout optimization issue(s). Mobile checkout abandonment averages 85.65%% (Baymard Institute). Each friction point increases abandonment by 5-10%%.', 'wpshadow' ),
			$issue_count
		);

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => $description,
			'severity'    => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => $auto_fixable,
			'kb_link'     => 'https://wpshadow.com/kb/mobile-checkout-optimization',
			'details'     => array(
				'issue_count'   => $issue_count,
				'issues'        => $issues,
				'why_important' => __(
					'Mobile checkout optimization is critical for revenue:
					
					Mobile Checkout Statistics:
					• 85.65% average mobile cart abandonment (Baymard)
					• 70% abandon due to complex checkout process
					• Each additional form field increases abandonment 5%
					• 23% abandon if forced to create account
					• 60% abandon if checkout takes >3 minutes
					
					Mobile-Specific Challenges:
					• Small screens make long forms overwhelming
					• Virtual keyboard covers fields
					• Typing on mobile is slower and error-prone
					• Payment input especially frustrating
					• Users distracted by notifications
					
					Best Practices:
					• Guest checkout enabled (no forced registration)
					• Maximum 7-8 form fields
					• Single-column layout
					• Address auto-complete
					• Mobile payment options (Apple Pay, Google Pay)
					• Progress indicator for multi-step
					• Persistent CTA button (sticky)
					• Auto-focus first field
					• Save cart for later
					
					Revenue Impact:
					• Every 1% reduction in abandonment = significant revenue
					• Optimized mobile checkout: 15-25% conversion improvement
					• Amazon reports 1-click ordering 3x higher conversion',
					'wpshadow'
				),
				'how_to_fix'    => __(
					'Optimize checkout for mobile completion:
					
					WooCommerce Settings:
					1. Enable guest checkout:
					   WooCommerce > Settings > Accounts & Privacy
					   ☑ "Allow customers to place orders without an account"
					
					2. Minimize required fields:
					   WooCommerce > Settings > Shipping
					   Uncheck optional fields like Company, Address 2
					
					3. Enable address autocomplete:
					   Use plugins like "WooCommerce Address Autocomplete"
					
					4. Add mobile payment options:
					   Install Stripe (Apple Pay/Google Pay support)
					   WooCommerce > Settings > Payments > Stripe
					   ☑ "Enable Payment Request Buttons"
					
					5. Use single-column checkout:
					   Add to theme functions.php:
					   add_filter( "woocommerce_checkout_fields", function( $fields ) {
					       $fields["billing"]["billing_first_name"]["class"] = ["form-row-wide"];
					       $fields["billing"]["billing_last_name"]["class"] = ["form-row-wide"];
					       return $fields;
					   });
					
					6. Add progress indicator:
					   Use plugin "WooCommerce Checkout Manager"
					
					Easy Digital Downloads:
					1. Settings > Payment Gateways
					   ☑ "Disable Guest Checkout" = OFF
					
					2. Settings > General
					   Minimize required fields in purchase confirmation
					
					General Improvements:
					• Use autocomplete attributes
					• Validate inline (not just on submit)
					• Save cart contents for 30+ days
					• Email cart recovery for abandoned checkouts
					• Test on real mobile devices regularly',
					'wpshadow'
				),
			),
		);
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
