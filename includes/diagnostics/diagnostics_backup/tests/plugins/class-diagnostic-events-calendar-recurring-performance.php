<?php
/**
 * The Events Calendar Recurring Events Diagnostic
 *
 * Recurring events slow down database.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.267.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Events Calendar Recurring Events Diagnostic Class
 *
 * @since 1.267.0000
 */
class Diagnostic_EventsCalendarRecurringPerformance extends Diagnostic_Base {

	protected static $slug = 'events-calendar-recurring-performance';
	protected static $title = 'The Events Calendar Recurring Events';
	protected static $description = 'Recurring events slow down database';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'Tribe__Events__Main' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Recurring events exist
		$recurring_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s",
				'_EventRecurrence'
			)
		);
		
		if ( $recurring_count === 0 ) {
			return null;
		}
		
		// Check 2: Occurrence generation limit
		$max_occurrences = tribe_get_option( 'recurrence_max_months_after', 24 );
		if ( $max_occurrences > 36 ) {
			$issues[] = sprintf( __( 'Generating occurrences %d months ahead (high database load)', 'wpshadow' ), $max_occurrences );
		}
		
		// Check 3: Total recurring occurrences
		$occurrence_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'tribe_events' AND post_parent > 0"
		);
		
		if ( $occurrence_count > 5000 ) {
			$issues[] = sprintf( __( '%d recurring event instances (query performance impact)', 'wpshadow' ), $occurrence_count );
		}
		
		// Check 4: Recurrence cron job
		$cron_scheduled = wp_next_scheduled( 'tribe_recurring_event_instances_cron' );
		if ( ! $cron_scheduled ) {
			$issues[] = __( 'Recurring event generation cron not scheduled', 'wpshadow' );
		}
		
		// Check 5: Event date indexes
		$has_date_index = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM information_schema.statistics
				 WHERE table_schema = %s AND table_name = %s
				 AND index_name LIKE %s",
				DB_NAME,
				$wpdb->postmeta,
				'%EventStartDate%'
			)
		);
		
		if ( $has_date_index === 0 && $occurrence_count > 1000 ) {
			$issues[] = __( 'Missing date index on postmeta (slow recurring event queries)', 'wpshadow' );
		}
		
		// Check 6: Occurrence cache
		$cache_enabled = tribe_get_option( 'recurring_event_instances_cache', false );
		if ( ! $cache_enabled && $recurring_count > 50 ) {
			$issues[] = __( 'Recurring event instance caching not enabled', 'wpshadow' );
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
				__( 'Events Calendar recurring events have %d performance issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/events-calendar-recurring-performance',
		);
	}
}
