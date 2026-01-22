<?php
/**
 * Finding Utilities for WPShadow
 *
 * Finding data manipulation and caching helpers.
 *
 * @package WPShadow
 * @subpackage Core
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Index findings by ID.
 *
 * @param array $findings Findings array.
 * @return array Indexed findings.
 */
function wpshadow_index_findings_by_id( array $findings ): array {
	$indexed = array();

	foreach ( $findings as $finding ) {
		if ( ! is_array( $finding ) ) {
			continue;
		}

		$id = $finding['id'] ?? sanitize_title( (string) ( $finding['title'] ?? '' ) );

		if ( empty( $id ) ) {
			$id = md5( wp_json_encode( $finding ) );
		}

		$finding['id'] = $id;
		$indexed[ $id ] = $finding;
	}

	return $indexed;
}

/**
 * Get cached findings.
 *
 * @return array Cached findings.
 */
function wpshadow_get_cached_findings(): array {
	$cached = get_option( 'wpshadow_site_findings', array() );

	if ( ! is_array( $cached ) ) {
		return array();
	}

	return array_values( $cached );
}

/**
 * Build gauge snapshot from findings.
 *
 * @param array $findings Findings array.
 * @param array $category_meta Category metadata.
 * @return array Gauge snapshot.
 */
function wpshadow_build_gauge_snapshot( array $findings, array $category_meta = array() ): array {
	$meta = ! empty( $category_meta ) ? $category_meta : wpshadow_get_gauge_category_meta();
	$by_category = array();
	$gauges = array();
	$total_findings = 0;
	$critical_count = 0;

	foreach ( $findings as $finding ) {
		$cat = $finding['category'] ?? 'uncategorized';
		if ( ! isset( $by_category[ $cat ] ) ) {
			$by_category[ $cat ] = array();
		}
		$by_category[ $cat ][] = $finding;
	}

	foreach ( $meta as $key => $cat_meta ) {
		$cat_findings = $by_category[ $key ] ?? array();
		$total = count( $cat_findings );
		$total_findings += $total;

		$threat_total = 0;
		foreach ( $cat_findings as $finding ) {
			$threat_total += $finding['threat_level'] ?? 50;
			if ( isset( $finding['severity'] ) && 'critical' === $finding['severity'] ) {
				$critical_count++;
			}
		}

		$percent = $total > 0 ? min( 100, ( $threat_total / $total ) / 100 * 100 ) : 0;
		$percent = 100 - $percent;

		$gauges[ $key ] = array(
			'label' => $cat_meta['label'],
			'percent' => $percent,
			'findings_count' => $total,
			'color' => $cat_meta['color'],
		);
	}

	$overall_health = wpshadow_calculate_overall_health( $by_category, $meta );

	return array(
		'overall_health' => round( $overall_health['score'] ),
		'total_findings' => $total_findings,
		'critical_count' => $critical_count,
		'gauges' => $gauges,
		'findings' => $findings,
		'by_category' => $by_category,
		'timestamp' => time(),
	);
}

/**
 * Store gauge snapshot.
 *
 * @param array $findings Findings array.
 * @return array Snapshot.
 */
function wpshadow_store_gauge_snapshot( array $findings ): array {
	$indexed_findings = wpshadow_index_findings_by_id( $findings );
	update_option( 'wpshadow_site_findings', $indexed_findings );
	update_option( 'wpshadow_last_findings_update', time() );

	$snapshot = wpshadow_build_gauge_snapshot( array_values( $indexed_findings ) );
	update_option( 'wpshadow_dashboard_snapshot', $snapshot );

	return $snapshot;
}
