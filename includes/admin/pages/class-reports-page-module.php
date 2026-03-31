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
 * @since  1.6090.1200
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
			'since'    => '1.6090.1200',
		),
		'security-gauge-report' => array(
			'title'    => __( 'Security Gauge Report', 'wpshadow' ),
			'desc'     => __( 'Detailed breakdown of the diagnostics behind your Security gauge so you can see which checks passed, which ones need attention, and what each result means in plain language.', 'wpshadow' ),
			'report'   => 'security-gauge-report',
			'category' => 'security',
			'icon'     => 'dashicons-shield-alt',
			'family'   => 'security',
			'enabled'  => true,
			'since'    => '1.6090.1200',
		),
		'performance-gauge-report' => array(
			'title'    => __( 'Performance Gauge Report', 'wpshadow' ),
			'desc'     => __( 'Detailed breakdown of the diagnostics behind your Performance gauge, including which speed and efficiency checks are passing and which ones could help your pages load faster.', 'wpshadow' ),
			'report'   => 'performance-gauge-report',
			'category' => 'performance',
			'icon'     => 'dashicons-performance',
			'family'   => 'performance',
			'enabled'  => true,
			'since'    => '1.6090.1200',
		),
		'code-quality-gauge-report' => array(
			'title'    => __( 'Code Quality Gauge Report', 'wpshadow' ),
			'desc'     => __( 'Detailed breakdown of the diagnostics behind your Code Quality gauge so you can spot risky patterns, technical debt, and maintenance concerns before they grow.', 'wpshadow' ),
			'report'   => 'code-quality-gauge-report',
			'category' => 'code-quality',
			'icon'     => 'dashicons-editor-code',
			'family'   => 'analysis',
			'enabled'  => true,
			'since'    => '1.6090.1200',
		),
		'seo-gauge-report' => array(
			'title'    => __( 'SEO Gauge Report', 'wpshadow' ),
			'desc'     => __( 'Detailed breakdown of the diagnostics behind your SEO gauge so you can understand what helps search engines read your site and what might be making it harder to discover.', 'wpshadow' ),
			'report'   => 'seo-gauge-report',
			'category' => 'seo',
			'icon'     => 'dashicons-search',
			'family'   => 'seo',
			'enabled'  => true,
			'since'    => '1.6090.1200',
		),
		'design-gauge-report' => array(
			'title'    => __( 'Design Gauge Report', 'wpshadow' ),
			'desc'     => __( 'Detailed breakdown of the diagnostics behind your Design gauge so you can review user experience, visual polish, and presentation issues that affect how your site feels to visitors.', 'wpshadow' ),
			'report'   => 'design-gauge-report',
			'category' => 'design',
			'icon'     => 'dashicons-admin-appearance',
			'family'   => 'analysis',
			'enabled'  => true,
			'since'    => '1.6090.1200',
		),
		'accessibility-gauge-report' => array(
			'title'    => __( 'Accessibility Gauge Report', 'wpshadow' ),
			'desc'     => __( 'Detailed breakdown of the diagnostics behind your Accessibility gauge so you can see how well your site supports keyboard users, screen readers, and people with different needs.', 'wpshadow' ),
			'report'   => 'accessibility-gauge-report',
			'category' => 'accessibility',
			'icon'     => 'dashicons-universal-access',
			'family'   => 'analysis',
			'enabled'  => true,
			'since'    => '1.6090.1200',
		),
		'settings-gauge-report' => array(
			'title'    => __( 'Settings Gauge Report', 'wpshadow' ),
			'desc'     => __( 'Detailed breakdown of the diagnostics behind your Settings gauge so you can review configuration issues that affect stability, reliability, and day-to-day administration.', 'wpshadow' ),
			'report'   => 'settings-gauge-report',
			'category' => 'settings',
			'icon'     => 'dashicons-admin-settings',
			'family'   => 'analysis',
			'enabled'  => true,
			'since'    => '1.6090.1200',
		),
		'monitoring-gauge-report' => array(
			'title'    => __( 'Monitoring Gauge Report', 'wpshadow' ),
			'desc'     => __( 'Detailed breakdown of the diagnostics behind your Monitoring gauge so you can understand how well your site is being watched for uptime, alerts, and ongoing issues.', 'wpshadow' ),
			'report'   => 'monitoring-gauge-report',
			'category' => 'monitoring',
			'icon'     => 'dashicons-visibility',
			'family'   => 'analysis',
			'enabled'  => true,
			'since'    => '1.6090.1200',
		),
		'workflows-gauge-report' => array(
			'title'    => __( 'Workflows Gauge Report', 'wpshadow' ),
			'desc'     => __( 'Detailed breakdown of the diagnostics behind your Workflows gauge so you can see whether your automations, scheduled tasks, and routine maintenance jobs are staying dependable.', 'wpshadow' ),
			'report'   => 'workflows-gauge-report',
			'category' => 'workflows',
			'icon'     => 'dashicons-update',
			'family'   => 'analysis',
			'enabled'  => true,
			'since'    => '1.6090.1200',
		),
	);
}

