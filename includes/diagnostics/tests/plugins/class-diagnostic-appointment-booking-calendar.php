<?php
/**
 * Appointment Booking Calendar Diagnostic
 *
 * Appointment calendar sync failing.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.605.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Appointment Booking Calendar Diagnostic Class
 *
 * @since 1.605.0000
 */
class Diagnostic_AppointmentBookingCalendar extends Diagnostic_Base {

	protected static $slug = 'appointment-booking-calendar';
	protected static $title = 'Appointment Booking Calendar';
	protected static $description = 'Appointment calendar sync failing';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'EDD_Bookings' ) && ! defined( 'WAPPOINTMENT_VERSION' ) && ! class_exists( 'WPBS_Init' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Calendar sync configured
		$calendar_sync = get_option( 'appointment_calendar_sync', '' );
		if ( empty( $calendar_sync ) ) {
			$issues[] = 'calendar sync not configured (bookings may conflict)';
		}

		// Check 2: Timezone settings
		$appointment_timezone = get_option( 'appointment_timezone', '' );
		$wp_timezone = get_option( 'timezone_string', '' );
		if ( ! empty( $appointment_timezone ) && $appointment_timezone !== $wp_timezone ) {
			$issues[] = 'timezone mismatch between plugin and WordPress';
		}

		// Check 3: Double booking prevention
		global $wpdb;
		$double_bookings = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}appointments a1
				 INNER JOIN {$wpdb->prefix}appointments a2
				 ON a1.start_time = a2.start_time
				 AND a1.id != a2.id
				 WHERE a1.status = %s AND a2.status = %s",
				'confirmed',
				'confirmed'
			)
		);
		if ( $double_bookings > 0 ) {
			$issues[] = "{$double_bookings} double bookings detected (sync issue)";
		}

		// Check 4: Failed sync attempts
		$sync_errors = get_transient( 'appointment_sync_errors' );
		if ( ! empty( $sync_errors ) ) {
			$error_count = is_array( $sync_errors ) ? count( $sync_errors ) : 1;
			$issues[] = "{$error_count} recent sync failures";
		}

		// Check 5: External calendar connection
		if ( ! empty( $calendar_sync ) ) {
			$last_sync = get_option( 'appointment_last_sync', 0 );
			if ( ! empty( $last_sync ) ) {
				$hours_ago = round( ( time() - $last_sync ) / 3600 );
				if ( $hours_ago > 24 ) {
					$issues[] = "calendar not synced in {$hours_ago} hours";
				}
			}
		}

		// Check 6: Pending appointments count
		$pending_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}appointments WHERE status = %s",
				'pending'
			)
		);
		if ( $pending_count > 20 ) {
			$issues[] = "{$pending_count} pending appointments (may indicate sync delays)";
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 40 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Appointment booking calendar issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/appointment-booking-calendar',
			);
		}

		return null;
	}
}
