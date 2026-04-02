<?php

/**
 * Site Health Bridge for WPShadow
 *
 * Integrates WPShadow with WordPress Site Health.
 *
 * @package WPShadow
 * @subpackage Admin
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/../systems/core/class-options-manager.php';
require_once __DIR__ . '/../systems/diagnostics/class-diagnostic-registry.php';

use WPShadow\Core\Options_Manager;
use WPShadow\Diagnostics\Diagnostic_Registry;

// Severity thresholds for Site Health status mapping.
if ( ! defined( 'WPSHADOW_SEVERITY_CRITICAL_THRESHOLD' ) ) {
	define( 'WPSHADOW_SEVERITY_CRITICAL_THRESHOLD', 75 );
}
if ( ! defined( 'WPSHADOW_SEVERITY_RECOMMENDED_THRESHOLD' ) ) {
	define( 'WPSHADOW_SEVERITY_RECOMMENDED_THRESHOLD', 50 );
}

// Initialize Site Health integration on admin_init
add_action( 'admin_init', 'wpshadow_register_diagnostic_site_health_tests', 20 );

/**
 * Site Health test: Deep Scan recency.
 *
 * @return array Site Health test result.
 */
function wpshadow_site_health_test_deep_scan() {
	$badge = $GLOBALS['wpshadow_site_health_badge'] ?? array(
		'label' => 'WPShadow',
		'color' => 'blue',
	);
	$last  = Options_Manager::get_int( 'wpshadow_last_heavy_tests', 0 );

	$now        = time();
	$label      = __( 'WPShadow Deep Scan', 'wpshadow' );
	$action_url = admin_url( 'admin.php?page=wpshadow' );

	if ( empty( $last ) ) {
		return array(
			'label'       => $label,
			'status'      => 'recommended',
			'badge'       => $badge,
			'description' => __( 'Deep Scan has not been run yet. Open WPShadow to run a Deep Scan.', 'wpshadow' ),
			'actions'     => array(
				sprintf(
					'<a href="%s">%s</a>',
					esc_url( $action_url . '#deep-scan' ),
					esc_html__( 'Run now', 'wpshadow' )
				),
			),
			'test'        => 'wpshadow_site_health_test_deep_scan',
		);
	}

	$age = $now - (int) $last;
	/* translators: %s: human-readable time difference */
	$age_str = sprintf( __( 'Last run %s ago.', 'wpshadow' ), human_time_diff( $last, $now ) );

	if ( $age > WEEK_IN_SECONDS ) {
		return array(
			'label'       => $label,
			'status'      => 'recommended',
			'badge'       => $badge,
			'description' => $age_str . ' ' . __( 'Consider running a new Deep Scan.', 'wpshadow' ),
			'actions'     => array(
				sprintf(
					'<a href="%s">%s</a>',
					esc_url( $action_url . '#deep-scan' ),
					esc_html__( 'Run now', 'wpshadow' )
				),
			),
			'test'        => 'wpshadow_site_health_test_deep_scan',
		);
	}

	return array(
		'label'       => $label,
		'status'      => 'good',
		'badge'       => $badge,
		'description' => $age_str,
		'test'        => 'wpshadow_site_health_test_deep_scan',
	);
}

// Removed wpshadow_site_health_test_overall function per Issue #558
// Individual findings are now shown as separate recommendations instead of being lumped together

// Removed unused functions that were never properly implemented:
// - wpshadow_init_individual_diagnostic_tests
// - wpshadow_generate_site_health_test
// - wpshadow_filter_site_health_tests
// All functionality is now in wpshadow_register_diagnostic_site_health_tests

/**
 * Register all WPShadow diagnostic tests directly with Site Health.
 *
 * Instead of dynamically creating tests, we register them all at once.
 * This ensures individual findings are listed separately in Site Health.
 * Issue #558 Implementation.
 *
 * @return void
 */
