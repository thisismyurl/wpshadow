<?php
/**
 * Appointment Booking Payment Diagnostic
 *
 * Appointment payments vulnerable.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.604.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Appointment Booking Payment Diagnostic Class
 *
 * @since 1.604.0000
 */
class Diagnostic_AppointmentBookingPayment extends Diagnostic_Base {

	protected static $slug = 'appointment-booking-payment';
	protected static $title = 'Appointment Booking Payment';
	protected static $description = 'Appointment payments vulnerable';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'EDD_Bookings' ) && ! defined( 'WAPPOINTMENT_VERSION' ) && ! class_exists( 'WPBS_Init' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Payment SSL.
		$payment_gateway = get_option( 'appointment_payment_gateway', '' );
		if ( ! is_ssl() && ! empty( $payment_gateway ) ) {
			$issues[] = 'payment processing without SSL (insecure transmission)';
		}

		// Check 2: Test mode in production.
		$test_mode = get_option( 'appointment_payment_test_mode', '0' );
		if ( '1' === $test_mode && ! defined( 'WP_DEBUG' ) ) {
			$issues[] = 'test mode enabled on production site';
		}

		// Check 3: Payment logging.
		$logging = get_option( 'appointment_log_payments', '0' );
		if ( '0' === $logging ) {
			$issues[] = 'payment logging disabled (cannot audit transactions)';
		}

		// Check 4: Failed payment handling.
		$failed_action = get_option( 'appointment_failed_payment_action', '' );
		if ( empty( $failed_action ) && ! empty( $payment_gateway ) ) {
			$issues[] = 'no action defined for failed payments';
		}

		// Check 5: Refund capability.
		$allow_refunds = get_option( 'appointment_allow_refunds', '0' );
		if ( '0' === $allow_refunds ) {
			$issues[] = 'refunds not allowed (customer service limitation)';
		}

		// Check 6: Currency mismatch.
		$appointment_currency = get_option( 'appointment_currency', get_option( 'woocommerce_currency', 'USD' ) );
		$woo_currency = get_option( 'woocommerce_currency', 'USD' );
		if ( function_exists( 'WC' ) && $appointment_currency !== $woo_currency ) {
			$issues[] = "currency mismatch: appointment '{$appointment_currency}' vs WooCommerce '{$woo_currency}'";
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 90, 75 + ( count( $issues ) * 3 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Appointment booking payment issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/appointment-booking-payment',
			);
		}

		return null;
	}
}
