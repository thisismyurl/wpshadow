<?php
declare(strict_types=1);

namespace WPShadow\Reporting;

use WPShadow\Core\KPI_Tracker;

/**
 * Event Logger for Reporting System
 * 
 * Logs all Guardian and auto-fix events for reporting.
 * Tracks: diagnostics, treatments, auto-fixes, recoveries, anomalies.
 * 
 * Features:
 * - Event capture and storage
 * - Event filtering and search
 * - Event statistics
 * - Retention management
 * - Event categorization
 * 
 * Philosophy: Transparency through comprehensive logging.
 */
class Event_Logger {
	
	const MAX_EVENTS_STORED = 10000;
	const DEFAULT_RETENTION_DAYS = 90;
	
	/**
	 * Log an event
	 * 
	 * @param string $event_type Type of event
	 * @param string $category Event category
	 * @param array  $data Event data
	 * 
	 * @return string Event ID
	 */
	public static function log_event( string $event_type, string $category, array $data = [] ): string {
		$event_id = 'event_' . time() . '_' . wp_generate_password( 8, false );
		
		$event = [
			'id'        => $event_id,
			'timestamp' => current_time( 'mysql' ),
			'type'      => sanitize_key( $event_type ),
			'category'  => sanitize_key( $category ),
			'user_id'   => get_current_user_id(),
			'data'      => self::sanitize_data( $data ),
		];
		
		// Store in database
		self::store_event( $event );
		
		// Update statistics
		self::update_statistics( $category, $event_type );
		
		return $event_id;
	}
	
	/**
	 * Get events by filter
	 * 
	 * @param array $filter Filters to apply
	 * @param int   $limit Results limit
	 * 
	 * @return array Events matching filter
	 */
	public static function get_events( array $filter = [], int $limit = 100 ): array {
		$events = get_option( 'wpshadow_events', [] );
		
		if ( empty( $events ) ) {
			return [];
		}
		
		// Apply filters
		if ( isset( $filter['category'] ) ) {
			$events = array_filter(
				$events,
				fn( $e ) => $e['category'] === $filter['category']
			);
		}
		
		if ( isset( $filter['type'] ) ) {
			$events = array_filter(
				$events,
				fn( $e ) => $e['type'] === $filter['type']
			);
		}
		
		if ( isset( $filter['user_id'] ) ) {
			$events = array_filter(
				$events,
				fn( $e ) => $e['user_id'] === intval( $filter['user_id'] )
			);
		}
		
		if ( isset( $filter['start_date'] ) && isset( $filter['end_date'] ) ) {
			$start = strtotime( $filter['start_date'] );
			$end   = strtotime( $filter['end_date'] );
			
			$events = array_filter(
				$events,
				fn( $e ) => strtotime( $e['timestamp'] ) >= $start && strtotime( $e['timestamp'] ) <= $end
			);
		}
		
		// Return most recent first, limited
		$events = array_reverse( array_values( $events ) );
		return array_slice( $events, 0, $limit );
	}
	
	/**
	 * Get event by ID
	 * 
	 * @param string $event_id Event ID
	 * 
	 * @return array|null Event or null
	 */
	public static function get_event( string $event_id ): ?array {
		$event_id = sanitize_key( $event_id );
		$events   = get_option( 'wpshadow_events', [] );
		
		foreach ( $events as $event ) {
			if ( $event['id'] === $event_id ) {
				return $event;
			}
		}
		
		return null;
	}
	
	/**
	 * Get event statistics
	 * 
	 * Returns counts by category and type.
	 * 
	 * @param string $category Optional filter by category
	 * 
	 * @return array Statistics
	 */
	public static function get_statistics( string $category = '' ): array {
		$stats = get_option( 'wpshadow_event_stats', [] );
		
		if ( empty( $category ) ) {
			return $stats;
		}
		
		return $stats[ $category ] ?? [];
	}
	
	/**
	 * Get timeline data for visualization
	 * 
	 * Groups events by hour/day for charts.
	 * 
	 * @param string $group_by 'hour' or 'day'
	 * @param int    $periods Number of periods
	 * 
	 * @return array Timeline data
	 */
	public static function get_timeline( string $group_by = 'day', int $periods = 30 ): array {
		$events = get_option( 'wpshadow_events', [] );
		$timeline = [];
		
		$format = $group_by === 'hour' ? 'Y-m-d H:00' : 'Y-m-d';
		
		foreach ( $events as $event ) {
			$period = date( $format, strtotime( $event['timestamp'] ) );
			
			if ( ! isset( $timeline[ $period ] ) ) {
				$timeline[ $period ] = [];
			}
			
			$category = $event['category'];
			$timeline[ $period ][ $category ] = ( $timeline[ $period ][ $category ] ?? 0 ) + 1;
		}
		
		// Keep only recent periods
		$timeline = array_slice( array_reverse( $timeline, true ), 0, $periods, true );
		
		return array_reverse( $timeline, true );
	}
	
