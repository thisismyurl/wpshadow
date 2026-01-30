<?php
/**
 * MemberPress Payment Gateway Security Diagnostic
 *
 * MemberPress payment gateways not secured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.319.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MemberPress Payment Gateway Security Diagnostic Class
 *
 * @since 1.319.0000
 */
class Diagnostic_MemberpressPaymentGatewaySecurity extends Diagnostic_Base {

	protected static $slug = 'memberpress-payment-gateway-security';
	protected static $title = 'MemberPress Payment Gateway Security';
	protected static $description = 'MemberPress payment gateways not secured';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'MEPR_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check if SSL is enabled for payment processing
		if ( ! is_ssl() ) {
			$issues[] = 'SSL not enabled for payment transactions';
		}

		// Check for test/sandbox mode in production
		$stripe_test = get_option( 'mepr_stripe_service_test_mode', '' );
		if ( ! empty( $stripe_test ) && 'yes' === $stripe_test ) {
			$issues[] = 'Stripe test mode enabled in production';
		}

		// Check for PayPal sandbox mode
		$paypal_sandbox = get_option( 'mepr_paypal_service_sandbox', '' );
		if ( ! empty( $paypal_sandbox ) && 'yes' === $paypal_sandbox ) {
			$issues[] = 'PayPal sandbox mode enabled in production';
		}

		// Check for webhook secrets configured
		$stripe_webhook = get_option( 'mepr_stripe_webhook_secret', '' );
		if ( defined( 'MEPR_STRIPE_WEBHOOK_SECRET' ) && empty( $stripe_webhook ) ) {
			$issues[] = 'Stripe webhook secret not configured';
		}

		// Check for API key storage in database
		global $wpdb;
		$api_keys = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s AND option_value != ''",
				'mepr_%_api_key'
			)
		);
		if ( $api_keys > 0 && ! defined( 'MEPR_SECURE_API_KEYS' ) ) {
			$issues[] = 'payment API keys stored in database (use constants instead)';
		}

		// Check for payment data logged to files
		$log_dir = WP_CONTENT_DIR . '/mepr/logs/';
		if ( is_dir( $log_dir ) && is_readable( $log_dir ) ) {
			$log_perms = fileperms( $log_dir );
			if ( ( $log_perms & 0044 ) > 0 ) {
				$issues[] = 'payment logs directory has world-readable permissions';
			}
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 95, 75 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'MemberPress payment gateway security issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/memberpress-payment-gateway-security',
			);
		}

		return null;
	}
}
