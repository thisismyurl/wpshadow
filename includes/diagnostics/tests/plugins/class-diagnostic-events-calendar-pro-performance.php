<?php
/**
 * Events Calendar Pro Performance Diagnostic
 *
 * Events Calendar Pro slowing site.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.573.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Events Calendar Pro Performance Diagnostic Class
 *
 * @since 1.573.0000
 */
class Diagnostic_EventsCalendarProPerformance extends Diagnostic_Base {

	protected static $slug = 'events-calendar-pro-performance';
	protected static $title = 'Events Calendar Pro Performance';
	protected static $description = 'Events Calendar Pro slowing site';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'Tribe__Events__Main' ) ) {
			return null;
		}

		$issues = array();

		// Check number of events in database
		global $wpdb;
		$event_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s",
				'tribe_events'
			)
		);

		if ( $event_count > 1000 ) {
			$issues[] = "large event database ({$event_count} events, consider archiving old events)";
		}

		// Check for recurring event expansion
		$recurring_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s",
				'_EventRecurrence'
			)
		);

		if ( $recurring_count > 100 ) {
			$issues[] = "excessive recurring events ({$recurring_count} patterns, impacts calendar generation)";
		}

		// Check if caching is enabled
		$cache_enabled = get_option( 'tribe_events_calendar_options', array() );
		if ( isset( $cache_enabled['tribeDisableTribeBar'] ) && false === $cache_enabled['tribeDisableTribeBar'] ) {
			$issues[] = 'tribe bar enabled (additional queries on every page load)';
		}

		// Check for past event cleanup
		$past_events = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} p
				 JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
				 WHERE p.post_type = %s AND pm.meta_key = %s
				 AND pm.meta_value < %s",
				'tribe_events',
				'_EventEndDate',
				date( 'Y-m-d', strtotime( '-1 year' ) )
			)
		);

		if ( $past_events > 500 ) {
			$issues[] = "many old events ({$past_events} from over a year ago)";
		}

		// Check for venue/organizer meta optimization
		$venue_queries = get_option( 'tribe_events_venue_queries_disabled', '0' );
		if ( '0' === $venue_queries && $event_count > 500 ) {
			$issues[] = 'venue queries not optimized for large event database';
		}

		// Check for AJAX-enabled calendar views
		$ajax_enabled = get_option( 'tribe_events_ajax_enabled', '1' );
		if ( '0' === $ajax_enabled ) {
			$issues[] = 'AJAX calendar navigation disabled (full page reloads)';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 75, 45 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Events Calendar Pro performance issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/events-calendar-pro-performance',
			);
		}

		return null;
	}
}
