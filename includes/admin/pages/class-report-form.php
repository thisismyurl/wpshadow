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
			<?php wpshadow_render_page_header(
				__( 'Generate Reports', 'wpshadow' ),
				__( 'Create, preview, and export custom reports of your site activity.', 'wpshadow' ),
				'dashicons-chart-line'
			); ?>

			<!-- Two-Column Layout: 33/66 Split -->
			<div class="wps-grid" style="grid-template-columns: 1fr 2fr; gap: 2rem;">
				<!-- LEFT COLUMN: Report Generator (33%) -->
				<div>
				<?php
				wpshadow_render_card(
					array(
						'title'       => __( 'Report Generator', 'wpshadow' ),
						'title_tag'   => 'h2',
						'description' => __( 'Configure date range, type, and format for your report.', 'wpshadow' ),
						'icon'        => 'dashicons-chart-line',
						'body'        => function() {
							?>
							<form class="report-form" id="wpshadow-report-form">
								<?php wp_nonce_field( 'wpshadow_generate_report', 'report_nonce' ); ?>

								<!-- Quick Presets -->
								<div class="wps-form-group">
									<label class="wps-form-label">
										<?php esc_html_e( 'Quick Presets', 'wpshadow' ); ?>
									</label>
									<div class="wps-flex wps-gap-2" style="flex-wrap: wrap;">
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
						<?php
					},
					'footer'      => function() {
						?>
						<button type="button" class="wps-btn wps-btn--primary" id="generate-report-btn" style="width: 100%;">
							<span class="dashicons dashicons-chart-line"></span>
							<?php esc_html_e( 'Generate Report', 'wpshadow' ); ?>
						</button>
						<?php
					},
				)
			);
			?>
				<!-- RIGHT COLUMN: Report Preview & Email (66%) -->
				<div>
					<!-- Loading State -->
					<div class="wps-alert wps-alert--info wps-mb-4 wps-none" id="loading-spinner">
						<span class="dashicons dashicons-hourglass"></span>
						<?php esc_html_e( 'Generating report...', 'wpshadow' ); ?>
					</div>

					<!-- Report Preview -->
					<?php
					wpshadow_render_card(
						array(
							'title'          => __( 'Report Preview', 'wpshadow' ),
							'card_class'     => 'wps-mb-4 wps-none',
							'attrs'          => array(
								'id' => 'report-preview',
							),
							'header_actions' => array(
								array(
									'label'      => __( 'Close', 'wpshadow' ),
									'class'      => 'wps-btn wps-btn--ghost',
									'icon'       => 'dashicons-no-alt',
									'attrs'      => array(
										'id' => 'close-preview',
									),
									'aria_label' => __( 'Close report preview', 'wpshadow' ),
								),
							),
							'body'           => function() {
								?>
								<div id="report-content"></div>
								<?php
							},
						)
					);
					?>

					<!-- Email Report Button -->
					<button type="button" class="wps-btn wps-btn--primary" id="email-report-btn" style="width: 100%;" data-wpshadow-modal-open="email-modal">
						<span class="dashicons dashicons-email-alt"></span>
						<?php esc_html_e( 'Email Report', 'wpshadow' ); ?>
					</button>
				</div>
			</div>

			<!-- Email Modal -->
			<div id="email-modal" class="wpshadow-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="email-modal-title" aria-hidden="true" data-wpshadow-modal="static" data-overlay-close="true" data-esc-close="true">
				<div class="wpshadow-modal wpshadow-modal--medium" role="document">
					<button type="button" class="wpshadow-modal-close" aria-label="<?php echo esc_attr__( 'Close dialog', 'wpshadow' ); ?>" data-wpshadow-modal-close="email-modal">
						<span class="dashicons dashicons-no-alt" aria-hidden="true"></span>
					</button>
					<div class="wpshadow-modal-header">
						<h3 id="email-modal-title" class="wpshadow-modal-title wps-m-0">
							<?php esc_html_e( 'Email Report', 'wpshadow' ); ?>
						</h3>
					</div>
					<div class="wpshadow-modal-body">
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
					<div class="wpshadow-modal-footer">
						<button type="button" class="wps-btn wps-btn--secondary" id="cancel-email" data-wpshadow-modal-close="email-modal">
							<?php esc_html_e( 'Cancel', 'wpshadow' ); ?>
						</button>
						<button type="button" class="wps-btn wps-btn--primary" id="confirm-email">
							<span class="dashicons dashicons-email-alt"></span>
							<?php esc_html_e( 'Send', 'wpshadow' ); ?>
						</button>
					</div>
				</div>
			</div>

			<!-- Previous Reports (100% width below) -->
			<?php echo wp_kses_post( self::render_previous_reports() ); ?>

			<!-- Activity History Section -->
			<div style="margin-top: 60px; border-top: 1px solid #e0e0e0; padding-top: 40px;">
				<?php
				if ( function_exists( 'wpshadow_render_page_activities' ) ) {
					wpshadow_render_page_activities( 'reports', 10 );
				}
				?>
			</div>
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
		ob_start();
		wpshadow_render_card(
			array(
				'title'      => __( 'Previous Reports', 'wpshadow' ),
				'icon'       => 'dashicons-archive',
				'card_class' => 'wps-mt-6',
				'body'       => function() {
					?>
					<table class="wp-list-table widefat striped wps-m-0">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Date Range', 'wpshadow' ); ?></th>
								<th><?php esc_html_e( 'Type', 'wpshadow' ); ?></th>
								<th><?php esc_html_e( 'Generated', 'wpshadow' ); ?></th>
								<th><?php esc_html_e( 'Actions', 'wpshadow' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>2026-01-01 to 2026-01-31</td>
								<td>Summary</td>
								<td>2026-02-01 10:00</td>
								<td>
									<a href="#" class="wps-btn wps-btn--secondary wps-mr-2"><?php esc_html_e( 'View', 'wpshadow' ); ?></a>
									<a href="#" class="wps-btn wps-btn--secondary"><?php esc_html_e( 'Download', 'wpshadow' ); ?></a>
								</td>
							</tr>
						</tbody>
					</table>
					<?php
				},
			)
		);
		return ob_get_clean();
	}
}
