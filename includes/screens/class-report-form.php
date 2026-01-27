<?php
declare(strict_types=1);

namespace WPShadow\Admin;

/**
 * Report Generation Form
 *
 * UI for generating and exporting reports.
 * Date range selector, format selection, download.
 *
 * Features:
 * - Date range picker
 * - Report type selection
 * - Export format selection
 * - Quick presets
 * - Email sending option
 * - Download functionality
 */
class Report_Form {

	/**
	 * Render report form
	 *
	 * @return string HTML output
	 */
	public static function render(): string {
		ob_start();
		?>
		<div class="wps-page-container">
			<!-- Page Header -->
			<div class="wps-page-header">
				<h1 class="wps-page-title">
					<span class="dashicons dashicons-chart-line"></span>
					<?php esc_html_e( 'Generate Reports', 'wpshadow' ); ?>
					<small style="font-size: 14px; color: #666; margin-left: 12px;">v<?php echo esc_html( WPSHADOW_VERSION ); ?></small>
				</h1>
				<p class="wps-page-subtitle">
					<?php esc_html_e( 'Create, preview, and export custom reports of your site activity.', 'wpshadow' ); ?>
				</p>
			</div>

			<div class="wps-card">
				<div class="wps-card-header">
					<div>
						<h2 class="wps-card-title wps-m-0">
							<span class="dashicons dashicons-chart-line"></span>
							<?php esc_html_e( 'Report Generator', 'wpshadow' ); ?>
						</h2>
						<p class="wps-card-description wps-m-0">
							<?php esc_html_e( 'Configure date range, type, and format for your report.', 'wpshadow' ); ?>
						</p>
					</div>
				</div>
				<div class="wps-card-body">
					<form class="report-form" id="wpshadow-report-form">
						<?php wp_nonce_field( 'wpshadow_generate_report', 'report_nonce' ); ?>

						<!-- Quick Presets -->
						<div class="wps-form-group">
							<label class="wps-form-label">
								<?php esc_html_e( 'Quick Presets', 'wpshadow' ); ?>
							</label>
							<div class="wps-flex wps-gap-2">
								<button type="button" class="wps-btn wps-btn--secondary" data-preset="today">
									<?php esc_html_e( 'Today', 'wpshadow' ); ?>
								</button>
								<button type="button" class="wps-btn wps-btn--secondary" data-preset="week">
									<?php esc_html_e( 'Last 7 Days', 'wpshadow' ); ?>
								</button>
								<button type="button" class="wps-btn wps-btn--secondary" data-preset="month">
									<?php esc_html_e( 'Last 30 Days', 'wpshadow' ); ?>
								</button>
								<button type="button" class="wps-btn wps-btn--secondary" data-preset="quarter">
									<?php esc_html_e( 'Last 90 Days', 'wpshadow' ); ?>
								</button>
							</div>
						</div>

						<!-- Date Range -->
						<div class="wps-grid wps-grid-auto-200 wps-gap-4 wps-my-4">
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

						<!-- Report Type -->
						<div class="wps-form-group">
							<label for="report_type" class="wps-form-label">
								<?php esc_html_e( 'Report Type', 'wpshadow' ); ?>
							</label>
							<select id="report_type" name="type" class="wps-select">
								<option value="summary"><?php esc_html_e( 'Summary Report', 'wpshadow' ); ?></option>
								<option value="detailed"><?php esc_html_e( 'Detailed Report', 'wpshadow' ); ?></option>
								<option value="executive"><?php esc_html_e( 'Executive Summary', 'wpshadow' ); ?></option>
							</select>
							<p class="wps-form-help">
								<?php esc_html_e( 'Summary: Overview. Detailed: All events. Executive: Board-level metrics.', 'wpshadow' ); ?>
							</p>
						</div>

						<!-- Export Format -->
						<div class="wps-form-group">
							<label for="report_format" class="wps-form-label">
								<?php esc_html_e( 'Export Format', 'wpshadow' ); ?>
							</label>
							<select id="report_format" name="format" class="wps-select">
								<option value="html"><?php esc_html_e( 'HTML (Email-friendly)', 'wpshadow' ); ?></option>
								<option value="json"><?php esc_html_e( 'JSON (API)', 'wpshadow' ); ?></option>
								<option value="csv"><?php esc_html_e( 'CSV (Excel)', 'wpshadow' ); ?></option>
							</select>
						</div>
					</form>
				</div>
				<div class="wps-card-footer">
					<button type="button" class="wps-btn wps-btn--primary" id="generate-report-btn">
						<span class="dashicons dashicons-chart-line"></span>
						<?php esc_html_e( 'Generate Report', 'wpshadow' ); ?>
					</button>
					<button type="button" class="wps-btn wps-btn--secondary" id="email-report-btn">
						<span class="dashicons dashicons-email-alt"></span>
						<?php esc_html_e( 'Email Report', 'wpshadow' ); ?>
					</button>
				</div>
			</div>

			<!-- Loading State -->
			<div class="wps-alert wps-alert--info wps-mt-4 wps-none" id="loading-spinner">
				<span class="dashicons dashicons-hourglass"></span>
				<?php esc_html_e( 'Generating report...', 'wpshadow' ); ?>
			</div>

			<!-- Report Preview -->
			<div id="report-preview" class="wps-card wps-none">
				<div class="wps-card-header wps-flex wps-items-center wps-justify-between">
					<h3 class="wps-card-title wps-m-0">
						<?php esc_html_e( 'Report Preview', 'wpshadow' ); ?>
					</h3>
					<button type="button" class="wps-btn wps-btn--ghost" id="close-preview">
						<span class="dashicons dashicons-no-alt"></span>
					</button>
				</div>
				<div class="wps-card-body" id="report-content"></div>
			</div>

			<!-- Email Modal -->
			<div id="email-modal" class="wps-none">
				<div class="wps-card wps-max-w-xl wps-mx-auto">
					<div class="wps-card-header wps-flex wps-items-center wps-justify-between">
						<h3 class="wps-card-title wps-m-0">
							<?php esc_html_e( 'Email Report', 'wpshadow' ); ?>
						</h3>
						<button type="button" class="wps-btn wps-btn--ghost" id="close-email-modal">
							<span class="dashicons dashicons-no-alt"></span>
						</button>
					</div>
					<div class="wps-card-body">
						<div class="wps-form-group">
							<label for="email_recipient" class="wps-form-label">
								<?php esc_html_e( 'Recipient Email', 'wpshadow' ); ?>
							</label>
							<input type="email" id="email_recipient" class="wps-input" placeholder="<?php esc_attr_e( 'Enter email address', 'wpshadow' ); ?>" />
						</div>
						<div class="wps-form-group">
							<label for="email_frequency" class="wps-form-label">
								<?php esc_html_e( 'Frequency', 'wpshadow' ); ?>
							</label>
							<select id="email_frequency" class="wps-select">
								<option value="now"><?php esc_html_e( 'Send Now', 'wpshadow' ); ?></option>
								<option value="daily"><?php esc_html_e( 'Daily', 'wpshadow' ); ?></option>
								<option value="weekly"><?php esc_html_e( 'Weekly', 'wpshadow' ); ?></option>
								<option value="monthly"><?php esc_html_e( 'Monthly', 'wpshadow' ); ?></option>
							</select>
						</div>
					</div>
					<div class="wps-card-footer">
						<button type="button" class="wps-btn wps-btn--secondary" id="cancel-email">
							<?php esc_html_e( 'Cancel', 'wpshadow' ); ?>
						</button>
						<button type="button" class="wps-btn wps-btn--primary" id="confirm-email">
							<span class="dashicons dashicons-email-alt"></span>
							<?php esc_html_e( 'Send', 'wpshadow' ); ?>
						</button>
					</div>
				</div>
			</div>

			<!-- Previous Reports -->
			<?php echo wp_kses_post( self::render_previous_reports() ); ?>
		</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Render previous reports list
	 *
	 * @return string HTML
	 */
	public static function render_previous_reports(): string {
		$html = '<div class="wps-card wps-mt-6">
			<div class="wps-card-header">
				<h3 class="wps-card-title wps-m-0">
					<span class="dashicons dashicons-archive"></span>
					' . esc_html__( 'Previous Reports', 'wpshadow' ) . '
				</h3>
			</div>
			<div class="wps-card-body">
				<table class="wp-list-table widefat striped wps-m-0">
					<thead>
						<tr>
							<th>' . esc_html__( 'Date Range', 'wpshadow' ) . '</th>
							<th>' . esc_html__( 'Type', 'wpshadow' ) . '</th>
							<th>' . esc_html__( 'Generated', 'wpshadow' ) . '</th>
							<th>' . esc_html__( 'Actions', 'wpshadow' ) . '</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>2026-01-01 to 2026-01-31</td>
							<td>Summary</td>
							<td>2026-02-01 10:00</td>
							<td>
								<a href="#" class="wps-btn wps-btn--secondary wps-mr-2">' . esc_html__( 'View', 'wpshadow' ) . '</a>
								<a href="#" class="wps-btn wps-btn--secondary">' . esc_html__( 'Download', 'wpshadow' ) . '</a>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>';

		return $html;
	}
}
