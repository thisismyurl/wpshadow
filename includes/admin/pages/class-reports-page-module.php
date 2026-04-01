<?php
/**
 * Reports Page Module for WPShadow
 *
 * Handles reports page rendering with card-based navigation to individual reports.
 *
 * @package WPShadow
 * @subpackage Admin
 */

declare(strict_types=1);

use WPShadow\Core\Form_Param_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle user privacy report downloads before admin output.
 *
 * @return void
 */
function wpshadow_maybe_handle_user_privacy_download() {
	if ( ! is_admin() ) {
		return;
	}

	$page   = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
	$report = isset( $_GET['report'] ) ? sanitize_key( wp_unslash( $_GET['report'] ) ) : '';
	$download = isset( $_GET['download'] ) ? sanitize_key( wp_unslash( $_GET['download'] ) ) : '';
	$snapshot_id = isset( $_GET['snapshot_id'] ) ? absint( wp_unslash( $_GET['snapshot_id'] ) ) : 0;

	if ( 'wpshadow-reports' !== $page || 'user-privacy-report' !== $report || empty( $download ) || ! $snapshot_id ) {
		return;
	}

	require_once WPSHADOW_PATH . 'includes/views/reports/partials/user-privacy-download-handler.php';
	if ( function_exists( 'wpshadow_handle_user_privacy_download' ) ) {
		wpshadow_handle_user_privacy_download();
	}
}

add_action( 'admin_init', 'wpshadow_maybe_handle_user_privacy_download', 1 );

/**
 * Handle SEO report downloads before admin output.
 *
 * @return void
 */
function wpshadow_maybe_handle_seo_report_download() {
	if ( ! is_admin() ) {
		return;
	}

	$page       = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
	$report     = isset( $_GET['report'] ) ? sanitize_key( wp_unslash( $_GET['report'] ) ) : '';
	$download   = isset( $_GET['download'] ) ? sanitize_key( wp_unslash( $_GET['download'] ) ) : '';
	$snapshot_id = isset( $_GET['snapshot_id'] ) ? absint( wp_unslash( $_GET['snapshot_id'] ) ) : 0;

	if ( 'wpshadow-reports' !== $page || 'seo-report' !== $report || empty( $download ) || ! $snapshot_id ) {
		return;
	}

	require_once WPSHADOW_PATH . 'includes/views/reports/partials/seo-download-handler.php';
	if ( function_exists( 'wpshadow_handle_seo_report_download' ) ) {
		wpshadow_handle_seo_report_download();
	}
}

add_action( 'admin_init', 'wpshadow_maybe_handle_seo_report_download', 1 );

/**
 * Handle delete all privacy reports action.
 *
 * @return void
 */
function wpshadow_handle_delete_privacy_reports() {
	check_admin_referer( 'wpshadow_delete_privacy_reports', 'wpshadow_delete_privacy_reports_nonce' );

	$current_user_id = get_current_user_id();
	$can_view_others = current_user_can( 'list_users' );
	$selected_user_id = $can_view_others
		? (int) ( isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : $current_user_id )
		: $current_user_id;

	$can_manage_reports = $can_view_others || $selected_user_id === $current_user_id;

	if ( $can_manage_reports && class_exists( 'WPShadow\\Reporting\\Report_Snapshot_Manager' ) ) {
		\WPShadow\Reporting\Report_Snapshot_Manager::delete_snapshots_for_user( 'user-privacy-report', $selected_user_id );
	}

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'    => 'wpshadow-reports',
				'report'  => 'user-privacy-report',
				'user_id' => $selected_user_id,
			),
			admin_url( 'admin.php' )
		)
	);
	exit;
}

add_action( 'admin_post_wpshadow_delete_privacy_reports', 'wpshadow_handle_delete_privacy_reports' );

/**
 * Handle delete all email reports action.
 *
 * @return void
 */