function wpshadow_register_diagnostic_site_health_tests() {
	// Bug fix: Changed from 'wpshadow_last_findings' (which was never set) to
	// 'wpshadow_site_findings' (the actual option used throughout the codebase).
	// See: class-finding-utils.php line 126 for where this option is updated.
	$findings = get_option( 'wpshadow_site_findings', array() );

	if ( empty( $findings ) || ! is_array( $findings ) ) {
		return;
	}

	$badge = array(
		'label' => 'WPShadow',
		'color' => 'blue',
	);

	// Register all findings as individual tests in a single filter callback
	add_filter(
		'site_health_tests',
		function ( $tests ) use ( $findings, $badge ) {
			// Register each finding as an individual test
			foreach ( $findings as $diagnostic_id => $finding_data ) {
				$test_id = 'wpshadow_diagnostic_' . sanitize_key( $diagnostic_id );

				// Add to Site Health tests if not already present
				if ( ! isset( $tests['direct'][ $test_id ] ) && ! isset( $tests['async'][ $test_id ] ) ) {
					// Create inline test callback
					$tests['direct'][ $test_id ] = array(
						'label' => isset( $finding_data['title'] ) ? $finding_data['title'] : $diagnostic_id,
						'test'  => function () use ( $diagnostic_id, $finding_data, $badge, $test_id ) {
							return wpshadow_generate_diagnostic_site_health_result( $diagnostic_id, $finding_data, $badge, $test_id );
						},
					);
				}
			}

			return $tests;
		}
	);
}

/**
 * Generate Site Health result for a specific diagnostic.
 *
 * @param string $diagnostic_id Diagnostic identifier.
 * @param array  $finding_data Finding data.
 * @param array  $badge Site Health badge.
 * @param string $test_id Test identifier.
 * @return array Site Health result.
 */
function wpshadow_generate_diagnostic_site_health_result( $diagnostic_id, $finding_data, $badge, $test_id ) {
	$action_url  = admin_url( 'admin.php?page=wpshadow' );
	$title       = isset( $finding_data['title'] ) ? $finding_data['title'] : $diagnostic_id;
	$description = isset( $finding_data['description'] ) ? $finding_data['description'] : __( 'WPShadow has detected an issue.', 'wpshadow' );

	// Get severity - can be string (high/medium/low) or numeric (0-100)
	$severity = isset( $finding_data['severity'] ) ? $finding_data['severity'] : 'medium';

	// Map severity to Site Health status
	// String format: 'high', 'medium', 'low'
	// Numeric format: 0-100 (from threat_level)
	$site_health_status = 'recommended';

	if ( is_numeric( $severity ) ) {
		// Numeric severity (0-100)
		$severity_num = (int) $severity;
		if ( $severity_num >= WPSHADOW_SEVERITY_CRITICAL_THRESHOLD ) {
			$site_health_status = 'critical';
		} elseif ( $severity_num >= WPSHADOW_SEVERITY_RECOMMENDED_THRESHOLD ) {
			$site_health_status = 'recommended';
		} else {
			$site_health_status = 'good';
		}
	} else {
		// String severity
		$severity_str = strtolower( (string) $severity );
		if ( 'critical' === $severity_str ) {
			$site_health_status = 'critical';
		} elseif ( 'high' === $severity_str ) {
			$site_health_status = 'critical';
		} elseif ( 'medium' === $severity_str ) {
			$site_health_status = 'recommended';
		} else {
			// 'low' or anything else
			$site_health_status = 'good';
		}
	}

	return array(
		'label'       => $title,
		'status'      => $site_health_status,
		'badge'       => $badge,
		'description' => $description,
		'actions'     => array(
			sprintf(
				'<a href="%s">%s</a>',
				esc_url( $action_url . '#' . sanitize_title_with_dashes( $diagnostic_id ) ),
				esc_html__( 'View in WPShadow', 'wpshadow' )
			),
		),
		'test'        => $test_id,
	);
}
