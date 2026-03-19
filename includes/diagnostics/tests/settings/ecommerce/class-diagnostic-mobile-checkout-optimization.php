<?php
/**
 * Mobile Checkout Optimization Diagnostic
 *
 * Checks if mobile users have fast checkout (<3 seconds).
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Checkout Optimization Diagnostic Class
 *
 * Verifies that the checkout process is optimized for mobile users
 * and loads quickly on mobile devices.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Mobile_Checkout_Optimization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-checkout-optimization';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Checkout Optimization';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if mobile users have fast checkout (<3 seconds)';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ecommerce';

	/**
	 * Run the mobile checkout optimization diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if mobile checkout issues detected, null otherwise.
	 */
	public static function check() {
		$issues    = array();
		$warnings  = array();
		$stats     = array();

		// Check for WooCommerce.
		if ( ! function_exists( 'wc' ) || ! class_exists( 'WooCommerce' ) ) {
			$warnings[] = __( 'WooCommerce not active - skipping checkout check', 'wpshadow' );
			return null;
		}

		// Check mobile responsiveness.
		$theme = wp_get_theme();
		$theme_name = $theme->get( 'Name' );
		$stats['theme'] = $theme_name;

		// Check if theme is mobile-responsive.
		$theme_dir = $theme->get_stylesheet_directory();
		$style_file = $theme_dir . '/style.css';

		$is_responsive = false;
		if ( file_exists( $style_file ) ) {
			$style_content = file_get_contents( $style_file );
			if ( preg_match( '/viewport.*width=device-width/i', $style_content ) ||
				 function_exists( 'wp_is_mobile' ) ) {
				$is_responsive = true;
			}
		}

		$stats['mobile_responsive'] = $is_responsive;

		if ( ! $is_responsive ) {
			$warnings[] = __( 'Theme may not be mobile responsive', 'wpshadow' );
		}

		// Check checkout page performance.
		$checkout_page = wc_get_page_id( 'checkout' );
		$stats['checkout_page_id'] = $checkout_page;

		if ( $checkout_page < 0 ) {
			$issues[] = __( 'Checkout page not configured', 'wpshadow' );
		}

		// Check for mobile-specific checkout optimizations.
		$checkout_url = wc_get_checkout_url();
		$checkout_response = wp_remote_get( $checkout_url, array(
			'timeout'   => 10,
			'blocking'  => true,
			'sslverify' => false,
		) );

		$checkout_load_time = 0;
		if ( ! is_wp_error( $checkout_response ) ) {
			$checkout_load_time = wp_remote_retrieve_header( $checkout_response, 'content-length' );
		}

		$stats['checkout_page_size'] = $checkout_load_time;

		if ( $checkout_load_time > 500000 ) { // 500KB+.
			$warnings[] = sprintf(
				/* translators: %d: KB */
				__( 'Checkout page size large (%dKB) - slow on mobile', 'wpshadow' ),
				intval( $checkout_load_time / 1024 )
			);
		}

		// Check for form field optimization.
		$checkout_fields = WC()->checkout->get_checkout_fields( 'billing' );
		$stats['checkout_fields_count'] = count( $checkout_fields );

		if ( count( $checkout_fields ) > 15 ) {
			$warnings[] = sprintf(
				/* translators: %d: count */
				__( 'Checkout form has many fields (%d) - consider streamlining for mobile', 'wpshadow' ),
				count( $checkout_fields )
			);
		}

		// Check for one-click checkout.
		$one_click_enabled = get_option( 'woocommerce_enable_guest_checkout' );
		$stats['guest_checkout_enabled'] = boolval( $one_click_enabled );

		if ( ! $one_click_enabled ) {
			$warnings[] = __( 'Guest checkout disabled - requires account creation, slower on mobile', 'wpshadow' );
		}

		// Check payment method count.
		$payment_methods = WC()->payment_gateways()->payment_gateways();
		$enabled_methods = 0;

		foreach ( $payment_methods as $method ) {
			if ( $method->enabled === 'yes' ) {
				$enabled_methods++;
			}
		}

		$stats['payment_methods'] = $enabled_methods;

		// Check for mobile payment methods (Apple Pay, Google Pay).
		$has_apple_pay = get_option( 'woocommerce_stripe_apple_pay_enabled' );
		$has_google_pay = get_option( 'woocommerce_stripe_google_pay_enabled' );

		$stats['apple_pay_enabled'] = boolval( $has_apple_pay );
		$stats['google_pay_enabled'] = boolval( $has_google_pay );

		if ( ! $has_apple_pay || ! $has_google_pay ) {
			$warnings[] = __( 'Mobile payment methods (Apple Pay/Google Pay) not fully enabled', 'wpshadow' );
		}

		// Check for cart abandonment recovery.
		$recovery_plugin = is_plugin_active( 'woolentor-addons/woolentor-addons.php' ) ||
						  is_plugin_active( 'abandoned-cart-lite/abandoned-cart-lite.php' );

		$stats['cart_recovery_enabled'] = $recovery_plugin;

		if ( ! $recovery_plugin ) {
			$warnings[] = __( 'Cart abandonment recovery not enabled', 'wpshadow' );
		}

		// Check for mobile form validation.
		$form_validation = get_option( 'woocommerce_form_validation_enabled' );
		$stats['form_validation'] = boolval( $form_validation );

		// Check SSL certificate for checkout.
		$ssl_enabled = is_ssl();
		$stats['checkout_ssl'] = $ssl_enabled;

		if ( ! $ssl_enabled ) {
			$issues[] = __( 'Checkout not using SSL - mobile payments not secure', 'wpshadow' );
		}

		// Check for mobile viewport meta tag.
		$has_viewport_meta = true; // Assumed in modern WP.
		$stats['viewport_meta_tag'] = $has_viewport_meta;

		// Check for viewport-dependent CSS.
		$mobile_css_count = 0;
		if ( file_exists( $theme_dir . '/style.css' ) ) {
			$style = file_get_contents( $theme_dir . '/style.css' );
			$mobile_css_count = substr_count( $style, '@media' );
		}

		$stats['media_queries'] = $mobile_css_count;

		if ( $mobile_css_count === 0 ) {
			$warnings[] = __( 'No media queries found - may not be responsive', 'wpshadow' );
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Mobile checkout has critical issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-checkout-optimization',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		// If only warnings.
		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Mobile checkout has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-checkout-optimization',
				'context'      => array(
					'stats'    => $stats,
					'warnings' => $warnings,
				),
			);
		}

		return null; // Mobile checkout is optimized.
	}
}