function wpshadow_handle_delete_email_reports() {
	check_admin_referer( 'wpshadow_delete_email_reports', 'wpshadow_delete_email_reports_nonce' );

	if ( current_user_can( 'manage_options' ) ) {
		$upload_dir  = wp_upload_dir();
		$reports_dir = trailingslashit( $upload_dir['basedir'] ) . 'wpshadow-reports/';

		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();
		global $wp_filesystem;

		if ( $wp_filesystem && is_dir( $reports_dir ) ) {
			$files     = glob( $reports_dir . 'email-report-*' );
			$base_real = realpath( $reports_dir );
			foreach ( $files as $file ) {
				$file_real = realpath( $file );
				if ( $base_real && $file_real && 0 === strpos( $file_real, $base_real ) ) {
					$wp_filesystem->delete( $file_real, false, 'f' );
				}
			}
		}
	}

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'   => 'wpshadow-reports',
				'report' => 'email-report',
			),
			admin_url( 'admin.php' )
		)
	);
	exit;
}

add_action( 'admin_post_wpshadow_delete_email_reports', 'wpshadow_handle_delete_email_reports' );

/**
 * Handle delete all SEO reports action.
 *
 * @return void
 */
function wpshadow_handle_delete_seo_reports() {
	check_admin_referer( 'wpshadow_delete_seo_reports', 'wpshadow_delete_seo_reports_nonce' );

	if ( current_user_can( 'manage_options' ) && class_exists( 'WPShadow\\Reporting\\Report_Snapshot_Manager' ) ) {
		\WPShadow\Reporting\Report_Snapshot_Manager::delete_snapshots( 'seo-report' );
	}

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'   => 'wpshadow-reports',
				'report' => 'seo-report',
			),
			admin_url( 'admin.php' )
		)
	);
	exit;
}

add_action( 'admin_post_wpshadow_delete_seo_reports', 'wpshadow_handle_delete_seo_reports' );

/**
 * Get dashboard gauge reports keyed by report slug.
 *
 * @since  0.6090.1200
 * @return array<string, array<string, mixed>> Dashboard gauge report definitions.
 */
