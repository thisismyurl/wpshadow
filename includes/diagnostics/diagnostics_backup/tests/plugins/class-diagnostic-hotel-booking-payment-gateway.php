<?php
/**
 * Hotel Booking Payment Gateway Diagnostic
 *
 * Hotel payment gateway vulnerable.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.608.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hotel Booking Payment Gateway Diagnostic Class
 *
 * @since 1.608.0000
 */
class Diagnostic_HotelBookingPaymentGateway extends Diagnostic_Base {

	protected static $slug = 'hotel-booking-payment-gateway';
	protected static $title = 'Hotel Booking Payment Gateway';
	protected static $description = 'Hotel payment gateway vulnerable';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'MPHB_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Payment gateway SSL.
		if ( ! is_ssl() ) {
			$issues[] = 'payment processing without HTTPS';
		}

		// Check 2: Gateway sandbox mode.
		$sandbox_mode = get_option( 'mphb_payment_sandbox_mode', '0' );
		if ( '1' === $sandbox_mode ) {
			$issues[] = 'sandbox mode enabled on live site';
		}

		// Check 3: API credentials storage.
		$api_key = get_option( 'mphb_payment_api_key', '' );
		if ( ! empty( $api_key ) && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$issues[] = 'API keys visible with debug mode on';
		}

		// Check 4: Payment logging.
		$payment_logging = get_option( 'mphb_payment_logging', '1' );
		if ( '0' === $payment_logging ) {
			$issues[] = 'payment logging disabled';
		}

		// Check 5: Gateway validation.
		$gateway_validation = get_option( 'mphb_gateway_validation', '1' );
		if ( '0' === $gateway_validation ) {
			$issues[] = 'gateway validation disabled';
		}

		// Check 6: PCI compliance mode.
		$pci_mode = get_option( 'mphb_pci_compliance', '0' );
		if ( '0' === $pci_mode ) {
			$issues[] = 'PCI compliance mode not enabled';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 95, 80 + ( count( $issues ) * 3 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Payment gateway issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/hotel-booking-payment-gateway',
			);
		}

		return null;
	}
}
