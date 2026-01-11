<?php
/**
 * Feature Registry for Suite Plugin Dependencies
 *
 * Allows plugins to register and check for capabilities/features they provide or require.
 * This enables flexible dependency management without hardcoding plugin names.
 *
 * @package wp_support_SUPPORT
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Feature Registry Class
 *
 * Manages registration and discovery of plugin features.
 */
class WPS_Feature_Registry {

	/**
	 * Registered features storage.
	 *
	 * @var array<string, array<string, mixed>>
	 */
	private static array $features = array();

	/**
	 * Initialize the registry.
	 *
	 * @return void
	 */
	public static function init(): void {
		// Provide hook for plugins to register features.
		add_action( 'WPS_register_features', array( __CLASS__, 'discover_features' ), 10, 0 );
	}

	/**
	 * Register a feature provided by a plugin.
	 *
	 * Features are organized by category and can have arbitrary metadata.
	 *
	 * Example:
	 *   register_WPS_feature( 'license_management', array(
	 *     'plugin'      => 'license-support-thisismyurl',
	 *     'name'        => 'License Management',
	 *     'description' => 'Handles license validation and activation',
	 *     'version'     => '1.0.0',
	 *   ) );
	 *
	 * @param string                   $feature The feature identifier (e.g., 'license_management').
	 * @param array<string, mixed> $data    Optional metadata about the feature.
	 *
	 * @return void
	 */
	public static function register_feature( string $feature, array $data = array() ): void {
		if ( empty( $feature ) ) {
			return;
		}

		self::$features[ $feature ] = array_merge(
			array(
				'registered_at' => time(),
			),
			$data
		);
	}

	/**
	 * Check if a feature is registered.
	 *
	 * @param string $feature The feature identifier to check.
	 *
	 * @return bool True if the feature is registered, false otherwise.
	 */
	public static function has_feature( string $feature ): bool {
		return isset( self::$features[ $feature ] );
	}

	/**
	 * Get all registered features.
	 *
	 * @return array<string, array<string, mixed>> All registered features.
	 */
	public static function get_features(): array {
		return self::$features;
	}

	/**
	 * Get a specific feature's metadata.
	 *
	 * @param string $feature The feature identifier.
	 *
	 * @return array<string, mixed>|null The feature metadata or null if not found.
	 */
	public static function get_feature( string $feature ): ?array {
		return self::$features[ $feature ] ?? null;
	}

	/**
	 * Check if any of multiple features are available.
	 *
	 * Useful for plugins with alternative dependencies.
	 *
	 * @param string[] $features Array of feature identifiers (OR logic).
	 *
	 * @return bool True if at least one feature is registered.
	 */
	public static function has_any_feature( array $features ): bool {
		foreach ( $features as $feature ) {
			if ( self::has_feature( $feature ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Check if all required features are available.
	 *
	 * @param string[] $features Array of feature identifiers (AND logic).
	 *
	 * @return bool True if all features are registered.
	 */
	public static function has_all_features( array $features ): bool {
		foreach ( $features as $feature ) {
			if ( ! self::has_feature( $feature ) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Discover and trigger feature registration from all plugins.
	 *
	 * @return void
	 */
	public static function discover_features(): void {
		// This hook is called on plugins_loaded to allow all plugins to register.
		do_action( 'WPS_register_features' );
	}
}