function wpshadow_get_dashboard_gauge_report_map(): array {
	return array(
		'overall-health-report' => array(
			'title'    => __( 'Overall Health Report', 'wpshadow' ),
			'desc'     => __( 'Detailed view of how your dashboard gauges are performing across security, speed, search visibility, accessibility, settings, and automation. See which areas are strongest and which ones deserve attention next.', 'wpshadow' ),
			'report'   => 'overall-health-report',
			'category' => 'overall',
			'icon'     => 'dashicons-heart',
			'family'   => 'analysis',
			'enabled'  => true,
			'since'    => '0.6090.1200',
		),
		'security-gauge-report' => array(
			'title'    => __( 'Security Gauge Report', 'wpshadow' ),
			'desc'     => __( 'Detailed breakdown of the diagnostics behind your Security gauge so you can see which checks passed, which ones need attention, and what each result means in plain language.', 'wpshadow' ),
			'report'   => 'security-gauge-report',
			'category' => 'security',
			'icon'     => 'dashicons-shield-alt',
			'family'   => 'security',
			'enabled'  => true,
			'since'    => '0.6090.1200',
		),
		'performance-gauge-report' => array(
			'title'    => __( 'Performance Gauge Report', 'wpshadow' ),
			'desc'     => __( 'Detailed breakdown of the diagnostics behind your Performance gauge, including which speed and efficiency checks are passing and which ones could help your pages load faster.', 'wpshadow' ),
			'report'   => 'performance-gauge-report',
			'category' => 'performance',
			'icon'     => 'dashicons-performance',
			'family'   => 'performance',
			'enabled'  => true,
			'since'    => '0.6090.1200',
		),
		'code-quality-gauge-report' => array(
			'title'    => __( 'Code Quality Gauge Report', 'wpshadow' ),
			'desc'     => __( 'Detailed breakdown of the diagnostics behind your Code Quality gauge so you can spot risky patterns, technical debt, and maintenance concerns before they grow.', 'wpshadow' ),
			'report'   => 'code-quality-gauge-report',
			'category' => 'code-quality',
			'icon'     => 'dashicons-editor-code',
			'family'   => 'analysis',
			'enabled'  => true,
			'since'    => '0.6090.1200',
		),
		'seo-gauge-report' => array(
			'title'    => __( 'SEO Gauge Report', 'wpshadow' ),
			'desc'     => __( 'Detailed breakdown of the diagnostics behind your SEO gauge so you can understand what helps search engines read your site and what might be making it harder to discover.', 'wpshadow' ),
			'report'   => 'seo-gauge-report',
			'category' => 'seo',
			'icon'     => 'dashicons-search',
			'family'   => 'seo',
			'enabled'  => true,
			'since'    => '0.6090.1200',
		),
		'design-gauge-report' => array(
			'title'    => __( 'Design Gauge Report', 'wpshadow' ),
			'desc'     => __( 'Detailed breakdown of the diagnostics behind your Design gauge so you can review user experience, visual polish, and presentation issues that affect how your site feels to visitors.', 'wpshadow' ),
			'report'   => 'design-gauge-report',
			'category' => 'design',
			'icon'     => 'dashicons-admin-appearance',
			'family'   => 'analysis',
			'enabled'  => true,
			'since'    => '0.6090.1200',
		),
		'accessibility-gauge-report' => array(
			'title'    => __( 'Accessibility Gauge Report', 'wpshadow' ),
			'desc'     => __( 'Detailed breakdown of the diagnostics behind your Accessibility gauge so you can see how well your site supports keyboard users, screen readers, and people with different needs.', 'wpshadow' ),
			'report'   => 'accessibility-gauge-report',
			'category' => 'accessibility',
			'icon'     => 'dashicons-universal-access',
			'family'   => 'analysis',
			'enabled'  => true,
			'since'    => '0.6090.1200',
		),
		'settings-gauge-report' => array(
			'title'    => __( 'Settings Gauge Report', 'wpshadow' ),
			'desc'     => __( 'Detailed breakdown of the diagnostics behind your Settings gauge so you can review configuration issues that affect stability, reliability, and day-to-day administration.', 'wpshadow' ),
			'report'   => 'settings-gauge-report',
			'category' => 'settings',
			'icon'     => 'dashicons-admin-settings',
			'family'   => 'analysis',
			'enabled'  => true,
			'since'    => '0.6090.1200',
		),
		'monitoring-gauge-report' => array(
			'title'    => __( 'Monitoring Gauge Report', 'wpshadow' ),
			'desc'     => __( 'Detailed breakdown of the diagnostics behind your Monitoring gauge so you can understand how well your site is being watched for uptime, alerts, and ongoing issues.', 'wpshadow' ),
			'report'   => 'monitoring-gauge-report',
			'category' => 'monitoring',
			'icon'     => 'dashicons-visibility',
			'family'   => 'analysis',
			'enabled'  => true,
			'since'    => '0.6090.1200',
		),
		'workflows-gauge-report' => array(
			'title'    => __( 'Workflows Gauge Report', 'wpshadow' ),
			'desc'     => __( 'Detailed breakdown of the diagnostics behind your Workflows gauge so you can see whether your automations, scheduled tasks, and routine maintenance jobs are staying dependable.', 'wpshadow' ),
			'report'   => 'workflows-gauge-report',
			'category' => 'workflows',
			'icon'     => 'dashicons-update',
			'family'   => 'analysis',
			'enabled'  => true,
			'since'    => '0.6090.1200',
		),
	);
}

/**
 * Get the dashboard gauge report definition for a category.
 *
 * @since  0.6090.1200
 * @param  string $category_key Dashboard gauge category key.
 * @return array<string, mixed>|null Gauge report definition when available.
 */
function wpshadow_get_dashboard_gauge_report_for_category( string $category_key ): ?array {
	foreach ( wpshadow_get_dashboard_gauge_report_map() as $report ) {
		if ( isset( $report['category'] ) && $category_key === $report['category'] ) {
			return $report;
		}
	}

	return null;
}

/**
 * Get reports catalog.
 *
 * @return array Reports organized by category.
 */
function wpshadow_get_reports_catalog() {
	return array_values( wpshadow_get_dashboard_gauge_report_map() );
}

/**
 * Render reports page or individual report.
 *
 * @return void
 */
function wpshadow_render_reports_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Insufficient permissions.', 'wpshadow' ) );
	}

	// Check if viewing a specific report
	$report = Form_Param_Helper::get( 'report', 'key', '' );

	if ( ! empty( $report ) ) {
		// Load individual report view
		wpshadow_render_report_detail( $report );
		return;
	}

	// Reports submenu has been removed. If accessed directly without a gauge report,
	// send users back to the dashboard.
	wp_safe_redirect( admin_url( 'admin.php?page=wpshadow' ) );
	exit;
}

/**
 * Render individual report card using standardized card function.
 *
 * @since 0.6093.1200
 * @param  array $item Report configuration.
 * @return void
 */
