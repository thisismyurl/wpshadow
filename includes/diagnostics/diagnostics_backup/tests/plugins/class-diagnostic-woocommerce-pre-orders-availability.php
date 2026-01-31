<?php
/**
 * Woocommerce Pre Orders Availability Diagnostic
 *
 * Woocommerce Pre Orders Availability issues detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.669.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woocommerce Pre Orders Availability Diagnostic Class
 *
 * @since 1.669.0000
 */
class Diagnostic_WoocommercePreOrdersAvailability extends Diagnostic_Base {

	protected static $slug = 'woocommerce-pre-orders-availability';
	protected static $title = 'Woocommerce Pre Orders Availability';
	protected static $description = 'Woocommerce Pre Orders Availability issues detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null;
		}
		
		// Check for Pre-Orders extension
		if ( ! class_exists( 'WC_Pre_Orders' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Count pre-order products
		$preorder_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta} 
				WHERE meta_key = %s AND meta_value = %s",
				'_wc_pre_orders_enabled',
				'yes'
			)
		);
		
		if ( $preorder_count === 0 ) {
			return null;
		}
		
		// Check 2: Pre-orders without release dates
		$missing_dates = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT p1.post_id) 
				FROM {$wpdb->postmeta} p1 
				LEFT JOIN {$wpdb->postmeta} p2 ON p1.post_id = p2.post_id AND p2.meta_key = %s
				WHERE p1.meta_key = %s AND p1.meta_value = %s 
				AND (p2.meta_value IS NULL OR p2.meta_value = '')",
				'_wc_pre_orders_availability_datetime',
				'_wc_pre_orders_enabled',
				'yes'
			)
		);
		
		if ( $missing_dates > 0 ) {
			$issues[] = sprintf( __( '%d pre-orders without release dates', 'wpshadow' ), $missing_dates );
		}
		
		// Check 3: Email notifications
		$email_enabled = get_option( 'woocommerce_pre_orders_availability_email_enabled', 'yes' );
		if ( 'no' === $email_enabled ) {
			$issues[] = __( 'Availability emails disabled (customer confusion)', 'wpshadow' );
		}
		
		// Check 4: Payment capture
		$payment_capture = get_option( 'woocommerce_pre_orders_payment_capture', 'upon_release' );
		if ( 'upfront' === $payment_capture ) {
			$issues[] = __( 'Upfront payment (refund liability)', 'wpshadow' );
		}
		
		// Check 5: Stock management
		$manage_stock = get_option( 'woocommerce_pre_orders_manage_stock', 'yes' );
		if ( 'no' === $manage_stock ) {
			$issues[] = __( 'Stock management disabled (overselling risk)', 'wpshadow' );
		}
		
		// Check 6: Cancellation policy
		$allow_cancel = get_option( 'woocommerce_pre_orders_allow_cancel', 'no' );
		if ( 'no' === $allow_cancel ) {
			$issues[] = __( 'Cancellations not allowed (customer dissatisfaction)', 'wpshadow' );
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
				/* translators: %s: list of pre-order issues */
				__( 'WooCommerce Pre-Orders has %d availability issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/woocommerce-pre-orders-availability',
		);
	}
}
