<?php
declare(strict_types=1);

namespace WPShadow\Reports;

/**
 * Report Builder - Build custom reports with filters
 *
 * Provides UI for generating reports with:
 * - Date range selection (presets + custom)
 * - Category filtering
 * - Action type filtering
 * - Report type selection (summary/detailed/executive)
 * - Export format selection (HTML/CSV/JSON)
 * - Scheduled report configuration
 *
 * Philosophy:
 * - #1 Helpful Neighbor: Intuitive report building
 * - #9 Show Value: Detailed metrics and trends
 * - #5 Drive to KB: Educational links in reports
 *
 * @package WPShadow\Reports
 */
class Report_Builder {

	/**
	 * Render report builder form and dashboard
	 *
	 * @return void
	 */
	public static function render(): void {
		wp_enqueue_style(
			'wpshadow-report-builder',
			WPSHADOW_URL . 'assets/css/report-builder.css',
			array(),
			WPSHADOW_VERSION
		);

		wp_enqueue_script(
			'wpshadow-report-builder',
			WPSHADOW_URL . 'assets/js/report-builder.js',
			array( 'jquery' ),
			WPSHADOW_VERSION,
			true
		);

		\WPShadow\Core\Admin_Asset_Registry::localize_with_ajax_nonce(
			'wpshadow-report-builder',
			'wpshadowReportBuilder',
			'wpshadow_report_builder',
			array(
				'strings'  => array(
					'loading' => __( 'Loading...', 'wpshadow' ),
					'error'   => __( 'Error generating report', 'wpshadow' ),
				),
			),
			'nonce',
			'ajax_url'
		);

		?>
		<div class="wrap wps-page-container">
			<?php
			wpshadow_render_page_header(
				__( 'Report Builder', 'wpshadow' ),
				__( 'Assemble custom reports, compare periods, and export in one place.', 'wpshadow' ),
				'dashicons-analytics'
			);
			?>

			<div class="wps-grid wps-gap-4" class="wps-grid-cols-320-1fr">
				<!-- Left Sidebar: Report Builder Form -->
				<div class="wps-card">
					<div class="wps-card-header">
						<h3 class="wps-card-title"><?php esc_html_e( 'Build Report', 'wpshadow' ); ?></h3>
					</div>
					<div class="wps-card-body">
						<form id="wpshadow-report-builder" class="wps-flex wps-flex-col wps-gap-4">
							<?php wp_nonce_field( 'wpshadow_report_builder', 'report_nonce' ); ?>

							<!-- Quick Presets -->
							<div class="wps-form-group">
								<label class="wps-form-label">
									<?php esc_html_e( 'Quick Presets', 'wpshadow' ); ?>
								</label>
								<div class="wps-grid wps-grid-auto-160 wps-gap-2">
									<button type="button" class="wps-btn wps-btn-secondary preset-btn" data-preset="today">
										<?php esc_html_e( 'Today', 'wpshadow' ); ?>
									</button>
									<button type="button" class="wps-btn wps-btn-secondary preset-btn" data-preset="week">
										<?php esc_html_e( 'Last 7 Days', 'wpshadow' ); ?>
									</button>
									<button type="button" class="wps-btn wps-btn-secondary preset-btn" data-preset="month">
										<?php esc_html_e( 'Last 30 Days', 'wpshadow' ); ?>
									</button>
									<button type="button" class="wps-btn wps-btn-secondary preset-btn" data-preset="quarter">
										<?php esc_html_e( 'Last 90 Days', 'wpshadow' ); ?>
									</button>
								</div>
							</div>

							<!-- Date Range -->
							<div class="wps-grid wps-grid-auto-200 wps-gap-3 wps-my-4">
								<div class="wps-form-group">
									<label for="report_start_date" class="wps-form-label">
										<?php esc_html_e( 'Start Date', 'wpshadow' ); ?>
									</label>
									<input type="date" id="report_start_date" name="start_date" class="wps-input"
										value="<?php echo esc_attr( date( 'Y-m-d', strtotime( '-30 days' ) ) ); ?>" />
								</div>

								<div class="wps-form-group">
									<label for="report_end_date" class="wps-form-label">
										<?php esc_html_e( 'End Date', 'wpshadow' ); ?>
									</label>
									<input type="date" id="report_end_date" name="end_date" class="wps-input"
										value="<?php echo esc_attr( date( 'Y-m-d' ) ); ?>" />
								</div>
							</div>

							<!-- Category Filter -->
							<div class="wps-form-group">
								<label for="report_category" class="wps-form-label">
									<?php esc_html_e( 'Category', 'wpshadow' ); ?>
								</label>
								<select id="report_category" name="category" class="wps-select">
									<option value=""><?php esc_html_e( 'All Categories', 'wpshadow' ); ?></option>
									<option value="security"><?php esc_html_e( 'Security', 'wpshadow' ); ?></option>
									<option value="performance"><?php esc_html_e( 'Performance', 'wpshadow' ); ?></option>
									<option value="maintenance"><?php esc_html_e( 'Maintenance', 'wpshadow' ); ?></option>
									<option value="workflow"><?php esc_html_e( 'Workflow', 'wpshadow' ); ?></option>
									<option value="backup"><?php esc_html_e( 'Backup', 'wpshadow' ); ?></option>
								</select>
							</div>

							<!-- Report Type -->
							<div class="wps-form-group">
								<label for="report_type" class="wps-form-label">
					<?php esc_html_e( 'What level of detail do you want?', 'wpshadow' ); ?>
				</label>
				<select id="report_type" name="type" class="wps-select">
					<option value="summary"><?php esc_html_e( 'Quick Summary (2-page highlights of what matters most)', 'wpshadow' ); ?></option>
					<option value="detailed"><?php esc_html_e( 'Complete Details (full list of everything we found)', 'wpshadow' ); ?></option>
					<option value="executive"><?php esc_html_e( 'Boss-Friendly Report (simple charts showing time and money saved)', 'wpshadow' ); ?></option>
				</select>
				<p class="wps-form-help">
					<?php esc_html_e( 'Not sure? Start with Quick Summary to see the highlights.', 'wpshadow' ); ?>
							<!-- Export Format -->
							<div class="wps-form-group">
								<label for="report_format" class="wps-form-label">
					<?php esc_html_e( 'How do you want to view it?', 'wpshadow' ); ?>
				</label>
				<select id="report_format" name="format" class="wps-select">
					<option value="html"><?php esc_html_e( 'Web Page (view in browser, print, or share link)', 'wpshadow' ); ?></option>
					<option value="csv"><?php esc_html_e( 'Spreadsheet (open in Excel, Google Sheets, or Numbers)', 'wpshadow' ); ?></option>
					<option value="json"><?php esc_html_e( 'Raw Data (for developers connecting to other tools)', 'wpshadow' ); ?></option>
				</select>
				<p class="wps-form-help">
					<?php esc_html_e( 'Most people choose Web Page. Pick Spreadsheet if you want to analyze numbers.', 'wpshadow' ); ?>
				</p>
							<div class="wps-grid wps-grid-auto-200 wps-gap-2 wps-mt-4">
								<button type="submit" class="wps-btn wps-btn-primary wps-w-full">
									<?php esc_html_e( 'Generate', 'wpshadow' ); ?>
								</button>
								<button type="button" class="wps-btn wps-btn-secondary wps-w-full" id="compare-periods-btn">
									<?php esc_html_e( 'Compare', 'wpshadow' ); ?>
								</button>
							</div>

							<!-- Email Option -->
							<div class="wps-form-group wps-mt-4 wps-report-builder-email-group">
								<label class="wps-flex wps-items-center wps-gap-2 wps-cursor-pointer">
									<input type="checkbox" name="email_report" id="email_report" />
									<?php esc_html_e( 'Email this report', 'wpshadow' ); ?>
								</label>
								<div id="email-options" class="wps-mt-3" class="wps-none">
									<input type="email" name="email_to" class="wps-input" placeholder="<?php esc_attr_e( 'Email address', 'wpshadow' ); ?>" />
									<label class="wps-flex wps-items-center wps-gap-2 wps-mt-2 wps-report-builder-cursor-pointer">
										<input type="checkbox" name="schedule_email" />
										<?php esc_html_e( 'Schedule monthly', 'wpshadow' ); ?>
									</label>
								</div>
							</div>

						</form>
					</div>
				</div>

				<!-- Right Content: Report Display -->
				<div class="report-display-area">
					<div id="report-content" class="wps-card" class="wps-min-h-500">
						<p class="wps-p-40">
							<?php esc_html_e( 'Build a report using the form on the left to see results here.', 'wpshadow' ); ?>
						</p>
					</div>
				</div>

			</div>
		</div>
		
		<?php
	}
}
