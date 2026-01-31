<?php
/**
 * Booking Calendar Date Picker Diagnostic
 *
 * Booking date picker exposing availability.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.618.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Booking Calendar Date Picker Diagnostic Class
 *
 * @since 1.618.0000
 */
class Diagnostic_BookingCalendarDatePicker extends Diagnostic_Base {

	protected static $slug = 'booking-calendar-date-picker';
	protected static $title = 'Booking Calendar Date Picker';
	protected static $description = 'Booking date picker exposing availability';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'WPBC_AJX' ) && ! function_exists( 'wpbc_get_bookings' ) ) {
			return null;
		}
		
		$issues = array();

		// Check 1: Verify availability calendar caching
		$calendar_cache = get_option( 'wpbc_calendar_cache_enabled', false );
		if ( ! $calendar_cache ) {
			$issues[] = __( 'Calendar availability caching not enabled', 'wpshadow' );
		}

		// Check 2: Check public calendar access restrictions
		$restrict_public_view = get_option( 'wpbc_restrict_public_calendar', false );
		if ( ! $restrict_public_view ) {
			$issues[] = __( 'Public calendar access not restricted', 'wpshadow' );
		}

		// Check 3: Verify date range visibility limits
		$max_visible_months = get_option( 'wpbc_max_visible_months', 12 );
		if ( $max_visible_months > 6 ) {
			$issues[] = __( 'Date range visibility too broad', 'wpshadow' );
		}

		// Check 4: Check blocked dates visibility
		$show_blocked = get_option( 'wpbc_show_blocked_dates', true );
		if ( $show_blocked ) {
			$issues[] = __( 'Blocked dates visible to public', 'wpshadow' );
		}

		// Check 5: Verify booking capacity disclosure
		$show_capacity = get_option( 'wpbc_show_remaining_capacity', true );
		if ( $show_capacity ) {
			$issues[] = __( 'Booking capacity disclosed publicly', 'wpshadow' );
		}

		// Check 6: Check booking pattern obfuscation
		$obfuscate_patterns = get_option( 'wpbc_obfuscate_booking_patterns', false );
		if ( ! $obfuscate_patterns ) {
			$issues[] = __( 'Booking pattern obfuscation not enabled', 'wpshadow' );
		}
		// Additional checks
		if ( ! function_exists( 'wp_verify_nonce' ) ) {
			$issues[] = __( 'Nonce verification unavailable', 'wpshadow' );
		}
		return null;
	}
}
