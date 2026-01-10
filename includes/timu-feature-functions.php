<?php
/**
 * Feature Registry Helper Functions
 *
 * Convenience functions for plugins to interact with the feature registry.
 *
 * @package TIMU_CORE_SUPPORT
 */

declare(strict_types=1);

namespace TIMU\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register a feature provided by a plugin.
 *
 * @param string                   $feature The feature identifier.
 * @param array<string, mixed> $data    Optional metadata.
 *
 * @return void
 */
function register_timu_feature( string $feature, array $data = array() ): void {
	TIMU_Feature_Registry::register_feature( $feature, $data );
}

/**
 * Check if a feature is available.
 *
 * @param string $feature The feature identifier.
 *
 * @return bool
 */
function has_timu_feature( string $feature ): bool {
	if ( TIMU_Feature_Registry::has_feature( $feature ) ) {
		return true;
	}

	return TIMU_Module_Registry::module_has_capability( $feature );
}

/**
 * Check if any of the given features are available (OR logic).
 *
 * @param string[] $features Array of feature identifiers.
 *
 * @return bool
 */
function has_any_timu_feature( array $features ): bool {
	return TIMU_Feature_Registry::has_any_feature( $features );
}

/**
 * Check if all given features are available (AND logic).
 *
 * @param string[] $features Array of feature identifiers.
 *
 * @return bool
 */
function has_all_timu_features( array $features ): bool {
	return TIMU_Feature_Registry::has_all_features( $features );
}

/**
 * Get all registered features.
 *
 * @return array<string, array<string, mixed>>
 */
function get_timu_features(): array {
	return TIMU_Feature_Registry::get_features();
}

/**
 * Get a specific feature's metadata.
 *
 * @param string $feature The feature identifier.
 *
 * @return array<string, mixed>|null
 */
function get_timu_feature( string $feature ): ?array {
	return TIMU_Feature_Registry::get_feature( $feature );
}

/**
 * Get registered modules with optional filtering.
 *
 * @param string|null $type  Module type filter.
 * @param string|null $suite Suite filter.
 * @return array
 */
function get_timu_modules( ?string $type = null, ?string $suite = null ): array {
	return TIMU_Module_Registry::get_modules_filtered( $type, $suite );
}
