<?php
/**
 * User Preferences Manager
 *
 * Centralized user preference management with schema validation.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User Preferences Manager
 *
 * Manages user-specific preferences with schema validation and type checking.
 * Supports tip preferences, dark mode, and extensible custom preferences.
 */
class User_Preferences_Manager {
	/**
	 * Schema definition for all supported preferences.
	 *
	 * @var array
	 */
	private static $schema = array(
		'tip_prefs' => array(
			'default' => array(
				'disabled_categories' => array(),
				'dismissed_tips'      => array(),
			),
			'type' => 'array',
		),
		'dark_mode' => array(
			'default' => false,
			'type'    => 'boolean',
		),
	);

	/**
	 * Get user preference with schema validation.
	 *
	 * @param int    $user_id   User ID.
	 * @param string $key       Preference key (must be in schema).
	 * @param mixed  $default   Default value if not found (uses schema default if not provided).
	 * @return mixed Preference value or default.
	 */
	public static function get( $user_id, $key, $default = null ) {
		// Validate key exists in schema
		if ( ! isset( self::$schema[ $key ] ) ) {
			return $default;
		}

		$meta_key = 'wpshadow_' . $key;
		$meta = get_user_meta( (int) $user_id, $meta_key, true );

		// Return stored value if found, otherwise use default
		if ( '' === $meta || null === $meta ) {
			// Use provided default, then schema default
			if ( null !== $default ) {
				return $default;
			}
			return self::$schema[ $key ]['default'];
		}

		return $meta;
	}

	/**
	 * Set user preference with validation.
	 *
	 * @param int    $user_id User ID.
	 * @param string $key     Preference key (must be in schema).
	 * @param mixed  $value   Value to set.
	 * @return bool True on success, false on validation failure.
	 */
	public static function set( $user_id, $key, $value ) {
		// Validate key exists in schema
		if ( ! isset( self::$schema[ $key ] ) ) {
			return false;
		}

		$schema_type = self::$schema[ $key ]['type'];

		// Type validation and conversion
		if ( $schema_type === 'boolean' ) {
			$value = (bool) $value;
		} elseif ( $schema_type === 'array' ) {
			if ( ! is_array( $value ) ) {
				return false;
			}
			$value = (array) $value;
		} elseif ( $schema_type === 'integer' ) {
			$value = (int) $value;
		}

		$meta_key = 'wpshadow_' . $key;
		update_user_meta( (int) $user_id, $meta_key, $value );

		return true;
	}

	/**
	 * Get all preferences for a user.
	 *
	 * @param int $user_id User ID.
	 * @return array All preference values with defaults.
	 */
	public static function get_all( $user_id ) {
		$prefs = array();

		foreach ( array_keys( self::$schema ) as $key ) {
			$prefs[ $key ] = self::get( $user_id, $key );
		}

		return $prefs;
	}

	/**
	 * Delete a specific preference.
	 *
	 * @param int    $user_id User ID.
	 * @param string $key     Preference key.
	 * @return bool True on success.
	 */
	public static function delete( $user_id, $key ) {
		if ( ! isset( self::$schema[ $key ] ) ) {
			return false;
		}

		$meta_key = 'wpshadow_' . $key;
		delete_user_meta( (int) $user_id, $meta_key );

		return true;
	}

	/**
	 * Get the schema definition.
	 *
	 * @return array Schema array.
	 */
	public static function get_schema() {
		return self::$schema;
	}

	/**
	 * Register a custom preference in the schema.
	 *
	 * @param string $key     Preference key.
	 * @param array  $config  Configuration with 'type' and 'default' keys.
	 * @return bool True on success.
	 */
	public static function register( $key, $config ) {
		if ( ! isset( $config['type'] ) || ! isset( $config['default'] ) ) {
			return false;
		}

		self::$schema[ $key ] = $config;
		return true;
	}
}
