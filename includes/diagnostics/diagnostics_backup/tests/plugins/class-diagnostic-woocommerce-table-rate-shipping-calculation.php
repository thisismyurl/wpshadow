<?php
/**
 * Woocommerce Table Rate Shipping Calculation Diagnostic
 *
 * Woocommerce Table Rate Shipping Calculation issues detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.686.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woocommerce Table Rate Shipping Calculation Diagnostic Class
 *
 * @since 1.686.0000
 */
class Diagnostic_WoocommerceTableRateShippingCalculation extends Diagnostic_Base {

	protected static $slug = 'woocommerce-table-rate-shipping-calculation';
	protected static $title = 'Woocommerce Table Rate Shipping Calculation';
	protected static $description = 'Woocommerce Table Rate Shipping Calculation issues detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify shipping calculation caching
		$calculation_cache = get_option( 'wc_table_rate_calculation_cache', false );
		if ( ! $calculation_cache ) {
			$issues[] = __( 'Shipping calculation caching not enabled', 'wpshadow' );
		}

		// Check 2: Check table size limits
		$table_size = get_option( 'wc_table_rate_table_size', 0 );
		if ( $table_size > 1000 ) {
			$issues[] = __( 'Shipping rate table too large', 'wpshadow' );
		}

		// Check 3: Verify rate complexity limits
		$rate_complexity = get_option( 'wc_table_rate_complexity_limit', false );
		if ( ! $rate_complexity ) {
			$issues[] = __( 'Rate complexity limits not configured', 'wpshadow' );
		}

		// Check 4: Check calculation timeout configuration
		$calculation_timeout = get_option( 'wc_table_rate_timeout', 0 );
		if ( $calculation_timeout === 0 || $calculation_timeout > 30 ) {
			$issues[] = __( 'Calculation timeout not optimally configured', 'wpshadow' );
		}

		// Check 5: Verify cache invalidation strategy
		$cache_invalidation = get_option( 'wc_table_rate_cache_invalidation', '' );
		if ( empty( $cache_invalidation ) ) {
			$issues[] = __( 'Cache invalidation strategy not configured', 'wpshadow' );
		}

		// Check 6: Check calculation logging for debugging
		$calculation_logging = get_option( 'wc_table_rate_calculation_logging', false );
		if ( $calculation_logging ) {
			$issues[] = __( 'Calculation logging enabled (performance impact)', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 80, 50 + ( count( $issues ) * 5 ) );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Comma-separated list of issues */
					__( 'WooCommerce table rate shipping calculation issues detected: %s', 'wpshadow' ),
					implode( ', ', $issues )
				),
				'severity'     => 'medium',
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/woocommerce-table-rate-shipping-calculation',
			);
		}

		return null;
	}
}
