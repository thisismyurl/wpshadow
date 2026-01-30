<?php
/**
 * The Events Calendar iCal Feeds Diagnostic
 *
 * iCal feed generation not cached.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.269.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Events Calendar iCal Feeds Diagnostic Class
 *
 * @since 1.269.0000
 */
class Diagnostic_EventsCalendarIcalFeed extends Diagnostic_Base {

	protected static $slug = 'events-calendar-ical-feed';
	protected static $title = 'The Events Calendar iCal Feeds';
	protected static $description = 'iCal feed generation not cached';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'Tribe__Events__Main' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: iCal feed caching enabled
		$ical_cache_enabled = get_option( 'tribe_events_ical_cache_enabled', false );
		if ( ! $ical_cache_enabled ) {
			$issues[] = __( 'iCal feed caching not enabled', 'wpshadow' );
		}
		
		// Check 2: Cache duration setting
		$cache_duration = get_option( 'tribe_events_ical_cache_duration', 0 );
		if ( $cache_duration < 3600 ) {
			$issues[] = sprintf( __( 'iCal cache duration too short: %d seconds (recommended: 3600+)', 'wpshadow' ), $cache_duration );
		}
		
		// Check 3: Check for large event counts
		global $wpdb;
		$event_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = %s",
				'tribe_events',
				'publish'
			)
		);
		
		if ( $event_count > 100 && ! $ical_cache_enabled ) {
			$issues[] = sprintf( __( '%d events without iCal caching (high load risk)', 'wpshadow' ), $event_count );
		}
		
		// Check 4: Transient cache usage
		$ical_transients = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s",
				'_transient_tribe_ical_%'
			)
		);
		
		if ( $ical_transients === 0 && $event_count > 20 ) {
			$issues[] = __( 'No iCal transients found despite multiple events', 'wpshadow' );
		}
		
		// Check 5: Feed generation timing
		$last_generation = get_option( 'tribe_events_ical_last_generation', 0 );
		$time_since = time() - $last_generation;
		if ( $last_generation > 0 && $time_since < 60 ) {
			$issues[] = sprintf( __( 'iCal feed regenerated %d seconds ago (may indicate cache issues)', 'wpshadow' ), $time_since );
		}
		
		// Check 6: Object caching support
		if ( ! wp_using_ext_object_cache() && $event_count > 100 ) {
			$issues[] = __( 'No external object cache for large event calendar', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 35;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 45;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 40;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of performance issues */
				__( 'The Events Calendar iCal feed has %d performance issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => true,
			'kb_link'     => 'https://wpshadow.com/kb/events-calendar-ical-feed',
		);
	}
}
