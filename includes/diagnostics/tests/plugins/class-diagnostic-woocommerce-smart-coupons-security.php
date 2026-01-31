<?php
/**
 * Woocommerce Smart Coupons Security Diagnostic
 *
 * Woocommerce Smart Coupons Security issues detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.680.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woocommerce Smart Coupons Security Diagnostic Class
 *
 * @since 1.680.0000
 */
class Diagnostic_WoocommerceSmartCouponsSecurity extends Diagnostic_Base {

	protected static $slug = 'woocommerce-smart-coupons-security';
	protected static $title = 'Woocommerce Smart Coupons Security';
	protected static $description = 'Woocommerce Smart Coupons Security issues detected';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'WooCommerce' ) || ! class_exists( 'WC_Smart_Coupons' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Verify coupon usage limits are configured
		global $wpdb;
		$coupons_without_limits = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} p
				LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = %s
				WHERE p.post_type = %s 
				AND p.post_status = %s
				AND (pm.meta_value IS NULL OR pm.meta_value = '' OR pm.meta_value = '0')",
				'usage_limit',
				'shop_coupon',
				'publish'
			)
		);
		
		if ( $coupons_without_limits > 5 ) {
			$issues[] = 'coupons_without_usage_limits';
		}
		
		// Check 2: Check for coupons without expiry dates
		$coupons_no_expiry = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} p
				LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = %s
				WHERE p.post_type = %s 
				AND p.post_status = %s
				AND (pm.meta_value IS NULL OR pm.meta_value = '')",
				'expiry_date',
				'shop_coupon',
				'publish'
			)
		);
		
		if ( $coupons_no_expiry > 5 ) {
			$issues[] = 'coupons_without_expiry';
		}
		
		// Check 3: Check for high-value coupons without restrictions
		$high_value_coupons = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT p.ID, pm.meta_value as amount
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = %s
				WHERE p.post_type = %s 
				AND p.post_status = %s
				AND CAST(pm.meta_value AS DECIMAL(10,2)) > %f",
				'coupon_amount',
				'shop_coupon',
				'publish',
				50.00
			)
		);
		
		if ( ! empty( $high_value_coupons ) ) {
			$issues[] = 'high_value_coupons_exist';
		}
		
		// Check 4: Verify coupon codes are not predictable
		$recent_coupons = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT post_title FROM {$wpdb->posts}
				WHERE post_type = %s 
				AND post_status = %s
				ORDER BY post_date DESC LIMIT 10",
				'shop_coupon',
				'publish'
			)
		);
		
		foreach ( $recent_coupons as $coupon ) {
			// Check for sequential or predictable patterns
			if ( preg_match( '/^(test|discount|sale|promo)[0-9]{1,3}$/i', $coupon->post_title ) ) {
				$issues[] = 'predictable_coupon_codes';
				break;
			}
		}
		
		// Check 5: Verify store credit security
		$allow_store_credit_purchase = get_option( 'wc_sc_allow_store_credit_purchase', 'no' );
		if ( 'yes' === $allow_store_credit_purchase ) {
			$issues[] = 'store_credit_purchase_enabled';
		}
		
		// Check 6: Check if gift certificates require authentication
		$gift_cert_auth = get_option( 'wc_sc_gift_certificate_require_auth', 'yes' );
		if ( 'no' === $gift_cert_auth ) {
			$issues[] = 'gift_certificates_no_auth_required';
		}
		
		if ( ! empty( $issues ) ) {
			$issues = array_unique( $issues );
			$description = sprintf(
				/* translators: %s: list of coupon security issues */
				__( 'WooCommerce Smart Coupons has security issues: %s. Insecure coupon configurations can lead to coupon fraud, revenue loss, and unauthorized discounts.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);
			
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => 70,
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/woocommerce-smart-coupons-security',
			);
		}
		
		return null;
	}
}
