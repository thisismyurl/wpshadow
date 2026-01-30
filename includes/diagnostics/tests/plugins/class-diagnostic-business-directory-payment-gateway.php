<?php
/**
 * Business Directory Payment Gateway Diagnostic
 *
 * Business Directory payments insecure.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.547.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Business Directory Payment Gateway Diagnostic Class
 *
 * @since 1.547.0000
 */
class Diagnostic_BusinessDirectoryPaymentGateway extends Diagnostic_Base {

	protected static $slug = 'business-directory-payment-gateway';
	protected static $title = 'Business Directory Payment Gateway';
	protected static $description = 'Business Directory payments insecure';
	protected static $family = 'security';

	public static function check() {
		if ( ! function_exists( 'wpbdp' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Payment gateway enabled
		$payments_enabled = get_option( 'wpbdp_payments_enabled', 'no' );
		if ( 'no' === $payments_enabled ) {
			return null; // Payments not in use
		}

		// Check 2: SSL enforcement
		if ( ! is_ssl() ) {
			$issues[] = __( 'Not using HTTPS (payment data unencrypted)', 'wpshadow' );
		}

		// Check 3: Payment gateway configured
		$gateway = get_option( 'wpbdp_payment_gateway', '' );
		if ( empty( $gateway ) ) {
			$issues[] = __( 'No payment gateway configured', 'wpshadow' );
		}

		// Check 4: Transaction logging
		$log_transactions = get_option( 'wpbdp_log_payments', 'no' );
		if ( 'no' === $log_transactions ) {
			$issues[] = __( 'Payments not logged (no audit trail)', 'wpshadow' );
		}

		// Check 5: Test mode in production
		$test_mode = get_option( 'wpbdp_payment_test_mode', 'no' );
		if ( 'yes' === $test_mode && ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			$issues[] = __( 'Test mode in production (no real charges)', 'wpshadow' );
		}

		// Check 6: Webhook verification
		$verify_webhooks = get_option( 'wpbdp_verify_payment_webhooks', 'no' );
		if ( 'no' === $verify_webhooks ) {
			$issues[] = __( 'Webhooks not verified (fake payments)', 'wpshadow' );
		}

		// Check 7: PCI compliance
		$store_cards = get_option( 'wpbdp_store_payment_details', 'yes' );
		if ( 'yes' === $store_cards ) {
			$issues[] = __( 'Storing payment details (PCI violation)', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$threat_level = 75;
		if ( count( $issues ) >= 5 ) {
			$threat_level = 90;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 83;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of business directory payment issues */
				__( 'Business directory payments have %d security issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/business-directory-payment-gateway',
		);
	}
}
