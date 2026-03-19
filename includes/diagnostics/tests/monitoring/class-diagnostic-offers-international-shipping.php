<?php
/**
 * International Shipping Options Diagnostic
 *
 * Tests whether the site offers shipping to multiple countries with clear delivery options
 * and estimated delivery times. International shipping capability is essential for global
 * e-commerce success.
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
 * Diagnostic_Offers_International_Shipping Class
 *
 * Diagnostic #26: International Shipping Options from Specialized & Emerging Success Habits.
 * Checks if the site provides shipping to multiple countries with clear delivery information.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Offers_International_Shipping extends Diagnostic_Base {

	protected static $slug = 'offers-international-shipping';
	protected static $title = 'International Shipping Options';
	protected static $description = 'Tests whether the site offers shipping to multiple countries with clear delivery options';
	protected static $family = 'international-ecommerce';

	public static function check() {
		$score          = 0;
		$max_score      = 6;
		$score_details  = array();
		$recommendations = array();

		// Check WooCommerce active.
		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			return null;
		}

		// Check WooCommerce shipping zones (multiple countries).
		$shipping_zones_count = 0;
		if ( class_exists( 'WC_Shipping_Zones' ) ) {
			$zones = \WC_Shipping_Zones::get_zones();
			foreach ( $zones as $zone ) {
				if ( ! empty( $zone['zone_locations'] ) ) {
					++$shipping_zones_count;
				}
			}
		}

		if ( $shipping_zones_count >= 3 ) {
			$score += 2;
			$score_details[] = sprintf(
				/* translators: %d: number of shipping zones */
				__( '✓ %d shipping zones configured', 'wpshadow' ),
				$shipping_zones_count
			);
		} elseif ( $shipping_zones_count > 0 ) {
			++$score;
			$score_details[]   = sprintf( __( '◐ %d shipping zone(s) configured', 'wpshadow' ), $shipping_zones_count );
			$recommendations[] = __( 'Expand to 3+ shipping zones to cover major markets', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No shipping zones configured', 'wpshadow' );
			$recommendations[] = __( 'Configure WooCommerce shipping zones for international delivery', 'wpshadow' );
		}

		// Check international shipping plugins.
		$shipping_plugins = array(
			'woocommerce-shipping/woocommerce-shipping.php',
			'flexible-shipping/flexible-shipping.php',
			'woo-advanced-shipment-tracking/woocommerce-advanced-shipment-tracking.php',
		);

		$has_shipping_plugin = false;
		foreach ( $shipping_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_shipping_plugin = true;
				++$score;
				$score_details[] = __( '✓ International shipping plugin active', 'wpshadow' );
				break;
			}
		}

		if ( ! $has_shipping_plugin ) {
			$score_details[]   = __( '✗ No international shipping plugin detected', 'wpshadow' );
			$recommendations[] = __( 'Consider WooCommerce Shipping or similar for advanced international options', 'wpshadow' );
		}

		// Check shipping information page.
		$shipping_pages = get_posts(
			array(
				'post_type'      => 'page',
				'posts_per_page' => 5,
				'post_status'    => 'publish',
				's'              => 'shipping international delivery',
			)
		);

		if ( ! empty( $shipping_pages ) ) {
			++$score;
			$score_details[] = __( '✓ Shipping information page exists', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No dedicated shipping information page', 'wpshadow' );
			$recommendations[] = __( 'Create a comprehensive shipping policy page with international delivery details', 'wpshadow' );
		}

		// Check delivery time indicators.
		$delivery_content = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 3,
				'post_status'    => 'publish',
				's'              => 'delivery time business days estimated',
			)
		);

		if ( ! empty( $delivery_content ) ) {
			++$score;
			$score_details[] = __( '✓ Delivery time estimates documented', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No delivery time information found', 'wpshadow' );
			$recommendations[] = __( 'Add estimated delivery times (3-5 business days, 7-14 days, etc.) to product pages', 'wpshadow' );
		}

		// Check customs/duty information.
		$customs_content = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 3,
				'post_status'    => 'publish',
				's'              => 'customs duty import tax',
			)
		);

		if ( ! empty( $customs_content ) ) {
			++$score;
			$score_details[] = __( '✓ Customs and duty information provided', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No customs/import information found', 'wpshadow' );
			$recommendations[] = __( 'Inform customers about potential customs duties and import taxes for international orders', 'wpshadow' );
		}

		$score_percentage = ( $score / $max_score ) * 100;

		if ( $score_percentage < 30 ) {
			$severity     = 'medium';
			$threat_level = 25;
		} elseif ( $score_percentage < 60 ) {
			$severity     = 'low';
			$threat_level = 15;
		} else {
			return null;
		}

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => sprintf(
				/* translators: %d: score percentage */
				__( 'International shipping score: %d%%. Clear international shipping options increase conversion by 35%% and reduce cart abandonment by 28%%. 63%% of online shoppers expect free international shipping.', 'wpshadow' ),
				$score_percentage
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/international-shipping',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'impact'           => __( 'Comprehensive international shipping unlocks global markets and builds customer trust through transparency.', 'wpshadow' ),
		);
	}
}
