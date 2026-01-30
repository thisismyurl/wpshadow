<?php
/**
 * Woocommerce Smart Coupons Store Credit Diagnostic
 *
 * Woocommerce Smart Coupons Store Credit issues detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.681.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woocommerce Smart Coupons Store Credit Diagnostic Class
 *
 * @since 1.681.0000
 */
class Diagnostic_WoocommerceSmartCouponsStoreCredit extends Diagnostic_Base {

	protected static $slug = 'woocommerce-smart-coupons-store-credit';
	protected static $title = 'Woocommerce Smart Coupons Store Credit';
	protected static $description = 'Woocommerce Smart Coupons Store Credit issues detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'WooCommerce' ) || ! class_exists( 'WC_Smart_Coupons' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Store credit enabled
		$credit_enabled = get_option( 'wc_sc_enable_store_credit', 'yes' );
		if ( 'no' === $credit_enabled ) {
			$issues[] = 'store credit feature disabled';
			return null; // No point checking further
		}
		
		// Check 2: Credit expiration policy
		$credit_expiry = get_option( 'wc_sc_store_credit_expiry_days', 0 );
		if ( empty( $credit_expiry ) ) {
			$issues[] = 'store credit never expires (accounting liability)';
		}
		
		// Check 3: Credit issuance limits
		$max_credit = get_option( 'wc_sc_max_store_credit', 0 );
		if ( empty( $max_credit ) ) {
			$issues[] = 'no maximum credit limit (fraud risk)';
		}
		
		// Check 4: Credit notification emails
		$email_enabled = get_option( 'wc_sc_credit_email_enabled', 'yes' );
		if ( 'no' === $email_enabled ) {
			$issues[] = 'credit notification emails disabled';
		}
		
		// Check 5: Credit balance tracking
		$track_balance = get_option( 'wc_sc_track_credit_balance', 'yes' );
		if ( 'no' === $track_balance ) {
			$issues[] = 'credit balance not tracked (reconciliation issues)';
		} else {
			$total_credit = get_option( 'wc_sc_total_credit_issued', 0 );
			if ( $total_credit > 10000 ) {
				$credit_formatted = wc_price( $total_credit );
				$issues[] = "{$credit_formatted} in outstanding store credit";
			}
		}
		
		// Check 6: Refund to credit default
		$refund_to_credit = get_option( 'wc_sc_refund_to_credit_default', 'no' );
		if ( 'yes' === $refund_to_credit ) {
			$issues[] = 'refunds default to store credit (may frustrate customers)';
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 40 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'WooCommerce store credit issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/woocommerce-smart-coupons-store-credit',
			);
		}
		
		return null;
	}
}
