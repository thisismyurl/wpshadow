<?php
/**
 * Woocommerce Subscriptions Payment Diagnostic
 *
 * Woocommerce Subscriptions Payment issues detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.638.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woocommerce Subscriptions Payment Diagnostic Class
 *
 * @since 1.638.0000
 */
class Diagnostic_WoocommerceSubscriptionsPayment extends Diagnostic_Base {

	protected static $slug = 'woocommerce-subscriptions-payment';
	protected static $title = 'Woocommerce Subscriptions Payment';
	protected static $description = 'Woocommerce Subscriptions Payment issues detected';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null;
		}
		
		// Check if WooCommerce Subscriptions extension is active
		if ( ! class_exists( 'WC_Subscriptions' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Verify SSL is enabled for checkout (critical for payment security)
		if ( ! wc_site_is_https() ) {
			$issues[] = 'ssl_not_enabled';
		}
		
		// Check 2: Verify payment gateways support subscriptions properly
		$available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
		$subscription_gateways = array();
		
		foreach ( $available_gateways as $gateway_id => $gateway ) {
			// Check if gateway supports subscriptions
			if ( isset( $gateway->supports ) && is_array( $gateway->supports ) ) {
				if ( in_array( 'subscriptions', $gateway->supports, true ) ) {
					$subscription_gateways[] = $gateway_id;
				}
			}
		}
		
		// If subscriptions plugin is active but no gateways support subscriptions, that's an issue
		if ( empty( $subscription_gateways ) && ! empty( $available_gateways ) ) {
			$issues[] = 'no_subscription_payment_gateways';
		}
		
		// Check 3: Verify automatic payment retry is configured
		$retry_enabled = get_option( 'woocommerce_subscriptions_retry_failed_payments', 'no' );
		if ( 'yes' !== $retry_enabled ) {
			$issues[] = 'payment_retry_disabled';
		}
		
		// Check 4: Check for failed payment handling
		$failed_payment_action = get_option( 'woocommerce_subscriptions_failed_payment_action', '' );
		if ( empty( $failed_payment_action ) ) {
			$issues[] = 'no_failed_payment_action';
		}
		
		// Check 5: Verify subscription status emails are configured
		$email_enabled = get_option( 'woocommerce_subscriptions_enable_subscription_email', 'yes' );
		if ( 'yes' !== $email_enabled ) {
			$issues[] = 'subscription_emails_disabled';
		}
		
		// Check 6: Verify payment method tokenization is enabled (for secure recurring payments)
		$tokenization_enabled = get_option( 'woocommerce_tokenization_enabled', 'no' );
		if ( 'yes' !== $tokenization_enabled ) {
			$issues[] = 'tokenization_disabled';
		}
		
		// Check 7: Verify webhook endpoints are configured for payment status updates
		$webhook_enabled = get_option( 'woocommerce_subscriptions_enable_webhooks', 'no' );
		if ( 'yes' !== $webhook_enabled ) {
			$issues[] = 'webhooks_not_configured';
		}
		
		// Check 8: Verify suspension grace period is reasonable (prevents immediate cancellation)
		$suspension_count = get_option( 'woocommerce_subscriptions_suspension_count', 0 );
		if ( $suspension_count < 1 ) {
			$issues[] = 'no_suspension_grace_period';
		}
		
		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of subscription payment issues */
				__( 'WooCommerce Subscriptions has payment configuration issues: %s. These could result in failed recurring payments, revenue loss, or security vulnerabilities.', 'wpshadow' ),
				implode( ', ', array_map( 'ucwords', str_replace( '_', ' ', $issues ) ) )
			);
			
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => self::calculate_severity( 70 ),
				'threat_level' => 70,
				'auto_fixable' => false, // Requires admin configuration of WooCommerce settings
				'kb_link'      => 'https://wpshadow.com/kb/woocommerce-subscriptions-payment',
			);
		}
		
		return null;
	}
}
