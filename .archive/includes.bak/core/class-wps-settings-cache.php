<?php

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Settings_Cache {

	private static array $cache = array();

	private static array $loaded = array();

	private static array $defaults = array();

	public static function init(): void {

		add_action( 'updated_option', array( __CLASS__, 'clear_cache_on_update' ), 10, 1 );
		add_action( 'added_option', array( __CLASS__, 'clear_cache_on_update' ), 10, 1 );
		add_action( 'deleted_option', array( __CLASS__, 'clear_cache_on_update' ), 10, 1 );
	}

	public static function register_defaults( string $option_name, $defaults ): void {
		$key                     = 'wpshadow_' . $option_name;
		self::$defaults[ $key ] = $defaults;
	}

	public static function get( string $option_name, $default = null, bool $network = false ) {
		$key = 'wpshadow_' . $option_name;

		if ( isset( self::$loaded[ $key ] ) ) {
			return self::$cache[ $key ];
		}

		if ( null === $default && isset( self::$defaults[ $key ] ) ) {
			$default = self::$defaults[ $key ];
		}

		if ( $network && is_multisite() ) {
			$value = get_site_option( $key, $default );
		} else {
			$value = get_option( $key, $default );
		}

		self::$cache[ $key ]  = $value;
		self::$loaded[ $key ] = true;

		return $value;
	}

	public static function update( string $option_name, $value, bool $network = false ): bool {
		$key = 'wpshadow_' . $option_name;

		if ( $network && is_multisite() ) {
			$result = update_site_option( $key, $value );
		} else {
			$result = update_option( $key, $value );
		}

		if ( $result ) {
			self::$cache[ $key ]  = $value;
			self::$loaded[ $key ] = true;
		}

		return $result;
	}

	public static function delete( string $option_name, bool $network = false ): bool {
		$key = 'wpshadow_' . $option_name;

		if ( $network && is_multisite() ) {
			$result = delete_site_option( $key );
		} else {
			$result = delete_option( $key );
		}

		if ( $result ) {
			unset( self::$cache[ $key ], self::$loaded[ $key ] );
		}

		return $result;
	}

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

			$result = array();
			foreach ( $option_names as $name ) {
				$key             = 'wpshadow_' . $name;
				$result[ $name ] = self::$cache[ $key ];
			}
			return $result;
		}

		$table         = $network && is_multisite() ? $wpdb->sitemeta : $wpdb->options;
		$column        = $network && is_multisite() ? 'meta_key' : 'option_name';
		$value_column  = $network && is_multisite() ? 'meta_value' : 'option_value';
		$placeholders  = implode( ',', array_fill( 0, count( $keys_to_load ), '%s' ) );
		$query         = $wpdb->prepare( "SELECT {$column} AS name, {$value_column} AS value FROM {$table} WHERE {$column} IN ({$placeholders})", $keys_to_load ); 
		$results       = $wpdb->get_results( $query ); 

		foreach ( $results as $row ) {
			$value                         = maybe_unserialize( $row->value );
			self::$cache[ $row->name ]    = $value;
			self::$loaded[ $row->name ]   = true;
		}

		foreach ( $keys_to_load as $key ) {
			if ( ! isset( self::$loaded[ $key ] ) ) {
				$default                = isset( self::$defaults[ $key ] ) ? self::$defaults[ $key ] : false;
				self::$cache[ $key ]   = $default;
				self::$loaded[ $key ]  = true;
			}
		}

		$result = array();
		foreach ( $option_names as $name ) {
			$key             = 'wpshadow_' . $name;
			$result[ $name ] = self::$cache[ $key ];
		}

		return $result;
	}

	public static function clear_all(): void {
		self::$cache  = array();
		self::$loaded = array();
	}

	public static function clear_cache_on_update( string $option ): void {

		if ( strpos( $option, 'wpshadow_' ) !== 0 && strpos( $option, 'wpshadow_' ) !== 0 ) {
			return;
		}

		unset( self::$cache[ $option ], self::$loaded[ $option ] );
	}

	public static function get_stats(): array {
		return array(
			'cached_items'  => count( self::$cache ),
			'loaded_items'  => count( self::$loaded ),
			'default_items' => count( self::$defaults ),
		);
	}
}
