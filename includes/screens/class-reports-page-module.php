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
			'desc'    => __( 'Comprehensive visual analysis showing your site\'s unique health profile, performance scores, and benchmarks against industry standards.', 'wpshadow' ),
			'report'  => 'site-dna',
			'icon'    => 'dashicons-chart-line',
			'family'  => 'analysis',
			'enabled' => true,
			'badge'   => 'new',
		),
		array(
			'title'   => __( 'Deep Scan Report', 'wpshadow' ),
			'desc'    => __( 'Comprehensive site analysis covering security, performance, accessibility, and SEO across all diagnostics.', 'wpshadow' ),
			'report'  => 'deep-scan',
			'icon'    => 'dashicons-search',
			'family'  => 'analysis',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Quick Scan Report', 'wpshadow' ),
			'desc'    => __( 'Rapid overview of critical issues that need immediate attention across your site.', 'wpshadow' ),
			'report'  => 'quick-scan',
			'icon'    => 'dashicons-performance',
			'family'  => 'analysis',
			'enabled' => true,
		),
		
		// Page-Specific Reports
		array(
			'title'   => __( 'Mobile Friendliness Report', 'wpshadow' ),
			'desc'    => __( 'Test specific pages for mobile compatibility, responsive design, and mobile UX issues.', 'wpshadow' ),
			'report'  => 'mobile-friendliness',
			'icon'    => 'dashicons-smartphone',
			'family'  => 'page-analysis',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Accessibility Audit Report', 'wpshadow' ),
			'desc'    => __( 'WCAG compliance check for specific pages including color contrast, ARIA labels, and keyboard navigation.', 'wpshadow' ),
			'report'  => 'a11y-audit',
			'icon'    => 'dashicons-universal-access',
			'family'  => 'page-analysis',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Broken Links Report', 'wpshadow' ),
			'desc'    => __( 'Scan pages for broken internal and external links that need fixing.', 'wpshadow' ),
			'report'  => 'broken-links',
			'icon'    => 'dashicons-admin-links',
			'family'  => 'page-analysis',
			'enabled' => true,
		),
		
		// Comparison & Historical Reports
		array(
			'title'   => __( 'Visual Comparison Report', 'wpshadow' ),
			'desc'    => __( 'Side-by-side visual comparison of pages before and after changes with screenshot diff analysis.', 'wpshadow' ),
			'report'  => 'visual-comparisons',
			'icon'    => 'dashicons-images-alt2',
			'family'  => 'comparison',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Customization Audit Report', 'wpshadow' ),
			'desc'    => __( 'Review all theme and plugin customizations, tracking changes and potential conflicts.', 'wpshadow' ),
			'report'  => 'customization-audit',
			'icon'    => 'dashicons-admin-customizer',
			'family'  => 'comparison',
			'enabled' => true,
		),
		
		// Activity & History Reports
		array(
			'title'   => __( 'Activity History Report', 'wpshadow' ),
			'desc'    => __( 'Complete timeline of all WPShadow actions, treatments applied, and system changes.', 'wpshadow' ),
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
function wpshadow_render_reports() {
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
	<div class="wps-page-container">
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
 * Render individual report card.
 *
 * @param array $item Report configuration.
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
