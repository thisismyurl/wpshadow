<?php
/**
 * Advanced Settings Page
 *
 * Advanced configuration options for power users including
 * performance tuning, debug modes, and API settings.
 *
 * @package    WPShadow
 * @subpackage Settings
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Admin\Pages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Advanced Settings Page
 *
 * @since 1.6093.1200
 */
class Advanced_Settings_Page {

	/**
	 * Render the advanced settings page
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'wpshadow' ) );
		}

		$version = defined( 'WPSHADOW_VERSION' ) ? WPSHADOW_VERSION : '1.0.0';

		wp_enqueue_style(
			'wpshadow-advanced-settings-page',
			WPSHADOW_URL . 'assets/css/advanced-settings-page.css',
			array(),
			$version
		);

		wp_enqueue_script(
			'wpshadow-advanced-settings-page',
			WPSHADOW_URL . 'assets/js/advanced-settings-page.js',
			array(),
			$version,
			true
		);

		wp_localize_script(
			'wpshadow-advanced-settings-page',
			'wpsAdvancedSettingsPage',
			array(
				'copiedText'     => __( 'Copied.', 'wpshadow' ),
				'copyFailedText' => __( 'Copy failed.', 'wpshadow' ),
			)
		);

		?>
		<div class="wrap wps-page-container">
			<?php
			wpshadow_render_page_header(
				__( 'Advanced Settings', 'wpshadow' ),
				__( 'Advanced configuration options for power users.', 'wpshadow' ),
				'dashicons-admin-tools'
			);
			?>

			<!-- Warning Notice -->
			<div class="wps-card wpshadow-advanced-warning-card">
				<div class="wps-card-body">
					<div class="wpshadow-advanced-warning-content">
						<div class="wpshadow-advanced-warning-icon">
							<span class="dashicons dashicons-warning"></span>
						</div>
						<div class="wpshadow-advanced-warning-text">
							<h3><?php esc_html_e( 'Advanced Settings', 'wpshadow' ); ?></h3>
							<p><?php esc_html_e( 'These settings are for advanced users only. Incorrect settings may cause performance issues or unexpected behavior. Proceed with caution.', 'wpshadow' ); ?></p>
						</div>
					</div>
				</div>
			</div>

			<form method="post" action="options.php" class="wps-settings-form wps-settings-form--advanced">
				<?php settings_fields( 'wpshadow_settings' ); ?>

				<?php
				// Note: Scan performance settings have been moved to Scan Settings page
				// (admin.php?page=wpshadow-scan-settings) to co-locate them with diagnostic management.
				?>

				<div class="wps-grid wps-grid-auto-320 wps-mb-4">
					<!-- Debug & Logging -->
					<?php
					wpshadow_render_card(
						array(
							'title'       => __( 'Debug & Logging', 'wpshadow' ),
							'description' => __( 'Enable logging for troubleshooting (useful when reporting issues).', 'wpshadow' ),
							'icon'        => 'dashicons-admin-tools',
							'icon_class'  => 'wps-text-primary',
							'card_class'  => 'wps-grid-span-half',
							'body'        => function() {
							?>
							<?php
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Form_Controls outputs escaped HTML.
							echo \WPShadow\Helpers\Form_Controls::toggle_switch(
								array(
									'id'          => 'wpshadow_debug_mode',
									'name'        => 'wpshadow_debug_mode',
									'label'       => __( 'Enable Debug Mode', 'wpshadow' ),
									'helper_text' => __( 'Log detailed diagnostic and treatment execution traces (affects performance).', 'wpshadow' ),
									'checked'     => (bool) get_option( 'wpshadow_debug_mode', false ),
								)
							);

							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Form_Controls outputs escaped HTML.
							echo \WPShadow\Helpers\Form_Controls::toggle_switch(
								array(
									'id'          => 'wpshadow_log_api_calls',
									'name'        => 'wpshadow_log_api_calls',
									'label'       => __( 'Log API Calls', 'wpshadow' ),
									'helper_text' => __( 'Log all WPShadow API requests and responses (for debugging integrations).', 'wpshadow' ),
									'checked'     => (bool) get_option( 'wpshadow_log_api_calls', false ),
								)
							);
							?>

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
								'card_class'  => 'wps-grid-span-half',
								'body'        => function() {
							?>
								<?php
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Form_Controls outputs escaped HTML.
								echo \WPShadow\Helpers\Form_Controls::toggle_switch(
									array(
										'id'          => 'wpshadow_rest_api_enabled',
										'name'        => 'wpshadow_rest_api_enabled',
										'label'       => __( 'Enable REST API', 'wpshadow' ),
										'helper_text' => __( 'Allow third-party tools to control WPShadow via WordPress REST API (requires authentication).', 'wpshadow' ),
										'checked'     => (bool) get_option( 'wpshadow_rest_api_enabled', true ),
									)
								);

								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Form_Controls outputs escaped HTML.
								echo \WPShadow\Helpers\Form_Controls::toggle_switch(
									array(
										'id'          => 'wpshadow_wp_cli_enabled',
										'name'        => 'wpshadow_wp_cli_enabled',
										'label'       => __( 'Enable WP-CLI Commands', 'wpshadow' ),
										'helper_text' => __( 'Allow WP-CLI to run WPShadow commands from the command line.', 'wpshadow' ),
										'checked'     => (bool) get_option( 'wpshadow_wp_cli_enabled', true ),
									)
								);

								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Form_Controls outputs escaped HTML.
								echo \WPShadow\Helpers\Form_Controls::toggle_switch(
									array(
										'id'          => 'wpshadow_webhooks_enabled',
										'name'        => 'wpshadow_webhooks_enabled',
										'label'       => __( 'Enable Webhooks (Experimental)', 'wpshadow' ),
										'helper_text' => __( 'Allow WPShadow to send webhooks to external services when events occur.', 'wpshadow' ),
										'checked'     => (bool) get_option( 'wpshadow_webhooks_enabled', false ),
									)
								);
								?>
							<?php
							},
						)
					);
					?>
					</div>

				<!-- Performance Monitoring -->
				<?php
				wpshadow_render_card(
					array(
						'title'       => __( 'Performance Monitoring', 'wpshadow' ),
						'description' => __( 'Track WPShadow impact on site performance.', 'wpshadow' ),
						'icon'        => 'dashicons-chart-line',
						'body'        => function() {
							?>
							<?php
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Form_Controls outputs escaped HTML.
							echo \WPShadow\Helpers\Form_Controls::toggle_switch(
								array(
									'id'          => 'wpshadow_track_performance',
									'name'        => 'wpshadow_track_performance',
									'label'       => __( 'Track Performance Impact', 'wpshadow' ),
									'helper_text' => __( 'Measure how much CPU time and memory WPShadow uses. Viewable in Reports.', 'wpshadow' ),
									'checked'     => (bool) get_option( 'wpshadow_track_performance', true ),
								)
							);
							?>

							<div class="wps-form-group wps-mt-4">
								<label for="wpshadow_performance_sample_rate" class="wps-form-label">
									<?php esc_html_e( 'Performance Sample Rate', 'wpshadow' ); ?>
									<span id="wpshadow_sample_rate_display" class="wps-advanced-sample-rate-display">
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
									class="wps-advanced-sample-rate-slider"
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
						'icon'        => 'dashicons-info',
						'body'        => function() {
							$diagnostic_count = \WPShadow\Diagnostics\Diagnostic_Registry::count();
							$treatment_count  = 0;

							if ( class_exists( '\WPShadow\Treatments\Treatment_Registry' ) && method_exists( '\WPShadow\Treatments\Treatment_Registry', 'get_all' ) ) {
								$treatment_count = count( \WPShadow\Treatments\Treatment_Registry::get_all() );
							}
							?>
							<table class="wps-table wps-table-collapse">
								<tr class="wps-advanced-system-row">
									<td class="wps-th-p-2-bold"><?php esc_html_e( 'WPShadow Version', 'wpshadow' ); ?></td>
									<td class="wps-td-p-2"><?php echo esc_html( WPSHADOW_VERSION ); ?></td>
								</tr>
								<tr class="wps-advanced-system-row">
									<td class="wps-th-p-2-bold"><?php esc_html_e( 'WordPress Version', 'wpshadow' ); ?></td>
									<td class="wps-td-p-2"><?php echo esc_html( get_bloginfo( 'version' ) ); ?></td>
								</tr>
								<tr class="wps-advanced-system-row">
									<td class="wps-th-p-2-bold"><?php esc_html_e( 'PHP Version', 'wpshadow' ); ?></td>
									<td class="wps-td-p-2"><?php echo esc_html( phpversion() ); ?></td>
								</tr>
								<tr class="wps-advanced-system-row">
									<td class="wps-th-p-2-bold"><?php esc_html_e( 'Registered Diagnostics', 'wpshadow' ); ?></td>
									<td class="wps-td-p-2">
										<?php echo esc_html( number_format_i18n( $diagnostic_count ) ); ?>
										<span class="description wps-advanced-system-description">
											<?php esc_html_e( 'Total diagnostic tests available', 'wpshadow' ); ?>
										</span>
									</td>
								</tr>
								<tr class="wps-advanced-system-row">
									<td class="wps-th-p-2-bold"><?php esc_html_e( 'Registered Treatments', 'wpshadow' ); ?></td>
									<td class="wps-td-p-2">
										<?php echo esc_html( number_format_i18n( $treatment_count ) ); ?>
										<span class="description wps-advanced-system-description">
											<?php esc_html_e( 'Total auto-fix treatments available', 'wpshadow' ); ?>
										</span>
									</td>
								</tr>
								<tr class="wps-advanced-system-row">
									<td class="wps-th-p-2-bold"><?php esc_html_e( 'Installation Path', 'wpshadow' ); ?></td>
									<td class="wps-td-p-2 wps-font-mono-xs"><?php echo esc_html( WPSHADOW_PATH ); ?></td>
								</tr>
								<tr>
									<td class="wps-th-p-2-bold"><?php esc_html_e( 'Database Prefix', 'wpshadow' ); ?></td>
									<td class="wps-td-p-2 wps-font-mono-xs"><?php echo esc_html( $GLOBALS['table_prefix'] ); ?></td>
								</tr>
							</table>
							<div class="wps-mt-3 wps-flex wps-items-center wps-gap-2 wps-justify-end">
								<button
									type="button"
									class="wps-btn wps-btn--secondary"
									id="wpshadow-copy-system-info"
									data-system-info="<?php echo esc_attr( wp_json_encode( array(
										'wpshadow_version'    => WPSHADOW_VERSION,
										'wordpress_version'   => get_bloginfo( 'version' ),
										'php_version'         => phpversion(),
										'diagnostics_count'   => $diagnostic_count,
										'treatments_count'    => $treatment_count,
										'installation_path'   => WPSHADOW_PATH,
										'database_prefix'     => $GLOBALS['table_prefix'],
									) ) ); ?>"
								>
									<?php esc_html_e( 'Copy for Support', 'wpshadow' ); ?>
								</button>
								<span id="wpshadow-copy-system-info-status" class="description" role="status" aria-live="polite"></span>
							</div>
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

			<!-- Recent Activity Section -->
			<?php
			if ( function_exists( 'wpshadow_render_page_activities' ) ) {
				wpshadow_render_page_activities( 'settings', 10 );
			}
			?>
		</div>
		<?php
	}
}