function wpshadow_render_report_card( $item ) {
	$report_url = add_query_arg(
		array(
			'page'   => 'wpshadow-reports',
			'report' => $item['report'],
		),
		admin_url( 'admin.php' )
	);

	// Get feature status
	$feature_status = wpshadow_get_feature_status( $item['since'] );
	$is_coming_soon = ( 'coming_soon' === $feature_status['status'] );

	// Build badge array
	$badge = array();
	if ( $is_coming_soon ) {
		$badge = array(
			'label' => __( 'Coming Soon', 'wpshadow' ),
			'class' => 'wps-badge wps-badge--info',
		);
	} elseif ( isset( $item['badge'] ) ) {
		$badge = array(
			'label' => ucfirst( $item['badge'] ),
			'class' => 'wps-badge wps-badge-new',
		);
	}

	// Build card classes
	$card_class = 'wps-card-hover';
	if ( $is_coming_soon ) {
		$card_class .= ' wps-card--coming-soon';
	}

	// Build card arguments
	$card_args = array(
		'title'       => $item['title'],
		'title_url'   => $is_coming_soon ? '' : $report_url,
		'description' => $item['desc'],
		'icon'        => $item['icon'],
		'icon_class'  => 'wps-text-primary',
		'card_class'  => $card_class,
		'badge'       => $badge,
		'attrs'       => $is_coming_soon ? array( 'style' => 'cursor: not-allowed; opacity: 0.8;' ) : array(),
		'footer'      => function() use ( $is_coming_soon, $report_url, $feature_status ) {
			if ( ! $is_coming_soon ) {
				?>
				<a href="<?php echo esc_url( $report_url ); ?>" class="wps-btn wps-btn--secondary">
					<span class="dashicons dashicons-arrow-right-alt"></span>
					<?php esc_html_e( 'Generate Report', 'wpshadow' ); ?>
				</a>
				<?php
			} else {
				?>
				<span class="wps-text-muted" style="font-size: 12px;">
					<?php
					echo esc_html(
						sprintf(
							/* translators: %s: launch date */
							__( 'Available %s', 'wpshadow' ),
							$feature_status['launch_date']
						)
					);
					?>
				</span>
				<?php
			}
		},
	);

	// Render using standardized function
	wpshadow_render_card( $card_args );
}

/**
 * Render individual report detail page.
 *
 * @param string $report Report slug.
 * @return void
 */
function wpshadow_render_report_detail( $report ) {
	$report_views = array_fill_keys( array_keys( wpshadow_get_dashboard_gauge_report_map() ), 'dashboard-gauge-report.php' );

	// Check if report exists
	if ( ! isset( $report_views[ $report ] ) ) {
		wp_safe_redirect( admin_url( 'admin.php?page=wpshadow' ) );
		exit;
	}

	$report_filename      = $report_views[ $report ];
	$ui_report_file       = WPSHADOW_PATH . 'includes/ui/reports/' . $report_filename;
	$legacy_report_file   = WPSHADOW_PATH . 'includes/views/reports/' . $report_filename;
	$has_ui_report        = file_exists( $ui_report_file );
	$has_legacy_report    = file_exists( $legacy_report_file );
	$report_file          = $has_ui_report ? $ui_report_file : $legacy_report_file;

	// Check if file exists in either canonical UI reports or legacy reports.
	if ( ! $has_ui_report && ! $has_legacy_report ) {
		wp_die( esc_html__( 'Report view file not found.', 'wpshadow' ) );
	}

	if ( class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
		$catalog      = wpshadow_get_reports_catalog();
		$report_title = '';
		foreach ( $catalog as $item ) {
			if ( isset( $item['report'] ) && $item['report'] === $report ) {
				$report_title = $item['title'];
				break;
			}
		}

		$details = $report_title
			? sprintf(
				/* translators: %s: report name */
				__( 'Report generated: %s', 'wpshadow' ),
				$report_title
			)
			: sprintf(
				/* translators: %s: report slug */
				__( 'Report generated: %s', 'wpshadow' ),
				$report
			);

		\WPShadow\Core\Activity_Logger::log(
			'report_generated',
			$details,
			'reports',
			array(
				'report' => $report,
			)
		);
	}

	// Load the report view
	require $report_file;
}
