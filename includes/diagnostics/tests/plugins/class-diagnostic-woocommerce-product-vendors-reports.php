<?php
/**
 * Woocommerce Product Vendors Reports Diagnostic
 *
 * Woocommerce Product Vendors Reports issues detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.654.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woocommerce Product Vendors Reports Diagnostic Class
 *
 * @since 1.654.0000
 */
class Diagnostic_WoocommerceProductVendorsReports extends Diagnostic_Base {

	protected static $slug = 'woocommerce-product-vendors-reports';
	protected static $title = 'Woocommerce Product Vendors Reports';
	protected static $description = 'Woocommerce Product Vendors Reports issues detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'WooCommerce' ) || ! class_exists( 'WC_Product_Vendors' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Vendor report access control
		$report_capability = get_option( 'wcpv_report_capability', 'manage_woocommerce' );
		if ( 'manage_product' === $report_capability ) {
			$issues[] = __( 'Low report capability (vendors see all data)', 'wpshadow' );
		}
		
		// Check 2: Commission calculation
		$commission_type = get_option( 'wcpv_commission_type', 'percentage' );
		$commission_value = get_option( 'wcpv_commission_value', 0 );
		
		if ( $commission_value === 0 ) {
			$issues[] = __( 'No default commission (unpaid vendors)', 'wpshadow' );
		}
		
		// Check 3: Report caching
		$cache_reports = get_option( 'wcpv_cache_reports', 'no' );
		if ( 'no' === $cache_reports ) {
			$issues[] = __( 'Reports not cached (slow dashboards)', 'wpshadow' );
		}
		
		// Check 4: Data isolation
		$isolate_data = get_option( 'wcpv_isolate_vendor_data', 'yes' );
		if ( 'no' === $isolate_data ) {
			$issues[] = __( 'Data not isolated (vendor data leaks)', 'wpshadow' );
		}
		
		// Check 5: Export sanitization
		$sanitize_exports = get_option( 'wcpv_sanitize_exports', 'no' );
		if ( 'no' === $sanitize_exports ) {
			$issues[] = __( 'Exports not sanitized (XSS in CSV)', 'wpshadow' );
		}
		
		// Check 6: Payout tracking
		$track_payouts = get_option( 'wcpv_track_payouts', 'no' );
		if ( 'no' === $track_payouts ) {
			$issues[] = __( 'No payout tracking (accounting issues)', 'wpshadow' );
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
				/* translators: %s: list of product vendors reporting issues */
				__( 'WooCommerce Product Vendors reports have %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/woocommerce-product-vendors-reports',
		);
	}
}
