<?php
/**
 * Checkout Flow Optimization Diagnostic
 *
 * Tests if purchase flow is streamlined and optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1516
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Checkout Optimization Diagnostic Class
 *
 * Evaluates whether the checkout/purchase flow is optimized for conversion.
 * Checks for eCommerce platforms, optimization plugins, payment gateways, and UX features.
 *
 * @since 1.6035.1516
 */
class Diagnostic_Checkout_Optimization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'optimizes_checkout_flow';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Checkout Flow Optimization';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if purchase flow is streamlined and optimized';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'conversion';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.1516
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$stats         = array();
		$issues        = array();
		$warnings      = array();
		$score         = 0;
		$total_points  = 0;
		$earned_points = 0;

		// Check for eCommerce platform.
		$total_points      += 20;
		$ecommerce_plugins = array(
			'woocommerce/woocommerce.php'       => 'WooCommerce',
			'easy-digital-downloads/easy-digital-downloads.php' => 'Easy Digital Downloads',
			'wp-ecommerce/wp-shopping-cart.php' => 'WP eCommerce',
			'cart66-lite/cart66-lite.php'       => 'Cart66',
			'shopp/shopp.php'                   => 'Shopp',
		);

		$active_ecommerce = null;
		foreach ( $ecommerce_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_ecommerce = $name;
				$earned_points   += 20;
				break;
			}
		}

		$stats['ecommerce_platform'] = $active_ecommerce;

		if ( ! $active_ecommerce ) {
			$issues[] = __( 'No eCommerce platform detected', 'wpshadow' );
			// If no eCommerce, most other checks are irrelevant.
			// Return early with low score.
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No eCommerce platform detected. This diagnostic is designed for sites with online stores.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/checkout-optimization',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => array( __( 'This diagnostic requires an eCommerce platform to be meaningful', 'wpshadow' ) ),
				),
			);
		}

		// Check for checkout optimization plugins.
		$total_points += 15;
		$checkout_plugins = array(
			'woocommerce-checkout-manager/woocommerce-checkout-manager.php' => 'Checkout Manager',
			'cartflows/cartflows.php'                                       => 'CartFlows',
			'funnel-builder/funnel-builder.php'                             => 'Funnel Builder',
			'checkout-field-editor-for-woocommerce/checkout-form-designer.php' => 'Checkout Field Editor',
			'yith-woocommerce-checkout-manager/init.php'                    => 'YITH Checkout Manager',
		);

		$active_checkout_plugins = array();
		foreach ( $checkout_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_checkout_plugins[] = $name;
			}
		}

		if ( ! empty( $active_checkout_plugins ) ) {
			$earned_points += 15;
		}

		$stats['checkout_plugins'] = array(
			'found' => count( $active_checkout_plugins ),
			'list'  => $active_checkout_plugins,
		);

		if ( empty( $active_checkout_plugins ) ) {
			$warnings[] = __( 'No checkout optimization plugins detected', 'wpshadow' );
		}

		// Check for one-click/express checkout options.
		$total_points += 15;
		$express_checkout = array(
			'woocommerce-gateway-paypal-express-checkout/woocommerce-gateway-paypal-express-checkout.php' => 'PayPal Express',
			'woocommerce-gateway-stripe/woocommerce-gateway-stripe.php' => 'Stripe (with Apple/Google Pay)',
			'woocommerce-square/woocommerce-square.php' => 'Square',
			'woo-paypal-gateway/woo-paypal-gateway.php' => 'PayPal',
		);

		$active_express_checkout = array();
		foreach ( $express_checkout as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_express_checkout[] = $name;
			}
		}

		if ( ! empty( $active_express_checkout ) ) {
			$earned_points += 15;
		}

		$stats['express_checkout'] = array(
			'found' => count( $active_express_checkout ),
			'list'  => $active_express_checkout,
		);

		// Check for guest checkout capability (WooCommerce specific).
		$total_points += 10;
		if ( 'WooCommerce' === $active_ecommerce ) {
			$guest_checkout_enabled = get_option( 'woocommerce_enable_guest_checkout', 'no' );
			if ( 'yes' === $guest_checkout_enabled ) {
				$earned_points += 10;
				$stats['guest_checkout_enabled'] = true;
			} else {
				$stats['guest_checkout_enabled'] = false;
				$warnings[] = __( 'Guest checkout is disabled (requires account creation)', 'wpshadow' );
			}
		} else {
			$stats['guest_checkout_enabled'] = null;
		}

		// Check for cart abandonment recovery.
		$total_points += 15;
		$abandonment_plugins = array(
			'woo-cart-abandonment-recovery/woo-cart-abandonment-recovery.php' => 'Cart Abandonment Recovery',
			'abandoned-cart-lite-for-woocommerce/woocommerce-ac.php' => 'Abandoned Cart Lite',
			'retainful-next-order-coupons-for-woocommerce/retainful-woocommerce.php' => 'Retainful',
			'cartbounty-abandoned-cart-recovery/cartbounty.php' => 'CartBounty',
		);

		$active_abandonment_plugins = array();
		foreach ( $abandonment_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_abandonment_plugins[] = $name;
			}
		}

		if ( ! empty( $active_abandonment_plugins ) ) {
			$earned_points += 15;
		}

		$stats['abandonment_recovery'] = array(
			'found' => count( $active_abandonment_plugins ),
			'list'  => $active_abandonment_plugins,
		);

		if ( empty( $active_abandonment_plugins ) ) {
			$warnings[] = __( 'No cart abandonment recovery detected', 'wpshadow' );
		}

		// Check for trust badges/security seals.
		$total_points += 10;
		$trust_plugins = array(
			'trust-badges-for-woocommerce/trust-badges.php' => 'Trust Badges',
			'ssl-seal-woocommerce/ssl-seal.php' => 'SSL Seal',
			'trust-symbols-for-woocommerce/trust-symbols.php' => 'Trust Symbols',
		);

		$active_trust_plugins = array();
		foreach ( $trust_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_trust_plugins[] = $name;
			}
		}

		if ( ! empty( $active_trust_plugins ) ) {
			$earned_points += 10;
		}

		$stats['trust_badges'] = array(
			'found' => count( $active_trust_plugins ),
			'list'  => $active_trust_plugins,
		);

		// Check for SSL/HTTPS.
		$total_points += 10;
		if ( is_ssl() ) {
			$earned_points += 10;
			$stats['ssl_enabled'] = true;
		} else {
			$stats['ssl_enabled'] = false;
			$issues[] = __( 'Site is not using HTTPS (critical for checkout security)', 'wpshadow' );
		}

		// Check for analytics/tracking.
		$total_points += 5;
		if ( wp_script_is( 'google-analytics', 'enqueued' ) ||
			 wp_script_is( 'gtag', 'enqueued' ) ||
			 is_plugin_active( 'woocommerce-google-analytics-integration/woocommerce-google-analytics-integration.php' ) ) {
			$earned_points += 5;
			$stats['ecommerce_tracking'] = true;
		} else {
			$stats['ecommerce_tracking'] = false;
			$warnings[] = __( 'No eCommerce analytics tracking detected', 'wpshadow' );
		}

		// Calculate final score.
		if ( $total_points > 0 ) {
			$score = round( ( $earned_points / $total_points ) * 100 );
		}

		$stats['score']         = $score;
		$stats['total_points']  = $total_points;
		$stats['earned_points'] = $earned_points;

		// Determine severity.
		$severity     = 'medium';
		$threat_level = 45;

		if ( $score < 30 ) {
			$severity     = 'high';
			$threat_level = 60;
		} elseif ( $score > 70 ) {
			$severity     = 'low';
			$threat_level = 25;
		}

		// Return finding if checkout optimization is insufficient.
		if ( $score < 50 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: optimization score percentage */
					__( 'Checkout flow optimization score: %d%%. Optimizing the purchase flow can significantly reduce cart abandonment and increase conversions.', 'wpshadow' ),
					$score
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/checkout-optimization',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		return null;
	}
}
