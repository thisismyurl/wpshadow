<?php
/**
 * Booking Calendar Conflict Detection Diagnostic
 *
 * Booking conflicts not detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.619.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Booking Calendar Conflict Detection Diagnostic Class
 *
 * @since 1.619.0000
 */
class Diagnostic_BookingCalendarConflictDetection extends Diagnostic_Base {

	protected static $slug = 'booking-calendar-conflict-detection';
	protected static $title = 'Booking Calendar Conflict Detection';
	protected static $description = 'Booking conflicts not detected';
	protected static $family = 'functionality';

	public static function check() {
		// Check for common booking calendar plugins
		$has_booking = class_exists( 'WP_Booking_Calendar' ) ||
		               function_exists( 'wpbc' ) ||
		               defined( 'BOOKING_CALENDAR_VERSION' ) ||
		               get_option( 'booking_calendar_enabled', '' ) !== '';

		if ( ! $has_booking ) {
			return null;
		}

		global $wpdb;
		$issues = array();

		// Check 1: Conflict detection enabled
		$detect_conflicts = get_option( 'booking_conflict_detection', 'yes' );
		if ( 'no' === $detect_conflicts ) {
			$issues[] = __( 'Conflict detection disabled (double bookings)', 'wpshadow' );
		}

		// Check 2: Buffer time
		$buffer_minutes = get_option( 'booking_buffer_time', 0 );
		if ( $buffer_minutes === 0 ) {
			$issues[] = __( 'No buffer time (back-to-back bookings)', 'wpshadow' );
		}

		// Check 3: Check for actual conflicts
		$conflicts = $wpdb->get_var(
			"SELECT COUNT(*) FROM (
				SELECT booking_date, booking_time FROM {$wpdb->prefix}booking_dates
				GROUP BY booking_date, booking_time HAVING COUNT(*) > 1
			) AS duplicates"
		);

		if ( $conflicts > 0 ) {
			$issues[] = sprintf( __( '%d actual conflicts found', 'wpshadow' ), $conflicts );
		}

		// Check 4: Timezone handling
		$use_timezone = get_option( 'booking_use_timezone', 'no' );
		if ( 'no' === $use_timezone ) {
			$issues[] = __( 'No timezone handling (international issues)', 'wpshadow' );
		}

		// Check 5: Minimum booking window
		$min_window = get_option( 'booking_min_advance_hours', 0 );
		if ( $min_window === 0 ) {
			$issues[] = __( 'No minimum advance notice (last-minute chaos)', 'wpshadow' );
		}

		// Check 6: Overlapping validation
		$validate_overlap = get_option( 'booking_validate_overlap', 'yes' );
		if ( 'no' === $validate_overlap ) {
			$issues[] = __( 'No overlap validation (time conflicts)', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$threat_level = 65;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 77;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 71;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of booking calendar conflict issues */
				__( 'Booking calendar has %d conflict issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/booking-calendar-conflict-detection',
		);
	}
}
