<?php
/**
 * WPS Cache Helper
 *
 * Centralized caching with consistent key generation, storage, and invalidation.
 *
 * @package WPS\CoreSupport
 * @since 1.2601.76000
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPSHADOW_Cache_Helper
 *
 * Centralized caching utility for consistent cache management across the plugin.
 */
final class WPSHADOW_Cache_Helper {

	/**
	 * Cache group for plugin data.
	 */
	private const CACHE_GROUP = 'wpshadow_core';

	/**
	 * Default cache expiration (1 hour).
	 */
	private const DEFAULT_EXPIRATION = HOUR_IN_SECONDS;

	/**
	 * Generate a cache key.
	 *
	 * Creates a standardized cache key from a prefix and optional parts.
	 * Arrays and objects in parts are automatically hashed.
	 *
	 * @param string $prefix   Cache key prefix (usually feature ID).
	 * @param mixed  ...$parts Additional parts to include in key.
	 * @return string Cache key.
	 */
	public static function generate_key( string $prefix, ...$parts ): string {
		$key_parts = array_merge( array( 'wps', $prefix ), $parts );

		// Hash complex types
		$has_complex = false;
		foreach ( $parts as $part ) {
			if ( is_array( $part ) || is_object( $part ) ) {
				$has_complex = true;
				break;
			}
		}

		$key = implode( '_', array_filter( array_map( 'strval', $key_parts ) ) );

		if ( $has_complex ) {
			$key .= '_' . md5( serialize( $parts ) );
		}

		return sanitize_key( substr( $key, 0, 172 ) ); // MySQL key length limit
	}

	/**
	 * Get cached data.
	 *
	 * @param string $key Cache key.
	 * @return mixed|false Cached data or false if not found.
	 */
	public static function get( string $key ) {
		return wp_cache_get( $key, self::CACHE_GROUP );
	}

	/**
	 * Set cached data.
	 *
	 * @param string $key        Cache key.
	 * @param mixed  $data       Data to cache.
	 * @param int    $expiration Expiration in seconds (default: 1 hour).
	 * @return bool True on success.
	 */
	public static function set( string $key, $data, int $expiration = self::DEFAULT_EXPIRATION ): bool {
		return wp_cache_set( $key, $data, self::CACHE_GROUP, $expiration );
	}

	/**
	 * Delete cached data.
	 *
	 * @param string $key Cache key.
	 * @return bool True on success.
	 */
	public static function delete( string $key ): bool {
		return wp_cache_delete( $key, self::CACHE_GROUP );
	}

	/**
	 * Get or set cached data with callback.
	 *
	 * If cache exists, returns it. Otherwise, calls callback, caches result, and returns it.
	 *
	 * @param string   $key        Cache key.
	 * @param callable $callback   Callback to generate data if cache miss.
	 * @param int      $expiration Expiration in seconds (default: 1 hour).
	 * @return mixed Cached or generated data.
	 */
	public static function remember( string $key, callable $callback, int $expiration = self::DEFAULT_EXPIRATION ) {
		$cached = self::get( $key );

		if ( false !== $cached ) {
			return $cached;
		}

		$data = $callback();
		self::set( $key, $data, $expiration );

		return $data;
	}

	/**
	 * Delete all cache entries matching a prefix.
	 *
	 * @param string $prefix Cache key prefix.
	 * @return int Number of keys deleted.
	 */
	public static function delete_by_prefix( string $prefix ): int {
		global $wp_object_cache;

		if ( ! isset( $wp_object_cache->cache[ self::CACHE_GROUP ] ) ) {
			return 0;
		}

		$deleted      = 0;
		$prefix_check = 'wpshadow_' . $prefix;

		foreach ( array_keys( $wp_object_cache->cache[ self::CACHE_GROUP ] ) as $key ) {
			if ( is_string( $key ) && strpos( $key, $prefix_check ) === 0 ) {
				self::delete( $key );
				$deleted++;
			}
		}

		return $deleted;
	}

	/**
	 * Clear all WPS caches.
	 *
	 * @return void
	 */
	public static function flush_all(): void {
		wp_cache_flush_group( self::CACHE_GROUP );
	}

	/**
	 * Get cache statistics.
	 *
	 * @return array{total: int, size: int} Cache statistics.
	 */
	public static function get_stats(): array {
		global $wp_object_cache;

		if ( ! isset( $wp_object_cache->cache[ self::CACHE_GROUP ] ) ) {
			return array(
				'total' => 0,
				'size'  => 0,
			);
		}

		$cache_data = $wp_object_cache->cache[ self::CACHE_GROUP ];

		return array(
			'total' => count( $cache_data ),
			'size'  => strlen( serialize( $cache_data ) ),
		);
	}

	/**
	 * Warm cache with multiple keys.
	 *
	 * @param array<string, callable> $items Array of cache key => callback pairs.
	 * @param int                     $expiration Expiration in seconds.
	 * @return int Number of items cached.
	 */
	public static function warm( array $items, int $expiration = self::DEFAULT_EXPIRATION ): int {
		$cached = 0;

		foreach ( $items as $key => $callback ) {
			if ( is_callable( $callback ) ) {
				$data = $callback();
				if ( self::set( $key, $data, $expiration ) ) {
					$cached++;
				}
			}
		}

		return $cached;
	}
}
