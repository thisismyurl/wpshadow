<?php
/**
 * Woocommerce Deposits Payment Diagnostic
 *
 * Woocommerce Deposits Payment issues detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.683.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woocommerce Deposits Payment Diagnostic Class
 *
 * @since 1.683.0000
 */
class Diagnostic_WoocommerceDepositsPayment extends Diagnostic_Base {

	protected static $slug = 'woocommerce-deposits-payment';
	protected static $title = 'Woocommerce Deposits Payment';
	protected static $description = 'Woocommerce Deposits Payment issues detected';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'WooCommerce' ) || ( ! class_exists( 'WC_Deposits' ) && ! defined( 'WC_DEPOSITS_VERSION' ) ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: SSL requirement for deposits
		if ( ! is_ssl() ) {
			$issues[] = 'deposits processed without SSL (payment data insecure)';
		}
		
		// Check 2: Deposit percentage validation
		$deposit_percent = get_option( 'wc_deposits_default_amount', 0 );
		if ( $deposit_percent < 10 || $deposit_percent > 90 ) {
			$issues[] = "unusual deposit percentage ({$deposit_percent}%, recommend 10-90%)";
		}
		
		// Check 3: Payment gateway compatibility
		$deposit_gateways = get_option( 'wc_deposits_enabled_gateways', array() );
		if ( empty( $deposit_gateways ) ) {
			$issues[] = 'no payment gateways configured for deposits';
		}
		
		// Check 4: Partial payment tracking
		global $wpdb;
		$incomplete_deposits = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}wc_deposits 
				 WHERE status = %s AND created < %s",
				'partial',
				date( 'Y-m-d', strtotime( '-30 days' ) )
			)
		);
		if ( $incomplete_deposits > 10 ) {
			$issues[] = "{$incomplete_deposits} incomplete deposits over 30 days old";
		}
		
		// Check 5: Deposit reminder emails
		$reminder_enabled = get_option( 'wc_deposits_reminder_enabled', '0' );
		if ( '0' === $reminder_enabled ) {
			$issues[] = 'payment reminders disabled (customers may forget remaining balance)';
		}
		
		// Check 6: Deposit expiration policy
		$expiration_days = get_option( 'wc_deposits_expiration_days', 0 );
		if ( empty( $expiration_days ) ) {
			$issues[] = 'no deposit expiration policy (refunds may be indefinite)';
		} elseif ( $expiration_days > 365 ) {
			$issues[] = "very long deposit expiration ({$expiration_days} days, consider shorter period)";
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 95, 70 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'WooCommerce deposits payment security issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/woocommerce-deposits-payment',
			);
		}
		
		return null;
	}
}
