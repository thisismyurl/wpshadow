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

		$finding['id']  = $id;
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
 * Build diagnostic coverage details for dashboard test counts.
 *
 * @param array $executed_diagnostics Diagnostics that completed successfully.
 * @param int   $timestamp            Optional. Coverage timestamp. Default 0.
 * @return array Coverage data.
 */
function wpshadow_build_diagnostic_run_coverage( array $executed_diagnostics = array(), int $timestamp = 0 ): array {
	$coverage = array(
		'timestamp'  => max( 0, $timestamp ),
		'run'        => 0,
		'total'      => 0,
		'categories' => array(),
	);

	$category_meta = wpshadow_get_category_metadata();
	foreach ( $category_meta as $category_key => $meta ) {
		if ( 'overall' === $category_key || 'wordpress-health' === $category_key ) {
			continue;
		}

		$coverage['categories'][ $category_key ] = array(
			'run'   => 0,
			'total' => 0,
		);
	}

	if ( ! class_exists( '\WPShadow\Diagnostics\Diagnostic_Registry' ) || ! method_exists( '\WPShadow\Diagnostics\Diagnostic_Registry', 'get_diagnostic_file_map' ) ) {
		return $coverage;
	}

	$disabled = get_option( 'wpshadow_disabled_diagnostic_classes', array() );
	if ( ! is_array( $disabled ) ) {
		$disabled = array();
	}

	$executed_lookup = array();
	foreach ( $executed_diagnostics as $diagnostic_class ) {
		if ( ! is_string( $diagnostic_class ) || '' === $diagnostic_class ) {
			continue;
		}

		$short_name     = ltrim( $diagnostic_class, '\\' );
		$qualified_name = 0 === strpos( $short_name, 'WPShadow\\Diagnostics\\' )
			? $short_name
			: 'WPShadow\\Diagnostics\\' . $short_name;

		$executed_lookup[ $qualified_name ] = true;
		$executed_lookup[ str_replace( 'WPShadow\\Diagnostics\\', '', $qualified_name ) ] = true;
	}

	$diagnostic_file_map = WPShadow\Diagnostics\Diagnostic_Registry::get_diagnostic_file_map();

	foreach ( $diagnostic_file_map as $class_name => $diagnostic_data ) {
		$qualified_class = 0 === strpos( $class_name, 'WPShadow\\Diagnostics\\' )
			? $class_name
			: 'WPShadow\\Diagnostics\\' . $class_name;

		$is_disabled = in_array( $qualified_class, $disabled, true ) || in_array( $class_name, $disabled, true );
		if ( $is_disabled ) {
			continue;
		}

		++$coverage['total'];

		$category = sanitize_key( (string) ( $diagnostic_data['family'] ?? '' ) );
		if ( isset( $coverage['categories'][ $category ] ) ) {
			++$coverage['categories'][ $category ]['total'];
		}

		$did_run = isset( $executed_lookup[ $qualified_class ] ) || isset( $executed_lookup[ $class_name ] );
		if ( ! $did_run ) {
			continue;
		}

		++$coverage['run'];
		if ( isset( $coverage['categories'][ $category ] ) ) {
			++$coverage['categories'][ $category ]['run'];
		}
	}

	return $coverage;
}

/**
 * Get stored diagnostic coverage details.
 *
 * @return array Coverage data.
 */
function wpshadow_get_diagnostic_run_coverage(): array {
	$baseline = wpshadow_build_diagnostic_run_coverage();
	$stored   = get_option( 'wpshadow_diagnostic_run_coverage', array() );

	if ( ! is_array( $stored ) ) {
		return $baseline;
	}

	$baseline['timestamp'] = max( 0, (int) ( $stored['timestamp'] ?? 0 ) );
	$baseline['run']       = min( $baseline['total'], max( 0, (int) ( $stored['run'] ?? 0 ) ) );

	foreach ( $baseline['categories'] as $category_key => $counts ) {
		$stored_counts = $stored['categories'][ $category_key ] ?? array();
		if ( ! is_array( $stored_counts ) ) {
			continue;
		}

		$baseline['categories'][ $category_key ]['run'] = min(
			$counts['total'],
			max( 0, (int) ( $stored_counts['run'] ?? 0 ) )
		);
	}

	return $baseline;
}

