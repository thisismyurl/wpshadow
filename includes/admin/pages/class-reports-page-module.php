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
 * Get reports catalog.
 *
 * @return array Reports organized by category.
 */
function wpshadow_get_reports_catalog() {
	return array(
		// Analysis & Insights Reports
		array(
			'title'   => __( 'Site DNA Report', 'wpshadow' ),
			'desc'    => __( 'Your site\'s unique health fingerprint showing what\'s working well and what needs attention (like a doctor\'s complete blood panel). See how your site compares to others in your industry.', 'wpshadow' ),
			'report'  => 'site-dna',
			'icon'    => 'dashicons-chart-line',
			'family'  => 'analysis',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Deep Scan Report', 'wpshadow' ),
			'desc'    => __( 'Complete checkup of your entire site (like a doctor\'s annual physical exam). We\'ll check security, speed, accessibility, and search visibility—everything that keeps your site healthy.', 'wpshadow' ),
			'report'  => 'deep-scan',
			'icon'    => 'dashicons-search',
			'family'  => 'analysis',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Quick Scan Report', 'wpshadow' ),
			'desc'    => __( '5-minute health check showing the most important things to fix first (like taking your temperature and blood pressure). Perfect when you need a fast overview.', 'wpshadow' ),
			'report'  => 'quick-scan',
			'icon'    => 'dashicons-performance',
			'family'  => 'analysis',
			'enabled' => true,
		),

		// Security Reports
		array(
			'title'   => __( 'Security Report', 'wpshadow' ),
			'desc'    => __( 'Check all your site\'s locks and alarms to keep intruders out (like a home security inspection). We\'ll find security holes, check your passwords, and suggest ways to protect your site better.', 'wpshadow' ),
			'report'  => 'security-report',
			'icon'    => 'dashicons-shield-alt',
			'family'  => 'security',
			'enabled' => true,
		),

		// Performance Reports
		array(
			'title'   => __( 'Performance Report', 'wpshadow' ),
			'desc'    => __( 'Find out why your site might feel slow and how to speed it up (like tuning up a car engine). We\'ll check page load times, memory usage, and how fast things work on phones.', 'wpshadow' ),
			'report'  => 'performance-report',
			'icon'    => 'dashicons-performance',
			'family'  => 'performance',
			'enabled' => true,
		),

		// SEO Reports
		array(
			'title'   => __( 'SEO Report', 'wpshadow' ),
			'desc'    => __( 'Help more people find your site on Google (like putting up better signs so customers can find your store). We\'ll check if search engines can read your site properly and suggest improvements.', 'wpshadow' ),
			'report'  => 'seo-report',
			'icon'    => 'dashicons-search',
			'family'  => 'seo',
			'enabled' => true,
		),

		// Optimization Reports
		array(
			'title'   => __( 'Database Optimization Report', 'wpshadow' ),
			'desc'    => __( 'Speed up your site by organizing its memory better (like cleaning out a messy filing cabinet so you can find things faster). We\'ll remove unnecessary clutter and make everything run smoother.', 'wpshadow' ),
			'report'  => 'database-report',
			'icon'    => 'dashicons-database',
			'family'  => 'optimization',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Plugin Audit Report', 'wpshadow' ),
			'desc'    => __( 'Check your add-ons for problems (like checking the apps on your phone for updates and security issues). We\'ll find plugins that slow your site down or need updating.', 'wpshadow' ),
			'report'  => 'plugins-report',
			'icon'    => 'dashicons-admin-plugins',
			'family'  => 'optimization',
			'enabled' => true,
		),

		// Commerce Reports
		array(
			'title'   => __( 'E-Commerce Health Report', 'wpshadow' ),
			'desc'    => __( 'Make sure your online store checkouts work smoothly (like ensuring your cash register works properly and customers can pay easily). We\'ll check payment processing and find where customers might be giving up.', 'wpshadow' ),
			'report'  => 'ecommerce-report',
			'icon'    => 'dashicons-cart',
			'family'  => 'commerce',
			'enabled' => true,
		),

		// Compliance & Operations Reports
		array(
			'title'   => __( 'Compliance & Privacy Report', 'wpshadow' ),
			'desc'    => __( 'Verify you\'re handling customer information responsibly and following privacy laws (like making sure you\'re not sharing people\'s secrets without permission). We\'ll check your privacy policy and consent forms.', 'wpshadow' ),
			'report'  => 'compliance-report',
			'icon'    => 'dashicons-privacy',
			'family'  => 'operations',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Email Deliverability Report', 'wpshadow' ),
			'desc'    => __( 'Check if your emails are actually reaching people (like making sure your letters don\'t end up in the junk drawer). We\'ll verify your email setup and make sure your messages get delivered reliably.', 'wpshadow' ),
			'report'  => 'email-report',
			'icon'    => 'dashicons-email-alt',
			'family'  => 'operations',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Backup Readiness Report', 'wpshadow' ),
			'desc'    => __( 'Make sure you have copies of your site in case something goes wrong (like keeping spare house keys with a neighbor). We\'ll check how often backups run and where they\'re stored.', 'wpshadow' ),
			'report'  => 'backup-report',
			'icon'    => 'dashicons-backup',
			'family'  => 'operations',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Multisite Network Report', 'wpshadow' ),
			'desc'    => __( 'Check all the sites in your WordPress network (like inspecting all the apartments in a building you manage). We\'ll find sites with problems and check if they\'re sharing resources fairly.', 'wpshadow' ),
			'report'  => 'multisite-report',
			'icon'    => 'dashicons-admin-multisite',
			'family'  => 'operations',
			'enabled' => true,
		),

		// Page-Specific Reports
		array(
			'title'   => __( 'Mobile Friendliness Report', 'wpshadow' ),
			'desc'    => __( 'Check if your pages work well on phones and tablets (like making sure your front door is wide enough for wheelchairs). We\'ll test how pages look on different screen sizes.', 'wpshadow' ),
			'report'  => 'mobile-friendliness',
			'icon'    => 'dashicons-smartphone',
			'family'  => 'page-analysis',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Accessibility Audit Report', 'wpshadow' ),
			'desc'    => __( 'Make sure everyone can use your site, including people with disabilities (like adding wheelchair ramps and braille signs to a building). We\'ll check color contrast, keyboard controls, and screen reader compatibility.', 'wpshadow' ),
			'report'  => 'a11y-audit',
			'icon'    => 'dashicons-universal-access',
			'family'  => 'page-analysis',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Broken Links Report', 'wpshadow' ),
			'desc'    => __( 'Find links that don\'t work anymore (like finding broken bridges on a road map). We\'ll scan your pages and show you which links need fixing or updating.', 'wpshadow' ),
			'report'  => 'broken-links',
			'icon'    => 'dashicons-admin-links',
			'family'  => 'page-analysis',
			'enabled' => true,
		),

		// Comparison & Historical Reports
		array(
			'title'   => __( 'Visual Comparison Report', 'wpshadow' ),
			'desc'    => __( 'See before-and-after pictures of your pages (like comparing photos from a home renovation). Perfect for reviewing design changes or tracking how your site evolves over time.', 'wpshadow' ),
			'report'  => 'visual-comparisons',
			'icon'    => 'dashicons-images-alt2',
			'family'  => 'comparison',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Customization Audit Report', 'wpshadow' ),
			'desc'    => __( 'Review all the custom changes you\'ve made to your site (like documenting home improvements so you remember what you changed). Helps track modifications and find where things might conflict.', 'wpshadow' ),
			'report'  => 'customization-audit',
			'icon'    => 'dashicons-admin-customizer',
			'family'  => 'comparison',
			'enabled' => true,
		),

		// Activity & History Reports
		array(
			'title'   => __( 'Activity History Report', 'wpshadow' ),
			'desc'    => __( 'See a complete timeline of everything WPShadow has done for your site (like a maintenance log for your car showing all the oil changes and repairs). Great for tracking improvements over time.', 'wpshadow' ),
			'report'  => 'activity-history',
			'icon'    => 'dashicons-backup',
			'family'  => 'history',
			'enabled' => true,
		),
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
	$catalog = wpshadow_get_reports_catalog();

	// Group by family
	$grouped = array();
	foreach ( $catalog as $item ) {
		$family                 = $item['family'] ?? 'other';
		$grouped[ $family ][] = $item;
	}

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

		<!-- Analysis & Insights Section -->
		<?php if ( ! empty( $grouped['analysis'] ) ) : ?>
			<div class="wps-section wps-mb-6">
				<h2 class="wps-section-title"><?php esc_html_e( 'Analysis & Insights', 'wpshadow' ); ?></h2>
				<div class="wps-grid wps-grid-auto-320">
					<?php foreach ( $grouped['analysis'] as $item ) : ?>
						<?php wpshadow_render_report_card( $item ); ?>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>

		<!-- Security Reports Section -->
		<?php if ( ! empty( $grouped['security'] ) ) : ?>
			<div class="wps-section wps-mb-6">
				<h2 class="wps-section-title"><?php esc_html_e( 'Security Reports', 'wpshadow' ); ?></h2>
				<div class="wps-grid wps-grid-auto-320">
					<?php foreach ( $grouped['security'] as $item ) : ?>
						<?php wpshadow_render_report_card( $item ); ?>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>

		<!-- Performance Reports Section -->
		<?php if ( ! empty( $grouped['performance'] ) ) : ?>
			<div class="wps-section wps-mb-6">
				<h2 class="wps-section-title"><?php esc_html_e( 'Performance Reports', 'wpshadow' ); ?></h2>
				<div class="wps-grid wps-grid-auto-320">
					<?php foreach ( $grouped['performance'] as $item ) : ?>
						<?php wpshadow_render_report_card( $item ); ?>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>

		<!-- SEO Reports Section -->
		<?php if ( ! empty( $grouped['seo'] ) ) : ?>
			<div class="wps-section wps-mb-6">
				<h2 class="wps-section-title"><?php esc_html_e( 'SEO Reports', 'wpshadow' ); ?></h2>
				<div class="wps-grid wps-grid-auto-320">
					<?php foreach ( $grouped['seo'] as $item ) : ?>
						<?php wpshadow_render_report_card( $item ); ?>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>

		<!-- Optimization Reports Section -->
		<?php if ( ! empty( $grouped['optimization'] ) ) : ?>
			<div class="wps-section wps-mb-6">
				<h2 class="wps-section-title"><?php esc_html_e( 'Optimization Reports', 'wpshadow' ); ?></h2>
				<div class="wps-grid wps-grid-auto-320">
					<?php foreach ( $grouped['optimization'] as $item ) : ?>
						<?php wpshadow_render_report_card( $item ); ?>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>

		<!-- Commerce Reports Section -->
		<?php if ( ! empty( $grouped['commerce'] ) ) : ?>
			<div class="wps-section wps-mb-6">
				<h2 class="wps-section-title"><?php esc_html_e( 'E-Commerce Reports', 'wpshadow' ); ?></h2>
				<div class="wps-grid wps-grid-auto-320">
					<?php foreach ( $grouped['commerce'] as $item ) : ?>
						<?php wpshadow_render_report_card( $item ); ?>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>

		<!-- Operations Reports Section -->
		<?php if ( ! empty( $grouped['operations'] ) ) : ?>
			<div class="wps-section wps-mb-6">
				<h2 class="wps-section-title"><?php esc_html_e( 'Compliance & Operations', 'wpshadow' ); ?></h2>
				<div class="wps-grid wps-grid-auto-320">
					<?php foreach ( $grouped['operations'] as $item ) : ?>
						<?php wpshadow_render_report_card( $item ); ?>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>

		<!-- Page-Specific Analysis Section -->
		<?php if ( ! empty( $grouped['page-analysis'] ) ) : ?>
			<div class="wps-section wps-mb-6">
				<h2 class="wps-section-title"><?php esc_html_e( 'Page-Specific Analysis', 'wpshadow' ); ?></h2>
				<div class="wps-grid wps-grid-auto-320">
					<?php foreach ( $grouped['page-analysis'] as $item ) : ?>
						<?php wpshadow_render_report_card( $item ); ?>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>

		<!-- Comparison & Historical Section -->
		<?php if ( ! empty( $grouped['comparison'] ) ) : ?>
			<div class="wps-section wps-mb-6">
				<h2 class="wps-section-title"><?php esc_html_e( 'Comparison & Historical', 'wpshadow' ); ?></h2>
				<div class="wps-grid wps-grid-auto-320">
					<?php foreach ( $grouped['comparison'] as $item ) : ?>
						<?php wpshadow_render_report_card( $item ); ?>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>

		<!-- Activity & History Section -->
		<?php if ( ! empty( $grouped['history'] ) ) : ?>
			<div class="wps-section wps-mb-6">
				<h2 class="wps-section-title"><?php esc_html_e( 'Activity & History', 'wpshadow' ); ?></h2>
				<div class="wps-grid wps-grid-auto-320">
					<?php foreach ( $grouped['history'] as $item ) : ?>
						<?php wpshadow_render_report_card( $item ); ?>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Render individual report card
 *
 * @since  1.6030.2148
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

	$badge_class = isset( $item['badge'] ) ? 'has-badge' : '';
	?>
	<a href="<?php echo esc_url( $report_url ); ?>" class="wps-card wps-card-hover <?php echo esc_attr( $badge_class ); ?>">
		<?php if ( isset( $item['badge'] ) ) : ?>
			<span class="wps-badge wps-badge-new"><?php echo esc_html( ucfirst( $item['badge'] ) ); ?></span>
		<?php endif; ?>

		<div class="wps-card-header wps-pb-3 wps-border-bottom">
			<div class="wps-flex wps-gap-3 wps-items-start">
				<span class="dashicons <?php echo esc_attr( $item['icon'] ); ?> wps-text-2xl wps-text-primary"></span>
				<div class="wps-flex-1">
					<h3 class="wps-card-title wps-mb-0"><?php echo esc_html( $item['title'] ); ?></h3>
				</div>
			</div>
		</div>

		<div class="wps-card-body">
			<p class="wps-text-muted wps-mb-0"><?php echo esc_html( $item['desc'] ); ?></p>
		</div>

		<div class="wps-card-footer wps-pt-3 wps-border-top">
			<span class="wps-button-link">
				<?php esc_html_e( 'Generate Report', 'wpshadow' ); ?>
				<span class="dashicons dashicons-arrow-right-alt2"></span>
			</span>
		</div>
	</a>
	<?php
}

/**
 * Render individual report detail page.
 *
 * @param string $report Report slug.
 * @return void
 */
function wpshadow_render_report_detail( $report ) {
	// Map report slugs to view files
	$report_views = array(
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
		'email-report'         => 'email-report.php',
		'backup-report'        => 'backup-report.php',
		'multisite-report'     => 'multisite-report.php',
		'mobile-friendliness'  => 'mobile-friendliness.php',
		'a11y-audit'           => 'a11y-audit.php',
		'broken-links'         => 'broken-links.php',
		'visual-comparisons'   => 'visual-comparisons.php',
		'customization-audit'  => 'customization-audit.php',
		'activity-history'     => 'activity-history.php',
	);

	// Check if report exists
	if ( ! isset( $report_views[ $report ] ) ) {
		wp_die( esc_html__( 'Invalid report requested.', 'wpshadow' ) );
	}

	$report_file = WPSHADOW_PATH . 'includes/views/reports/' . $report_views[ $report ];

	// Check if file exists
	if ( ! file_exists( $report_file ) ) {
		wp_die( esc_html__( 'Report view file not found.', 'wpshadow' ) );
	}

	// Load the report view
	require $report_file;
}
