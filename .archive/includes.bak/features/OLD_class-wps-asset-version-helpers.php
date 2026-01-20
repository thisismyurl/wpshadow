<?php

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPSHADOW_Asset_Version_Helpers {

	public static function remove_version( $src ) {
		if ( ! is_string( $src ) || strpos( $src, 'ver=' ) === false ) {
			return $src;
		}

		if ( self::should_ignore_asset( $src ) ) {
			return $src;
		}

		if ( self::should_preserve_plugin_version( $src ) ) {
			return $src;
		}

		return remove_query_arg( 'ver', $src );
	}

	public static function should_ignore_asset( string $src ): bool {

		if ( strpos( $src, '.css' ) !== false ) {
			$css_patterns = get_option( 'wpshadow_asset-version-removal_css_ignore_patterns', array() );
			foreach ( $css_patterns as $pattern ) {
				if ( self::match_pattern( $src, $pattern ) ) {
					return true;
				}
			}
		}

		if ( strpos( $src, '.js' ) !== false ) {
			$js_patterns = get_option( 'wpshadow_asset-version-removal_js_ignore_patterns', array() );
			foreach ( $js_patterns as $pattern ) {
				if ( self::match_pattern( $src, $pattern ) ) {
					return true;
				}
			}
		}

		$ignored_plugins = get_option( 'wpshadow_asset-version-removal_ignored_plugins', array() );
		if ( ! empty( $ignored_plugins ) && strpos( $src, '/plugins/' ) !== false ) {
			foreach ( $ignored_plugins as $plugin_slug ) {
				if ( strpos( $src, '/plugins/' . $plugin_slug . '/' ) !== false ) {
					return true;
				}
			}
		}

		return false;
	}

	private static function match_pattern( string $url, string $pattern ): bool {

		if ( preg_match( '/^\/.*\/[gimsuvy]*$/', $pattern ) ) {
			return (bool) preg_match( $pattern, $url );
		}

		$pattern = str_replace( '*', '.*', preg_quote( $pattern, '/' ) );
		return (bool) preg_match( '/^' . $pattern . '$/', $url );
	}

	public static function should_preserve_plugin_version( string $src ): bool {
		$preserve = get_option( 'wpshadow_asset-version-removal_preserve_plugin_versions', false );

		if ( ! $preserve ) {
			return false;
		}

		return strpos( $src, '/plugins/' ) !== false;
	}

	public static function init_sub_feature_defaults( array $defaults, string $feature_id ): void {
		foreach ( $defaults as $key => $default_value ) {
			$option_name   = "wpshadow_{$feature_id}_{$key}";
			$current_value = get_option( $option_name, null );

			if ( null === $current_value ) {
				update_option( $option_name, $default_value, false );
			}
		}
	}

	public static function is_sub_feature_enabled( string $feature_id, string $sub_feature_key, bool $default = true ): bool {
		$option_name = "wpshadow_{$feature_id}_{$sub_feature_key}";
		return (bool) get_option( $option_name, $default );
	}
}
