<?php
/**
 * Global Function Aliases for Category Metadata
 *
 * Provides backward-compatible global function aliases for namespaced functions.
 * This allows calling category metadata functions without namespace prefix.
 *
 * @package WPShadow
 * @subpackage Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Ensure the namespaced file is loaded first
require_once __DIR__ . '/class-category-metadata.php';

/**
 * Global alias for WPShadow\Core\wpshadow_get_category_metadata()
 *
 * @return array Category metadata.
 */
function wpshadow_get_category_metadata(): array {
	return \WPShadow\Core\wpshadow_get_category_metadata();
}

/**
 * Global alias for WPShadow\Core\wpshadow_calculate_overall_health()
 *
 * @param array $findings_by_category Findings grouped by category.
 * @param array $category_meta Category metadata array.
 * @return array Health status.
 */
function wpshadow_calculate_overall_health( array $findings_by_category, array $category_meta ): array {
	return \WPShadow\Core\wpshadow_calculate_overall_health( $findings_by_category, $category_meta );
}

/**
 * Global alias for WPShadow\Core\wpshadow_calculate_wordpress_native_health()
 *
 * @return array WordPress health status.
 */
function wpshadow_calculate_wordpress_native_health(): array {
	return \WPShadow\Core\wpshadow_calculate_wordpress_native_health();
}
