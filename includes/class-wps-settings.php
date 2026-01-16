<?php
/**
 * WPS Settings API (network + site with overrides).
 *
 * @package wpshadow_SUPPORT
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings helper providing network/site inheritance and override control.
 */
class WPSHADOW_Settings {
	private const SITE_OPTION    = 'wpshadow_site_settings';
	private const NETWORK_OPTION = 'wpshadow_network_settings';
	private const OVERRIDE_KEY   = '_allow_override';

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'plugins_loaded', array( __CLASS__, 'bootstrap' ), 1 );
	}

	/**
	 * Ensure options exist.
	 *
	 * @return void
	 */
	public static function bootstrap(): void {
		if ( is_multisite() ) {
			if ( false === get_site_option( self::NETWORK_OPTION ) ) {
				update_site_option( self::NETWORK_OPTION, array() );
			}
		}

		if ( false === get_option( self::SITE_OPTION ) ) {
			update_option( self::SITE_OPTION, array() );
		}
	}

	/**
	 * Get a setting with inheritance rules.
	 *
	 * @param string      $module  Module slug.
	 * @param string      $key     Setting key.
	 * @param mixed|null  $default Default value.
	 * @param bool        $network Force network scope.
	 * @return mixed|null
	 */
	public static function get( string $module, string $key, $default = null, bool $network = false ) {
		$module = sanitize_key( $module );
		$key    = sanitize_key( $key );

		$network_settings = self::get_settings( true );
		$site_settings    = self::get_settings( false );
		$net_module       = $network_settings[ $module ] ?? array();
		$site_module      = $site_settings[ $module ] ?? array();
		$allow_override   = (bool) ( $net_module[ self::OVERRIDE_KEY ] ?? true );

		if ( $network || ! is_multisite() ) {
			return $net_module[ $key ] ?? $default;
		}

		if ( ! $allow_override ) {
			return $net_module[ $key ] ?? $default;
		}

		if ( array_key_exists( $key, $site_module ) ) {
			return $site_module[ $key ];
		}

		return $net_module[ $key ] ?? $default;
	}

	/**
	 * Update a setting.
	 *
	 * @param string $module  Module slug.
	 * @param string $key     Setting key.
	 * @param mixed  $value   Setting value.
	 * @param bool   $network Update network scope.
	 * @return bool
	 */
	public static function update( string $module, string $key, $value, bool $network = false ): bool {
		$module = sanitize_key( $module );
		$key    = sanitize_key( $key );

		$settings = self::get_settings( $network );
		if ( ! isset( $settings[ $module ] ) || ! is_array( $settings[ $module ] ) ) {
			$settings[ $module ] = array();
		}

		$settings[ $module ][ $key ] = $value;

		return self::persist( $settings, $network );
	}

	/**
	 * Delete a setting.
	 *
	 * @param string $module  Module slug.
	 * @param string $key     Setting key.
	 * @param bool   $network Delete network scope.
	 * @return bool
	 */
	public static function delete( string $module, string $key, bool $network = false ): bool {
		$module = sanitize_key( $module );
		$key    = sanitize_key( $key );

		$settings = self::get_settings( $network );
		if ( isset( $settings[ $module ] ) ) {
			unset( $settings[ $module ][ $key ] );
			return self::persist( $settings, $network );
		}

		return true;
	}

	/**
	 * Allow or block site overrides for a module.
	 *
	 * @param string $module Module slug.
	 * @param bool   $allowed Whether overrides are allowed.
	 * @return bool
	 */
	public static function set_override( string $module, bool $allowed ): bool {
		$module   = sanitize_key( $module );
		$settings = self::get_settings( true );

		if ( ! isset( $settings[ $module ] ) || ! is_array( $settings[ $module ] ) ) {
			$settings[ $module ] = array();
		}

		$settings[ $module ][ self::OVERRIDE_KEY ] = $allowed;

		return self::persist( $settings, true );
	}

	/**
	 * Check if a module/key can be overridden at site level.
	 *
	 * @param string $module Module slug.
	 * @param string $key    Setting key.
	 * @return bool
	 */
	public static function can_override( string $module, string $key ): bool {
		$module = sanitize_key( $module );
		$key    = sanitize_key( $key );

		$network_settings = self::get_settings( true );
		$net_module       = $network_settings[ $module ] ?? array();

		return (bool) ( $net_module[ self::OVERRIDE_KEY ] ?? true ) && array_key_exists( $key, $net_module );
	}

	/**
	 * Retrieve settings for site or network scope.
	 *
	 * @param bool $network Whether to fetch network scope.
	 * @return array
	 */
	private static function get_settings( bool $network ): array {
		if ( $network && is_multisite() ) {
			return (array) get_site_option( self::NETWORK_OPTION, array() );
		}

		return (array) get_option( self::SITE_OPTION, array() );
	}

	/**
	 * Persist settings to storage.
	 *
	 * @param array $settings Settings to persist.
	 * @param bool  $network  Whether to save network scope.
	 * @return bool
	 */
	private static function persist( array $settings, bool $network ): bool {
		if ( $network && is_multisite() ) {
			return update_site_option( self::NETWORK_OPTION, $settings );
		}

		return update_option( self::SITE_OPTION, $settings );
	}

	/**
	 * Get all settings for a specific module.
	 *
	 * @param string $module  Module slug.
	 * @param bool   $network Force network scope.
	 * @return array
	 */
	public static function get_module_settings( string $module, bool $network = false ): array {
		$module   = sanitize_key( $module );
		$settings = self::get_settings( $network );

		return $settings[ $module ] ?? array();
	}

	/**
	 * Get all settings (all modules).
	 *
	 * @param bool $network Force network scope.
	 * @return array
	 */
	public static function get_all_settings( bool $network = false ): array {
		return self::get_settings( $network );
	}

	/**
	 * Reset a module's settings to defaults.
	 *
	 * @param string $module  Module slug.
	 * @param bool   $network Network scope.
	 * @return bool
	 */
	public static function reset_module( string $module, bool $network = false ): bool {
		$module   = sanitize_key( $module );
		$settings = self::get_settings( $network );

		if ( isset( $settings[ $module ] ) ) {
			unset( $settings[ $module ] );
			return self::persist( $settings, $network );
		}

		return true;
	}

	/**
	 * Reset all settings to defaults.
	 *
	 * @param bool $network Network scope.
	 * @return bool
	 */
	public static function reset_all( bool $network = false ): bool {
		return self::persist( array(), $network );
	}
}
