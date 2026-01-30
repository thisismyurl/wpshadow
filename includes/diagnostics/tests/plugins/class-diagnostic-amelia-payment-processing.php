<?php
/**
 * Amelia Payment Processing Diagnostic
 *
 * Amelia payment processing vulnerable.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.465.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Amelia Payment Processing Diagnostic Class
 *
 * @since 1.465.0000
 */
class Diagnostic_AmeliaPaymentProcessing extends Diagnostic_Base {

	protected static $slug = 'amelia-payment-processing';
	protected static $title = 'Amelia Payment Processing';
	protected static $description = 'Amelia payment processing vulnerable';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'AMELIA_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Payment gateway configured.
		$gateway = get_option( 'amelia_settings_payments_gateway', '' );
		if ( empty( $gateway ) ) {
			$issues[] = 'no payment gateway configured';
		}
		
		// Check 2: SSL for payment pages.
		if ( ! is_ssl() && ! empty( $gateway ) ) {
			$issues[] = 'payment processing without SSL (insecure)';
		}
		
		// Check 3: Test mode in production.
		$test_mode = get_option( 'amelia_settings_payments_testMode', '0' );
		if ( '1' === $test_mode && ! defined( 'WP_DEBUG' ) ) {
			$issues[] = 'test mode enabled on production site';
		}
		
		// Check 4: Payment logging.
		$logging = get_option( 'amelia_settings_payments_logging', '0' );
		if ( '0' === $logging ) {
			$issues[] = 'payment logging disabled (cannot track transactions)';
		}
		
		// Check 5: PCI compliance.
		$store_cards = get_option( 'amelia_settings_payments_pci_storeCards', '0' );
		if ( '1' === $store_cards ) {
			$issues[] = 'storing credit card data (PCI compliance risk)';
		}
		
		// Check 6: Refund policy.
		$refund_policy = get_option( 'amelia_settings_payments_refundPolicy', '' );
		if ( empty( $refund_policy ) ) {
			$issues[] = 'no refund policy configured';
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 95, 80 + ( count( $issues ) * 3 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Amelia payment processing issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/amelia-payment-processing',
			);
		}
		
		return null;
	}
}
