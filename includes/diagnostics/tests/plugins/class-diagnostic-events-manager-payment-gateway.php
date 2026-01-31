<?php
/**
 * Events Manager Payment Gateway Diagnostic
 *
 * Events Manager payments vulnerable.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.577.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Events Manager Payment Gateway Diagnostic Class
 *
 * @since 1.577.0000
 */
class Diagnostic_EventsManagerPaymentGateway extends Diagnostic_Base {

	protected static $slug = 'events-manager-payment-gateway';
	protected static $title = 'Events Manager Payment Gateway';
	protected static $description = 'Events Manager payments vulnerable';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'EM_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Payment gateway configuration
		$gateway = get_option( 'em_payment_gateway_configured', 0 );
		if ( ! $gateway ) {
			$issues[] = 'Payment gateway not configured';
		}

		// Check 2: SSL required
		$ssl = get_option( 'em_payment_ssl_required', 0 );
		if ( ! $ssl ) {
			$issues[] = 'SSL not enforced for payments';
		}

		// Check 3: PCI compliance
		$pci = get_option( 'em_pci_compliance_enabled', 0 );
		if ( ! $pci ) {
			$issues[] = 'PCI compliance checks not enabled';
		}

		// Check 4: Card data encryption
		$encryption = get_option( 'em_card_encryption_enabled', 0 );
		if ( ! $encryption ) {
			$issues[] = 'Card data encryption not enabled';
		}

		// Check 5: Tokenization enabled
		$token = get_option( 'em_payment_tokenization_enabled', 0 );
		if ( ! $token ) {
			$issues[] = 'Payment tokenization not enabled';
		}

		// Check 6: Fraud detection
		$fraud = get_option( 'em_fraud_detection_enabled', 0 );
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
					'Found %d payment security issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/events-manager-payment-gateway',
			);
		}

		return null;
	}
}
