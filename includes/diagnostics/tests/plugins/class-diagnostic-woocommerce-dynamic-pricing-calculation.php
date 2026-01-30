<?php
/**
 * Woocommerce Dynamic Pricing Calculation Diagnostic
 *
 * Woocommerce Dynamic Pricing Calculation issues detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.657.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woocommerce Dynamic Pricing Calculation Diagnostic Class
 *
 * @since 1.657.0000
 */
class Diagnostic_WoocommerceDynamicPricingCalculation extends Diagnostic_Base {

	protected static $slug = 'woocommerce-dynamic-pricing-calculation';
	protected static $title = 'Woocommerce Dynamic Pricing Calculation';
	protected static $description = 'Woocommerce Dynamic Pricing Calculation issues detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null;
		}
		
		// Check for Dynamic Pricing plugin
		if ( ! class_exists( 'WC_Dynamic_Pricing' ) && ! defined( 'WC_DYNAMIC_PRICING_VERSION' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Count pricing rules
		$pricing_rules = get_option( 'woocommerce_dynamic_pricing_rules', array() );
		if ( ! empty( $pricing_rules ) && count( $pricing_rules ) > 20 ) {
			$issues[] = sprintf( __( '%d pricing rules (cart calculation slowdown)', 'wpshadow' ), count( $pricing_rules ) );
		}
		
		// Check 2: Complex conditions
		$complex_rules = 0;
		foreach ( $pricing_rules as $rule ) {
			if ( isset( $rule['conditions'] ) && count( $rule['conditions'] ) > 3 ) {
				$complex_rules++;
			}
		}
		if ( $complex_rules > 5 ) {
			$issues[] = sprintf( __( '%d complex rules (3+ conditions each)', 'wpshadow' ), $complex_rules );
		}
		
		// Check 3: Cart calculation caching
		$enable_cache = get_option( 'woocommerce_dynamic_pricing_enable_cache', 'no' );
		if ( 'no' === $enable_cache ) {
			$issues[] = __( 'Price calculation caching disabled (recalculates every page load)', 'wpshadow' );
		}
		
		// Check 4: Role-based pricing
		$role_rules = 0;
		foreach ( $pricing_rules as $rule ) {
			if ( isset( $rule['type'] ) && 'role' === $rule['type'] ) {
				$role_rules++;
			}
		}
		if ( $role_rules > 0 ) {
			$issues[] = sprintf( __( '%d role-based rules (user query overhead)', 'wpshadow' ), $role_rules );
		}
		
		// Check 5: Bulk pricing with large catalogs
		$product_count = wp_count_posts( 'product' );
		if ( $product_count->publish > 1000 && count( $pricing_rules ) > 10 ) {
			$issues[] = sprintf( __( '%d products with %d rules (scale issue)', 'wpshadow' ), $product_count->publish, count( $pricing_rules ) );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of dynamic pricing issues */
				__( 'WooCommerce dynamic pricing has %d calculation issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/woocommerce-dynamic-pricing-calculation',
		);
	}
}
