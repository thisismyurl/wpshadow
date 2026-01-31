<?php
/**
 * BookingPress Calendar Performance Diagnostic
 *
 * BookingPress calendar slowing page loads.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.461.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BookingPress Calendar Performance Diagnostic Class
 *
 * @since 1.461.0000
 */
class Diagnostic_BookingpressCalendarPerformance extends Diagnostic_Base {

	protected static $slug = 'bookingpress-calendar-performance';
	protected static $title = 'BookingPress Calendar Performance';
	protected static $description = 'BookingPress calendar slowing page loads';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'BOOKINGPRESS_VERSION' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Calendar AJAX loading
		$ajax_calendar = get_option( 'bookingpress_ajax_calendar', false );
		if ( ! $ajax_calendar ) {
			$issues[] = __( 'Calendar not loading via AJAX (slow initial page load)', 'wpshadow' );
		}
		
		// Check 2: Date range optimization
		$date_range = get_option( 'bookingpress_calendar_date_range', 365 );
		if ( $date_range > 180 ) {
			$issues[] = sprintf( __( 'Calendar date range: %d days (recommend 90-180)', 'wpshadow' ), $date_range );
		}
		
		// Check 3: Availability cache
		$cache_availability = get_option( 'bookingpress_cache_availability', false );
		if ( ! $cache_availability ) {
			$issues[] = __( 'Availability caching not enabled', 'wpshadow' );
		}
		
		// Check 4: Calendar rendering mode
		$render_mode = get_option( 'bookingpress_calendar_render_mode', 'full' );
		if ( $render_mode === 'full' ) {
			$issues[] = __( 'Full calendar rendering (consider on-demand mode)', 'wpshadow' );
		}
		
		// Check 5: Appointment query optimization
		$appointment_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}bookingpress_appointments WHERE bookingpress_appointment_status = '1'"
		);
		
		if ( $appointment_count > 1000 && ! $cache_availability ) {
			$issues[] = sprintf( __( '%d appointments without availability caching (slow calendar loads)', 'wpshadow' ), $appointment_count );
		}
		
		// Check 6: Database indexes
		$has_indexes = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM information_schema.statistics 
				 WHERE table_schema = %s 
				 AND table_name = %s 
				 AND index_name LIKE %s",
				DB_NAME,
				$wpdb->prefix . 'bookingpress_appointments',
				'%date%'
			)
		);
		
		if ( $has_indexes === 0 && $appointment_count > 500 ) {
			$issues[] = __( 'Missing date indexes on appointments table', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 5 ) {
			$threat_level = 65;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 58;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of performance issues */
				__( 'BookingPress calendar has %d performance issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => true,
			'kb_link'     => 'https://wpshadow.com/kb/bookingpress-calendar-performance',
		);
	}
}