/**
 * Persist diagnostic coverage details after a scan or report run.
 *
 * @param array $executed_diagnostics Diagnostics that completed successfully.
 * @param int   $timestamp            Optional. Coverage timestamp. Default 0.
 * @return array Coverage data.
 */
function wpshadow_record_diagnostic_run_coverage( array $executed_diagnostics, int $timestamp = 0 ): array {
	$recorded_at = $timestamp > 0 ? $timestamp : time();
	$coverage    = wpshadow_build_diagnostic_run_coverage( $executed_diagnostics, $recorded_at );

	update_option( 'wpshadow_diagnostic_run_coverage', $coverage );

	return $coverage;
}

/**
 * Resolve result cache TTL for a diagnostic class.
 *
 * @param string $class_name Fully-qualified diagnostic class name.
 * @return int TTL in seconds.
 */
function wpshadow_get_diagnostic_result_ttl( string $class_name ): int {
	$default_ttl = (int) get_option( 'wpshadow_cache_duration', HOUR_IN_SECONDS );
	if ( $default_ttl <= 0 ) {
		$default_ttl = HOUR_IN_SECONDS;
	}

	$ttl = $default_ttl;

	if ( defined( $class_name . '::RESULT_TTL' ) ) {
		$ttl = (int) constant( $class_name . '::RESULT_TTL' );
	} elseif ( defined( $class_name . '::CACHE_TTL' ) ) {
		$ttl = (int) constant( $class_name . '::CACHE_TTL' );
	}

	$ttl = max( 5 * MINUTE_IN_SECONDS, $ttl );
	$ttl = min( WEEK_IN_SECONDS, $ttl );

	/**
	 * Filters diagnostic result TTL.
	 *
	 * @param int    $ttl        TTL in seconds.
	 * @param string $class_name Fully-qualified diagnostic class name.
	 */
	return (int) apply_filters( 'wpshadow_diagnostic_result_ttl', $ttl, $class_name );
}

/**
 * Get all persisted diagnostic test states.
 *
 * @return array<string, array<string, mixed>> States keyed by class name.
 */
function wpshadow_get_diagnostic_test_states(): array {
	$stored = get_option( 'wpshadow_diagnostic_test_states', array() );
	if ( ! is_array( $stored ) ) {
		return array();
	}

	return $stored;
}

/**
 * Build transient key for a diagnostic state.
 *
 * @param string $class_name Fully-qualified diagnostic class name.
 * @return string Transient key.
 */
function wpshadow_get_diagnostic_state_transient_key( string $class_name ): string {
	return 'wpshadow_diag_state_' . md5( $class_name );
}

/**
 * Get a valid (non-expired) diagnostic test state.
 *
 * @param string $class_name Fully-qualified diagnostic class name.
 * @param int    $timestamp  Optional. Reference timestamp. Defaults to now.
 * @return array<string, mixed>|null Valid state when present, null otherwise.
 */
function wpshadow_get_valid_diagnostic_test_state( string $class_name, int $timestamp = 0 ): ?array {
	$transient_key = wpshadow_get_diagnostic_state_transient_key( $class_name );
	$cached_state  = get_transient( $transient_key );
	if ( is_array( $cached_state ) ) {
		$status = isset( $cached_state['status'] ) ? (string) $cached_state['status'] : 'unknown';
		if ( 'passed' === $status || 'failed' === $status ) {
			return $cached_state;
		}
	}

	$states = wpshadow_get_diagnostic_test_states();
	if ( ! isset( $states[ $class_name ] ) || ! is_array( $states[ $class_name ] ) ) {
		return null;
	}

	$state = $states[ $class_name ];
	$now   = $timestamp > 0 ? $timestamp : time();

	$expires_at = isset( $state['expires_at'] ) ? (int) $state['expires_at'] : 0;
	if ( $expires_at <= 0 || $expires_at < $now ) {
		return null;
	}

	$status = isset( $state['status'] ) ? (string) $state['status'] : 'unknown';
	if ( 'passed' !== $status && 'failed' !== $status ) {
		return null;
	}

	return $state;
}

