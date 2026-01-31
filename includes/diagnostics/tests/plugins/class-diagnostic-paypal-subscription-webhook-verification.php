<?php
/**
 * Paypal Subscription Webhook Verification Diagnostic
 *
 * Paypal Subscription Webhook Verification vulnerability detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1397.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Paypal Subscription Webhook Verification Diagnostic Class
 *
 * @since 1.1397.0000
 */
class Diagnostic_PaypalSubscriptionWebhookVerification extends Diagnostic_Base {

	protected static $slug = 'paypal-subscription-webhook-verification';
	protected static $title = 'Paypal Subscription Webhook Verification';
	protected static $description = 'Paypal Subscription Webhook Verification vulnerability detected';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'WC_Gateway_Paypal' ) && ! get_option( 'paypal_webhook_id', '' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Webhook verification enabled
		$verification = get_option( 'paypal_webhook_verification', 0 );
		if ( ! $verification ) {
			$issues[] = 'Webhook verification not enabled';
		}

		// Check 2: Webhook ID configured
		$webhook_id = get_option( 'paypal_webhook_id', '' );
		if ( empty( $webhook_id ) ) {
			$issues[] = 'Webhook ID not configured';
		}

		// Check 3: Webhook secret configured
		$webhook_secret = get_option( 'paypal_webhook_secret', '' );
		if ( empty( $webhook_secret ) ) {
			$issues[] = 'Webhook secret not configured';
		}

		// Check 4: Signature verification enabled
		$signature_verify = get_option( 'paypal_webhook_signature', 0 );
		if ( ! $signature_verify ) {
			$issues[] = 'Webhook signature verification not enabled';
		}

		// Check 5: IPN enabled for subscriptions
		$ipn_enabled = get_option( 'paypal_ipn_enabled', 0 );
		if ( ! $ipn_enabled ) {
			$issues[] = 'PayPal IPN not enabled for subscriptions';
		}

		// Check 6: Sandbox webhook configured when in sandbox
		$sandbox = get_option( 'paypal_sandbox_enabled', 0 );
		$sandbox_webhook = get_option( 'paypal_sandbox_webhook_id', '' );
		if ( $sandbox && empty( $sandbox_webhook ) ) {
			$issues[] = 'Sandbox webhook ID not configured';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 60;
			$threat_multiplier = 6;
			$max_threat = 90;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d PayPal webhook verification issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/paypal-subscription-webhook-verification',
			);
		}

		return null;
	}
}
