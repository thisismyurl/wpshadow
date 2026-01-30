<?php
/**
 * Directory Payment Integration Diagnostic
 *
 * Directory payment integration insecure.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.560.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Directory Payment Integration Diagnostic Class
 *
 * @since 1.560.0000
 */
class Diagnostic_DirectoryPaymentIntegration extends Diagnostic_Base {

	protected static $slug = 'directory-payment-integration';
	protected static $title = 'Directory Payment Integration';
	protected static $description = 'Directory payment integration insecure';
	protected static $family = 'security';

	public static function check() {
		if ( ! function_exists( 'wpbdp' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Payment gateway configured
		$gateway = get_option( 'wpbdp_payment_gateway', '' );
		if ( empty( $gateway ) ) {
			$issues[] = 'Payment gateway not configured';
		}
		
		// Check 2: SSL enforcement
		$ssl = get_option( 'wpbdp_ssl_enforcement', 0 );
		if ( ! $ssl ) {
			$issues[] = 'SSL enforcement not enabled';
		}
		
		// Check 3: Payment data encryption
		$encryption = get_option( 'wpbdp_payment_data_encryption', 0 );
		if ( ! $encryption ) {
			$issues[] = 'Payment data encryption not enabled';
		}
		
		// Check 4: PCI compliance
		$pci = get_option( 'wpbdp_pci_compliance_enabled', 0 );
		if ( ! $pci ) {
			$issues[] = 'PCI compliance not enabled';
		}
		
		// Check 5: Transaction logging
		$logging = get_option( 'wpbdp_transaction_logging', 0 );
		if ( ! $logging ) {
			$issues[] = 'Transaction logging not enabled';
		}
		
		// Check 6: Fraud detection
		$fraud = get_option( 'wpbdp_fraud_detection', 0 );
		if ( ! $fraud ) {
			$issues[] = 'Fraud detection not enabled';
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
					'Found %d payment integration security issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/directory-payment-integration',
			);
		}
		
		return null;
	}
}