/**
 * Persist diagnostic test states after a scan run.
 *
 * @param array $results   Diagnostic results keyed by class name.
 * @param int   $timestamp Optional. Checked timestamp. Defaults to now.
 * @return array<string, array<string, mixed>> Updated state map.
 */
function wpshadow_record_diagnostic_test_states( array $results, int $timestamp = 0 ): array {
	$checked_at = $timestamp > 0 ? $timestamp : time();
	$states     = wpshadow_get_diagnostic_test_states();

	foreach ( $results as $class_name => $result ) {
		if ( ! is_string( $class_name ) || '' === $class_name || ! is_array( $result ) ) {
			continue;
		}

		$status = isset( $result['status'] ) ? (string) $result['status'] : '';
		if ( 'passed' !== $status && 'failed' !== $status ) {
			continue;
		}

		$ttl = isset( $result['ttl'] ) ? (int) $result['ttl'] : wpshadow_get_diagnostic_result_ttl( $class_name );
		$ttl = max( 5 * MINUTE_IN_SECONDS, min( WEEK_IN_SECONDS, $ttl ) );

		$states[ $class_name ] = array(
			'status'     => $status,
			'category'   => isset( $result['category'] ) ? sanitize_key( (string) $result['category'] ) : '',
			'finding_id' => isset( $result['finding_id'] ) ? sanitize_key( (string) $result['finding_id'] ) : '',
			'checked_at' => $checked_at,
			'expires_at' => $checked_at + $ttl,
			'ttl'        => $ttl,
		);

		set_transient( wpshadow_get_diagnostic_state_transient_key( $class_name ), $states[ $class_name ], $ttl );
	}

	update_option( 'wpshadow_diagnostic_test_states', $states );

	return $states;
}

/**
 * Build gauge snapshot from findings.
 *
 * @param array $findings Findings array.
 * @param array $category_meta Category metadata.
 * @return array Gauge snapshot.
 */
function wpshadow_build_gauge_snapshot( array $findings, array $category_meta = array() ): array {
	$meta           = ! empty( $category_meta ) ? $category_meta : wpshadow_get_category_metadata();
	$by_category    = array();
	$gauges         = array();
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
		$cat_findings    = $by_category[ $key ] ?? array();
		$total           = count( $cat_findings );
		$total_findings += $total;

		$threat_total = 0;
		foreach ( $cat_findings as $finding ) {
			$threat_total += $finding['threat_level'] ?? 50;
			if ( isset( $finding['severity'] ) && 'critical' === $finding['severity'] ) {
				++$critical_count;
			}
		}

		$percent = $total > 0 ? min( 100, ( $threat_total / $total ) / 100 * 100 ) : 0;
		$percent = 100 - $percent;

		$gauges[ $key ] = array(
			'label'          => $cat_meta['label'],
			'percent'        => $percent,
			'findings_count' => $total,
			'color'          => $cat_meta['color'],
		);
	}

	// Sum all non-overall gauge percents for a 0–1000 overall score (10 categories × 100).
	$overall_sum = 0;
	foreach ( $gauges as $key => $gauge_data ) {
		if ( 'overall' !== $key ) {
			$overall_sum += $gauge_data['percent'];
		}
	}

	return array(
		'overall_health' => (int) round( $overall_sum ),
		'total_findings' => $total_findings,
		'critical_count' => $critical_count,
		'gauges'         => $gauges,
		'findings'       => $findings,
		'by_category'    => $by_category,
		'timestamp'      => time(),
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
