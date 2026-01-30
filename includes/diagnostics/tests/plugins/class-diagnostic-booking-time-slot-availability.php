<?php
/**
 * Booking Time Slot Availability Diagnostic
 *
 * Booking time slots overbookable.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.628.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Booking Time Slot Availability Diagnostic Class
 *
 * @since 1.628.0000
 */
class Diagnostic_BookingTimeSlotAvailability extends Diagnostic_Base {

	protected static $slug = 'booking-time-slot-availability';
	protected static $title = 'Booking Time Slot Availability';
	protected static $description = 'Booking time slots overbookable';
	protected static $family = 'functionality';

	public static function check() {
		// Check for common booking plugins
		$booking_active = defined( 'BOOKINGPRESS_VERSION' ) || class_exists( 'WC_Bookings' ) || function_exists( 'wc_appointments_init' );
		
		if ( ! $booking_active ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Overlapping bookings
		if ( defined( 'BOOKINGPRESS_VERSION' ) ) {
			$overlaps = $wpdb->get_var(
				"SELECT COUNT(DISTINCT a1.bookingpress_appointment_id) FROM {$wpdb->prefix}bookingpress_appointments a1
				 INNER JOIN {$wpdb->prefix}bookingpress_appointments a2
				 ON a1.bookingpress_service_id = a2.bookingpress_service_id
				 AND a1.bookingpress_appointment_id != a2.bookingpress_appointment_id
				 AND a1.bookingpress_appointment_date = a2.bookingpress_appointment_date
				 AND a1.bookingpress_appointment_time = a2.bookingpress_appointment_time
				 WHERE a1.bookingpress_appointment_status = '1' AND a2.bookingpress_appointment_status = '1'"
			);
			
			if ( $overlaps > 0 ) {
				$issues[] = sprintf( __( '%d overlapping bookings detected', 'wpshadow' ), $overlaps );
			}
		}
		
		// Check 2: Buffer time configuration
		$buffer_before = get_option( 'booking_buffer_before', 0 );
		$buffer_after = get_option( 'booking_buffer_after', 0 );
		
		if ( $buffer_before === 0 && $buffer_after === 0 ) {
			$issues[] = __( 'No buffer time between bookings (overbooking risk)', 'wpshadow' );
		}
		
		// Check 3: Concurrent booking limits
		$max_concurrent = get_option( 'booking_max_concurrent', 0 );
		if ( $max_concurrent === 0 ) {
			$issues[] = __( 'No concurrent booking limit set', 'wpshadow' );
		}
		
		// Check 4: Booking capacity validation
		if ( class_exists( 'WC_Bookings' ) ) {
			$over_capacity = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->postmeta} pm1
					 INNER JOIN {$wpdb->postmeta} pm2 ON pm1.post_id = pm2.post_id
					 WHERE pm1.meta_key = %s AND pm2.meta_key = %s
					 AND CAST(pm1.meta_value AS UNSIGNED) > CAST(pm2.meta_value AS UNSIGNED)",
					'_wc_booking_qty',
					'_wc_booking_max_persons_group'
				)
			);
			
			if ( $over_capacity > 0 ) {
				$issues[] = sprintf( __( '%d bookings exceed capacity limits', 'wpshadow' ), $over_capacity );
			}
		}
		
		// Check 5: Booking lock mechanism
		$locking_enabled = get_option( 'booking_enable_locking', false );
		if ( ! $locking_enabled ) {
			$issues[] = __( 'Booking lock mechanism not enabled (race condition risk)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 65;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 78;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 72;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of availability issues */
				__( 'Booking time slot availability has %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/booking-time-slot-availability',
		);
	}
}
