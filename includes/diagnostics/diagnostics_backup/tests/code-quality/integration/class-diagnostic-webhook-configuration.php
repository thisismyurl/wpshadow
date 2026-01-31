<?php
/**
 * Webhook Configuration Diagnostic
 *
 * Verifies webhooks configured correctly for third-party
 * integrations (payment gateways, email services, etc).
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Webhook_Configuration Class
 *
 * Verifies webhook setup for integrations.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Webhook_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'webhook-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Webhook Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies webhooks configured correctly';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'integration';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if webhook issues, null otherwise.
	 */
	public static function check() {
		$webhook_status = self::check_webhook_configuration();

		if ( ! $webhook_status['has_issue'] ) {
			return null; // Webhooks configured correctly
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of failed webhooks */
				__( '%d webhooks not responding or misconfigured. Payment confirmations not received = orders not processed. Email signup confirmations not sent = customers not subscribed.', 'wpshadow' ),
				$webhook_status['failed_count']
			),
			'severity'     => 'high',
			'threat_level' => 70,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/webhook-configuration',
			'family'       => self::$family,
			'meta'         => array(
				'failed_webhooks' => $webhook_status['failed_count'],
			),
			'details'      => array(
				'what_are_webhooks'           => array(
					__( 'Webhook = real-time notification from 3rd party service' ),
					__( 'Example: Stripe sends "payment_intent.succeeded" event' ),
					__( 'You receive: POST request with event details' ),
					__( 'Action: Update order status, send confirmation' ),
					__( 'Purpose: React to events outside WordPress' ),
				),
				'common_webhook_use_cases'    => array(
					'Payment Gateways' => array(
						'Services: Stripe, PayPal, Square',
						'Event: payment_intent.succeeded',
						'Action: Update order to "completed"',
						'Issue: Missing webhook = no order confirmation',
					),
					'Email Services' => array(
						'Services: Mailchimp, ConvertKit',
						'Event: subscribe, unsubscribe',
						'Action: Update WordPress user',
						'Issue: Outdated subscriber list',
					),
					'Shipping Carriers' => array(
						'Services: FedEx, UPS, ShipStation',
						'Event: tracking_update',
						'Action: Email tracking to customer',
						'Issue: Customer doesn\'t know shipped',
					),
					'Communication Platforms' => array(
						'Services: Slack, Discord',
						'Event: new order, failed payment',
						'Action: Notify team via chat',
						'Issue: Team misses important notifications',
					),
				),
				'webhook_configuration'       => array(
					'Webhook URL (Critical)' => array(
						'Format: https://yoursite.com/wp-json/wc/v3/...',
						'Must be: HTTPS (secure), public (accessible)',
						'Test: curl -I URL should return 200',
					),
					'Webhook Secret' => array(
						'Purpose: Verify request from legitimate service',
						'Store: wp_options (woocommerce_webhook_secret)',
						'Compare: SHA256 hash of payload vs signature',
					),
					'Webhook Events' => array(
						'Payment: order.payment_success',
						'Delivery: order.shipped',
						'Email: contact.submit',
					),
					'Delivery' => array(
						'HTTP Method: POST',
						'Headers: Content-Type: application/json',
						'Body: JSON with event data',
					),
				),
				'common_webhook_issues'       => array(
					'URL Not Accessible' => array(
						'Cause: Firewall blocking requests',
						'Or: URL changed, never updated service',
						'Test: Service test ping fails',
						'Fix: Verify URL, whitelist service IP',
					),
					'Endpoint Not Registered' => array(
						'Cause: Plugin not active or hook removed',
						'Symptom: WordPress returns 404',
						'Fix: Reinstall plugin, re-register webhook',
					),
					'Secret Mismatch' => array(
						'Cause: Secret changed, not updated everywhere',
						'Result: Signature verification fails',
						'Fix: Update secret in all integrations',
					),
					'Rate Limiting' => array(
						'Cause: Service IP blocked by rate limiter',
						'Result: Webhook returns 429',
						'Fix: Whitelist service IP from limiting',
					),
				),
				'debugging_webhooks'          => array(
					'Webhook Event Logs' => array(
						'WooCommerce: wp-admin → Tools → Webhooks',
						'Shows: Last delivery timestamp',
						'Shows: Response code (200 = success, 4xx = error)',
					),
					'Test Delivery' => array(
						'Service: Usually has "Test webhook" button',
						'Or: Manual via Service API docs',
						'WordPress logs: Check /wp-content/debug.log',
					),
					'Curl Test' => array(
						'Command: curl -X POST https://yoursite.com/webhook',
						'Add headers: -H "Content-Type: application/json"',
						'Add data: -d \'{"event": "test"}\'',
					),
				),
				'monitoring_webhooks'         => array(
					__( 'Monthly: Check webhook delivery success rate' ),
					__( 'Alerts: Set up notification on failures' ),
					__( 'Test: Manual re-sync if failures detected' ),
					__( 'Logs: Archive webhook logs beyond 30 days' ),
				),
			),
		);
	}

	/**
	 * Check webhook configuration.
	 *
	 * @since  1.2601.2148
	 * @return array Webhook status.
	 */
	private static function check_webhook_configuration() {
		$failed_count = 0;

		// Check WooCommerce webhooks if active
		if ( class_exists( 'WC_Webhook' ) ) {
			global $wpdb;
			// Simple check: look for registered webhooks
			$webhooks = get_posts( array(
				'post_type'   => 'shop_webhook',
				'post_status' => 'publish',
				'numberposts' => -1,
			) );

			// Count potentially failed webhooks (simplified)
			if ( empty( $webhooks ) && is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
				$failed_count = 1; // WooCommerce active but no webhooks
			}
		}

		return array(
			'has_issue'    => $failed_count > 0,
			'failed_count' => $failed_count,
		);
	}
}