	/**
	 * Search events
	 * 
	 * @param string $query Search query
	 * @param int    $limit Results limit
	 * 
	 * @return array Matching events
	 */
	public static function search_events( string $query, int $limit = 50 ): array {
		$query  = sanitize_text_field( $query );
		$events = get_option( 'wpshadow_events', [] );
		$results = [];
		
		foreach ( $events as $event ) {
			// Search in data values
			$data_str = implode( ' ', array_values( (array) $event['data'] ) );
			
			if ( stripos( $event['type'], $query ) !== false ||
			     stripos( $event['category'], $query ) !== false ||
			     stripos( $data_str, $query ) !== false ) {
				$results[] = $event;
			}
			
			if ( count( $results ) >= $limit ) {
				break;
			}
		}
		
		return array_reverse( $results );
	}
	
	/**
	 * Clear events by filter
	 * 
	 * @param array $filter Filters to apply
	 */
	public static function clear_events( array $filter = [] ): void {
		if ( empty( $filter ) ) {
			// Clear all events (dangerous)
			delete_option( 'wpshadow_events' );
			delete_option( 'wpshadow_event_stats' );
			return;
		}
		
		$events = get_option( 'wpshadow_events', [] );
		
		// Apply inverse filter (keep events that DON'T match)
		if ( isset( $filter['older_than_days'] ) ) {
			$cutoff = time() - ( $filter['older_than_days'] * DAY_IN_SECONDS );
			
			$events = array_filter(
				$events,
				fn( $e ) => strtotime( $e['timestamp'] ) > $cutoff
			);
		}
		
		update_option( 'wpshadow_events', array_values( $events ) );
		self::rebuild_statistics();
	}
	
	/**
	 * Cleanup old events
	 * 
	 * Keeps only recent events to manage storage.
	 * 
	 * @param int $retention_days Retention period
	 */
	public static function cleanup_old_events( int $retention_days = self::DEFAULT_RETENTION_DAYS ): void {
		self::clear_events( ['older_than_days' => $retention_days] );
		
		// Also trim if too many stored
		$events = get_option( 'wpshadow_events', [] );
		if ( count( $events ) > self::MAX_EVENTS_STORED ) {
			$events = array_slice( $events, -self::MAX_EVENTS_STORED );
			update_option( 'wpshadow_events', $events );
		}
	}
	
	/**
	 * Store event in database
	 * 
	 * @param array $event Event to store
	 */
	private static function store_event( array $event ): void {
		$events = get_option( 'wpshadow_events', [] );
		$events[] = $event;
		
		update_option( 'wpshadow_events', $events );
	}
	
	/**
	 * Update event statistics
	 * 
	 * @param string $category Category
	 * @param string $type Type
	 */
	private static function update_statistics( string $category, string $type ): void {
		$stats = get_option( 'wpshadow_event_stats', [] );
		
		if ( ! isset( $stats[ $category ] ) ) {
			$stats[ $category ] = [];
		}
		
		$stats[ $category ][ $type ] = ( $stats[ $category ][ $type ] ?? 0 ) + 1;
		
		update_option( 'wpshadow_event_stats', $stats );
	}
	
	/**
	 * Rebuild statistics from events
	 * 
	 * Called after events are cleared.
	 */
	private static function rebuild_statistics(): void {
		$events = get_option( 'wpshadow_events', [] );
		$stats  = [];
		
		foreach ( $events as $event ) {
			$category = $event['category'];
			$type     = $event['type'];
			
			if ( ! isset( $stats[ $category ] ) ) {
				$stats[ $category ] = [];
			}
			
			$stats[ $category ][ $type ] = ( $stats[ $category ][ $type ] ?? 0 ) + 1;
		}
		
		update_option( 'wpshadow_event_stats', $stats );
	}
	
	/**
	 * Sanitize event data
	 * 
	 * @param array $data Data to sanitize
	 * 
	 * @return array Sanitized data
	 */
	private static function sanitize_data( array $data ): array {
		$sanitized = [];
		
		foreach ( $data as $key => $value ) {
			$key = sanitize_key( $key );
			
			if ( is_string( $value ) ) {
				$value = sanitize_text_field( $value );
			} elseif ( is_array( $value ) ) {
				$value = self::sanitize_data( $value );
			} elseif ( ! is_numeric( $value ) && ! is_bool( $value ) && ! is_null( $value ) ) {
				$value = sanitize_text_field( (string) $value );
			}
			
			$sanitized[ $key ] = $value;
		}
		
		return $sanitized;
	}
	
	/**
	 * Get summary for dashboard
	 * 
	 * @return array Summary statistics
	 */
	public static function get_summary(): array {
		$events = get_option( 'wpshadow_events', [] );
		$stats  = get_option( 'wpshadow_event_stats', [] );
		
		return [
			'total_events'     => count( $events ),
			'events_today'     => count( self::get_events( [], PHP_INT_MAX ) ), // Filter in get_events
			'categories'       => count( $stats ),
			'by_category'      => $stats,
		];
	}
}
