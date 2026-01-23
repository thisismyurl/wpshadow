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

require_once __DIR__ . '/../core/class-options-manager.php';

use WPShadow\Core\Options_Manager;

/**
 * Site Health test: Quick Scan recency.
 *
 * @return array Site Health test result.
 */
function wpshadow_site_health_test_quick_scan() {
	$badge = $GLOBALS['wpshadow_site_health_badge'] ?? array( 'label' => 'WPShadow', 'color' => 'blue' );
	$last  = Options_Manager::get_int( 'wpshadow_last_quick_checks', 0 );

	$now   = time();
	$label = __( 'WPShadow Quick Scan', 'wpshadow' );
	$desc  = __( 'WPShadow provides a fast, lightweight scan of common issues. Run it regularly to keep your site in shape.', 'wpshadow' );
	$action_url = admin_url( 'admin.php?page=wpshadow' );

	if ( empty( $last ) ) {
		return array(
			'label'       => $label,
			'status'      => 'recommended',
			'badge'       => $badge,
			'description' => __( 'Quick Scan has not been run yet. Open WPShadow to run a Quick Scan.', 'wpshadow' ),
			'actions'     => array(
				sprintf(
					'<a href="%s">%s</a>',
					esc_url( $action_url ),
					esc_html__( 'Run now', 'wpshadow' )
				),
			),
			'test'        => 'wpshadow_site_health_test_quick_scan',
		);
	}

	$age = $now - (int) $last;
	$age_str = sprintf( __( 'Last run %s ago.', 'wpshadow' ), human_time_diff( $last, $now ) );

	if ( $age > DAY_IN_SECONDS * 2 ) {
		return array(
			'label'       => $label,
			'status'      => 'recommended',
			'badge'       => $badge,
			'description' => $age_str . ' ' . __( 'Consider running a new Quick Scan.', 'wpshadow' ),
			'actions'     => array(
				sprintf(
					'<a href="%s">%s</a>',
					esc_url( $action_url ),
					esc_html__( 'Run now', 'wpshadow' )
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
function wpshadow_site_health_test_deep_scan() {
	$badge = $GLOBALS['wpshadow_site_health_badge'] ?? array( 'label' => 'WPShadow', 'color' => 'blue' );
	$last  = Options_Manager::get_int( 'wpshadow_last_heavy_tests', 0 );

	$now   = time();
	$label = __( 'WPShadow Deep Scan', 'wpshadow' );
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

/**
 * Site Health test: Overall WPShadow summary.
 *
 * @return array Site Health test result.
 */
function wpshadow_site_health_test_overall() {
	$badge = $GLOBALS['wpshadow_site_health_badge'] ?? array( 'label' => 'WPShadow', 'color' => 'blue' );
	$label = __( 'WPShadow Overall Status', 'wpshadow' );

	// If we have recent scans, mark good; otherwise recommend action.
	$quick = Options_Manager::get_int( 'wpshadow_last_quick_checks', 0 );
	$deep  = Options_Manager::get_int( 'wpshadow_last_heavy_tests', 0 );

	$action_url = admin_url( 'admin.php?page=wpshadow' );

	if ( empty( $quick ) && empty( $deep ) ) {
		return array(
			'label'       => $label,
			'status'      => 'recommended',
			'badge'       => $badge,
			'description' => __( 'No WPShadow scans have been recorded yet. Run Quick or Deep Scan in the WPShadow dashboard.', 'wpshadow' ),
			'actions'     => array(
				sprintf(
					'<a href="%s">%s</a>',
					esc_url( $action_url . '#quick-scan' ),
					esc_html__( 'Run now', 'wpshadow' )
				),
			),
			'test'        => 'wpshadow_site_health_test_overall',
		);
	}

	return array(
		'label'       => $label,
		'status'      => 'good',
		'badge'       => $badge,
		'description' => __( 'WPShadow scans are active. See the WPShadow dashboard for detailed category health.', 'wpshadow' ),
		'actions'     => array(
			sprintf(
				'<a href="%s">%s</a>',
				esc_url( $action_url ),
				esc_html__( 'View Dashboard', 'wpshadow' )
			),
		),
		'test'        => 'wpshadow_site_health_test_overall',
	);
}

/**
 * Site Health test: Individual WPShadow finding (Issue #558)
 *
 * @param array  $finding Finding data.
 * @param array  $badge Site Health badge.
 * @param string $finding_id Finding identifier.
 * @return array Site Health test result.
 */
function wpshadow_site_health_test_finding( $finding, $badge, $finding_id ) {
	$action_url = admin_url( 'admin.php?page=wpshadow' );
	$threat_level = isset( $finding['threat_level'] ) ? $finding['threat_level'] : 50;
	
	// Check if already fixed
	$status_manager = new \WPShadow\Core\Finding_Status_Manager();
	$status = $status_manager->get_finding_status( $finding_id );
	
	if ( $status === 'fixed' ) {
		return array(
			'label'       => $finding['title'] ?? __( 'Security Issue', 'wpshadow' ),
			'status'      => 'good',
			'badge'       => $badge,
			'description' => __( '✓ This issue has been resolved by WPShadow.', 'wpshadow' ),
			'test'        => 'wpshadow_finding_' . $finding_id,
		);
	}

	return array(
		'label'       => $finding['title'] ?? __( 'Security Issue', 'wpshadow' ),
		'status'      => $threat_level > 75 ? 'critical' : 'recommended',
		'badge'       => $badge,
		'description' => $finding['description'] ?? __( 'WPShadow has detected an issue.', 'wpshadow' ),
		'actions'     => array(
			sprintf(
				'<a href="%s">%s</a>',
				esc_url( $action_url ),
				esc_html__( 'View in WPShadow', 'wpshadow' )
			),
		),
		'test'        => 'wpshadow_finding_' . $finding_id,
	);
}
