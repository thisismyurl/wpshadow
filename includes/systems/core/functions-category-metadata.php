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

/**
 * Get all site findings from the database
 *
 * Retrieves findings stored in 'wpshadow_site_findings' option.
 *
 * @return array Array of findings.
 */
function wpshadow_get_site_findings(): array {
	return get_option( 'wpshadow_site_findings', array() );
}

/**
 * Get human-readable threat level label
 *
 * Converts numeric threat level (0-100) to readable label.
 *
 * @since  1.6046.2100
 * @param  int $threat_level Threat level (0-100).
 * @return string Threat label (Critical, High, Medium, Low).
 */
function wpshadow_get_threat_label( int $threat_level ): string {
	if ( $threat_level >= 80 ) {
		return __( 'Critical', 'wpshadow' );
	} elseif ( $threat_level >= 60 ) {
		return __( 'High', 'wpshadow' );
	} elseif ( $threat_level >= 40 ) {
		return __( 'Medium', 'wpshadow' );
	} else {
		return __( 'Low', 'wpshadow' );
	}
}

/**
 * Get threat-level gauge color
 *
 * Returns a color code for visualizing threat levels.
 *
 * @since  1.6046.2100
 * @param  int $threat_level Threat level (0-100).
 * @return string Hex color code.
 */
function wpshadow_get_threat_gauge_color( int $threat_level ): string {
	if ( $threat_level >= 80 ) {
		return '#f44336'; // Red - Critical
	} elseif ( $threat_level >= 60 ) {
		return '#ff9800'; // Orange - High
	} elseif ( $threat_level >= 40 ) {
		return '#ffc107'; // Amber - Medium
	} else {
		return '#2196f3'; // Blue - Low
	}
}
