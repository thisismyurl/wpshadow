<?php
/**
 * Modern Events Calendar Payment Diagnostic
 *
 * Modern Events Calendar payments vulnerable.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.586.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Modern Events Calendar Payment Diagnostic Class
 *
 * @since 1.586.0000
 */
class Diagnostic_ModernEventsCalendarPayment extends Diagnostic_Base {

	protected static $slug = 'modern-events-calendar-payment';
	protected static $title = 'Modern Events Calendar Payment';
	protected static $description = 'Modern Events Calendar payments vulnerable';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'Tribe__Events__Main' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: SSL enabled for payments
		if ( ! is_ssl() ) {
			$issues[] = 'SSL not enabled for payment pages';
		}

		// Check 2: Payment gateway security
		$gateway_security = get_option( 'mec_payment_gateway_security', false );
		if ( ! $gateway_security ) {
			$issues[] = 'Payment gateway security not configured';
		}

		// Check 3: PCI compliance mode
		$pci_compliance = get_option( 'mec_pci_compliance_mode', false );
		if ( ! $pci_compliance ) {
			$issues[] = 'PCI compliance mode disabled';
		}

		// Check 4: Payment logging enabled
		$payment_logging = get_option( 'mec_payment_logging', false );
		if ( ! $payment_logging ) {
			$issues[] = 'Payment logging disabled';
		}

		// Check 5: Refund handling configured
		$refund_handling = get_option( 'mec_refund_handling', false );
		if ( ! $refund_handling ) {
			$issues[] = 'Refund handling not configured';
		}

		// Check 6: Transaction verification enabled
		$transaction_verify = get_option( 'mec_transaction_verification', false );
		if ( ! $transaction_verify ) {
			$issues[] = 'Transaction verification disabled';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 90, 60 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Modern Events Calendar payment security issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/modern-events-calendar-payment',
			);
		}
		// Additional checks
		if ( ! function_exists( 'wp_verify_nonce' ) ) {
			$issues[] = __( 'Nonce verification unavailable', 'wpshadow' );
		}
		return null;
	}
}
