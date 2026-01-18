<?php
/**
 * Settings Cache Manager
 *
 * Centralized settings management with caching to prevent redundant database queries.
 * Provides a single source of truth for all feature settings across the plugin.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.74000
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings Cache Manager
 *
 * Manages feature settings with intelligent caching and batch loading.
 */
final class WPSHADOW_Settings_Cache {
	/**
	 * Settings cache storage.
	 *
	 * @var array<string, mixed>
	 */
	private static array $cache = array();

	/**
	 * Track which settings have been loaded.
	 *
	 * @var array<string, bool>
	 */
	private static array $loaded = array();

	/**
	 * Default values registry.
	 *
	 * @var array<string, mixed>
	 */
	private static array $defaults = array();

	/**
	 * Initialize the settings cache.
	 *
	 * @return void
	 */
	public static function init(): void {
		// Hook to clear cache on option updates.
		add_action( 'updated_option', array( __CLASS__, 'clear_cache_on_update' ), 10, 1 );
		add_action( 'added_option', array( __CLASS__, 'clear_cache_on_update' ), 10, 1 );
		add_action( 'deleted_option', array( __CLASS__, 'clear_cache_on_update' ), 10, 1 );
	}

	/**
	 * Register default values for a setting.
	 *
	 * @param string $option_name Option name (without WPSHADOW_ prefix).
	 * @param mixed  $defaults    Default value.
	 * @return void
	 */
	public static function register_defaults( string $option_name, $defaults ): void {
		$key                     = 'wpshadow_' . $option_name;
		self::$defaults[ $key ] = $defaults;
	}

	/**
	 * Get a setting with caching and default value support.
	 *
	 * @param string $option_name Option name (without WPSHADOW_ prefix).
	 * @param mixed  $default     Default value if not set.
	 * @param bool   $network     Whether to get network option.
	 * @return mixed Option value.
	 */
	public static function get( string $option_name, $default = null, bool $network = false ) {
		$key = 'wpshadow_' . $option_name;

		// Use cached value if available.
		if ( isset( self::$loaded[ $key ] ) ) {
			return self::$cache[ $key ];
		}

		// Determine default value.
		if ( null === $default && isset( self::$defaults[ $key ] ) ) {
			$default = self::$defaults[ $key ];
		}

		// Load from database.
		if ( $network && is_multisite() ) {
			$value = get_site_option( $key, $default );
		} else {
			$value = get_option( $key, $default );
		}

		// Cache the value.
		self::$cache[ $key ]  = $value;
		self::$loaded[ $key ] = true;

		return $value;
	}

	/**
	 * Update a setting and clear its cache.
	 *
	 * @param string $option_name Option name (without WPSHADOW_ prefix).
	 * @param mixed  $value       New value.
	 * @param bool   $network     Whether to update network option.
	 * @return bool True if update succeeded.
	 */
	public static function update( string $option_name, $value, bool $network = false ): bool {
		$key = 'wpshadow_' . $option_name;

		// Update database.
		if ( $network && is_multisite() ) {
			$result = update_site_option( $key, $value );
		} else {
			$result = update_option( $key, $value );
		}

		// Update cache.
		if ( $result ) {
			self::$cache[ $key ]  = $value;
			self::$loaded[ $key ] = true;
		}

		return $result;
	}

	/**
	 * Delete a setting and clear its cache.
	 *
	 * @param string $option_name Option name (without WPSHADOW_ prefix).
	 * @param bool   $network     Whether to delete network option.
	 * @return bool True if deletion succeeded.
	 */
	public static function delete( string $option_name, bool $network = false ): bool {
		$key = 'wpshadow_' . $option_name;

		// Delete from database.
		if ( $network && is_multisite() ) {
			$result = delete_site_option( $key );
		} else {
			$result = delete_option( $key );
		}

		// Clear cache.
		if ( $result ) {
			unset( self::$cache[ $key ], self::$loaded[ $key ] );
		}

		return $result;
	}

	/**
	 * Batch load multiple settings at once.
	 *
	 * @param array<string> $option_names Array of option names (without WPSHADOW_ prefix).
	 * @param bool          $network      Whether to load network options.
	 * @return array<string, mixed> Loaded settings.
	 */
	public static function load_batch( array $option_names, bool $network = false ): array {
		global $wpdb;

		$keys_to_load = array();
		foreach ( $option_names as $name ) {
			$key = 'wpshadow_' . $name;
			if ( ! isset( self::$loaded[ $key ] ) ) {
				$keys_to_load[] = $key;
			}
		}

		if ( empty( $keys_to_load ) ) {
			// All already cached.
			$result = array();
			foreach ( $option_names as $name ) {
				$key             = 'wpshadow_' . $name;
				$result[ $name ] = self::$cache[ $key ];
			}
			return $result;
		}

		// Batch load from database.
		$table         = $network && is_multisite() ? $wpdb->sitemeta : $wpdb->options;
		$column        = $network && is_multisite() ? 'meta_key' : 'option_name';
		$value_column  = $network && is_multisite() ? 'meta_value' : 'option_value';
		$placeholders  = implode( ',', array_fill( 0, count( $keys_to_load ), '%s' ) );
		$query         = $wpdb->prepare( "SELECT {$column} AS name, {$value_column} AS value FROM {$table} WHERE {$column} IN ({$placeholders})", $keys_to_load ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$results       = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		// Cache loaded values.
		foreach ( $results as $row ) {
			$value                         = maybe_unserialize( $row->value );
			self::$cache[ $row->name ]    = $value;
			self::$loaded[ $row->name ]   = true;
		}

		// Mark missing keys as loaded with defaults.
		foreach ( $keys_to_load as $key ) {
			if ( ! isset( self::$loaded[ $key ] ) ) {
				$default                = isset( self::$defaults[ $key ] ) ? self::$defaults[ $key ] : false;
				self::$cache[ $key ]   = $default;
				self::$loaded[ $key ]  = true;
			}
		}

		// Return result array.
		$result = array();
		foreach ( $option_names as $name ) {
			$key             = 'wpshadow_' . $name;
			$result[ $name ] = self::$cache[ $key ];
		}

		return $result;
	}

	/**
	 * Clear all cached settings.
	 *
	 * @return void
	 */
	public static function clear_all(): void {
		self::$cache  = array();
		self::$loaded = array();
	}

	/**
	 * Clear cache for a specific setting when it's updated.
	 *
	 * @param string $option Option name.
	 * @return void
	 */
	public static function clear_cache_on_update( string $option ): void {
		// Only clear WPS settings.
		if ( strpos( $option, 'wpshadow_' ) !== 0 && strpos( $option, 'wpshadow_' ) !== 0 ) {
			return;
		}

		unset( self::$cache[ $option ], self::$loaded[ $option ] );
	}

	/**
	 * Get cache statistics for debugging.
	 *
	 * @return array<string, int> Cache statistics.
	 */
	public static function get_stats(): array {
		return array(
			'cached_items'  => count( self::$cache ),
			'loaded_items'  => count( self::$loaded ),
			'default_items' => count( self::$defaults ),
		);
	}
}
