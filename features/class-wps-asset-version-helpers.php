<?php
/**
 * Asset Version Removal Helpers
 *
 * Shared utility functions for asset version removal features.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.75000
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Asset Version Removal Helper Functions
 */
class WPSHADOW_Asset_Version_Helpers {

	/**
	 * Remove version query parameter from an asset URL.
	 *
	 * @param string|mixed $src Asset source URL.
	 * @return string|mixed Modified URL or original value.
	 */
	public static function remove_version( $src ) {
		if ( ! is_string( $src ) || strpos( $src, 'ver=' ) === false ) {
			return $src;
		}

		// Check against ignore lists.
		if ( self::should_ignore_asset( $src ) ) {
			return $src;
		}

		// Honor preserve-plugin-versions setting.
		if ( self::should_preserve_plugin_version( $src ) ) {
			return $src;
		}

		return remove_query_arg( 'ver', $src );
	}

	/**
	 * Check if an asset URL should be ignored based on ignore rules and plugin list.
	 *
	 * @param string $src Asset URL to check.
	 * @return bool True if asset should be ignored.
	 */
	public static function should_ignore_asset( string $src ): bool {
		// Check CSS ignore rules for .css files.
		if ( strpos( $src, '.css' ) !== false ) {
			$css_patterns = get_option( 'wpshadow_asset-version-removal_css_ignore_patterns', array() );
			foreach ( $css_patterns as $pattern ) {
				if ( self::match_pattern( $src, $pattern ) ) {
					return true;
				}
			}
		}

		// Check JS ignore rules for .js files.
		if ( strpos( $src, '.js' ) !== false ) {
			$js_patterns = get_option( 'wpshadow_asset-version-removal_js_ignore_patterns', array() );
			foreach ( $js_patterns as $pattern ) {
				if ( self::match_pattern( $src, $pattern ) ) {
					return true;
				}
			}
		}

		// Check plugin ignore list.
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

	/**
	 * Match a URL against a pattern (supports simple wildcards and regex).
	 *
	 * @param string $url URL to check.
	 * @param string $pattern Pattern to match against.
	 * @return bool True if URL matches pattern.
	 */
	private static function match_pattern( string $url, string $pattern ): bool {
		// If pattern starts and ends with /, treat as regex.
		if ( preg_match( '/^\/.*\/[gimsuvy]*$/', $pattern ) ) {
			return (bool) preg_match( $pattern, $url );
		}

		// Otherwise use simple wildcard matching.
		$pattern = str_replace( '*', '.*', preg_quote( $pattern, '/' ) );
		return (bool) preg_match( '/^' . $pattern . '$/', $url );
	}

	/**
	 * Check if plugin asset versions should be preserved.
	 *
	 * @param string $src Asset URL to check.
	 * @return bool True if version should be preserved.
	 */
	public static function should_preserve_plugin_version( string $src ): bool {
		$preserve = get_option( 'wpshadow_asset-version-removal_preserve_plugin_versions', false );
		
		if ( ! $preserve ) {
			return false;
		}

		// URLs containing /plugins/ are treated as plugin assets.
		return strpos( $src, '/plugins/' ) !== false;
	}

	/**
	 * Initialize default sub-feature options.
	 *
	 * @param array $defaults Key-value pairs of option keys and default values.
	 * @param string $feature_id Feature ID for option name prefix.
	 * @return void
	 */
	public static function init_sub_feature_defaults( array $defaults, string $feature_id ): void {
		foreach ( $defaults as $key => $default_value ) {
			$option_name   = "wpshadow_{$feature_id}_{$key}";
			$current_value = get_option( $option_name, null );

			if ( null === $current_value ) {
				update_option( $option_name, $default_value, false );
			}
		}
	}

	/**
	 * Check if a sub-feature setting is enabled.
	 *
	 * @param string $feature_id Parent feature ID.
	 * @param string $sub_feature_key Sub-feature key.
	 * @param bool   $default Default value if option doesn't exist.
	 * @return bool Whether the sub-feature is enabled.
	 */
	public static function is_sub_feature_enabled( string $feature_id, string $sub_feature_key, bool $default = true ): bool {
		$option_name = "wpshadow_{$feature_id}_{$sub_feature_key}";
		return (bool) get_option( $option_name, $default );
	}
}

