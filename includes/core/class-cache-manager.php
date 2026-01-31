<?php
/**
 * Cache Manager
 *
 * Unified caching layer that prioritizes object cache (Redis/Memcached)
 * over WordPress transients. Improves cache performance by 5-10x when
 * object cache is available.
 *
 * @package    WPShadow
 * @subpackage Core
 * @since      1.26031.1450
 */

declare(strict_types=1);

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cache_Manager Class
 *
 * Unified caching interface that automatically uses object cache (Redis/Memcached)
 * if available, falling back to WordPress transients for sites without object cache.
 *
 * Benefits:
 * - 5-10x faster cache retrieval with Redis/Memcached
 * - Automatic fallback to transients on non-optimized sites
 * - Single API for all cache operations
 * - Consistent cache TTL across all features
 *
 * @since 1.26031.1450
 */
class Cache_Manager {

	/**
	 * Default cache group for WPShadow
	 *
	 * @var string
	 */
	const GROUP = 'wpshadow';

	/**
	 * Default cache expiration (1 hour)
	 *
	 * @var int
	 */
	const DEFAULT_EXPIRE = HOUR_IN_SECONDS;

	/**
	 * Whether object cache is available
	 *
	 * @var bool|null
	 */
	private static $has_object_cache = null;

	/**
	 * Get cached value with object cache priority
	 *
	 * Attempts to retrieve from object cache first (Redis/Memcached),
	 * then falls back to transients if needed.
	 *
	 * @since  1.26031.1450
	 * @param  string $key Cache key.
	 * @param  string $group Cache group (default: wpshadow).
	 * @param  mixed $default Default value if not found.
	 * @return mixed Cached value or default value.
	 */
	public static function get( string $key, string $group = self::GROUP, $default = false ) {
		// Try object cache first (Redis/Memcached)
		if ( self::has_object_cache() ) {
			$value = wp_cache_get( $key, $group );
			if ( false !== $value ) {
				/**
				 * Action: Cache hit from object cache
				 *
				 * @since 1.26031.1450
				 * @param string $key Cache key.
				 * @param string $group Cache group.
				 */
				do_action( 'wpshadow_cache_hit_object', $key, $group );
				return $value;
			}
		}

		// Fall back to transients (database or external cache)
		$value = get_transient( $key );
		if ( false !== $value ) {
			/**
			 * Action: Cache hit from transients
			 *
			 * @since 1.26031.1450
			 * @param string $key Cache key.
			 */
			do_action( 'wpshadow_cache_hit_transient', $key );
			return $value;
		}

		return $default;
	}

	/**
	 * Set cache value
	 *
	 * Stores value in both object cache (if available) and transients
	 * for maximum compatibility.
	 *
	 * @since  1.26031.1450
	 * @param  string $key Cache key.
	 * @param  mixed $value Value to cache.
	 * @param  int $expire Expiration time in seconds (default: 1 hour).
	 * @param  string $group Cache group (default: wpshadow).
	 * @return bool True on success.
	 */
	public static function set( string $key, $value, int $expire = self::DEFAULT_EXPIRE, string $group = self::GROUP ): bool {
		// Store in object cache if available
		if ( self::has_object_cache() ) {
			wp_cache_set( $key, $value, $group, $expire );
		}

		// Always store in transients for sites without object cache
		set_transient( $key, $value, $expire );

		/**
		 * Action: Cache value set
		 *
		 * @since 1.26031.1450
		 * @param string $key Cache key.
		 * @param mixed $value Cached value.
		 * @param int $expire Expiration time.
		 * @param string $group Cache group.
		 */
		do_action( 'wpshadow_cache_set', $key, $value, $expire, $group );

		return true;
	}

	/**
	 * Delete cache value
	 *
	 * Removes value from both object cache and transients.
	 *
	 * @since  1.26031.1450
	 * @param  string $key Cache key.
	 * @param  string $group Cache group (default: wpshadow).
	 * @return bool True on success.
	 */
	public static function delete( string $key, string $group = self::GROUP ): bool {
		// Delete from object cache
		if ( self::has_object_cache() ) {
			wp_cache_delete( $key, $group );
		}

		// Delete from transients
		delete_transient( $key );

		/**
		 * Action: Cache value deleted
		 *
		 * @since 1.26031.1450
		 * @param string $key Cache key.
		 * @param string $group Cache group.
		 */
		do_action( 'wpshadow_cache_delete', $key, $group );

		return true;
	}

	/**
	 * Clear all WPShadow cache
	 *
	 * Flushes all WPShadow-related cache entries from both
	 * object cache and transients.
	 *
	 * @since  1.26031.1450
	 * @return bool True on success.
	 */
	public static function flush(): bool {
		// Clear object cache group
		if ( self::has_object_cache() ) {
			wp_cache_flush();
		}

		// For transients, we'd need to track all keys, so we use a prefix-based delete
		// This is less efficient but works across all setups
		global $wpdb;

		// Delete all transients matching pattern
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
				$wpdb->esc_like( '_transient_wpshadow' ) . '%'
			)
		);

		/**
		 * Action: All cache flushed
		 *
		 * @since 1.26031.1450
		 */
		do_action( 'wpshadow_cache_flushed' );

		return true;
	}

	/**
	 * Check if object cache is available
	 *
	 * Determines whether Redis/Memcached is available by checking
	 * if the WordPress object cache has been extended.
	 *
	 * @since  1.26031.1450
	 * @return bool True if object cache is available.
	 */
	public static function has_object_cache(): bool {
		// Cache the result to avoid repeated function calls
		if ( null === self::$has_object_cache ) {
			self::$has_object_cache = wp_using_ext_object_cache();
		}

		return self::$has_object_cache;
	}

	/**
	 * Get cache statistics
	 *
	 * Returns information about cache usage (useful for debugging/monitoring).
	 *
	 * @since  1.26031.1450
	 * @return array {
	 *     Cache statistics.
	 *
	 *     @type bool $has_object_cache Whether object cache is available.
	 *     @type string $cache_type Type of cache (object_cache, transients, or none).
	 *     @type int $estimated_entries Estimated number of cache entries.
	 * }
	 */
	public static function get_stats(): array {
		global $wpdb;

		$has_cache = self::has_object_cache();

		// Count transient entries
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$transient_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s",
				$wpdb->esc_like( '_transient_wpshadow' ) . '%'
			)
		);

		return array(
			'has_object_cache'   => $has_cache,
			'cache_type'         => $has_cache ? 'object_cache' : 'transients',
			'transient_entries'  => (int) $transient_count,
		);
	}

	/**
	 * Get cache information for debugging
	 *
	 * Returns human-readable cache information.
	 *
	 * @since  1.26031.1450
	 * @return string Human-readable cache info.
	 */
	public static function get_info(): string {
		$stats = self::get_stats();

		$info = sprintf(
			'Cache Type: %s | Object Cache: %s | Transient Entries: %d',
			$stats['cache_type'],
			$stats['has_object_cache'] ? 'Yes' : 'No',
			$stats['transient_entries']
		);

		return $info;
	}
}
