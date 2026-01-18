<?php
/**
 * Capability mapping for WPS modules.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Capability manager maps module capabilities to WordPress capabilities.
 */
class WPSHADOW_Capabilities {
	private const OPTION_KEY = 'wpshadow_capability_map';

	/**
	 * Register a capability mapping.
	 *
	 * @param string $module      Module slug.
	 * @param string $capability  Module capability key.
	 * @param string $wp_cap      WordPress capability to map to.
	 * @return bool
	 */
	public static function register_capability( string $module, string $capability, string $wp_cap ): bool {
		$module     = sanitize_key( $module );
		$capability = sanitize_key( $capability );
		$wp_cap     = sanitize_key( $wp_cap );

		$map = self::get_map();
		if ( ! isset( $map[ $module ] ) ) {
			$map[ $module ] = array();
		}

		$map[ $module ][ $capability ] = $wp_cap;

		return self::persist( $map );
	}

	/**
	 * Determine whether a user can perform a module capability.
	 *
	 * @param string   $module     Module slug.
	 * @param string   $capability Module capability.
	 * @param int|null $user_id    Optional user ID; defaults to current.
	 * @return bool
	 */
	public static function user_can( string $module, string $capability, ?int $user_id = null ): bool {
		$module     = sanitize_key( $module );
		$capability = sanitize_key( $capability );
		$user_id    = $user_id ?? get_current_user_id();

		$map      = self::get_map();
		$wp_cap   = $map[ $module ][ $capability ] ?? 'manage_options';
		$user_can = user_can( $user_id, $wp_cap );

		return (bool) $user_can;
	}

	/**
	 * Get capability map for a module.
	 *
	 * @param string $module Module slug.
	 * @return array<string,string>
	 */
	public static function get_module_capabilities( string $module ): array {
		$module = sanitize_key( $module );
		$map    = self::get_map();

		return $map[ $module ] ?? array();
	}

	/**
	 * Fetch the stored map.
	 *
	 * @return array<string,array<string,string>>
	 */
	public static function get_map(): array {
		if ( is_multisite() ) {
			return (array) get_site_option( self::OPTION_KEY, array() );
		}

		return (array) get_option( self::OPTION_KEY, array() );
	}

	/**
	 * Persist capability map.
	 *
	 * @param array $map Capability map.
	 * @return bool
	 */
	private static function persist( array $map ): bool {
		if ( is_multisite() ) {
			return update_site_option( self::OPTION_KEY, $map );
		}

		return update_option( self::OPTION_KEY, $map );
	}
}
