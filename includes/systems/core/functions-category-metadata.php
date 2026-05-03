<?php
/**
 * Global Function Aliases for Category Metadata
 *
 * Provides backward-compatible global function aliases for namespaced functions.
 * This allows calling category metadata functions without namespace prefix.
 *
 * @package ThisIsMyURL\Shadow
 * @subpackage Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Ensure the namespaced file is loaded first
require_once __DIR__ . '/class-category-metadata.php';

/**
 * Global alias for ThisIsMyURL\Shadow\Core\thisismyurl_shadow_get_category_metadata()
 *
 * @return array Category metadata.
 */
function thisismyurl_shadow_get_category_metadata(): array {
	return \ThisIsMyURL\Shadow\Core\thisismyurl_shadow_get_category_metadata();
}

/**
 * Global alias for ThisIsMyURL\Shadow\Core\thisismyurl_shadow_calculate_overall_health()
 *
 * @param array $findings_by_category Findings grouped by category.
 * @param array $category_meta Category metadata array.
 * @return array Health status.
 */
function thisismyurl_shadow_calculate_overall_health( array $findings_by_category, array $category_meta ): array {
	return \ThisIsMyURL\Shadow\Core\thisismyurl_shadow_calculate_overall_health( $findings_by_category, $category_meta );
}

/**
 * Global alias for ThisIsMyURL\Shadow\Core\thisismyurl_shadow_calculate_wordpress_native_health()
 *
 * @return array WordPress health status.
 */
function thisismyurl_shadow_calculate_wordpress_native_health(): array {
	return \ThisIsMyURL\Shadow\Core\thisismyurl_shadow_calculate_wordpress_native_health();
}

/**
 * Get all site findings from the database
 *
 * Retrieves findings stored in 'thisismyurl_shadow_site_findings' option.
 *
 * @return array Array of findings.
 */
function thisismyurl_shadow_get_site_findings(): array {
	return get_option( 'thisismyurl_shadow_site_findings', array() );
}
