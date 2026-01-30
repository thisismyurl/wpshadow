<?php
/**
 * Advanced Settings Page
 *
 * Advanced configuration options for power users including
 * performance tuning, debug modes, and API settings.
 *
 * @package    WPShadow
 * @subpackage Settings
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Advanced Settings Page
 *
 * @since 1.2601.2148
 */
class Advanced_Settings_Page {

	/**
	 * Render the advanced settings page
	 *
	 * @since  1.2601.2148
	 * @return void
	 */
	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'wpshadow' ) );
		}

		?>
		<div class="wps-page-container">
			<div class="wps-page-header">
				<h1 class="wps-page-title">
					<span class="dashicons dashicons-admin-tools"></span>
					<?php esc_html_e( 'Advanced Settings', 'wpshadow' ); ?>
				</h1>
				<p class="wps-page-subtitle">
					<?php esc_html_e( 'Advanced configuration options for power users.', 'wpshadow' ); ?>
				</p>
			</div>

			<!-- Warning Notice -->
			<div class="wps-card wps-card--warning">
				<div class="wps-card-body">
					<p>
						<strong><?php esc_html_e( '⚠️ Advanced Settings', 'wpshadow' ); ?></strong><br/>
						<?php esc_html_e( 'These settings are for advanced users only. Incorrect settings may cause performance issues or unexpected behavior. Proceed with caution.', 'wpshadow' ); ?>
					</p>
				</div>
			</div>

			<form method="post" action="options.php" class="wps-settings-form">
				<?php settings_fields( 'wpshadow_settings' ); ?>

				<!-- Performance Tuning -->
				<div class="wps-card">
					<div class="wps-card-header">
						<h3 class="wps-card-title">
							<span class="dashicons dashicons-performance"></span>
							<?php esc_html_e( 'Performance Tuning', 'wpshadow' ); ?>
						</h3>
						<p class="wps-card-description">
							<?php esc_html_e( 'Fine-tune performance for your specific environment.', 'wpshadow' ); ?>
						</p>
					</div>
					<div class="wps-card-body">
						<div class="wps-form-group">
							<label for="wpshadow_scan_batch_size" class="wps-form-label">
								<?php esc_html_e( 'Scan Batch Size', 'wpshadow' ); ?>
							</label>
							<div class="wps-input-group">
								<input 
									type="number" 
									id="wpshadow_scan_batch_size" 
									name="wpshadow_scan_batch_size" 
									value="<?php echo esc_attr( get_option( 'wpshadow_scan_batch_size', 10 ) ); ?>"
									min="1"
									max="100"
									step="1"
									class="wps-input wps-w-32"
								/>
								<span class="wps-input-addon"><?php esc_html_e( 'diagnostics', 'wpshadow' ); ?></span>
							</div>
							<p class="wps-form-description">
								<?php esc_html_e( 'Run this many diagnostics per batch. Lower = less memory but slower. Higher = faster but more memory.', 'wpshadow' ); ?>
							</p>
						</div>

						<div class="wps-form-group wps-mt-4">
							<label for="wpshadow_timeout_seconds" class="wps-form-label">
								<?php esc_html_e( 'Scan Timeout', 'wpshadow' ); ?>
							</label>
							<div class="wps-input-group">
								<input 
									type="number" 
									id="wpshadow_timeout_seconds" 
									name="wpshadow_timeout_seconds" 
									value="<?php echo esc_attr( get_option( 'wpshadow_timeout_seconds', 60 ) ); ?>"
									min="30"
									max="300"
									step="5"
									class="wps-input wps-w-32"
								/>
								<span class="wps-input-addon"><?php esc_html_e( 'seconds', 'wpshadow' ); ?></span>
							</div>
							<p class="wps-form-description">
								<?php esc_html_e( 'Maximum time to wait for a scan. Increase on slow servers, decrease to avoid timeouts.', 'wpshadow' ); ?>
							</p>
						</div>

						<div class="wps-form-group wps-mt-4">
							<label class="wps-toggle" for="wpshadow_parallel_scans">
								<input 
									type="checkbox" 
									id="wpshadow_parallel_scans" 
									name="wpshadow_parallel_scans" 
									value="1"
									<?php checked( get_option( 'wpshadow_parallel_scans', false ) ); ?>
								/>
								<span class="wps-toggle-slider"></span>
								<?php esc_html_e( 'Enable Parallel Scanning', 'wpshadow' ); ?>
							</label>
							<p class="wps-form-description">
								<?php esc_html_e( 'Run multiple diagnostics simultaneously (requires good server resources). Faster but uses more CPU/memory.', 'wpshadow' ); ?>
							</p>
						</div>
					</div>
				</div>

				<!-- Debug & Logging -->
				<div class="wps-card">
					<div class="wps-card-header">
						<h3 class="wps-card-title">
							<span class="dashicons dashicons-bug"></span>
							<?php esc_html_e( 'Debug & Logging', 'wpshadow' ); ?>
						</h3>
						<p class="wps-card-description">
							<?php esc_html_e( 'Enable logging for troubleshooting (useful when reporting issues).', 'wpshadow' ); ?>
						</p>
					</div>
					<div class="wps-card-body">
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
					</div>
				</div>

				<!-- Advanced Features -->
				<div class="wps-card">
					<div class="wps-card-header">
						<h3 class="wps-card-title">
							<span class="dashicons dashicons-lightbulb"></span>
							<?php esc_html_e( 'Advanced Features', 'wpshadow' ); ?>
						</h3>
						<p class="wps-card-description">
							<?php esc_html_e( 'Enable experimental features and advanced integrations.', 'wpshadow' ); ?>
						</p>
					</div>
					<div class="wps-card-body">
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
					</div>
				</div>

				<!-- Performance Monitoring -->
				<div class="wps-card">
					<div class="wps-card-header">
						<h3 class="wps-card-title">
							<span class="dashicons dashicons-chart-line"></span>
							<?php esc_html_e( 'Performance Monitoring', 'wpshadow' ); ?>
						</h3>
						<p class="wps-card-description">
							<?php esc_html_e( 'Track WPShadow impact on site performance.', 'wpshadow' ); ?>
						</p>
					</div>
					<div class="wps-card-body">
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
							</label>
							<select 
								id="wpshadow_performance_sample_rate" 
								name="wpshadow_performance_sample_rate"
								class="wps-input"
							>
								<option value="1" <?php selected( get_option( 'wpshadow_performance_sample_rate', 0.1 ), '1' ); ?>>
									<?php esc_html_e( 'All requests (100%)', 'wpshadow' ); ?>
								</option>
								<option value="0.5" <?php selected( get_option( 'wpshadow_performance_sample_rate', 0.1 ), '0.5' ); ?>>
									<?php esc_html_e( 'Half of requests (50%)', 'wpshadow' ); ?>
								</option>
								<option value="0.1" <?php selected( get_option( 'wpshadow_performance_sample_rate', 0.1 ), '0.1' ); ?>>
									<?php esc_html_e( 'One in ten requests (10%)', 'wpshadow' ); ?>
								</option>
								<option value="0.01" <?php selected( get_option( 'wpshadow_performance_sample_rate', 0.1 ), '0.01' ); ?>>
									<?php esc_html_e( 'One in hundred requests (1%)', 'wpshadow' ); ?>
								</option>
							</select>
							<p class="wps-form-description">
								<?php esc_html_e( 'Higher percentages = more accurate data but more overhead. Lower = less overhead but less data.', 'wpshadow' ); ?>
							</p>
						</div>
					</div>
				</div>

				<!-- System Information -->
				<div class="wps-card">
					<div class="wps-card-header">
						<h3 class="wps-card-title">
							<span class="dashicons dashicons-info"></span>
							<?php esc_html_e( 'System Information', 'wpshadow' ); ?>
						</h3>
						<p class="wps-card-description">
							<?php esc_html_e( 'View information about your WPShadow installation.', 'wpshadow' ); ?>
						</p>
					</div>
					<div class="wps-card-body">
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
									<?php
									$diagnostic_count = \WPShadow\Diagnostics\Diagnostic_Registry::count();
									echo esc_html( number_format_i18n( $diagnostic_count ) );
									?>
									<span class="description" style="display: block; font-size: 11px; margin-top: 2px;">
										<?php esc_html_e( 'Total diagnostic tests available', 'wpshadow' ); ?>
									</span>
								</td>
							</tr>
							<tr style="border-bottom: 1px solid #ddd;">
								<td class="wps-th-p-2-bold"><?php esc_html_e( 'Registered Treatments', 'wpshadow' ); ?></td>
								<td class="wps-td-p-2">
									<?php
									$treatment_count = class_exists( '\WPShadow\Treatments\Treatment_Registry' ) 
										? \WPShadow\Treatments\Treatment_Registry::count() 
										: 0;
									echo esc_html( number_format_i18n( $treatment_count ) );
									?>
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
					</div>
				</div>

				<!-- Action Buttons -->
				<div class="wps-card wps-card--action">
					<div class="wps-card-body wps-flex wps-gap-3">
						<?php submit_button( __( 'Save Changes', 'wpshadow' ), 'primary', 'submit', false ); ?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-settings' ) ); ?>" class="wps-btn wps-btn--secondary">
							<?php esc_html_e( 'Cancel', 'wpshadow' ); ?>
						</a>
					</div>
				</div>
			</form>
		</div>
		<?php
	}
}
