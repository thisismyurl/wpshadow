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
		?>
		<div class="wpshadow-reports-container" style="padding: 20px;">
			<div style="display: grid; grid-template-columns: 300px 1fr; gap: 20px;">
				
				<!-- Left Sidebar: Report Builder Form -->
				<div class="report-builder-form" style="background: #f9f9f9; padding: 20px; border-radius: 8px;">
					<h3><?php esc_html_e( 'Build Report', 'wpshadow' ); ?></h3>
					
					<form id="wpshadow-report-builder" style="display: flex; flex-direction: column; gap: 15px;">
						<?php wp_nonce_field( 'wpshadow_report_builder', 'report_nonce' ); ?>
						
						<!-- Quick Presets -->
						<div class="form-group">
							<label style="display: block; font-weight: bold; margin-bottom: 8px;">
								<?php esc_html_e( 'Quick Presets', 'wpshadow' ); ?>
							</label>
							<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
								<button type="button" class="preset-btn" data-preset="today" style="padding: 8px; background: #0073aa; color: white; border: none; border-radius: 4px; cursor: pointer;">
									<?php esc_html_e( 'Today', 'wpshadow' ); ?>
								</button>
								<button type="button" class="preset-btn" data-preset="week" style="padding: 8px; background: #0073aa; color: white; border: none; border-radius: 4px; cursor: pointer;">
									<?php esc_html_e( 'Last 7 Days', 'wpshadow' ); ?>
								</button>
								<button type="button" class="preset-btn" data-preset="month" style="padding: 8px; background: #0073aa; color: white; border: none; border-radius: 4px; cursor: pointer;">
									<?php esc_html_e( 'Last 30 Days', 'wpshadow' ); ?>
								</button>
								<button type="button" class="preset-btn" data-preset="quarter" style="padding: 8px; background: #0073aa; color: white; border: none; border-radius: 4px; cursor: pointer;">
									<?php esc_html_e( 'Last 90 Days', 'wpshadow' ); ?>
								</button>
							</div>
						</div>
						
						<!-- Date Range -->
						<div class="form-group">
							<label for="report_start_date" style="display: block; font-weight: bold; margin-bottom: 4px;">
								<?php esc_html_e( 'Start Date', 'wpshadow' ); ?>
							</label>
							<input type="date" id="report_start_date" name="start_date" 
								value="<?php echo esc_attr( date( 'Y-m-d', strtotime( '-30 days' ) ) ); ?>"
								style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" />
						</div>
						
						<div class="form-group">
							<label for="report_end_date" style="display: block; font-weight: bold; margin-bottom: 4px;">
								<?php esc_html_e( 'End Date', 'wpshadow' ); ?>
							</label>
							<input type="date" id="report_end_date" name="end_date" 
								value="<?php echo esc_attr( date( 'Y-m-d' ) ); ?>"
								style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" />
						</div>
						
						<!-- Category Filter -->
						<div class="form-group">
							<label for="report_category" style="display: block; font-weight: bold; margin-bottom: 4px;">
								<?php esc_html_e( 'Category', 'wpshadow' ); ?>
							</label>
							<select id="report_category" name="category" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
								<option value=""><?php esc_html_e( 'All Categories', 'wpshadow' ); ?></option>
								<option value="security"><?php esc_html_e( 'Security', 'wpshadow' ); ?></option>
								<option value="performance"><?php esc_html_e( 'Performance', 'wpshadow' ); ?></option>
								<option value="maintenance"><?php esc_html_e( 'Maintenance', 'wpshadow' ); ?></option>
								<option value="workflow"><?php esc_html_e( 'Workflow', 'wpshadow' ); ?></option>
								<option value="backup"><?php esc_html_e( 'Backup', 'wpshadow' ); ?></option>
							</select>
						</div>
						
						<!-- Report Type -->
						<div class="form-group">
							<label for="report_type" style="display: block; font-weight: bold; margin-bottom: 4px;">
								<?php esc_html_e( 'Report Type', 'wpshadow' ); ?>
							</label>
							<select id="report_type" name="type" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
								<option value="summary"><?php esc_html_e( 'Summary', 'wpshadow' ); ?></option>
								<option value="detailed"><?php esc_html_e( 'Detailed', 'wpshadow' ); ?></option>
								<option value="executive"><?php esc_html_e( 'Executive', 'wpshadow' ); ?></option>
							</select>
							<p style="font-size: 12px; color: #666; margin-top: 4px;">
								<?php esc_html_e( 'Summary: Overview. Detailed: All events. Executive: Board-level KPIs.', 'wpshadow' ); ?>
							</p>
						</div>
						
						<!-- Export Format -->
						<div class="form-group">
							<label for="report_format" style="display: block; font-weight: bold; margin-bottom: 4px;">
								<?php esc_html_e( 'Export Format', 'wpshadow' ); ?>
							</label>
							<select id="report_format" name="format" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
								<option value="html"><?php esc_html_e( 'HTML (View)', 'wpshadow' ); ?></option>
								<option value="csv"><?php esc_html_e( 'CSV (Excel)', 'wpshadow' ); ?></option>
								<option value="json"><?php esc_html_e( 'JSON (API)', 'wpshadow' ); ?></option>
							</select>
						</div>
						
						<!-- Action Buttons -->
						<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 20px;">
							<button type="submit" class="button button-primary" style="width: 100%; cursor: pointer;">
								<?php esc_html_e( 'Generate', 'wpshadow' ); ?>
							</button>
							<button type="button" class="button" id="compare-periods-btn" style="width: 100%; cursor: pointer;">
								<?php esc_html_e( 'Compare', 'wpshadow' ); ?>
							</button>
						</div>
						
						<!-- Email Option -->
						<div class="form-group" style="border-top: 1px solid #ddd; padding-top: 15px; margin-top: 15px;">
							<label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
								<input type="checkbox" name="email_report" id="email_report" />
								<?php esc_html_e( 'Email this report', 'wpshadow' ); ?>
							</label>
							<div id="email-options" style="display: none; margin-top: 10px;">
								<input type="email" name="email_to" placeholder="<?php esc_attr_e( 'Email address', 'wpshadow' ); ?>" 
									style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 8px;" />
								<label style="display: flex; align-items: center; gap: 8px;">
									<input type="checkbox" name="schedule_email" />
									<?php esc_html_e( 'Schedule monthly', 'wpshadow' ); ?>
								</label>
							</div>
						</div>
						
					</form>
				</div>
				
				<!-- Right Content: Report Display -->
				<div class="report-display-area">
					<div id="report-content" style="background: white; padding: 20px; border-radius: 8px; min-height: 500px;">
						<p style="color: #666; text-align: center; padding: 40px 20px;">
							<?php esc_html_e( 'Build a report using the form on the left to see results here.', 'wpshadow' ); ?>
						</p>
					</div>
				</div>
				
			</div>
		</div>
		
		<?php self::render_scripts(); ?>
		<?php
	}
	
	/**
	 * Render JavaScript for form interactions
	 * 
	 * @return void
	 */
	private static function render_scripts(): void {
		?>
		<script>
		jQuery(document).ready(function($) {
			// Quick preset handling
			$('.preset-btn').on('click', function(e) {
				e.preventDefault();
				const preset = $(this).data('preset');
				const today = new Date();
				let startDate, endDate;
				
				endDate = new Date(today);
				endDate.setHours(23, 59, 59, 999);
				
				switch(preset) {
					case 'today':
						startDate = new Date(today);
						startDate.setHours(0, 0, 0, 0);
						break;
					case 'week':
						startDate = new Date(today);
						startDate.setDate(startDate.getDate() - 7);
						break;
					case 'month':
						startDate = new Date(today);
						startDate.setDate(startDate.getDate() - 30);
						break;
					case 'quarter':
						startDate = new Date(today);
						startDate.setDate(startDate.getDate() - 90);
						break;
				}
				
				$('#report_start_date').val(startDate.toISOString().split('T')[0]);
				$('#report_end_date').val(endDate.toISOString().split('T')[0]);
			});
			
			// Email checkbox toggle
			$('#email_report').on('change', function() {
				$('#email-options').toggle(this.checked);
			});
			
			// Form submission
			$('#wpshadow-report-builder').on('submit', function(e) {
				e.preventDefault();
				
				const formData = {
					date_from: $('#report_start_date').val(),
					date_to: $('#report_end_date').val(),
					category: $('#report_category').val(),
					type: $('#report_type').val(),
					format: $('#report_format').val(),
					action: 'wpshadow_generate_report',
					nonce: $('input[name="report_nonce"]').val()
				};
				
				$('#report-content').html('<p style="text-align: center; padding: 40px;">Loading...</p>');
				
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: formData,
					success: function(response) {
						if (response.success) {
							$('#report-content').html(response.data.html);
						} else {
							$('#report-content').html('<p style="color: red;">' + response.data.message + '</p>');
						}
					},
					error: function() {
						$('#report-content').html('<p style="color: red;">Error generating report</p>');
					}
				});
			});
		});
		</script>
		<?php
	}
}
