<?php
/**
 * Booking Multi-Day Events Diagnostic
 *
 * Multi-day booking calculations wrong.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.630.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Booking Multi-Day Events Diagnostic Class
 *
 * @since 1.630.0000
 */
class Diagnostic_BookingMultiDayEvents extends Diagnostic_Base {

	protected static $slug = 'booking-multi-day-events';
	protected static $title = 'Booking Multi-Day Events';
	protected static $description = 'Multi-day booking calculations wrong';
	protected static $family = 'functionality';

	public static function check() {
		$issues = array();

		// Check 1: Verify multi-day bookings enabled
		$multi_day = get_option( 'booking_multi_day_enabled', 0 );
		if ( ! $multi_day ) {
			$issues[] = 'Multi-day bookings not enabled';
		}

		// Check 2: Check for correct day calculation
		$calculate_nights = get_option( 'booking_calculate_nights', 0 );
		if ( ! $calculate_nights ) {
			$issues[] = 'Night-based calculation not enabled';
		}

		// Check 3: Verify timezone configuration
		$timezone = get_option( 'booking_timezone', '' );
		if ( empty( $timezone ) ) {
			$issues[] = 'Booking timezone not configured';
		}

		// Check 4: Check for checkout date validation
		$date_validation = get_option( 'booking_checkout_date_validation', 0 );
		if ( ! $date_validation ) {
			$issues[] = 'Checkout date validation not enabled';
		}

		// Check 5: Verify overlapping bookings prevention
		$overlap_prevention = get_option( 'booking_overlap_prevention', 0 );
		if ( ! $overlap_prevention ) {
			$issues[] = 'Overlap prevention not enabled';
		}

		// Check 6: Check for multi-day pricing rules
		$multi_day_pricing = get_option( 'booking_multi_day_pricing', 0 );
		if ( ! $multi_day_pricing ) {
			$issues[] = 'Multi-day pricing rules not configured';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 40;
			$threat_multiplier = 6;
			$max_threat = 70;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d multi-day booking issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/booking-multi-day-events',
			);
		}

		return null;
	}
}
