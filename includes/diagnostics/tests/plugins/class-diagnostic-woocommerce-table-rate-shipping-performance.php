<?php
/**
 * Woocommerce Table Rate Shipping Performance Diagnostic
 *
 * Woocommerce Table Rate Shipping Performance issues detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.688.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woocommerce Table Rate Shipping Performance Diagnostic Class
 *
 * @since 1.688.0000
 */
class Diagnostic_WoocommerceTableRateShippingPerformance extends Diagnostic_Base {

	protected static $slug = 'woocommerce-table-rate-shipping-performance';
	protected static $title = 'Woocommerce Table Rate Shipping Performance';
	protected static $description = 'Woocommerce Table Rate Shipping Performance issues detected';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null;
		}
		
		if ( ! class_exists( 'WC_Shipping_Table_Rate' ) && ! defined( 'WC_TABLE_RATE_SHIPPING_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Verify rate caching
		$rate_cache = get_option( 'woocommerce_table_rate_cache', 0 );
		if ( ! $rate_cache ) {
			$issues[] = 'Table rate cache not enabled';
		}
		
		// Check 2: Check for excessive rules
		$rule_count = get_option( 'woocommerce_table_rate_rule_count', 0 );
		if ( $rule_count > 200 ) {
			$issues[] = 'Table rate rules exceed 200 (performance impact)';
		}
		
		// Check 3: Verify condition grouping
		$grouping = get_option( 'woocommerce_table_rate_grouping', 0 );
		if ( ! $grouping ) {
			$issues[] = 'Rule grouping not enabled';
		}
		
		// Check 4: Check for debug logging
		$debug = get_option( 'woocommerce_table_rate_debug', 0 );
		if ( $debug ) {
			$issues[] = 'Debug logging enabled (performance impact)';
		}
		
		// Check 5: Verify shipping zones optimization
		$zone_count = function_exists( 'wc_get_shipping_zones' ) ? count( wc_get_shipping_zones() ) : 0;
		if ( $zone_count > 20 ) {
			$issues[] = 'Too many shipping zones configured (over 20)';
		}
		
		// Check 6: Check for per-product rates
		$per_product = get_option( 'woocommerce_table_rate_per_product', 0 );
		if ( $per_product ) {
			$issues[] = 'Per-product table rates enabled (can slow checkout)';
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 45;
			$threat_multiplier = 6;
			$max_threat = 75;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d table rate shipping performance issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/woocommerce-table-rate-shipping-performance',
			);
		}
		
		return null;
	}
}
