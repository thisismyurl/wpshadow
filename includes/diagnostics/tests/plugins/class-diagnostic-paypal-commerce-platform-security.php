<?php
/**
 * Paypal Commerce Platform Security Diagnostic
 *
 * Paypal Commerce Platform Security vulnerability detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1396.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Paypal Commerce Platform Security Diagnostic Class
 *
 * @since 1.1396.0000
 */
class Diagnostic_PaypalCommercePlatformSecurity extends Diagnostic_Base {

	protected static $slug = 'paypal-commerce-platform-security';
	protected static $title = 'Paypal Commerce Platform Security';
	protected static $description = 'Paypal Commerce Platform Security vulnerability detected';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'WC_Gateway_Paypal' ) && ! class_exists( 'WooCommerce' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: PayPal API credentials configured
		$client_id = get_option( 'woocommerce_ppcp_client_id', '' );
		$secret = get_option( 'woocommerce_ppcp_secret', '' );
		
		if ( empty( $client_id ) || empty( $secret ) ) {
			$issues[] = __( 'PayPal Commerce API credentials not configured', 'wpshadow' );
		}
		
		// Check 2: Webhook signature verification
		$webhook_id = get_option( 'woocommerce_ppcp_webhook_id', '' );
		if ( empty( $webhook_id ) && ! empty( $client_id ) ) {
			$issues[] = __( 'PayPal webhook not configured (missing payment notifications)', 'wpshadow' );
		}
		
		// Check 3: IPN verification enabled
		$ipn_enabled = get_option( 'woocommerce_paypal_ipn_enabled', false );
		if ( ! $ipn_enabled ) {
			$issues[] = __( 'PayPal IPN verification not enabled', 'wpshadow' );
		}
		
		// Check 4: Sandbox mode in production
		$sandbox_mode = get_option( 'woocommerce_ppcp_sandbox_enabled', false );
		if ( $sandbox_mode && ! wp_get_environment_type() === 'development' ) {
			$issues[] = __( 'PayPal sandbox mode enabled on production site', 'wpshadow' );
		}
		
		// Check 5: SSL requirement
		if ( ! is_ssl() ) {
			$issues[] = __( 'PayPal requires SSL for secure transactions', 'wpshadow' );
		}
		
		// Check 6: Transaction logging
		$logging = get_option( 'woocommerce_ppcp_logging_enabled', false );
		if ( ! $logging ) {
			$issues[] = __( 'PayPal transaction logging not enabled (troubleshooting difficult)', 'wpshadow' );
		}
		
		// Check 7: Check for failed transactions
		global $wpdb;
		$failed_orders = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} 
				 WHERE post_type = %s 
				 AND post_status = %s
				 AND post_modified > DATE_SUB(NOW(), INTERVAL 7 DAY)",
				'shop_order',
				'wc-failed'
			)
		);
		
		if ( $failed_orders > 10 ) {
			$issues[] = sprintf( __( '%d failed orders in past week (investigate PayPal configuration)', 'wpshadow' ), $failed_orders );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 75;
		if ( count( $issues ) >= 5 ) {
			$threat_level = 88;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 82;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of security issues */
				__( 'PayPal Commerce Platform has %d security issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/paypal-commerce-platform-security',
		);
	}
}
