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

if (! defined('ABSPATH')) {
	exit;
}

require_once __DIR__ . '/../core/class-options-manager.php';
require_once __DIR__ . '/../diagnostics/class-diagnostic-registry.php';

use WPShadow\Core\Options_Manager;
use WPShadow\Diagnostics\Diagnostic_Registry;

// Initialize Site Health integration on admin_init
add_action( 'admin_init', 'wpshadow_register_diagnostic_site_health_tests', 20 );

/**
 * Site Health test: Quick Scan recency.
 *
 * @return array Site Health test result.
 */
function wpshadow_site_health_test_quick_scan()
{
	$badge = $GLOBALS['wpshadow_site_health_badge'] ?? array('label' => 'WPShadow', 'color' => 'blue');
	$last  = Options_Manager::get_int('wpshadow_last_quick_checks', 0);

	$now   = time();
	$label = __('WPShadow Quick Scan', 'wpshadow');
	$desc  = __('WPShadow provides a fast, lightweight scan of common issues. Run it regularly to keep your site in shape.', 'wpshadow');
	$action_url = admin_url('admin.php?page=wpshadow');

	if (empty($last)) {
		return array(
			'label'       => $label,
			'status'      => 'recommended',
			'badge'       => $badge,
			'description' => __('Quick Scan has not been run yet. Open WPShadow to run a Quick Scan.', 'wpshadow'),
			'actions'     => array(
				sprintf(
					'<a href="%s">%s</a>',
					esc_url($action_url),
					esc_html__('Run now', 'wpshadow')
				),
			),
			'test'        => 'wpshadow_site_health_test_quick_scan',
		);
	}

	$age = $now - (int) $last;
	$age_str = sprintf(__('Last run %s ago.', 'wpshadow'), human_time_diff($last, $now));

	if ($age > DAY_IN_SECONDS * 2) {
		return array(
			'label'       => $label,
			'status'      => 'recommended',
			'badge'       => $badge,
			'description' => $age_str . ' ' . __('Consider running a new Quick Scan.', 'wpshadow'),
			'actions'     => array(
				sprintf(
					'<a href="%s">%s</a>',
					esc_url($action_url),
					esc_html__('Run now', 'wpshadow')
				),
			),
			'test'        => 'wpshadow_site_health_test_quick_scan',
		);
	}

	return array(
		'label'       => $label,
		'status'      => 'good',
		'badge'       => $badge,
		'description' => $age_str,
		'test'        => 'wpshadow_site_health_test_quick_scan',
	);
}

/**
 * Site Health test: Deep Scan recency.
 *
 * @return array Site Health test result.
 */
