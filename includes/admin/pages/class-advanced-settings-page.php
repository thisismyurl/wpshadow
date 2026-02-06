<?php
/**
 * Advanced Settings Page
 *
 * Advanced configuration options for power users including
 * performance tuning, debug modes, and API settings.
 *
 * @package    WPShadow
 * @subpackage Settings
 * @since      1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Admin\Pages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Advanced Settings Page
 *
 * @since 1.6030.2148
 */
class Advanced_Settings_Page {

	/**
	 * Render the advanced settings page
	 *
	 * @since  1.6030.2148
	 * @return void
	 */
	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'wpshadow' ) );
		}

		?>
		<div class="wps-page-container">
			<?php
			wpshadow_render_page_header(
				__( 'Advanced Settings', 'wpshadow' ),
				__( 'Advanced configuration options for power users.', 'wpshadow' ),
				'dashicons-admin-tools'
			);
			?>

			<!-- Warning Notice -->
			<?php
			wpshadow_render_card(
				array(
					'card_class' => 'wps-card--warning',
					'body'       => '<p><strong>' . esc_html__( '⚠️ Advanced Settings', 'wpshadow' ) . '</strong><br/>' . esc_html__( 'These settings are for advanced users only. Incorrect settings may cause performance issues or unexpected behavior. Proceed with caution.', 'wpshadow' ) . '</p>',
				)
			);
			?>

			<form method="post" action="options.php" class="wps-settings-form">
				<?php settings_fields( 'wpshadow_settings' ); ?>

				<?php
				// Note: Scan performance settings have been moved to Scan Settings page
				// (admin.php?page=wpshadow-scan-settings) to co-locate them with diagnostic management.
				?>

				<!-- Debug & Logging -->
				<?php
				wpshadow_render_card(
					array(
						'title'       => __( 'Debug & Logging', 'wpshadow' ),
						'description' => __( 'Enable logging for troubleshooting (useful when reporting issues).', 'wpshadow' ),
						'icon'        => 'dashicons-bug',
						'body'        => function() {
							?>
							<div class="wps-form-group">
								<label class="wps-toggle" for="wpshadow_debug_mode">
									<input 
										type="checkbox" 
										id="wpshadow_debug_mode" 
										name="wpshadow_debug_mode" 
										value="1"
										<?php checked( get_option( 'wpshadow_debug_mode', false ) ); ?>
									/>
									<span class="wps-toggle-slider"></span>
									<?php esc_html_e( 'Enable Debug Mode', 'wpshadow' ); ?>
								</label>
								<p class="wps-form-description">
									<?php esc_html_e( 'Log detailed diagnostic and treatment execution traces (affects performance).', 'wpshadow' ); ?>
								</p>
							</div>

							<div class="wps-form-group wps-mt-4">
								<label class="wps-toggle" for="wpshadow_log_api_calls">
									<input 
										type="checkbox" 
										id="wpshadow_log_api_calls" 
										name="wpshadow_log_api_calls" 
										value="1"
										<?php checked( get_option( 'wpshadow_log_api_calls', false ) ); ?>
									/>
									<span class="wps-toggle-slider"></span>
									<?php esc_html_e( 'Log API Calls', 'wpshadow' ); ?>
								</label>
								<p class="wps-form-description">
									<?php esc_html_e( 'Log all WPShadow API requests and responses (for debugging integrations).', 'wpshadow' ); ?>
								</p>
							</div>

							<div class="wps-form-group wps-mt-4">
								<label for="wpshadow_log_level" class="wps-form-label">
									<?php esc_html_e( 'Log Level', 'wpshadow' ); ?>
								</label>
								<select 
									id="wpshadow_log_level" 
									name="wpshadow_log_level"
									class="wps-input"
								>
									<option value="info" <?php selected( get_option( 'wpshadow_log_level', 'info' ), 'info' ); ?>>
										<?php esc_html_e( 'Info (basic messages)', 'wpshadow' ); ?>
									</option>
									<option value="debug" <?php selected( get_option( 'wpshadow_log_level', 'info' ), 'debug' ); ?>>
										<?php esc_html_e( 'Debug (detailed info)', 'wpshadow' ); ?>
									</option>
									<option value="trace" <?php selected( get_option( 'wpshadow_log_level', 'info' ), 'trace' ); ?>>
										<?php esc_html_e( 'Trace (everything)', 'wpshadow' ); ?>
									</option>
								</select>
							</div>
							<?php
						},
					)
				);
				?>

				<!-- Advanced Features -->
				<?php
				wpshadow_render_card(
					array(
						'title'       => __( 'Advanced Features', 'wpshadow' ),
						'description' => __( 'Enable experimental features and advanced integrations.', 'wpshadow' ),
						'icon'        => 'dashicons-lightbulb',
						'body'        => function() {
							?>
							<div class="wps-form-group">
								<label class="wps-toggle" for="wpshadow_rest_api_enabled">
									<input 
										type="checkbox" 
										id="wpshadow_rest_api_enabled" 
										name="wpshadow_rest_api_enabled" 
										value="1"
										<?php checked( get_option( 'wpshadow_rest_api_enabled', true ) ); ?>
									/>
									<span class="wps-toggle-slider"></span>
									<?php esc_html_e( 'Enable REST API', 'wpshadow' ); ?>
								</label>
								<p class="wps-form-description">
									<?php esc_html_e( 'Allow third-party tools to control WPShadow via WordPress REST API (requires authentication).', 'wpshadow' ); ?>
								</p>
							</div>

							<div class="wps-form-group wps-mt-4">
								<label class="wps-toggle" for="wpshadow_wp_cli_enabled">
									<input 
										type="checkbox" 
										id="wpshadow_wp_cli_enabled" 
										name="wpshadow_wp_cli_enabled" 
										value="1"
										<?php checked( get_option( 'wpshadow_wp_cli_enabled', true ) ); ?>
									/>
									<span class="wps-toggle-slider"></span>
									<?php esc_html_e( 'Enable WP-CLI Commands', 'wpshadow' ); ?>
								</label>
								<p class="wps-form-description">
									<?php esc_html_e( 'Allow WP-CLI to run WPShadow commands from the command line.', 'wpshadow' ); ?>
								</p>
							</div>

							<div class="wps-form-group wps-mt-4">
								<label class="wps-toggle" for="wpshadow_magic_link_expiry_notifications">
									<input 
										type="checkbox" 
										id="wpshadow_magic_link_expiry_notifications" 
										name="wpshadow_magic_link_expiry_notifications" 
										value="1"
										<?php checked( get_option( 'wpshadow_magic_link_expiry_notifications', false ) ); ?>
									/>
									<span class="wps-toggle-slider"></span>
									<?php esc_html_e( 'Magic Link Expiry Notifications', 'wpshadow' ); ?>
								</label>
								<p class="wps-form-description">
									<?php esc_html_e( 'Send email notifications when temporary access links expire, with option to create permanent user accounts.', 'wpshadow' ); ?>
								</p>
							</div>

							<div class="wps-form-group wps-mt-4">
								<label class="wps-toggle" for="wpshadow_webhooks_enabled">
									<input 
										type="checkbox" 
										id="wpshadow_webhooks_enabled" 
										name="wpshadow_webhooks_enabled" 
										value="1"
										<?php checked( get_option( 'wpshadow_webhooks_enabled', false ) ); ?>
									/>
									<span class="wps-toggle-slider"></span>
									<?php esc_html_e( 'Enable Webhooks (Experimental)', 'wpshadow' ); ?>
								</label>
								<p class="wps-form-description">
									<?php esc_html_e( 'Allow WPShadow to send webhooks to external services when events occur.', 'wpshadow' ); ?>
								</p>
							</div>
							<?php
						},
					)
				);
				?>

				<!-- Performance Monitoring -->
				<?php
				wpshadow_render_card(
					array(
						'title'       => __( 'Performance Monitoring', 'wpshadow' ),
						'description' => __( 'Track WPShadow impact on site performance.', 'wpshadow' ),
						'icon'        => 'dashicons-chart-line',
						'body'        => function() {
							?>
							<div class="wps-form-group">
								<label class="wps-toggle" for="wpshadow_track_performance">
									<input 
										type="checkbox" 
										id="wpshadow_track_performance" 
										name="wpshadow_track_performance" 
										value="1"
										<?php checked( get_option( 'wpshadow_track_performance', true ) ); ?>
									/>
									<span class="wps-toggle-slider"></span>
									<?php esc_html_e( 'Track Performance Impact', 'wpshadow' ); ?>
								</label>
								<p class="wps-form-description">
									<?php esc_html_e( 'Measure how much CPU time and memory WPShadow uses. Viewable in Reports.', 'wpshadow' ); ?>
								</p>
							</div>

							<div class="wps-form-group wps-mt-4">
								<label for="wpshadow_performance_sample_rate" class="wps-form-label">
									<?php esc_html_e( 'Performance Sample Rate', 'wpshadow' ); ?>
									<span id="wpshadow_sample_rate_display" style="font-weight: bold; margin-left: 8px;">
										<?php 
										$current_rate = floatval( get_option( 'wpshadow_performance_sample_rate', 0.1 ) );
										echo esc_html( round( $current_rate * 100 ) . '%' );
										?>
									</span>
								</label>
								<input 
									type="range" 
									id="wpshadow_performance_sample_rate_slider" 
									name="wpshadow_performance_sample_rate_temp"
									min="1" 
									max="100" 
									step="1"
									value="<?php echo esc_attr( round( floatval( get_option( 'wpshadow_performance_sample_rate', 0.1 ) ) * 100 ) ); ?>"
									style="width: 100%; max-width: 400px;"
								/>
								<input 
									type="hidden" 
									id="wpshadow_performance_sample_rate" 
									name="wpshadow_performance_sample_rate"
									value="<?php echo esc_attr( get_option( 'wpshadow_performance_sample_rate', 0.1 ) ); ?>"
								/>
								<p class="wps-form-description">
									<?php esc_html_e( 'Higher percentages = more accurate data but more overhead. Lower = less overhead but less data.', 'wpshadow' ); ?>
								</p>
								<script>
								(function() {
									const slider = document.getElementById('wpshadow_performance_sample_rate_slider');
									const display = document.getElementById('wpshadow_sample_rate_display');
									const hiddenInput = document.getElementById('wpshadow_performance_sample_rate');
									
									if (slider && display && hiddenInput) {
										slider.addEventListener('input', function() {
											const percentage = parseInt(this.value);
											display.textContent = percentage + '%';
											hiddenInput.value = (percentage / 100).toFixed(2);
										});
									}
								})();
								</script>
							</div>
							<?php
						},
					)
				);
				?>

				<!-- System Information -->
				<?php
				wpshadow_render_card(
					array(
						'title'       => __( 'System Information', 'wpshadow' ),
						'description' => __( 'View information about your WPShadow installation.', 'wpshadow' ),
						'icon'        => 'info',
						'body'        => function() {
							$diagnostic_count = \WPShadow\Diagnostics\Diagnostic_Registry::count();
							$treatment_count  = class_exists( '\WPShadow\Treatments\Treatment_Registry' ) 
								? \WPShadow\Treatments\Treatment_Registry::count() 
								: 0;
							?>
							<table class="wps-table wps-table-collapse">
								<tr style="border-bottom: 1px solid #ddd;">
									<td class="wps-th-p-2-bold"><?php esc_html_e( 'WPShadow Version', 'wpshadow' ); ?></td>
									<td class="wps-td-p-2"><?php echo esc_html( WPSHADOW_VERSION ); ?></td>
								</tr>
								<tr style="border-bottom: 1px solid #ddd;">
									<td class="wps-th-p-2-bold"><?php esc_html_e( 'WordPress Version', 'wpshadow' ); ?></td>
									<td class="wps-td-p-2"><?php echo esc_html( get_bloginfo( 'version' ) ); ?></td>
								</tr>
								<tr style="border-bottom: 1px solid #ddd;">
									<td class="wps-th-p-2-bold"><?php esc_html_e( 'PHP Version', 'wpshadow' ); ?></td>
									<td class="wps-td-p-2"><?php echo esc_html( phpversion() ); ?></td>
								</tr>
								<tr style="border-bottom: 1px solid #ddd;">
									<td class="wps-th-p-2-bold"><?php esc_html_e( 'Registered Diagnostics', 'wpshadow' ); ?></td>
									<td class="wps-td-p-2">
										<?php echo esc_html( number_format_i18n( $diagnostic_count ) ); ?>
										<span class="description" style="display: block; font-size: 11px; margin-top: 2px;">
											<?php esc_html_e( 'Total diagnostic tests available', 'wpshadow' ); ?>
										</span>
									</td>
								</tr>
								<tr style="border-bottom: 1px solid #ddd;">
									<td class="wps-th-p-2-bold"><?php esc_html_e( 'Registered Treatments', 'wpshadow' ); ?></td>
									<td class="wps-td-p-2">
										<?php echo esc_html( number_format_i18n( $treatment_count ) ); ?>
										<span class="description" style="display: block; font-size: 11px; margin-top: 2px;">
											<?php esc_html_e( 'Total auto-fix treatments available', 'wpshadow' ); ?>
										</span>
									</td>
								</tr>
								<tr style="border-bottom: 1px solid #ddd;">
									<td class="wps-th-p-2-bold"><?php esc_html_e( 'Installation Path', 'wpshadow' ); ?></td>
									<td class="wps-td-p-2 wps-font-mono-xs"><?php echo esc_html( WPSHADOW_PATH ); ?></td>
								</tr>
								<tr>
									<td class="wps-th-p-2-bold"><?php esc_html_e( 'Database Prefix', 'wpshadow' ); ?></td>
									<td class="wps-td-p-2 wps-font-mono-xs"><?php echo esc_html( $GLOBALS['table_prefix'] ); ?></td>
								</tr>
							</table>
							<?php
						},
					)
				);
				?>

				<!-- Action Buttons -->
				<?php
				wpshadow_render_card(
					array(
						'card_class' => 'wps-card--action',
						'body_class' => 'wps-card-body wps-flex wps-gap-3',
						'body'       => function() {
							?>
							<?php submit_button( __( 'Save Changes', 'wpshadow' ), 'primary', 'submit', false ); ?>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-settings' ) ); ?>" class="wps-btn wps-btn--secondary">
								<?php esc_html_e( 'Cancel', 'wpshadow' ); ?>
							</a>
							<?php
						},
					)
				);
				?>
			</form>
		</div>
		<?php
	}
}
