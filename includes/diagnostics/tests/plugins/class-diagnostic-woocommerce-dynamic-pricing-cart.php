<?php
/**
 * Woocommerce Dynamic Pricing Cart Diagnostic
 *
 * Woocommerce Dynamic Pricing Cart issues detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.658.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woocommerce Dynamic Pricing Cart Diagnostic Class
 *
 * @since 1.658.0000
 */
class Diagnostic_WoocommerceDynamicPricingCart extends Diagnostic_Base {

	protected static $slug = 'woocommerce-dynamic-pricing-cart';
	protected static $title = 'Woocommerce Dynamic Pricing Cart';
	protected static $description = 'Woocommerce Dynamic Pricing Cart issues detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null;
		}

		// Check for Dynamic Pricing plugin
		$has_dynamic_pricing = class_exists( 'WC_Dynamic_Pricing' ) ||
		                       function_exists( 'wc_dynamic_pricing_init' );

		if ( ! $has_dynamic_pricing ) {
			return null;
		}

		global $wpdb;
		$issues = array();

		// Check 1: Pricing rules count
		$rules_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_pricing_rules'"
		);
		if ( $rules_count > 50 ) {
			$issues[] = sprintf( __( '%d pricing rules (cart calculation slow)', 'wpshadow' ), $rules_count );
		}

		// Check 2: Rule caching
		$caching = get_option( 'wc_dynamic_pricing_cache', 'no' );
		if ( 'no' === $caching ) {
			$issues[] = __( 'Pricing rules not cached (repeated calculations)', 'wpshadow' );
		}

		// Check 3: Complex conditions
		$complex_rules = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta}
			 WHERE meta_key = '_pricing_rules'
			 AND meta_value LIKE '%conditions%'"
		);
		if ( $complex_rules > 20 ) {
			$issues[] = sprintf( __( '%d conditional rules (processing overhead)', 'wpshadow' ), $complex_rules );
		}

		// Check 4: Stacked discounts
		$stacking = get_option( 'wc_dynamic_pricing_stacking', 'yes' );
		if ( 'yes' === $stacking ) {
			$issues[] = __( 'Discount stacking enabled (calculation complexity)', 'wpshadow' );
		}

		// Check 5: AJAX cart updates
		$ajax_updates = get_option( 'wc_dynamic_pricing_ajax', 'no' );
		if ( 'no' === $ajax_updates ) {
			$issues[] = __( 'No AJAX updates (full page reloads)', 'wpshadow' );
		}

		// Check 6: Price display
		$display = get_option( 'wc_dynamic_pricing_display', 'original' );
		if ( 'original' === $display ) {
			$issues[] = __( 'Showing original price (discount not obvious)', 'wpshadow' );
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
				__( 'WooCommerce Dynamic Pricing has %d cart issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/woocommerce-dynamic-pricing-cart',
		);
	}
}
