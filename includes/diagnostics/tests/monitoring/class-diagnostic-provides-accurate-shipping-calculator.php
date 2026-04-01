<?php
/**
 * Shipping Calculator Accurate Diagnostic
 *
 * Tests whether the site provides real-time, accurate shipping cost calculations to
 * prevent cart abandonment. Unexpected shipping costs are the #1 cart abandonment reason.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Provides_Accurate_Shipping_Calculator Class
 *
 * Diagnostic #8: Shipping Calculator Accurate from Specialized & Emerging Success Habits.
 * Checks if the site provides real-time shipping calculations.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Provides_Accurate_Shipping_Calculator extends Diagnostic_Base {

	protected static $slug = 'provides-accurate-shipping-calculator';
	protected static $title = 'Shipping Calculator Accurate';
	protected static $description = 'Tests whether the site provides real-time shipping cost calculations';
	protected static $family = 'ecommerce-optimization';

	public static function check() {
		$score          = 0;
		$max_score      = 5;
		$score_details  = array();
		$recommendations = array();

		// Check WooCommerce active.
		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			return null;
		}

		// Check shipping calculator enabled.
		$calc_enabled = get_option( 'woocommerce_enable_shipping_calc', 'yes' );
		if ( 'yes' === $calc_enabled ) {
			++$score;
			$score_details[] = __( '✓ Shipping calculator enabled', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ Shipping calculator disabled', 'wpshadow' );
			$recommendations[] = __( 'Enable WooCommerce shipping calculator on cart page', 'wpshadow' );
		}

		// Check real-time shipping plugins.
		$shipping_plugins = array(
			'woocommerce-shipping/woocommerce-shipping.php',
			'woocommerce-shipping-usps/woocommerce-shipping-usps.php',
			'flexible-shipping/flexible-shipping.php',
		);

		$has_realtime_shipping = false;
		foreach ( $shipping_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_realtime_shipping = true;
				$score += 2;
				$score_details[] = __( '✓ Real-time shipping rates plugin active', 'wpshadow' );
				break;
			}
		}

		if ( ! $has_realtime_shipping ) {
			$score_details[]   = __( '✗ No real-time shipping integration', 'wpshadow' );
			$recommendations[] = __( 'Integrate with shipping carriers (USPS, UPS, FedEx) for live rate quotes', 'wpshadow' );
		}

		// Check free shipping threshold.
		$free_shipping = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 3,
				'post_status'    => 'publish',
				's'              => 'free shipping over minimum',
			)
		);

		if ( ! empty( $free_shipping ) ) {
			++$score;
			$score_details[] = __( '✓ Free shipping threshold communicated', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No free shipping threshold', 'wpshadow' );
			$recommendations[] = __( 'Offer free shipping on orders over a specific amount to increase AOV', 'wpshadow' );
		}

		// Check delivery estimates.
		$delivery_estimates = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 5,
				'post_status'    => 'publish',
				's'              => 'delivery estimate arrives ships',
			)
		);

		if ( ! empty( $delivery_estimates ) ) {
			++$score;
			$score_details[] = __( '✓ Delivery timeframes provided', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No delivery time estimates', 'wpshadow' );
			$recommendations[] = __( 'Display expected delivery dates on product and cart pages', 'wpshadow' );
		}

		$score_percentage = ( $score / $max_score ) * 100;

		if ( $score_percentage < 30 ) {
			$severity     = 'medium';
			$threat_level = 30;
		} elseif ( $score_percentage < 60 ) {
			$severity     = 'low';
			$threat_level = 20;
		} else {
			return null;
		}

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => sprintf(
				/* translators: %d: score percentage */
				__( 'Shipping calculator score: %d%%. Unexpected shipping costs cause 48%% of cart abandonment. Real-time calculations reduce surprises and increase checkout completion by 37%%. Free shipping thresholds boost AOV by 30%%.', 'wpshadow' ),
				$score_percentage
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/shipping-calculator?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'impact'           => __( 'Accurate shipping costs set proper expectations and eliminate sticker shock at checkout.', 'wpshadow' ),
		);
	}
}
