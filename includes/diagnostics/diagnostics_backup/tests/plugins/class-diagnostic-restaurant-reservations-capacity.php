<?php
/**
 * Restaurant Reservations Capacity Diagnostic
 *
 * Restaurant capacity checks bypassed.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.601.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Restaurant Reservations Capacity Diagnostic Class
 *
 * @since 1.601.0000
 */
class Diagnostic_RestaurantReservationsCapacity extends Diagnostic_Base {

	protected static $slug = 'restaurant-reservations-capacity';
	protected static $title = 'Restaurant Reservations Capacity';
	protected static $description = 'Restaurant capacity checks bypassed';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'rtbInit' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Capacity checks enabled
		$capacity_check = get_option( 'rtb_capacity_check_enabled', 0 );
		if ( ! $capacity_check ) {
			$issues[] = 'Capacity checks not enabled';
		}

		// Check 2: Default capacity set
		$default_capacity = absint( get_option( 'rtb_default_capacity', 0 ) );
		if ( $default_capacity <= 0 ) {
			$issues[] = 'Default restaurant capacity not configured';
		}

		// Check 3: Seating time enforcement
		$seating_time = absint( get_option( 'rtb_seating_time_minutes', 0 ) );
		if ( $seating_time <= 0 ) {
			$issues[] = 'Seating time not configured';
		}

		// Check 4: Reservation time slots
		$slots_enabled = get_option( 'rtb_time_slots_enabled', 0 );
		if ( ! $slots_enabled ) {
			$issues[] = 'Reservation time slots not enabled';
		}

		// Check 5: Overbooking prevention
		$overbook_prevent = get_option( 'rtb_prevent_overbooking', 0 );
		if ( ! $overbook_prevent ) {
			$issues[] = 'Overbooking prevention not enabled';
		}

		// Check 6: Walk-in limit enforcement
		$walkin_limit = get_option( 'rtb_walkin_limit_enforcement', 0 );
		if ( ! $walkin_limit ) {
			$issues[] = 'Walk-in limit enforcement not enabled';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 50;
			$threat_multiplier = 6;
			$max_threat = 80;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d reservation capacity issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/restaurant-reservations-capacity',
			);
		}

		return null;
	}
}
