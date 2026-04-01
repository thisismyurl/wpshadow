<?php
declare(strict_types=1);

namespace WPShadow\Guardian;

/**
 * Cache Invalidation Analyzer
 *
 * Monitors cache invalidation frequency to detect excessive cache clearing
 * that can impact performance.
 *
 * Philosophy: Show value (#9) - Optimize caching for better performance.
 *
 * @package WPShadow
 * @subpackage Guardian
 * @since 0.6093.1200
 */
class Cache_Invalidation_Analyzer {

	/**
	 * Initialize cache monitoring
	 *
	 * @return void
	 */
	public static function init(): void {
		// Track cache clearing events
		add_action( 'clean_post_cache', array( __CLASS__, 'track_post_cache_clear' ) );
		add_action( 'clean_term_cache', array( __CLASS__, 'track_term_cache_clear' ) );
		add_action( 'clean_user_cache', array( __CLASS__, 'track_user_cache_clear' ) );
		add_action( 'clean_comment_cache', array( __CLASS__, 'track_comment_cache_clear' ) );

		// Track object cache flushes
		add_action( 'wp_cache_flush_group', array( __CLASS__, 'track_cache_group_flush' ), 10, 1 );

		// Track transient deletions
		add_action( 'deleted_transient', array( __CLASS__, 'track_transient_deletion' ), 10, 1 );

		// Run hourly analysis
		if ( ! wp_next_scheduled( 'wpshadow_analyze_cache_invalidation' ) ) {
			wp_schedule_event( time(), 'hourly', 'wpshadow_analyze_cache_invalidation' );
		}
		add_action( 'wpshadow_analyze_cache_invalidation', array( __CLASS__, 'analyze' ) );
	}

	/**
	 * Track post cache clearing
	 *
	 * @param int $post_id Post ID
	 * @return void
	 */
	public static function track_post_cache_clear( int $post_id ): void {
		self::record_cache_event( 'post_cache', $post_id );
	}

	/**
	 * Track term cache clearing
	 *
	 * @param array $ids Term IDs
	 * @return void
	 */
	public static function track_term_cache_clear( array $ids ): void {
		self::record_cache_event( 'term_cache', count( $ids ) );
	}

	/**
	 * Track user cache clearing
	 *
	 * @param int $user_id User ID
	 * @return void
	 */
	public static function track_user_cache_clear( int $user_id ): void {
		self::record_cache_event( 'user_cache', $user_id );
	}

	/**
	 * Track comment cache clearing
	 *
	 * @param int $comment_id Comment ID
	 * @return void
	 */
	public static function track_comment_cache_clear( int $comment_id ): void {
		self::record_cache_event( 'comment_cache', $comment_id );
	}

	/**
	 * Track cache group flush
	 *
	 * @param string $group Cache group
	 * @return void
	 */
	public static function track_cache_group_flush( string $group ): void {
		self::record_cache_event( 'group_flush', 0, $group );
	}

	/**
	 * Track transient deletion
	 *
	 * @param string $transient Transient name
	 * @return void
	 */
	public static function track_transient_deletion( string $transient ): void {
		self::record_cache_event( 'transient_delete', 0, $transient );
	}

	/**
	 * Record cache event
	 *
	 * @param string $type Event type
	 * @param int $object_id Object ID
	 * @param string $extra Extra data
	 * @return void
	 */
	private static function record_cache_event( string $type, int $object_id = 0, string $extra = '' ): void {
		$events = \WPShadow\Core\Cache_Manager::get( 'cache_invalidation_events', 'wpshadow_monitoring' );
		if ( ! is_array( $events ) ) {
			$events = array(
				'hourly' => array(),
				'daily'  => array(),
			);
		}

		$event = array(
			'type'      => $type,
			'timestamp' => time(),
			'object_id' => $object_id,
			'extra'     => $extra,
		);

		// Add to hourly tracking
		$events['hourly'][] = $event;

		// Add to daily tracking
		$events['daily'][] = $event;

		// Keep only last hour for hourly
		$one_hour_ago     = time() - HOUR_IN_SECONDS;
		$events['hourly'] = array_filter(
			$events['hourly'],
			function ( $e ) use ( $one_hour_ago ) {
				return $e['timestamp'] > $one_hour_ago;
			}
		);

		// Keep only last 24 hours for daily
		$one_day_ago     = time() - DAY_IN_SECONDS;
		$events['daily'] = array_filter(
			$events['daily'],
			function ( $e ) use ( $one_day_ago ) {
				return $e['timestamp'] > $one_day_ago;
			}
		);

		\WPShadow\Core\Cache_Manager::set( 'cache_invalidation_events', $events, DAY_IN_SECONDS , 'wpshadow_monitoring');
	}

	/**
	 * Analyze cache invalidation patterns
	 *
	 * @return array Analysis results
	 */
	public static function analyze(): array {
		$events = \WPShadow\Core\Cache_Manager::get( 'cache_invalidation_events', 'wpshadow_monitoring' );

		$results = array(
			'hourly_count'     => 0,
			'daily_count'      => 0,
			'events_by_type'   => array(),
			'is_excessive'     => false,
			'top_invalidators' => array(),
		);

		if ( ! is_array( $events ) ) {
			\WPShadow\Core\Cache_Manager::set( 'cache_invalidation_frequency', $results, HOUR_IN_SECONDS , 'wpshadow_monitoring');
			return $results;
		}

		// Count events
		$results['hourly_count'] = count( $events['hourly'] ?? array() );
		$results['daily_count']  = count( $events['daily'] ?? array() );

		// Categorize by type
		foreach ( ( $events['daily'] ?? array() ) as $event ) {
			$type = $event['type'];
			if ( ! isset( $results['events_by_type'][ $type ] ) ) {
				$results['events_by_type'][ $type ] = 0;
			}
			++$results['events_by_type'][ $type ];
		}

		// Determine if excessive (>100 per hour or >1000 per day)
		$results['is_excessive'] = $results['hourly_count'] > 100 || $results['daily_count'] > 1000;

		// Find top invalidators
		if ( ! empty( $results['events_by_type'] ) ) {
			arsort( $results['events_by_type'] );
			$results['top_invalidators'] = array_slice( $results['events_by_type'], 0, 5, true );
		}

		// Set cache for diagnostic
		\WPShadow\Core\Cache_Manager::set( 'cache_invalidation_frequency', $results, HOUR_IN_SECONDS , 'wpshadow_monitoring');

		return $results;
	}

	/**
	 * Get summary
	 *
	 * @return array Summary data
	 */
	public static function get_summary(): array {
		$results = \WPShadow\Core\Cache_Manager::get( 'cache_invalidation_frequency', 'wpshadow_monitoring' );
		return is_array( $results ) ? $results : array(
			'hourly_count' => 0,
			'daily_count'  => 0,
			'is_excessive' => false,
		);
	}

	/**
	 * Clear cached data
	 *
	 * @return void
	 */
	public static function clear_cache(): void {
		\WPShadow\Core\Cache_Manager::delete( 'cache_invalidation_events', 'wpshadow_monitoring' );
		\WPShadow\Core\Cache_Manager::delete( 'cache_invalidation_frequency', 'wpshadow_monitoring' );
	}
}
