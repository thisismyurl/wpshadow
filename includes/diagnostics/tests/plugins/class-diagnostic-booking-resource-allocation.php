<?php
/**
 * Booking Resource Allocation Diagnostic
 *
 * Booking resources not managed.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.629.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Booking Resource Allocation Diagnostic Class
 *
 * @since 1.629.0000
 */
class Diagnostic_BookingResourceAllocation extends Diagnostic_Base {

	protected static $slug = 'booking-resource-allocation';
	protected static $title = 'Booking Resource Allocation';
	protected static $description = 'Booking resources not managed';
	protected static $family = 'functionality';

	public static function check() {
		// Check for booking plugins with resource management
		$has_resources = class_exists( 'WC_Product_Booking_Resource' ) || defined( 'BOOKINGPRESS_VERSION' );
		
		if ( ! $has_resources ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Resources configured
		if ( class_exists( 'WC_Product_Booking_Resource' ) ) {
			$resource_count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s",
					'bookable_resource'
				)
			);
			
			if ( $resource_count === 0 ) {
				return null;
			}
			
			// Check 2: Resource assignments
			$unassigned_bookings = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->postmeta}
					 WHERE meta_key = %s AND (meta_value = '' OR meta_value IS NULL)",
					'_booking_resource_id'
				)
			);
			
			if ( $unassigned_bookings > 0 ) {
				$issues[] = sprintf( __( '%d bookings without resource assignment', 'wpshadow' ), $unassigned_bookings );
			}
		}
		
		// Check 3: Resource capacity limits
		$over_capacity = get_option( 'booking_resource_overbooked_count', 0 );
		if ( $over_capacity > 0 ) {
			$issues[] = sprintf( __( '%d resource capacity violations', 'wpshadow' ), $over_capacity );
		}
		
		// Check 4: Resource conflict detection
		$conflict_checking = get_option( 'booking_check_resource_conflicts', true );
		if ( ! $conflict_checking ) {
			$issues[] = __( 'Resource conflict checking disabled (double-booking risk)', 'wpshadow' );
		}
		
		// Check 5: Resource availability tracking
		$track_availability = get_option( 'booking_track_resource_availability', false );
		if ( ! $track_availability ) {
			$issues[] = __( 'Resource availability not tracked (inefficient allocation)', 'wpshadow' );
		}
		
		// Check 6: Resource utilization reporting
		$utilization_reports = get_option( 'booking_resource_utilization_enabled', false );
		if ( ! $utilization_reports ) {
			$issues[] = __( 'Resource utilization reports not enabled (no optimization data)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 60;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 72;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 66;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of resource allocation issues */
				__( 'Booking resource allocation has %d management issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/booking-resource-allocation',
		);
	}
}
