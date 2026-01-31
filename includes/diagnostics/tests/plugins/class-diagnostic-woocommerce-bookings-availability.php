<?php
/**
 * Woocommerce Bookings Availability Diagnostic
 *
 * Woocommerce Bookings Availability issues detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.649.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woocommerce Bookings Availability Diagnostic Class
 *
 * @since 1.649.0000
 */
class Diagnostic_WoocommerceBookingsAvailability extends Diagnostic_Base {

	protected static $slug = 'woocommerce-bookings-availability';
	protected static $title = 'Woocommerce Bookings Availability';
	protected static $description = 'Woocommerce Bookings Availability issues detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'WooCommerce' ) && ! class_exists( 'WC_Bookings' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Default availability set
		$default_availability = get_option( 'wc_bookings_default_availability', '' );
		if ( empty( $default_availability ) ) {
			$issues[] = 'Default availability not configured';
		}

		// Check 2: Availability range configured
		$availability_range = get_option( 'wc_bookings_availability_range', '' );
		if ( empty( $availability_range ) ) {
			$issues[] = 'Availability range not configured';
		}

		// Check 3: Buffer period configured
		$buffer_period = absint( get_option( 'wc_bookings_buffer_period', 0 ) );
		if ( $buffer_period <= 0 ) {
			$issues[] = 'Buffer period not configured';
		}

		// Check 4: Max bookings per slot
		$max_bookings = absint( get_option( 'wc_bookings_max_bookings_per_slot', 0 ) );
		if ( $max_bookings <= 0 ) {
			$issues[] = 'Max bookings per slot not configured';
		}

		// Check 5: Confirmation requirement
		$requires_confirmation = get_option( 'wc_bookings_requires_confirmation', 0 );
		if ( ! $requires_confirmation ) {
			$issues[] = 'Bookings do not require confirmation';
		}

		// Check 6: Timezone handling
		$timezone_handling = get_option( 'wc_bookings_timezone_handling', '' );
		if ( empty( $timezone_handling ) ) {
			$issues[] = 'Timezone handling not configured';
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
					'Found %d booking availability issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/woocommerce-bookings-availability',
			);
		}

		return null;
	}
}
