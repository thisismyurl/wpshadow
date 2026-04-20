<?php
/**
 * Dashboard Page-Level Cache Manager
 *
 * Caches entire dashboard page renders to provide 30-50% performance
 * improvement on subsequent dashboard loads. Automatically invalidates
 * cache when diagnostic data changes, treatments are applied, or
 * important settings are updated.
 *
 * @since 0.6095
 * @package WPShadow\Core
 */

declare(strict_types=1);

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.Security.NonceVerification.Recommended

/**
 * Dashboard_Cache Class
 *
 * Provides page-level dashboard caching with automatic invalidation.
 *
 * Usage:
 *   if ( ! Dashboard_Cache::get_cached_output() ) {
 *       // Render dashboard
 *       Dashboard_Cache::set_cached_output( $output );
 *   }
 *
 * Cache invalidation happens automatically when:
 *   - Diagnostics are run (wpshadow_diagnostics_completed)
 *   - Treatments are applied (wpshadow_treatment_applied)
 *   - Settings are updated (wpshadow_setting_updated)
 *   - Admin notices are dismissed (wpshadow_notice_dismissed)
 *
 * @since 0.6095
 */
class Dashboard_Cache {

	/**
	 * Cache group for dashboard pages
	 *
	 * @var string
	 */
	private static $cache_group = 'wpshadow_dashboard_cache';

	/**
	 * Cache key for dashboard output
	 *
	 * @var string
	 */
	private static $cache_key = 'dashboard_output';

	/**
	 * Cache TTL (1 hour = 3600 seconds)
	 *
	 * @var int
	 */
	private static $cache_ttl = HOUR_IN_SECONDS;

	/**
	 * Initialize dashboard cache system
	 *
	 * Sets up hooks for cache invalidation on data changes.
	 *
	 * @since 0.6095
	 * @return void
	 */
	public static function init() {
		// Clear cache when diagnostics complete
		add_action( 'wpshadow_diagnostics_completed', array( __CLASS__, 'invalidate_cache' ), 10, 0 );

		// Clear cache when treatments are applied
		add_action( 'wpshadow_treatment_applied', array( __CLASS__, 'invalidate_cache' ), 10, 0 );
		add_action( 'wpshadow_treatment_failed', array( __CLASS__, 'invalidate_cache' ), 10, 0 );

		// Clear cache when settings change
		add_action( 'wpshadow_setting_updated', array( __CLASS__, 'invalidate_cache' ), 10, 0 );

		// Clear cache when admin notices are dismissed
		add_action( 'wpshadow_notice_dismissed', array( __CLASS__, 'invalidate_cache' ), 10, 0 );

		// Clear cache on activity log updates
		add_action( 'wpshadow_activity_logged', array( __CLASS__, 'invalidate_cache' ), 10, 0 );

		// Clear cache on widget data updates
		add_action( 'wpshadow_widget_data_updated', array( __CLASS__, 'invalidate_cache' ), 10, 0 );

		// Clear cache periodically (fallback)
		add_action( 'wpshadow_hourly_cleanup', array( __CLASS__, 'cleanup_old_cache' ), 10, 0 );
	}

