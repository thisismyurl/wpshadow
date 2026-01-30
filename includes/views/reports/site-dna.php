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
wp_localize_script(
	'wpshadow-site-dna',
	'wpshadowSiteDNA',
	array(
		'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
		'nonce'     => wp_create_nonce( 'wpshadow_generate_dna' ),
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
			<div id="wpshadow-dna-progress" class="wps-none" style="margin-top: 20px;">
				<div style="background: #f0f0f1; border-radius: 4px; overflow: hidden; margin-bottom: 10px;">
					<div id="wpshadow-dna-progress-bar" style="height: 24px; background: linear-gradient(90deg, #0073aa 0%, #005177 100%); width: 0%; transition: width 0.3s ease; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px; font-weight: 600;">
						<span id="wpshadow-dna-progress-text">0%</span>
					</div>
				</div>
				<div id="wpshadow-dna-progress-status" style="font-size: 13px; color: #50575e; text-align: center;"></div>
			</div>

			<div id="wpshadow-dna-error" class="notice notice-error wps-none" role="alert" aria-live="assertive"></div>
		</div>

		<!-- Info Panel -->
		<div class="wpshadow-dna-panel wps-card" role="region" aria-labelledby="wpshadow-dna-info-heading">
			<h3 id="wpshadow-dna-info-heading"><?php esc_html_e( 'What is Site DNA?', 'wpshadow' ); ?></h3>
			<p><?php esc_html_e( 'Your Site DNA Report creates a unique health signature by analyzing:', 'wpshadow' ); ?></p>
			<ul style="list-style: disc; margin-left: 18px;">
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

<style>
.wpshadow-dna-grid {
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: 20px;
	margin-bottom: 20px;
}

@media (max-width: 768px) {
	.wpshadow-dna-grid {
		grid-template-columns: 1fr;
	}
}

.wpshadow-dna-viz-container {
	display: flex;
	justify-content: center;
	align-items: center;
	min-height: 400px;
	background: linear-gradient(135deg, #f5f7fa 0%, #e8eef5 100%);
	border-radius: 8px;
	padding: 20px;
	margin: 20px 0;
}

.wpshadow-dna-score-display {
	text-align: center;
	padding: 30px;
	background: #fff;
	border-radius: 8px;
	box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.wpshadow-dna-overall-score {
	font-size: 72px;
	font-weight: 700;
	color: #0073aa;
	margin: 0;
	line-height: 1;
}

.wpshadow-dna-categories-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
	gap: 15px;
	margin-top: 15px;
}

.wpshadow-dna-category {
	background: #f5f7fa;
	padding: 20px;
	border-radius: 8px;
	text-align: center;
	border: 2px solid #e0e5ea;
	transition: all 0.3s ease;
}

.wpshadow-dna-category:hover {
	border-color: #0073aa;
	transform: translateY(-2px);
	box-shadow: 0 4px 12px rgba(0,115,170,0.1);
}

.wpshadow-dna-category-score {
	font-size: 42px;
	font-weight: 700;
	margin: 10px 0;
}

.wpshadow-dna-category-label {
	font-size: 14px;
	color: #50575e;
	margin-top: 5px;
}

.wps-badge-new {
	position: absolute;
	top: 10px;
	right: 10px;
	background: linear-gradient(135deg, #00a32a 0%, #008a20 100%);
	color: white;
	padding: 4px 12px;
	border-radius: 12px;
	font-size: 11px;
	font-weight: 600;
	text-transform: uppercase;
	letter-spacing: 0.5px;
}

.wps-card.has-badge {
	position: relative;
}

.wps-button-link {
	display: inline-flex;
	align-items: center;
	gap: 5px;
	color: #0073aa;
	font-weight: 600;
	transition: all 0.2s ease;
}

.wps-card-hover:hover .wps-button-link {
	gap: 10px;
	color: #005177;
}

.wps-card-hover:hover {
	transform: translateY(-2px);
	box-shadow: 0 8px 16px rgba(0,0,0,0.1);
}
</style>

<?php
Tool_View_Base::render_footer();
