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

		// Honor preserve-plugin-versions setting.
		if ( self::should_preserve_plugin_version( $src ) ) {
			return $src;
		}

		return remove_query_arg( 'ver', $src );
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

