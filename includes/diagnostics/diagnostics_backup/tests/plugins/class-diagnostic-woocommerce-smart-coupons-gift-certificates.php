<?php
/**
 * Woocommerce Smart Coupons Gift Certificates Diagnostic
 *
 * Woocommerce Smart Coupons Gift Certificates issues detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.682.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woocommerce Smart Coupons Gift Certificates Diagnostic Class
 *
 * @since 1.682.0000
 */
class Diagnostic_WoocommerceSmartCouponsGiftCertificates extends Diagnostic_Base {

	protected static $slug = 'woocommerce-smart-coupons-gift-certificates';
	protected static $title = 'Woocommerce Smart Coupons Gift Certificates';
	protected static $description = 'Woocommerce Smart Coupons Gift Certificates issues detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null;
		}

		// Check for Smart Coupons
		$has_smart_coupons = class_exists( 'WC_Smart_Coupons' ) ||
		                     function_exists( 'wc_sc_get_coupon_meta_data' );

		if ( ! $has_smart_coupons ) {
			return null;
		}

		global $wpdb;
		$issues = array();

		// Check 1: Gift certificate security
		$gc_security = get_option( 'wc_sc_gift_certificate_security', 'low' );
		if ( 'low' === $gc_security ) {
			$issues[] = __( 'Low gift certificate security (fraud risk)', 'wpshadow' );
		}

		// Check 2: Unlimited value certificates
		$unlimited = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_coupon_amount' AND meta_value = '0'"
		);
		if ( $unlimited > 0 ) {
			$issues[] = sprintf( __( '%d unlimited value certificates (abuse potential)', 'wpshadow' ), $unlimited );
		}

		// Check 3: Email delivery
		$email_delivery = get_option( 'wc_sc_email_delivery', 'immediate' );
		if ( 'immediate' === $email_delivery ) {
			$issues[] = __( 'Immediate email delivery (payment verification bypass)', 'wpshadow' );
		}

		// Check 4: Expiration policy
		$expiration = get_option( 'wc_sc_gift_certificate_expiration', 'never' );
		if ( 'never' === $expiration ) {
			$issues[] = __( 'Certificates never expire (liability)', 'wpshadow' );
		}

		// Check 5: Duplicate prevention
		$duplicate_check = get_option( 'wc_sc_prevent_duplicates', 'no' );
		if ( 'no' === $duplicate_check ) {
			$issues[] = __( 'No duplicate prevention (multiple redemptions)', 'wpshadow' );
		}

		// Check 6: Audit trail
		$audit_trail = get_option( 'wc_sc_audit_trail', 'no' );
		if ( 'no' === $audit_trail ) {
			$issues[] = __( 'No audit trail (fraud detection impossible)', 'wpshadow' );
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
				__( 'WooCommerce Smart Coupons has %d gift certificate issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/woocommerce-smart-coupons-gift-certificates',
		);
	}
}
