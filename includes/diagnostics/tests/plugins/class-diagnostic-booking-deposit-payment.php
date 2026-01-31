<?php
/**
 * Booking Deposit Payment Diagnostic
 *
 * Booking deposit system insecure.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.623.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Booking Deposit Payment Diagnostic Class
 *
 * @since 1.623.0000
 */
class Diagnostic_BookingDepositPayment extends Diagnostic_Base {

	protected static $slug = 'booking-deposit-payment';
	protected static $title = 'Booking Deposit Payment';
	protected static $description = 'Booking deposit system insecure';
	protected static $family = 'security';

	public static function check() {
		if ( ! get_option( 'booking_deposit_enabled', '' ) && ! get_option( 'booking_payment_gateway', '' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Deposit percentage configured
		$deposit_pct = absint( get_option( 'booking_deposit_percentage', 0 ) );
		if ( $deposit_pct <= 0 || $deposit_pct > 100 ) {
			$issues[] = 'Deposit percentage not properly configured';
		}

		// Check 2: Payment gateway security
		$payment_gateway = get_option( 'booking_payment_gateway', '' );
		if ( empty( $payment_gateway ) ) {
			$issues[] = 'Payment gateway not configured';
		}

		// Check 3: SSL/TLS enforced
		$ssl_enforced = get_option( 'booking_ssl_enforced', 0 );
		if ( ! $ssl_enforced ) {
			$issues[] = 'SSL/TLS not enforced for payment';
		}

		// Check 4: PCI compliance checked
		$pci_check = get_option( 'booking_pci_compliance_check', 0 );
		if ( ! $pci_check ) {
			$issues[] = 'PCI compliance checks not enabled';
		}

		// Check 5: Payment token encryption
		$token_encrypt = get_option( 'booking_payment_token_encryption', 0 );
		if ( ! $token_encrypt ) {
			$issues[] = 'Payment token encryption not enabled';
		}

		// Check 6: Audit logging
		$audit_log = get_option( 'booking_payment_audit_logging', 0 );
		if ( ! $audit_log ) {
			$issues[] = 'Payment audit logging not enabled';
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
					'Found %d booking deposit payment issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/booking-deposit-payment',
			);
		}

		return null;
	}
}