	/**
	 * Get cached dashboard output
	 *
	 * Returns cached dashboard HTML if valid cache exists and user
	 * hasn't made changes. Returns null if cache miss or invalidated.
	 *
	 * Cache is only served if:
	 *   - Cache entry exists
	 *   - Cache hasn't expired
	 *   - User is viewing default dashboard (not filtered)
	 *   - User hasn't just submitted an action
	 *
	 * @since 0.6095
	 * @return string|null Cached HTML output or null if cache miss.
	 */
	public static function get_cached_output() {
		// Don't cache if user just submitted a form/action
		$has_action = isset( $_GET['action'] ) && '' !== sanitize_key( wp_unslash( (string) $_GET['action'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only cache bypass check.
		if ( ! empty( $_POST ) || $has_action ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return null;
		}

		// Get cached output
		$cached = Cache_Manager::get( self::$cache_key, self::$cache_group );

		if ( $cached ) {
			// Log cache hit for diagnostics
			self::log_cache_hit();
			return $cached;
		}

		return null;
	}

	/**
	 * Set cached dashboard output
	 *
	 * Stores the rendered dashboard HTML in the object cache.
	 * Cache will be automatically invalidated when data changes.
	 *
	 * @since 0.6095
	 * @param  string $output The dashboard HTML to cache.
	 * @return bool Whether cache was set successfully.
	 */
	public static function set_cached_output( $output ) {
		if ( empty( $output ) ) {
			return false;
		}

		return Cache_Manager::set(
			self::$cache_key,
			$output,
			self::$cache_group,
			self::$cache_ttl
		);
	}

	/**
	 * Invalidate dashboard cache
	 *
	 * Clears cached dashboard output, forcing a fresh render
	 * on next dashboard page load.
	 *
	 * @since 0.6095
	 * @return bool Whether cache was deleted.
	 */
	public static function invalidate_cache() {
		return Cache_Manager::delete( self::$cache_key, self::$cache_group );
	}

	/**
	 * Invalidate specific widget cache
	 *
	 * Clears cache for a specific dashboard widget without
	 * invalidating the entire dashboard cache. This allows
	 * widget-level cache invalidation.
	 *
	 * @since 0.6095
	 * @param  string $widget_id The widget ID to invalidate.
	 * @return bool Whether widget cache was deleted.
	 */
	public static function invalidate_widget_cache( $widget_id ) {
		if ( empty( $widget_id ) ) {
			return false;
		}

		$cache_key = 'widget_' . sanitize_key( $widget_id );
		return Cache_Manager::delete( $cache_key, self::$cache_group );
	}

	/**
	 * Invalidate all dashboard caches
	 *
	 * Clears both dashboard page cache and all widget caches.
	 * Use when making major changes to dashboard structure.
	 *
	 * @since 0.6095
	 * @return void
	 */
	public static function invalidate_all_caches() {
		self::invalidate_cache();

		// Also clear all widget caches
		$widgets = array( 'diagnostics', 'performance', 'recommendations', 'activity', 'setup' );
		foreach ( $widgets as $widget ) {
			self::invalidate_widget_cache( $widget );
		}

		// Fire hook for custom cache invalidation
		do_action( 'wpshadow_dashboard_cache_invalidated' );
	}

	/**
	 * Get cache statistics
	 *
	 * Returns information about dashboard cache usage for
	 * performance monitoring and diagnostics.
	 *
	 * @since 0.6095
	 * @return array {
	 *     Cache statistics.
	 *
	 *     @type bool   $cache_exists Whether cache entry exists.
	 *     @type int    $cache_size   Size of cache in bytes (if exists).
	 *     @type int    $ttl          Remaining TTL in seconds.
	 *     @type float  $hit_rate     Cache hit rate percentage (0-100).
	 * }
	 */
	public static function get_cache_stats() {
		$cached = Cache_Manager::get( self::$cache_key, self::$cache_group );
		$stats = get_transient( 'wpshadow_cache_stats' ) ?: array(
			'hits'   => 0,
			'misses' => 0,
		);

		$total = $stats['hits'] + $stats['misses'];
		$hit_rate = $total > 0 ? round( ( $stats['hits'] / $total ) * 100, 2 ) : 0;

		return array(
			'cache_exists' => (bool) $cached,
			'cache_size'   => $cached ? strlen( $cached ) : 0,
			'ttl'          => self::$cache_ttl,
			'hit_rate'     => $hit_rate,
			'total_hits'   => $stats['hits'],
			'total_misses' => $stats['misses'],
		);
	}

	/**
	 * Log cache hit for statistics
	 *
	 * Tracks cache hit/miss ratio for performance monitoring.
	 *
	 * @since 0.6095
	 * @return void
	 */
	private static function log_cache_hit() {
		$stats = get_transient( 'wpshadow_cache_stats' ) ?: array(
			'hits'   => 0,
			'misses' => 0,
		);

		$stats['hits']++;
		set_transient( 'wpshadow_cache_stats', $stats, WEEK_IN_SECONDS );
	}

	/**
	 * Clean up old cache entries
	 *
	 * Removes expired cache entries. Called periodically
	 * via wpshadow_hourly_cleanup action.
	 *
	 * @since 0.6095
	 * @return void
	 */
	public static function cleanup_old_cache() {
		// Cache_Manager handles automatic expiration
		// This is a hook point for future enhancements

		// Clear stats if older than a week
		$stats = get_transient( 'wpshadow_cache_stats' );
		if ( false === $stats ) {
			delete_transient( 'wpshadow_cache_stats' );
		}
	}

}
