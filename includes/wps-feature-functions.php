<?php
/**
 * Feature Registry Helper Functions
 *
 * Convenience functions for plugins to interact with the feature registry.
 *
 * @package wp_support_SUPPORT
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

use WPS\CoreSupport\Features\WPS_Feature_Registry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register a feature provided by a plugin.
 *
 * @param mixed $feature Feature identifier or object implementing WPS_Feature_Interface.
 * @param array $data    Optional metadata for legacy array registration.
 *
 * @return void
 */
function register_WPS_feature( $feature, array $data = array() ): void {
	WPS_Feature_Registry::register_feature( $feature, $data );
}

/**
 * Check if a feature is available.
 *
 * @param string $feature The feature identifier.
 *
 * @return bool
 */
function has_WPS_feature( string $feature ): bool {
	$feature_data = WPS_Feature_Registry::get_feature( $feature );
	if ( $feature_data ) {
		return ! empty( $feature_data['enabled'] );
	}

	return WPS_Module_Registry::module_has_capability( $feature );
}

/**
 * Check if any of the given features are available (OR logic).
 *
 * @param string[] $features Array of feature identifiers.
 *
 * @return bool
 */
function has_any_WPS_feature( array $features ): bool {
	if ( WPS_Feature_Registry::has_any_feature( $features ) ) {
		return true;
	}

	foreach ( $features as $feature ) {
		if ( WPS_Module_Registry::module_has_capability( $feature ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Check if all given features are available (AND logic).
 *
 * @param string[] $features Array of feature identifiers.
 *
 * @return bool
 */
function has_all_WPS_features( array $features ): bool {
	foreach ( $features as $feature ) {
		if ( ! has_WPS_feature( $feature ) ) {
			return false;
		}
	}

	return true;
}

/**
 * Get all registered features.
 *
 * @return array<string, array<string, mixed>>
 */
function get_WPS_features(): array {
	return WPS_Feature_Registry::get_features();
}

/**
 * Get a specific feature's metadata.
 *
 * @param string $feature The feature identifier.
 *
 * @return array<string, mixed>|null
 */
function get_WPS_feature( string $feature ): ?array {
	return WPS_Feature_Registry::get_feature( $feature );
}

/**
 * Get registered modules with optional filtering.
 *
 * @param string|null $type  Module type filter.
 * @param string|null $suite Suite filter.
 * @return array
 */
function get_WPS_modules( ?string $type = null, ?string $suite = null ): array {
	return WPS_Module_Registry::get_modules_filtered( $type, $suite );
}