/**
 * Get the dashboard gauge report definition for a category.
 *
 * @since  1.6090.1200
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
	$dashboard_gauge_reports = array_values( wpshadow_get_dashboard_gauge_report_map() );

	return array_merge(
		array(
		// Analysis & Insights Reports
		array(
			'title'   => __( 'Site DNA Report', 'wpshadow' ),
			'desc'    => __( 'Your site\'s unique health fingerprint showing what\'s working well and what needs attention (like a doctor\'s complete blood panel). See how your site compares to others in your industry.', 'wpshadow' ),
			'report'  => 'site-dna',
			'icon'    => 'dashicons-chart-line',
			'family'  => 'analysis',
			'enabled' => true,
			'since'   => '1.6177.1200', // Release1.0 (June 2026)
		),
		array(
			'title'   => __( 'Deep Scan Report', 'wpshadow' ),
			'desc'    => __( 'Complete checkup of your entire site (like a doctor\'s annual physical exam). We\'ll check security, speed, accessibility, and search visibility—everything that keeps your site healthy.', 'wpshadow' ),
			'report'  => 'deep-scan',
			'icon'    => 'dashicons-search',
			'family'  => 'analysis',
			'enabled' => true,
			'since'   => '1.6177.1200', // Release1.0 (June 2026)
		),
		array(
			'title'   => __( 'Quick Scan Report', 'wpshadow' ),
			'desc'    => __( '5-minute health check showing the most important things to fix first (like taking your temperature and blood pressure). Perfect when you need a fast overview.', 'wpshadow' ),
			'report'  => 'quick-scan',
			'icon'    => 'dashicons-performance',
			'family'  => 'analysis',
			'enabled' => true,
			'since'   => '1.6212.1200', // Release1.0 (July 2026)
		),
		array(
			'title'   => __( 'Diagnostics Fix Rate Report', 'wpshadow' ),
			'desc'    => __( 'See how many diagnostics have run and how fixes are getting resolved (like a repair log showing which issues were solved automatically versus manually). Helpful for tracking progress over time.', 'wpshadow' ),
			'report'  => 'diagnostics-fix-rate',
			'icon'    => 'dashicons-yes-alt',
			'family'  => 'analysis',
			'enabled' => true,
			'since'   => '1.7038.1200',
		),
		),
		$dashboard_gauge_reports,
		array(

		// Security Reports
		array(
			'title'   => __( 'Security Report', 'wpshadow' ),
			'desc'    => __( 'Check all your site\'s locks and alarms to keep intruders out (like a home security inspection). We\'ll find security holes, check your passwords, and suggest ways to protect your site better.', 'wpshadow' ),
			'report'  => 'security-report',
			'icon'    => 'dashicons-shield-alt',
			'family'  => 'security',
			'enabled' => true,
			'since'   => '1.6119.1200', // Release1.0 (April 2026)
		),

		// Performance Reports
		array(
			'title'   => __( 'Performance Report', 'wpshadow' ),
			'desc'    => __( 'Find out why your site might feel slow and how to speed it up (like tuning up a car engine). We\'ll check page load times, memory usage, and how fast things work on phones.', 'wpshadow' ),
			'report'  => 'performance-report',
			'icon'    => 'dashicons-performance',
			'family'  => 'performance',
			'enabled' => true,
			'since'   => '1.6119.1200', // Release1.0 (April 2026)
		),

		// SEO Reports
		array(
			'title'   => __( 'SEO Report', 'wpshadow' ),
			'desc'    => __( 'Help more people find your site on Google (like putting up better signs so customers can find your store). We\'ll check if search engines can read your site properly and suggest improvements.', 'wpshadow' ),
			'report'  => 'seo-report',
			'icon'    => 'dashicons-search',
			'family'  => 'seo',
			'enabled' => true,
			'since'   => '1.6038.1200', // Release1.0 (February 2026)
		),

		// Optimization Reports
		array(
			'title'   => __( 'Database Optimization Report', 'wpshadow' ),
			'desc'    => __( 'Speed up your site by organizing its memory better (like cleaning out a messy filing cabinet so you can find things faster). We\'ll remove unnecessary clutter and make everything run smoother.', 'wpshadow' ),
			'report'  => 'database-report',
			'icon'    => 'dashicons-database',
			'family'  => 'optimization',
			'enabled' => true,
			'since'   => '1.6212.1200', // Release1.0 (July 2026)
		),
		array(
			'title'   => __( 'Plugin Audit Report', 'wpshadow' ),
			'desc'    => __( 'Check your add-ons for problems (like checking the apps on your phone for updates and security issues). We\'ll find plugins that slow your site down or need updating.', 'wpshadow' ),
			'report'  => 'plugins-report',
			'icon'    => 'dashicons-admin-plugins',
			'family'  => 'optimization',
			'enabled' => true,
			'since'   => '1.6240.1200', // Release1.0 (August 2026)
		),

		// Commerce Reports
		array(
			'title'   => __( 'E-Commerce Health Report', 'wpshadow' ),
			'desc'    => __( 'Make sure your online store checkouts work smoothly (like ensuring your cash register works properly and customers can pay easily). We\'ll check payment processing and find where customers might be giving up.', 'wpshadow' ),
			'report'  => 'ecommerce-report',
			'icon'    => 'dashicons-cart',
			'family'  => 'commerce',
			'enabled' => true,
			'since'   => '1.6240.1200', // Release1.0 (August 2026)
		),

		// Compliance & Operations Reports
		array(
			'title'   => __( 'Compliance & Privacy Report', 'wpshadow' ),
			'desc'    => __( 'Verify you\'re handling customer information responsibly and following privacy laws (like making sure you\'re not sharing people\'s secrets without permission). We\'ll check your privacy policy and consent forms.', 'wpshadow' ),
			'report'  => 'compliance-report',
			'icon'    => 'dashicons-privacy',
			'family'  => 'operations',
			'enabled' => true,
			'since'   => '1.6150.1200', // Release1.0 (May 2026)
		),
		array(
			'title'   => __( 'User Privacy Report', 'wpshadow' ),
			'desc'    => __( 'See exactly what WPShadow stores about a specific user (like a personal file folder you can open and review). This helps administrators answer privacy questions quickly and transparently.', 'wpshadow' ),
			'report'  => 'user-privacy-report',
			'icon'    => 'dashicons-id-alt',
			'family'  => 'operations',
			'enabled' => true,
			'since'   => '1.6038.1200',
		),
		array(
			'title'   => __( 'Email Deliverability Report', 'wpshadow' ),
			'desc'    => __( 'Check if your emails are actually reaching people (like making sure your letters don\'t end up in the junk drawer). We\'ll verify your email setup and make sure your messages get delivered reliably.', 'wpshadow' ),
			'report'  => 'email-report',
			'icon'    => 'dashicons-email-alt',
			'family'  => 'operations',
			'enabled' => true,
			'since'   => '1.6038.1200', // Release1.0 (February 2026)
		),
		array(
			'title'   => __( 'Backup Readiness Report', 'wpshadow' ),
			'desc'    => __( 'Make sure you have copies of your site in case something goes wrong (like keeping spare house keys with a neighbor). We\'ll check how often backups run and where they\'re stored.', 'wpshadow' ),
			'report'  => 'backup-report',
			'icon'    => 'dashicons-backup',
			'family'  => 'operations',
			'enabled' => true,
			'since'   => '1.6268.1200', // Release1.0 (September 2026)
		),
		array(
			'title'   => __( 'Multisite Network Report', 'wpshadow' ),
			'desc'    => __( 'Check all the sites in your WordPress network (like inspecting all the apartments in a building you manage). We\'ll find sites with problems and check if they\'re sharing resources fairly.', 'wpshadow' ),
			'report'  => 'multisite-report',
			'icon'    => 'dashicons-admin-multisite',
			'family'  => 'operations',
			'enabled' => true,
			'since'   => '1.6268.1200', // Release1.0 (September 2026)
		),

		// Page-Specific Reports
		array(
			'title'   => __( 'Mobile Friendliness Report', 'wpshadow' ),
			'desc'    => __( 'Check if your pages work well on phones and tablets (like making sure your front door is wide enough for wheelchairs). We\'ll test how pages look on different screen sizes.', 'wpshadow' ),
			'report'  => 'mobile-friendliness',
			'icon'    => 'dashicons-smartphone',
			'family'  => 'page-analysis',
			'enabled' => true,
			'since'   => '1.6303.1200', // Release1.0 (October 2026)
		),
		array(
			'title'   => __( 'Accessibility Audit Report', 'wpshadow' ),
			'desc'    => __( 'Make sure everyone can use your site, including people with disabilities (like adding wheelchair ramps and braille signs to a building). We\'ll check color contrast, keyboard controls, and screen reader compatibility.', 'wpshadow' ),
			'report'  => 'a11y-audit',
			'icon'    => 'dashicons-universal-access',
			'family'  => 'page-analysis',
			'enabled' => true,
			'since'   => '1.6303.1200', // Release1.0 (October 2026)
		),
		array(
			'title'   => __( 'Broken Links Report', 'wpshadow' ),
			'desc'    => __( 'Find links that don\'t work anymore (like finding broken bridges on a road map). We\'ll scan your pages and show you which links need fixing or updating.', 'wpshadow' ),
			'report'  => 'broken-links',
			'icon'    => 'dashicons-admin-links',
			'family'  => 'page-analysis',
			'enabled' => true,
			'since'   => '1.6331.1200', // Release1.0 (November 2026)
		),

		// Comparison & Historical Reports
		array(
			'title'   => __( 'Visual Comparison Report', 'wpshadow' ),
			'desc'    => __( 'See before-and-after pictures of your pages (like comparing photos from a home renovation). Perfect for reviewing design changes or tracking how your site evolves over time.', 'wpshadow' ),
			'report'  => 'visual-comparisons',
			'icon'    => 'dashicons-images-alt2',
			'family'  => 'comparison',
			'enabled' => true,
			'since'   => '1.6331.1200', // Release1.0 (November 2026)
		),
		array(
			'title'   => __( 'Customization Audit Report', 'wpshadow' ),
			'desc'    => __( 'Review all the custom changes you\'ve made to your site (like documenting home improvements so you remember what you changed). Helps track modifications and find where things might conflict.', 'wpshadow' ),
			'report'  => 'customization-audit',
			'icon'    => 'dashicons-admin-customizer',
			'family'  => 'comparison',
			'enabled' => true,
			'since'   => '1.6359.1200', // Release1.0 (December 2026)
		),

		)
	);
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

	// Render reports catalog page
	$all_reports = wpshadow_get_reports_catalog();
	$catalog = wpshadow_filter_features_by_status( $all_reports );

	?>
	<div class="wrap wps-page-container">
		<!-- Page Header -->
		<?php
		wpshadow_render_page_header(
			__( 'Reports & Analytics', 'wpshadow' ),
			__( 'Generate comprehensive reports about your site\'s health, performance, and security.', 'wpshadow' ),
			'dashicons-chart-line'
		);
		?>

		<!-- All Reports Grid -->
		<div class="wps-grid wps-grid-auto-320">
			<?php foreach ( $catalog as $item ) : ?>
				<?php wpshadow_render_report_card( $item ); ?>
			<?php endforeach; ?>
		</div>

		<!-- Recent Activity Section -->
		<?php
		if ( function_exists( 'wpshadow_render_page_activities' ) ) {
			wpshadow_render_page_activities( 'reports', 10 );
		}
		?>
	</div>
	<?php
}

/**
 * Render individual report card using standardized card function.
 *
 * @since 1.6093.1200
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
	$dashboard_gauge_report_views = array_fill_keys( array_keys( wpshadow_get_dashboard_gauge_report_map() ), 'dashboard-gauge-report.php' );

	// Map report slugs to view files
	$report_views = array_merge(
		array(
		'site-dna'             => 'site-dna.php',
		'deep-scan'            => 'deep-scan.php',
		'quick-scan'           => 'quick-scan.php',
		'security-report'      => 'security-report.php',
		'performance-report'   => 'performance-report.php',
		'seo-report'           => 'seo-report.php',
		'database-report'      => 'database-report.php',
		'ecommerce-report'     => 'ecommerce-report.php',
		'plugins-report'       => 'plugins-report.php',
		'compliance-report'    => 'compliance-report.php',
		'user-privacy-report'  => 'user-privacy-report.php',
		'email-report'         => 'email-report.php',
		'diagnostics-fix-rate' => 'diagnostics-fix-rate.php',
		'backup-report'        => 'backup-report.php',
		'multisite-report'     => 'multisite-report.php',
		'mobile-friendliness'  => 'mobile-friendliness.php',
		'a11y-audit'           => 'a11y-audit.php',
		'broken-links'         => 'broken-links.php',
		'visual-comparisons'   => 'visual-comparisons.php',
		'customization-audit'  => 'customization-audit.php',
		),
		$dashboard_gauge_report_views
	);

	// Check if report exists
	if ( ! isset( $report_views[ $report ] ) ) {
		wp_die( esc_html__( 'Invalid report requested.', 'wpshadow' ) );
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
