<?php
/**
 * Site DNA Report
 *
 * Comprehensive visual analysis showing site's unique health profile,
 * performance scores, and benchmarks against industry standards.
 *
 * @package WPShadow
 * @subpackage Reports
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPShadow\Views\Tool_View_Base;

require WPSHADOW_PATH . 'includes/views/class-tool-view-base.php';

// Verify access
Tool_View_Base::verify_access( 'manage_options' );

// Enqueue assets
wp_enqueue_script(
	'wpshadow-site-dna',
	WPSHADOW_URL . 'assets/js/site-dna.js',
	array(),
	WPSHADOW_VERSION,
	true
);

// Localize script with settings and translations
\WPShadow\Core\Admin_Asset_Registry::localize_with_ajax_nonce(
	'wpshadow-site-dna',
	'wpshadowSiteDNA',
	'wpshadow_generate_dna',
	array(
		'i18nStage1' => __( 'Initializing diagnostic scan...', 'wpshadow' ),
		'i18nStage2' => __( 'Analyzing security posture...', 'wpshadow' ),
		'i18nStage3' => __( 'Evaluating performance metrics...', 'wpshadow' ),
		'i18nStage4' => __( 'Checking accessibility compliance...', 'wpshadow' ),
		'i18nStage5' => __( 'Assessing code quality...', 'wpshadow' ),
		'i18nStage6' => __( 'Measuring UX excellence...', 'wpshadow' ),
		'i18nStage7' => __( 'Computing DNA signature...', 'wpshadow' ),
		'i18nStage8' => __( 'Generating visualizations...', 'wpshadow' ),
		'i18nComplete' => __( 'Analysis complete!', 'wpshadow' ),
		'i18nError'  => __( 'An error occurred. Please try again.', 'wpshadow' ),
	)
);

// Render header
Tool_View_Base::render_header( __( 'Site DNA Report', 'wpshadow' ) );
?>
	<p class="wps-lead-text">
		<?php esc_html_e( 'Your site\'s comprehensive health profile analyzed across 3,964 diagnostic checks, visualized as a unique DNA signature.', 'wpshadow' ); ?>
	</p>

	<div class="wpshadow-dna-grid">
		<!-- Control Panel -->
		<div class="wpshadow-dna-panel wps-card wps-form-card" role="region" aria-labelledby="wpshadow-dna-controls-heading">
			<h3 id="wpshadow-dna-controls-heading"><?php esc_html_e( 'Generate DNA Report', 'wpshadow' ); ?></h3>
			<form id="wpshadow-dna-form">
				<div class="wps-settings-section">
					<div class="wps-form-group">
						<label class="wps-label" for="wpshadow-dna-depth">
							<?php esc_html_e( 'Analysis Depth', 'wpshadow' ); ?>
						</label>
						<select id="wpshadow-dna-depth" name="depth" class="wps-input">
							<option value="quick"><?php esc_html_e( 'Quick Scan (Critical issues only)', 'wpshadow' ); ?></option>
							<option value="standard" selected><?php esc_html_e( 'Standard (Recommended)', 'wpshadow' ); ?></option>
							<option value="deep"><?php esc_html_e( 'Deep Analysis (All diagnostics)', 'wpshadow' ); ?></option>
						</select>
						<span class="wps-help-text">
							<?php esc_html_e( 'Deeper analysis takes longer but provides more comprehensive insights.', 'wpshadow' ); ?>
						</span>
					</div>

					<div class="wps-form-group">
						<label class="wps-label" for="wpshadow-dna-benchmark">
							<?php esc_html_e( 'Benchmark Against', 'wpshadow' ); ?>
						</label>
						<select id="wpshadow-dna-benchmark" name="benchmark" class="wps-input">
							<option value="none"><?php esc_html_e( 'No Comparison', 'wpshadow' ); ?></option>
							<option value="industry" selected><?php esc_html_e( 'Industry Average', 'wpshadow' ); ?></option>
							<option value="similar"><?php esc_html_e( 'Similar-Sized Sites', 'wpshadow' ); ?></option>
							<option value="top-performers"><?php esc_html_e( 'Top 10% Performers', 'wpshadow' ); ?></option>
							<option value="historical"><?php esc_html_e( 'My Historical Data', 'wpshadow' ); ?></option>
						</select>
					</div>

					<div class="wps-form-group">
						<label class="wps-checkbox">
							<input type="checkbox" id="wpshadow-dna-save-snapshot" name="save_snapshot" checked />
							<?php esc_html_e( 'Save snapshot for historical comparison', 'wpshadow' ); ?>
						</label>
					</div>
				</div>
			
				<p class="submit">
				<button type="submit" class="wps-btn wps-btn-primary wps-btn-icon-left" id="wpshadow-dna-submit-btn" aria-label="<?php esc_attr_e( 'Generate Site DNA Report', 'wpshadow' ); ?>">
					<span class="dashicons dashicons-chart-line"></span>
					<?php esc_html_e( 'Generate DNA Report', 'wpshadow' ); ?>
				</button>
			</p>

			<!-- Progress Bar -->
		<div id="wpshadow-dna-progress" class="wps-none wpshadow-dna-progress">
			<div class="wpshadow-dna-progress-container">
				<div id="wpshadow-dna-progress-bar" class="wpshadow-dna-progress-bar">
					<span id="wpshadow-dna-progress-text">0%</span>
				</div>
			</div>
			<div id="wpshadow-dna-progress-status" class="wpshadow-dna-progress-status"></div>

			<div id="wpshadow-dna-error" class="notice notice-error wps-none" role="alert" aria-live="assertive"></div>
		</div>

		<!-- Info Panel -->
		<div class="wpshadow-dna-panel wps-card" role="region" aria-labelledby="wpshadow-dna-info-heading">
			<h3 id="wpshadow-dna-info-heading"><?php esc_html_e( 'What is Site DNA?', 'wpshadow' ); ?></h3>
			<p><?php esc_html_e( 'Your Site DNA Report creates a unique health signature by analyzing:', 'wpshadow' ); ?></p>
			<ul class="wpshadow-report-list">
				<li><strong><?php esc_html_e( 'Design Maturity', 'wpshadow' ); ?></strong> - <?php esc_html_e( 'Visual consistency, accessibility, and modern design patterns', 'wpshadow' ); ?></li>
				<li><strong><?php esc_html_e( 'Performance Grade', 'wpshadow' ); ?></strong> - <?php esc_html_e( 'Speed, optimization, and resource efficiency', 'wpshadow' ); ?></li>
				<li><strong><?php esc_html_e( 'Security Posture', 'wpshadow' ); ?></strong> - <?php esc_html_e( 'Vulnerabilities, hardening, and threat protection', 'wpshadow' ); ?></li>
				<li><strong><?php esc_html_e( 'Code Quality', 'wpshadow' ); ?></strong> - <?php esc_html_e( 'WordPress standards, best practices, and maintainability', 'wpshadow' ); ?></li>
				<li><strong><?php esc_html_e( 'UX Excellence', 'wpshadow' ); ?></strong> - <?php esc_html_e( 'User experience, navigation, and conversion optimization', 'wpshadow' ); ?></li>
			</ul>
			<div class="wps-info-box wps-info-box-info wps-mt-4">
				<strong><?php esc_html_e( 'Share Your Score!', 'wpshadow' ); ?></strong>
				<p class="wps-mb-0"><?php esc_html_e( 'Get a shareable badge and show off your site\'s health score on social media.', 'wpshadow' ); ?></p>
			</div>
		</div>
	</div>

	<!-- Results Section -->
	<div class="wpshadow-dna-results wps-none" id="wpshadow-dna-results" role="region" aria-live="polite" aria-labelledby="wpshadow-dna-results-heading">
		<!-- DNA Visualization -->
		<div class="wps-card wps-mb-4">
			<h3 id="wpshadow-dna-results-heading"><?php esc_html_e( 'Your Site DNA', 'wpshadow' ); ?></h3>
			<div class="wpshadow-dna-viz-container">
				<canvas id="wpshadow-dna-canvas" width="800" height="400"></canvas>
			</div>
			<div id="wpshadow-dna-overall-score" class="wpshadow-dna-score-display"></div>
		</div>

		<!-- Category Scores -->
		<div class="wps-card wps-mb-4">
			<h3><?php esc_html_e( 'Category Breakdown', 'wpshadow' ); ?></h3>
			<div id="wpshadow-dna-categories" class="wpshadow-dna-categories-grid"></div>
		</div>

		<!-- Benchmark Comparison -->
		<div class="wps-card wps-mb-4 wps-none" id="wpshadow-dna-benchmark-section">
			<h3><?php esc_html_e( 'Benchmark Comparison', 'wpshadow' ); ?></h3>
			<div id="wpshadow-dna-benchmark-chart"></div>
		</div>

		<!-- Key Findings -->
		<div class="wps-card wps-mb-4">
			<h3><?php esc_html_e( 'Key Insights', 'wpshadow' ); ?></h3>
			<div id="wpshadow-dna-insights"></div>
		</div>

		<!-- Actions -->
		<div class="wps-card">
			<div class="wps-flex wps-gap-3">
				<button type="button" class="wps-btn wps-btn-secondary" id="wpshadow-dna-export-pdf">
					<span class="dashicons dashicons-pdf"></span>
					<?php esc_html_e( 'Export PDF Report', 'wpshadow' ); ?>
				</button>
				<button type="button" class="wps-btn wps-btn-secondary" id="wpshadow-dna-share-badge">
					<span class="dashicons dashicons-share"></span>
					<?php esc_html_e( 'Get Shareable Badge', 'wpshadow' ); ?>
				</button>
				<button type="button" class="wps-btn wps-btn-secondary" id="wpshadow-dna-compare-historical">
					<span class="dashicons dashicons-chart-area"></span>
					<?php esc_html_e( 'View Historical Trend', 'wpshadow' ); ?>
				</button>
			</div>
		</div>
	</div>
</div>

<?php
Tool_View_Base::render_footer();
