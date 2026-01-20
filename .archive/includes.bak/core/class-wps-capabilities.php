<?php

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPSHADOW_Capabilities {
	private const OPTION_KEY = 'wpshadow_capability_map';

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

	public static function user_can( string $module, string $capability, ?int $user_id = null ): bool {
		$module     = sanitize_key( $module );
		$capability = sanitize_key( $capability );
		$user_id    = $user_id ?? get_current_user_id();

		$map      = self::get_map();
		$wp_cap   = $map[ $module ][ $capability ] ?? 'manage_options';
		$user_can = user_can( $user_id, $wp_cap );

		return (bool) $user_can;
	}

	public static function get_module_capabilities( string $module ): array {
		$module = sanitize_key( $module );
		$map    = self::get_map();

		return $map[ $module ] ?? array();
	}

	public static function get_map(): array {
		if ( is_multisite() ) {
			return (array) get_site_option( self::OPTION_KEY, array() );
		}

		return (array) get_option( self::OPTION_KEY, array() );
	}

	private static function persist( array $map ): bool {
		if ( is_multisite() ) {
			return update_site_option( self::OPTION_KEY, $map );
		}

		return update_option( self::OPTION_KEY, $map );
	}
}
