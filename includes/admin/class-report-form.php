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
		<div class="wpshadow-report-form-container">
			<h2><?php esc_html_e( 'Generate Reports', 'wpshadow' ); ?></h2>
			
			<div class="report-form-card">
				<form class="report-form" id="wpshadow-report-form">
					<?php wp_nonce_field( 'wpshadow_generate_report', 'report_nonce' ); ?>
					
					<!-- Quick Presets -->
					<div class="form-group quick-presets">
						<label><?php esc_html_e( 'Quick Presets', 'wpshadow' ); ?></label>
						<div class="preset-buttons">
							<button type="button" class="preset-btn" data-preset="today">
								<?php esc_html_e( 'Today', 'wpshadow' ); ?>
							</button>
							<button type="button" class="preset-btn" data-preset="week">
								<?php esc_html_e( 'Last 7 Days', 'wpshadow' ); ?>
							</button>
							<button type="button" class="preset-btn" data-preset="month">
								<?php esc_html_e( 'Last 30 Days', 'wpshadow' ); ?>
							</button>
							<button type="button" class="preset-btn" data-preset="quarter">
								<?php esc_html_e( 'Last 90 Days', 'wpshadow' ); ?>
							</button>
						</div>
					</div>
					
					<!-- Date Range -->
					<div class="form-row">
						<div class="form-group">
							<label for="report_start_date">
								<?php esc_html_e( 'Start Date', 'wpshadow' ); ?>
							</label>
							<input type="date" id="report_start_date" name="start_date" 
								value="<?php echo esc_attr( date( 'Y-m-d', strtotime( '-30 days' ) ) ); ?>" />
						</div>
						
						<div class="form-group">
							<label for="report_end_date">
								<?php esc_html_e( 'End Date', 'wpshadow' ); ?>
							</label>
							<input type="date" id="report_end_date" name="end_date" 
								value="<?php echo esc_attr( date( 'Y-m-d' ) ); ?>" />
						</div>
					</div>
					
					<!-- Report Type -->
					<div class="form-group">
						<label for="report_type">
							<?php esc_html_e( 'Report Type', 'wpshadow' ); ?>
						</label>
						<select id="report_type" name="type">
							<option value="summary"><?php esc_html_e( 'Summary Report', 'wpshadow' ); ?></option>
							<option value="detailed"><?php esc_html_e( 'Detailed Report', 'wpshadow' ); ?></option>
							<option value="executive"><?php esc_html_e( 'Executive Summary', 'wpshadow' ); ?></option>
						</select>
						<p class="description">
							<?php esc_html_e( 'Summary: Overview. Detailed: All events. Executive: Board-level metrics.', 'wpshadow' ); ?>
						</p>
					</div>
					
					<!-- Export Format -->
					<div class="form-group">
						<label for="report_format">
							<?php esc_html_e( 'Export Format', 'wpshadow' ); ?>
						</label>
						<select id="report_format" name="format">
							<option value="html"><?php esc_html_e( 'HTML (Email-friendly)', 'wpshadow' ); ?></option>
							<option value="json"><?php esc_html_e( 'JSON (API)', 'wpshadow' ); ?></option>
							<option value="csv"><?php esc_html_e( 'CSV (Excel)', 'wpshadow' ); ?></option>
						</select>
					</div>
					
					<!-- Actions -->
					<div class="form-actions">
						<button type="button" class="button button-primary" id="generate-report-btn">
							<?php esc_html_e( 'Generate Report', 'wpshadow' ); ?>
						</button>
						<button type="button" class="button" id="email-report-btn">
							<?php esc_html_e( 'Email Report', 'wpshadow' ); ?>
						</button>
					</div>
					
					<!-- Loading State -->
					<div class="loading-spinner" style="display: none;">
						<span class="spinner"></span>
						<?php esc_html_e( 'Generating report...', 'wpshadow' ); ?>
					</div>
				</form>
			</div>
			
			<!-- Report Preview -->
			<div id="report-preview" class="report-preview" style="display: none;">
				<div class="report-header">
					<h3><?php esc_html_e( 'Report Preview', 'wpshadow' ); ?></h3>
					<button type="button" class="button" id="close-preview">
						<?php esc_html_e( 'Close', 'wpshadow' ); ?>
					</button>
				</div>
				<div id="report-content"></div>
			</div>
			
			<!-- Email Modal -->
			<div id="email-modal" class="email-modal" style="display: none;">
				<div class="modal-backdrop"></div>
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<h3><?php esc_html_e( 'Email Report', 'wpshadow' ); ?></h3>
							<button type="button" class="close" id="close-email-modal">&times;</button>
						</div>
						<div class="modal-body">
							<div class="form-group">
								<label for="email_recipient">
									<?php esc_html_e( 'Recipient Email', 'wpshadow' ); ?>
								</label>
								<input type="email" id="email_recipient" placeholder="<?php esc_attr_e( 'Enter email address', 'wpshadow' ); ?>" />
							</div>
							<div class="form-group">
								<label for="email_frequency">
									<?php esc_html_e( 'Frequency', 'wpshadow' ); ?>
								</label>
								<select id="email_frequency">
									<option value="now"><?php esc_html_e( 'Send Now', 'wpshadow' ); ?></option>
									<option value="daily"><?php esc_html_e( 'Daily', 'wpshadow' ); ?></option>
									<option value="weekly"><?php esc_html_e( 'Weekly', 'wpshadow' ); ?></option>
									<option value="monthly"><?php esc_html_e( 'Monthly', 'wpshadow' ); ?></option>
								</select>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="button" id="cancel-email">
								<?php esc_html_e( 'Cancel', 'wpshadow' ); ?>
							</button>
							<button type="button" class="button button-primary" id="confirm-email">
								<?php esc_html_e( 'Send', 'wpshadow' ); ?>
							</button>
						</div>
					</div>
				</div>
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
		$html = '<div class="previous-reports">
			<h3>' . esc_html__( 'Previous Reports', 'wpshadow' ) . '</h3>
			<table class="wp-list-table widefat striped">
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
							<a href="#" class="button button-small">' . esc_html__( 'View', 'wpshadow' ) . '</a>
							<a href="#" class="button button-small">' . esc_html__( 'Download', 'wpshadow' ) . '</a>
						</td>
					</tr>
				</tbody>
			</table>
		</div>';
		
		return $html;
	}
}