function wpshadow_site_health_test_deep_scan()
{
	$badge = $GLOBALS['wpshadow_site_health_badge'] ?? array('label' => 'WPShadow', 'color' => 'blue');
	$last  = Options_Manager::get_int('wpshadow_last_heavy_tests', 0);

	$now   = time();
	$label = __('WPShadow Deep Scan', 'wpshadow');
	$action_url = admin_url('admin.php?page=wpshadow');

	if (empty($last)) {
		return array(
			'label'       => $label,
			'status'      => 'recommended',
			'badge'       => $badge,
			'description' => __('Deep Scan has not been run yet. Open WPShadow to run a Deep Scan.', 'wpshadow'),
			'actions'     => array(
				sprintf(
					'<a href="%s">%s</a>',
					esc_url($action_url . '#deep-scan'),
					esc_html__('Run now', 'wpshadow')
				),
			),
			'test'        => 'wpshadow_site_health_test_deep_scan',
		);
	}

	$age = $now - (int) $last;
	$age_str = sprintf(__('Last run %s ago.', 'wpshadow'), human_time_diff($last, $now));

	if ($age > WEEK_IN_SECONDS) {
		return array(
			'label'       => $label,
			'status'      => 'recommended',
			'badge'       => $badge,
			'description' => $age_str . ' ' . __('Consider running a new Deep Scan.', 'wpshadow'),
			'actions'     => array(
				sprintf(
					'<a href="%s">%s</a>',
					esc_url($action_url . '#deep-scan'),
					esc_html__('Run now', 'wpshadow')
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

/**
 * Site Health test: Overall WPShadow summary.
 *
 * @return array Site Health test result.
 */
function wpshadow_site_health_test_overall()
{
	$badge = $GLOBALS['wpshadow_site_health_badge'] ?? array('label' => 'WPShadow', 'color' => 'blue');
	$label = __('WPShadow Overall Status', 'wpshadow');

	// If we have recent scans, mark good; otherwise recommend action.
	$quick = Options_Manager::get_int('wpshadow_last_quick_checks', 0);
	$deep  = Options_Manager::get_int('wpshadow_last_heavy_tests', 0);

	$action_url = admin_url('admin.php?page=wpshadow');

	if (empty($quick) && empty($deep)) {
		return array(
			'label'       => $label,
			'status'      => 'recommended',
			'badge'       => $badge,
			'description' => __('No WPShadow scans have been recorded yet. Run Quick or Deep Scan in the WPShadow dashboard.', 'wpshadow'),
			'actions'     => array(
				sprintf(
					'<a href="%s">%s</a>',
					esc_url($action_url . '#quick-scan'),
					esc_html__('Run now', 'wpshadow')
				),
			),
			'test'        => 'wpshadow_site_health_test_overall',
		);
	}

	return array(
		'label'       => $label,
		'status'      => 'good',
		'badge'       => $badge,
		'description' => __('WPShadow scans are active. See the WPShadow dashboard for detailed category health.', 'wpshadow'),
		'actions'     => array(
			sprintf(
				'<a href="%s">%s</a>',
				esc_url($action_url),
				esc_html__('View Dashboard', 'wpshadow')
			),
		),
		'test'        => 'wpshadow_site_health_test_overall',
	);
}

/**
 * Initialize Site Health tests for all active diagnostics.
 *
 * Registers individual test callbacks for each diagnostic that has findings.
 * This replaces the grouped approach with individual recommendations per issue.
 * Issue #558: site-health.php page improvements
 *
 * @return void
 */
function wpshadow_init_individual_diagnostic_tests() {
	// Get all findings from the last scan
	$findings = get_option( 'wpshadow_last_findings', array() );

	if ( empty( $findings ) || ! is_array( $findings ) ) {
		return;
	}

	// Create test callbacks for each finding
	foreach ( $findings as $diagnostic_id => $finding_data ) {
		// Create a unique callback for this diagnostic
		$callback_name = 'wpshadow_site_health_test_' . sanitize_key( $diagnostic_id );

		// Only register if not already registered
		if ( ! function_exists( $callback_name ) ) {
			// Create closure with captured variables
			$closure = function() use ( $diagnostic_id, $finding_data ) {
				return wpshadow_generate_site_health_test( $diagnostic_id, $finding_data );
			};

			// Register the test
			add_filter(
				'site_health_navigation_tabs',
				function( $tabs ) use ( $diagnostic_id, $callback_name ) {
					return $tabs;
				}
			);
		}
	}

	// Register with Site Health
	add_filter( 'site_health_test_result', 'wpshadow_filter_site_health_tests', 10, 2 );
}

/**
 * Generate individual Site Health test for a diagnostic.
 *
 * @param string $diagnostic_id Diagnostic identifier.
 * @param array  $finding_data Finding data from last scan.
 * @return array Site Health test result.
 */
function wpshadow_generate_site_health_test( $diagnostic_id, $finding_data ) {
	$badge = array(
		'label' => 'WPShadow',
		'color' => 'blue',
	);

	$action_url = admin_url( 'admin.php?page=wpshadow' );
	$title      = isset( $finding_data['title'] ) ? $finding_data['title'] : $diagnostic_id;
	$description = isset( $finding_data['description'] ) ? $finding_data['description'] : __( 'WPShadow has detected an issue.', 'wpshadow' );
	$status     = isset( $finding_data['status'] ) ? $finding_data['status'] : 'recommended';
	$severity   = isset( $finding_data['severity'] ) ? (int) $finding_data['severity'] : 50;

	// Map severity to Site Health status
	$site_health_status = 'recommended';
	if ( $severity >= 75 ) {
		$site_health_status = 'critical';
	} elseif ( $severity >= 50 ) {
		$site_health_status = 'recommended';
	} else {
		$site_health_status = 'good';
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
		'test'        => 'wpshadow_diagnostic_' . sanitize_key( $diagnostic_id ),
	);
}

/**
 * Filter and register WPShadow diagnostic tests with WordPress Site Health.
 *
 * Dynamically registers individual diagnostic tests instead of grouping them.
 * Issue #558: Ensures each diagnostic is a standalone recommendation.
 *
 * @param array  $result Test result.
 * @param string $test Test identifier.
 * @return array Modified test result.
 */
function wpshadow_filter_site_health_tests( $result, $test ) {
	// This allows dynamic test registration through the Site Health filter
	return $result;
}

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
	// Only run if we have findings
	$findings = get_option( 'wpshadow_last_findings', array() );

	if ( empty( $findings ) || ! is_array( $findings ) ) {
		return;
	}

	$badge = array(
		'label' => 'WPShadow',
		'color' => 'blue',
	);

	// Register each finding as an individual test
	foreach ( $findings as $diagnostic_id => $finding_data ) {
		$test_id = 'wpshadow_diagnostic_' . sanitize_key( $diagnostic_id );

		// Register the test using WordPress Site Health API
		add_filter(
			'site_health_tests',
			function( $tests ) use ( $test_id, $diagnostic_id, $finding_data, $badge ) {
				// Add to Site Health tests if not already present
				if ( ! isset( $tests['direct'][ $test_id ] ) && ! isset( $tests['async'][ $test_id ] ) ) {
					// Create inline test callback
					$tests['direct'][ $test_id ] = array(
						'label' => isset( $finding_data['title'] ) ? $finding_data['title'] : $diagnostic_id,
						'test'  => function() use ( $diagnostic_id, $finding_data, $badge, $test_id ) {
							return wpshadow_generate_diagnostic_site_health_result( $diagnostic_id, $finding_data, $badge, $test_id );
						},
					);
				}

				return $tests;
			}
		);
	}
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
	$action_url = admin_url( 'admin.php?page=wpshadow' );
	$title      = isset( $finding_data['title'] ) ? $finding_data['title'] : $diagnostic_id;
	$description = isset( $finding_data['description'] ) ? $finding_data['description'] : __( 'WPShadow has detected an issue.', 'wpshadow' );
	$severity   = isset( $finding_data['severity'] ) ? (int) $finding_data['severity'] : 50;

	// Map severity to Site Health status
	$site_health_status = 'recommended';
	if ( $severity >= 75 ) {
		$site_health_status = 'critical';
	} elseif ( $severity >= 50 ) {
		$site_health_status = 'recommended';
	} else {
		$site_health_status = 'good';
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
