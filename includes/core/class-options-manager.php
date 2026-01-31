<?php

declare(strict_types=1);

namespace WPShadow\Core;

/**
 * Options Manager - Centralized option access with caching
 *
 * Provides unified interface for getting/setting options with:
 * - Automatic transient caching for temporary data
 * - Autoload flag optimization
 * - Type casting
 * - Default values
 *
 * Philosophy Alignment:
 * - Commandment #7: Ridiculously Good - Better performance through smart caching
 *
 * @package WPShadow
 * @subpackage Core
 */
class Options_Manager {


	/**
	 * Get option with optional caching
	 *
	 * Automatically uses transients for temporary data to reduce database load.
	 *
	 * @param string $option Option name
	 * @param mixed $default Default value
	 * @param bool $use_transient Whether to cache with transients (for temp data)
	 * @param int $transient_ttl Transient expiration (seconds)
	 * @return mixed Option value
	 */
	public static function get(
		string $option,
		$default = false,
		bool $use_transient = false,
		int $transient_ttl = HOUR_IN_SECONDS
	) {
		// Use transient if requested (for temporary data)
		if ( $use_transient ) {
			$transient_name = self::get_transient_name( $option );
			$cached         = \WPShadow\Core\Cache_Manager::get(
				$transient_name,
				'wpshadow_options'
			);
			if ( false !== $cached ) {
				return $cached;
			}
		}

		// Fall back to option
		return get_option( $option, $default );
	}

	/**
	 * Set option with optional caching
	 *
	 * @param string $option Option name
	 * @param mixed $value Option value
	 * @param bool $use_transient Whether to cache with transients
	 * @param int $transient_ttl Transient expiration (seconds)
	 * @param bool $autoload Whether to autoload option (false for large data)
	 * @return bool Success
	 */
	public static function set(
		string $option,
		$value,
		bool $use_transient = false,
		int $transient_ttl = HOUR_IN_SECONDS,
		bool $autoload = false
	): bool {
		// Set option (autoload=false for large/rarely-used data)
		$result = update_option( $option, $value );

		// Cache with transient if requested
		if ( $use_transient ) {
			$transient_name = self::get_transient_name( $option );
			\WPShadow\Core\Cache_Manager::set(
				$transient_name,
				$value,
				'wpshadow_options',
				$transient_ttl
			);
		}

		return $result;
	}

	/**
	 * Get option and cast to specific type
	 *
	 * @param string $option Option name
	 * @param string $type Type to cast: 'int', 'float', 'bool', 'string', 'array'
	 * @param mixed $default Default value
	 * @return mixed Typed value
	 */
	public static function get_typed( string $option, string $type = 'string', $default = null ) {
		$value = get_option( $option );

		if ( false === $value ) {
			return $default;
		}

		switch ( $type ) {
			case 'int':
				return (int) $value;
			case 'float':
				return (float) $value;
			case 'bool':
				return (bool) $value;
			case 'array':
				return is_array( $value ) ? $value : array();
			case 'string':
			default:
				return (string) $value;
		}
	}

	/**
	 * Get transient name for option
	 *
	 * Prefixes option name to avoid collisions
	 *
	 * @param string $option Option name
	 * @return string Transient name
	 */
	private static function get_transient_name( string $option ): string {
		// Transients have 40-char limit, hash if necessary
		$transient = 'wpshadow_tmp_' . $option;
		if ( strlen( $transient ) > 40 ) {
			$transient = 'wpshadow_tmp_' . substr( md5( $option ), 0, 27 );
		}
		return $transient;
	}

	/**
	 * Delete option and associated transient
	 *
	 * @param string $option Option name
	 * @return bool Success
	 */
	public static function delete( string $option ): bool {
		// Delete option
		delete_option( $option );

		// Delete associated cache
		$transient_name = self::get_transient_name( $option );
		\WPShadow\Core\Cache_Manager::delete(
			$transient_name,
			'wpshadow_options'
		);

		return true;
	}

	/**
	 * Get integer option
	 *
	 * @param string $option Option name
	 * @param int $default Default value
	 * @return int Option value
	 */
	public static function get_int( string $option, int $default = 0 ): int {
		return (int) get_option( $option, $default );
	}

	/**
	 * Get boolean option
	 *
	 * @param string $option Option name
	 * @param bool $default Default value
	 * @return bool Option value
	 */
	public static function get_bool( string $option, bool $default = false ): bool {
		return (bool) get_option( $option, $default );
	}

	/**
	 * Get array option
	 *
	 * @param string $option Option name
	 * @param array $default Default value
	 * @return array Option value
	 */
	public static function get_array( string $option, array $default = array() ): array {
		$value = get_option( $option );
		return is_array( $value ) ? $value : $default;
	}

	/**
	 * Increment numeric option
	 *
	 * @param string $option Option name
	 * @param int $offset Increment amount
	 * @return int New value
	 */
	public static function increment( string $option, int $offset = 1 ): int {
		$value = self::get_int( $option, 0 );
		$new   = $value + $offset;
		self::set( $option, $new );
		return $new;
	}

	/**
	 * Append to array option
	 *
	 * @param string $option Option name
	 * @param mixed $item Item to append
	 * @param int $limit Maximum array size (optional)
	 * @return array Updated array
	 */
	public static function append_array( string $option, $item, int $limit = 0 ): array {
		$array   = self::get_array( $option, array() );
		$array[] = $item;

		// Limit array size if specified
		if ( $limit > 0 && count( $array ) > $limit ) {
			$array = array_slice( $array, -$limit );
		}

		self::set( $option, $array );
		return $array;
	}
}
