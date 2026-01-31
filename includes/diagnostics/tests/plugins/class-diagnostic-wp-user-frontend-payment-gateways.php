<?php
/**
 * Wp User Frontend Payment Gateways Diagnostic
 *
 * Wp User Frontend Payment Gateways issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1222.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wp User Frontend Payment Gateways Diagnostic Class
 *
 * @since 1.1222.0000
 */
class Diagnostic_WpUserFrontendPaymentGateways extends Diagnostic_Base {

	protected static $slug = 'wp-user-frontend-payment-gateways';
	protected static $title = 'Wp User Frontend Payment Gateways';
	protected static $description = 'Wp User Frontend Payment Gateways issue found';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'WPUF_VERSION' ) ) {
			return null;
		}
		
		$issues = array();

		// Check 1: Verify payment encryption
		$encryption_enabled = get_option( 'wpuf_payment_encryption', false );
		if ( ! $encryption_enabled ) {
			$issues[] = __( 'Payment data encryption not enabled', 'wpshadow' );
		}

		// Check 2: Check SSL for payment processing
		if ( ! is_ssl() ) {
			$issues[] = __( 'SSL not enabled for payment processing', 'wpshadow' );
		}

		// Check 3: Verify gateway configuration
		$gateway_config = get_option( 'wpuf_payment_gateway_config', array() );
		if ( empty( $gateway_config ) ) {
			$issues[] = __( 'Payment gateways not configured', 'wpshadow' );
		}

		// Check 4: Check transaction logging
		$transaction_logging = get_option( 'wpuf_transaction_logging', false );
		if ( ! $transaction_logging ) {
			$issues[] = __( 'Transaction logging not enabled', 'wpshadow' );
		}

		// Check 5: Verify PCI compliance
		$pci_compliance = get_option( 'wpuf_pci_compliance_mode', false );
		if ( ! $pci_compliance ) {
			$issues[] = __( 'PCI compliance mode not enabled', 'wpshadow' );
		}

		// Check 6: Check error handling
		$error_handling = get_option( 'wpuf_payment_error_handling', false );
		if ( ! $error_handling ) {
			$issues[] = __( 'Payment error handling not properly configured', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 100, 80 + ( count( $issues ) * 5 ) );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Comma-separated list of issues */
					__( 'WP User Frontend payment gateway security issues detected: %s', 'wpshadow' ),
					implode( ', ', $issues )
				),
				'severity'     => 'high',
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/wp-user-frontend-payment-gateways',
			);
		}

		return null;
	}
}
