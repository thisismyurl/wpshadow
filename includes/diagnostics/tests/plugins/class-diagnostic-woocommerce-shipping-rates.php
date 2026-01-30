<?php
/**
 * Woocommerce Shipping Rates Diagnostic
 *
 * Woocommerce Shipping Rates issues detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.661.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woocommerce Shipping Rates Diagnostic Class
 *
 * @since 1.661.0000
 */
class Diagnostic_WoocommerceShippingRates extends Diagnostic_Base {

	protected static $slug = 'woocommerce-shipping-rates';
	protected static $title = 'Woocommerce Shipping Rates';
	protected static $description = 'Woocommerce Shipping Rates issues detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Verify shipping is enabled
		$shipping_enabled = get_option( 'woocommerce_calc_shipping', 'no' );
		if ( 'yes' !== $shipping_enabled ) {
			$issues[] = 'Shipping calculations disabled';
		}
		
		// Check 2: Check for shipping zones
		$zones = function_exists( 'wc_get_shipping_zones' ) ? wc_get_shipping_zones() : array();
		if ( empty( $zones ) ) {
			$issues[] = 'No shipping zones configured';
		}
		
		// Check 3: Verify at least one shipping method enabled
		$enabled_methods = 0;
		if ( ! empty( $zones ) ) {
			foreach ( $zones as $zone ) {
				if ( isset( $zone['shipping_methods'] ) ) {
					foreach ( $zone['shipping_methods'] as $method ) {
						if ( ! empty( $method->enabled ) && 'yes' === $method->enabled ) {
							$enabled_methods++;
						}
					}
				}
			}
		}
		if ( $enabled_methods === 0 ) {
			$issues[] = 'No enabled shipping methods found';
		}
		
		// Check 4: Check for free shipping threshold
		$free_shipping_min = get_option( 'woocommerce_free_shipping_min_amount', '' );
		if ( '' !== $free_shipping_min && (float) $free_shipping_min <= 0 ) {
			$issues[] = 'Free shipping minimum amount not configured';
		}
		
		// Check 5: Verify shipping tax settings
		$shipping_tax = get_option( 'woocommerce_shipping_tax_class', '' );
		if ( empty( $shipping_tax ) ) {
			$issues[] = 'Shipping tax class not configured';
		}
		
		// Check 6: Check for shipping destination restrictions
		$ship_to = get_option( 'woocommerce_ship_to_countries', 'all' );
		if ( 'disabled' === $ship_to ) {
			$issues[] = 'Shipping destinations disabled';
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 40;
			$threat_multiplier = 6;
			$max_threat = 70;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d WooCommerce shipping rate issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/woocommerce-shipping-rates',
			);
		}
		
		return null;
	}
}
