<?php
/**
 * Booking Group Reservations Diagnostic
 *
 * Group booking limits bypassable.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.631.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Booking Group Reservations Diagnostic Class
 *
 * @since 1.631.0000
 */
class Diagnostic_BookingGroupReservations extends Diagnostic_Base {

	protected static $slug = 'booking-group-reservations';
	protected static $title = 'Booking Group Reservations';
	protected static $description = 'Group booking limits bypassable';
	protected static $family = 'security';

	public static function check() {
		if ( ! function_exists( 'booking_calendar_exists' ) && ! class_exists( 'Booking_Calendar' ) ) {
			return null;
		}
		
		$issues = array();

		// Check 1: Verify group size validation
		$group_validation = get_option( 'booking_group_size_validation', false );
		if ( ! $group_validation ) {
			$issues[] = __( 'Group size validation not enabled', 'wpshadow' );
		}

		// Check 2: Check concurrent booking limits
		$concurrent_limit = get_option( 'booking_concurrent_group_limit', 0 );
		if ( $concurrent_limit === 0 ) {
			$issues[] = __( 'Concurrent group booking limits not configured', 'wpshadow' );
		}

		// Check 3: Verify reservation validation
		$reservation_validation = get_option( 'booking_reservation_validation', false );
		if ( ! $reservation_validation ) {
			$issues[] = __( 'Reservation validation not enabled', 'wpshadow' );
		}

		// Check 4: Check payment verification for groups
		$payment_verification = get_option( 'booking_group_payment_verification', false );
		if ( ! $payment_verification ) {
			$issues[] = __( 'Payment verification not configured for group bookings', 'wpshadow' );
		}

		// Check 5: Verify double-booking prevention
		$double_booking_prevention = get_option( 'booking_double_booking_prevention', false );
		if ( ! $double_booking_prevention ) {
			$issues[] = __( 'Double-booking prevention not enabled', 'wpshadow' );
		}

		// Check 6: Check capacity validation
		$capacity_validation = get_option( 'booking_capacity_validation', false );
		if ( ! $capacity_validation ) {
			$issues[] = __( 'Capacity validation not configured', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 90, 60 + ( count( $issues ) * 5 ) );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Comma-separated list of issues */
					__( 'Booking group reservation issues detected: %s', 'wpshadow' ),
					implode( ', ', $issues )
				),
				'severity'     => 'high',
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/booking-group-reservations',
			);
		}

		return null;
	}
}
