<?php

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Cache_Helper {

	private const CACHE_GROUP = 'wpshadow_core';

	private const DEFAULT_EXPIRATION = HOUR_IN_SECONDS;

	public static function generate_key( string $prefix, ...$parts ): string {
		$key_parts = array_merge( array( 'wps', $prefix ), $parts );

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

		return sanitize_key( substr( $key, 0, 172 ) ); 
	}

	public static function get( string $key ) {
		return wp_cache_get( $key, self::CACHE_GROUP );
	}

	public static function set( string $key, $data, int $expiration = self::DEFAULT_EXPIRATION ): bool {
		return wp_cache_set( $key, $data, self::CACHE_GROUP, $expiration );
	}

	public static function delete( string $key ): bool {
		return wp_cache_delete( $key, self::CACHE_GROUP );
	}

	public static function remember( string $key, callable $callback, int $expiration = self::DEFAULT_EXPIRATION ) {
		$cached = self::get( $key );

		if ( false !== $cached ) {
			return $cached;
		}

		$data = $callback();
		self::set( $key, $data, $expiration );

		return $data;
	}

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

	public static function flush_all(): void {
		wp_cache_flush_group( self::CACHE_GROUP );
	}

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
