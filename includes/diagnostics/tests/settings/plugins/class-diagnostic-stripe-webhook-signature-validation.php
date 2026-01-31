<?php
/**
 * Stripe Webhook Signature Validation Diagnostic
 *
 * Stripe Webhook Signature Validation vulnerability detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1389.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stripe Webhook Signature Validation Diagnostic Class
 *
 * @since 1.1389.0000
 */
class Diagnostic_StripeWebhookSignatureValidation extends Diagnostic_Base {

	protected static $slug = 'stripe-webhook-signature-validation';
	protected static $title = 'Stripe Webhook Signature Validation';
	protected static $description = 'Stripe Webhook Signature Validation vulnerability detected';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'WC_Stripe' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Verify webhook secret is configured
		$webhook_secret = get_option( 'woocommerce_stripe_webhook_secret', '' );
		if ( empty( $webhook_secret ) ) {
			$issues[] = 'webhook_secret_not_configured';
		}
		
		// Check 2: Verify webhook secret follows correct format (whsec_)
		if ( ! empty( $webhook_secret ) && ! preg_match( '/^whsec_/', $webhook_secret ) ) {
			$issues[] = 'invalid_webhook_secret_format';
		}
		
		// Check 3: Verify webhook endpoint is accessible
		$webhook_url = WC()->api_request_url( 'wc_stripe' );
		if ( empty( $webhook_url ) ) {
			$issues[] = 'webhook_endpoint_not_configured';
		}
		
		// Check 4: Verify webhook endpoint uses HTTPS
		if ( ! empty( $webhook_url ) && strpos( $webhook_url, 'https://' ) !== 0 ) {
			$issues[] = 'webhook_endpoint_not_secure';
		}
		
		// Check 5: Check if webhook logging is enabled for troubleshooting
		$logging_enabled = get_option( 'woocommerce_stripe_logging', 'no' );
		if ( 'yes' !== $logging_enabled ) {
			$issues[] = 'webhook_logging_disabled';
		}
		
		// Check 6: Verify webhook events are properly configured in Stripe dashboard
		// We can't check this directly, but we can check if any webhook events have been received
		global $wpdb;
		$recent_webhooks = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}wc_webhooks 
				WHERE topic LIKE %s AND date_created > DATE_SUB(NOW(), INTERVAL 30 DAY)",
				'stripe%'
			)
		);
		
		if ( 0 === (int) $recent_webhooks && ! empty( $webhook_secret ) ) {
			$issues[] = 'no_recent_webhook_events';
		}
		
		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of webhook validation issues */
				__( 'Stripe webhook signature validation has issues: %s. Without proper webhook validation, fraudulent payment notifications could be processed.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);
			
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => 75,
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/stripe-webhook-signature-validation',
			);
		}
		
		return null;
	}
}
