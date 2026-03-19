<?php
/**
 * Version Checker - Feature Availability Utility
 *
 * Determines whether features are "live" based on their @since tags,
 * preventing cards/links from being displayed before release.
 *
 * @package    WPShadow
 * @subpackage Core
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Version checker for feature availability
 *
 * Usage:
 * ```php
 * if ( Version_Checker::is_feature_live( 'WPShadow\Diagnostics\Diagnostic_Example' ) ) {
 *     echo 'Display card';
 * }
 * ```
 *
 * @since 1.6093.1200
 */
class Version_Checker {

	/**
	 * Current plugin version
	 *
	 * @var string
	 */
	private static string $current_version = '';

	/**
	 * Cache of checked features
	 *
	 * @var array
	 */
	private static array $feature_cache = array();

	/**
	 * Initialize current version
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function init(): void {
		self::$current_version = defined( 'WPSHADOW_VERSION' ) ? WPSHADOW_VERSION : '';
	}

	/**
	 * Check if a feature/class is live (available in current version)
	 *
	 * Reads the @since tag from a class docblock and compares it to
	 * the current plugin version. Returns false if @since is newer.
	 *
	 * @since 1.6093.1200
	 * @param  string $class_name Full class name (e.g., 'WPShadow\Diagnostics\Diagnostic_Example').
	 * @return bool True if feature is live, false if coming soon.
	 */
	public static function is_feature_live( string $class_name ): bool {
		// Return cached result if available
		if ( isset( self::$feature_cache[ $class_name ] ) ) {
			return self::$feature_cache[ $class_name ];
		}

		$is_live = self::compare_versions( $class_name );
		self::$feature_cache[ $class_name ] = $is_live;

		return $is_live;
	}

	/**
	 * Compare class @since tag with current version
	 *
	 * @since 1.6093.1200
	 * @param  string $class_name Full class name.
	 * @return bool True if available, false if coming soon.
	 */
	private static function compare_versions( string $class_name ): bool {
		if ( empty( self::$current_version ) ) {
			self::init();
		}

		$since_version = self::extract_since_tag( $class_name );

		if ( empty( $since_version ) ) {
			// If no @since found, assume it's live (legacy safety)
			return true;
		}

		return self::is_version_available( $since_version );
	}

	/**
	 * Extract @since tag from class docblock
	 *
	 * @since 1.6093.1200
	 * @param  string $class_name Full class name.
	 * @return string Version string from @since tag, or empty string.
	 */
	private static function extract_since_tag( string $class_name ): string {
		try {
			if ( ! class_exists( $class_name, false ) ) {
				return '';
			}

			$reflection = new \ReflectionClass( $class_name );
			$docblock = $reflection->getDocComment();

			if ( empty( $docblock ) ) {
				return '';
			}

			// Match @since tag: @since 1.6093.1200
			if ( preg_match( '/@since\s+(\S+)/', $docblock, $matches ) ) {
				return $matches[1];
			}
		} catch ( \Exception $e ) {
			// Return empty if reflection fails
			return '';
		}

		return '';
	}

	/**
	 * Check if a version is available (less than or equal to current)
	 *
	 * WPShadow version format: 1.YDDD.HHMM
	 * - 1 = major version
	 * - YDDD = last digit of year + julian day
	 * - HHMM = hour and minute in 24-hour format
	 *
	 * @since 1.6093.1200
	 * @param  string $since_version Version to check.
	 * @return bool True if feature is available.
	 */
	private static function is_version_available( string $since_version ): bool {
		return version_compare( self::$current_version, $since_version, '>=' );
	}

	/**
	 * Get a feature's @since version
	 *
	 * Useful for displaying "Coming Soon in v1.6050.1200" messages
	 *
	 * @since 1.6093.1200
	 * @param  string $class_name Full class name.
	 * @return string Version string or empty string.
	 */
	public static function get_feature_since( string $class_name ): string {
		return self::extract_since_tag( $class_name );
	}

	/**
	 * Check if multiple features are all live
	 *
	 * Useful for checking if an entire category is available
	 *
	 * @since 1.6093.1200
	 * @param  array $class_names Array of full class names.
	 * @return bool True if all features are live.
	 */
	public static function are_all_features_live( array $class_names ): bool {
		foreach ( $class_names as $class_name ) {
			if ( ! self::is_feature_live( $class_name ) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Filter an array of features to only include live ones
	 *
	 * @since 1.6093.1200
	 * @param  array $class_names Array of class names.
	 * @return array Only live feature class names.
	 */
	public static function filter_live_features( array $class_names ): array {
		return array_filter(
			$class_names,
			function ( $class_name ) {
				return self::is_feature_live( $class_name );
			}
		);
	}

	/**
	 * Clear version cache (for testing)
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function clear_cache(): void {
		self::$feature_cache = array();
	}
}
