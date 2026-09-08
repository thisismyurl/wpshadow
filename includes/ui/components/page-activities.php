<?php
/**
 * Page Activity Display Component
 *
 * Reusable component for displaying page-specific activities with real-time AJAX updates.
 * Displays filtered activities based on current page context (tools, reports, workflows, etc.)
 *
 * @package WPShadow
 * @subpackage Views
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render page-specific activity display
 *
 * @param string $context Page context (tools, reports, workflows, settings, security, performance)
 * @param int    $limit Maximum activities to display (default: 10)
 * @param string $report_slug Optional report slug for report-specific filtering
 * @return void
 */
function wpshadow_render_page_activities( string $context = '', int $limit = 10, string $report_slug = '' ): void {
	$show_activities = (bool) apply_filters( 'wpshadow_show_page_activities', false, $context, $limit, $report_slug );
	if ( ! $show_activities ) {
		return;
	}

	if ( empty( $context ) ) {
		return;
	}

	// The dedicated activity timeline JS bundle is not shipped in the current build,
	// so this component renders nothing until that bundle is added back.
	return;
}

/**
 * Localization data for activity display
 */
function wpshadow_activity_display_localization(): void {
	if ( is_admin() && function_exists( 'get_current_screen' ) ) {
		$screen = get_current_screen();
		if ( ! $screen || strpos( (string) $screen->id, 'wpshadow' ) === false ) {
			return;
		}
	}

	if ( ! wp_script_is( 'wpshadow-page-activities', 'enqueued' ) ) {
		return;
	}

	wp_localize_script( 'wpshadow-page-activities', 'wpshadow_i18n', array(
		'no_activities' => __( 'No activities yet', 'wpshadow' ),
		'loading'       => __( 'Loading activity...', 'wpshadow' ),
		'report_label'  => __( 'Report', 'wpshadow' ),
		'page_label'    => __( 'Page', 'wpshadow' ),
		'of_label'      => __( 'of', 'wpshadow' ),
	) );
}
add_action( 'wp_enqueue_scripts', 'wpshadow_activity_display_localization' );
add_action( 'admin_enqueue_scripts', 'wpshadow_activity_display_localization' );
