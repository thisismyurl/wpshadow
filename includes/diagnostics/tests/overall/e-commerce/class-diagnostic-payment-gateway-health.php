<?php
/**
 * Payment Gateway Health Diagnostic
 *
 * Verifies payment processor is operational and properly configured
 * to prevent transaction failures and lost sales.
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
 * Diagnostic_Payment_Gateway_Health Class
 *
 * Verifies payment gateway health.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Payment_Gateway_Health extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'payment-gateway-health';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Payment Gateway Health';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies payment processor';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ecommerce';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issues detected, null otherwise.
	 */
	public static function check() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null; // Not e-commerce
		}

		$gateway_status = self::check_payment_gateways();

		if ( ! $gateway_status['has_issue'] ) {
			return null; // Payment gateway configured
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: payment gateway status */
				__( 'Payment gateway issue: %s. Customers cannot checkout = 100%% of sales lost. Even 1 hour downtime = thousands in revenue lost.', 'wpshadow' ),
				$gateway_status['issue']
			),
			'severity'     => 'critical',
			'threat_level' => 95,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/payment-gateway',
			'family'       => self::$family,
			'meta'         => array(
				'status' => $gateway_status['status'],
			),
			'details'      => array(
				'payment_gateway_criticality'   => array(
					__( 'No payment = no sales = business stops' ),
					__( '1 hour downtime averages $5,000-50,000 lost' ),
					__( 'Every minute matters' ),
					__( 'Customer experience destroyed' ),
					__( 'Higher stress than website downtime' ),
				),
				'popular_payment_gateways'     => array(
					'Stripe' => array(
						'Fee: 2.9% + $0.30 per transaction',
						'Settlement: 2 days',
						'Integrations: 1000+ apps',
						'Reliability: 99.99% uptime',
					),
					'PayPal' => array(
						'Fee: 3.49% + $0.49 per transaction',
						'Settlement: 1 day',
						'Integrations: Everywhere',
						'Reliability: 99.9% uptime',
					),
					'Square' => array(
						'Fee: 2.6% + $0.30 (online)',
						'Settlement: Next business day',
						'For: Retail + online',
					),
					'Authorize.net' => array(
						'Fee: $0.10 per transaction + gateway fee',
						'Settlement: 1-2 days',
						'For: Direct integration merchants',
					),
				),
				'testing_payment_gateway'      => array(
					'Test Mode' => array(
						'WooCommerce → Settings → Payments',
						'Enable: Test/sandbox mode',
						'Use: Test credit cards provided by gateway',
					),
					'Test Credit Cards' => array(
						'Stripe: 4242 4242 4242 4242 (any date/CVV)',
						'PayPal: Sandbox accounts',
						'Don\'t use: Real credit card numbers',
					),
					'Place Test Order' => array(
						'Go to checkout',
						'Use test card',
						'Verify: Payment successful',
						'Verify: Order created in WooCommerce',
					),
				),
				'common_payment_issues'        => array(
					'Missing API Keys' => array(
						'Cause: Keys not entered in settings',
						'Fix: Copy API keys from gateway dashboard',
						'Stripe: Publishable + Secret key',
						'PayPal: Client ID + Secret',
					),
					'Live vs Test Mode Mismatch' => array(
						'Issue: Live keys in test mode = fails',
						'Fix: Switch to Live mode, use live keys',
					),
					'Domain Mismatch' => array(
						'Issue: Keys registered for example.com but using test.example.com',
						'Fix: Add domain to gateway settings',
					),
					'PCI Compliance Issue' => array(
						'Issue: Payment form not PCI compliant',
						'Fix: Use hosted payment form (not custom)',
					),
				),
				'security_best_practices'      => array(
					'Never Handle Credit Cards' => array(
						'Use: Hosted payment form (Stripe.js)',
						'Not: Custom form processing',
						'Benefit: PCI compliance handled',
					),
					'API Keys' => array(
						'Never: Commit to GitHub',
						'Use: Environment variables',
						'Rotate: Quarterly',
					),
					'SSL Certificate' => array(
						'Required: HTTPS on all checkout pages',
						'Inspect: Trust certificate (not warning)',
					),
					'PCI Compliance' => array(
						'Requirement: Level 1 for e-commerce',
						'Verify: Annual assessment',
						'Simplify: Use hosted payment processor',
					),
				),
				'monitoring_gateway_health'    => array(
					__( 'Gateway dashboard: Check status/alerts' ),
					__( 'Successful transactions: Monitor daily' ),
					__( 'Failed transactions: Investigate immediately' ),
					__( 'Settlement reports: Verify amounts' ),
					__( 'Chargeback alerts: Address disputes' ),
				),
			),
		);
	}

	/**
	 * Check payment gateways.
	 *
	 * @since  1.2601.2148
	 * @return array Gateway status.
	 */
	private static function check_payment_gateways() {
		// Get configured payment gateways
		$gateways = WC()->payment_gateways->get_available_payment_gateways();

		if ( empty( $gateways ) ) {
			return array(
				'has_issue' => true,
				'issue'     => 'No payment gateways configured',
				'status'    => 'critical',
			);
		}

		// Check if all gateways enabled but no methods
		$enabled_gateways = array_filter( $gateways, function( $gateway ) {
			return isset( $gateway->enabled ) && 'yes' === $gateway->enabled;
		} );

		if ( empty( $enabled_gateways ) ) {
			return array(
				'has_issue' => true,
				'issue'     => 'No payment gateways enabled',
				'status'    => 'critical',
			);
		}

		// Check if Stripe/PayPal configured but API keys might be missing
		$stripe_configured = isset( $enabled_gateways['stripe'] ) && ! empty( get_option( 'woocommerce_stripe_settings' ) );
		$paypal_configured = isset( $enabled_gateways['paypal'] ) && ! empty( get_option( 'woocommerce_paypal_settings' ) );

		if ( ! $stripe_configured && ! $paypal_configured && count( $enabled_gateways ) < 1 ) {
			return array(
				'has_issue' => true,
				'issue'     => 'Payment gateways may not be properly configured',
				'status'    => 'warning',
			);
		}

		return array(
			'has_issue' => false,
			'issue'     => 'OK',
			'status'    => 'healthy',
		);
	}
}
