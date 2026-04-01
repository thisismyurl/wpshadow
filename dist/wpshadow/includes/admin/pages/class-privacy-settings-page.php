<?php
/**
 * Privacy & Data Settings Page
 *
 * @package WPShadow
 * @subpackage Settings
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Admin\Pages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Privacy & Data Settings Page
 *
 * @since 0.6093.1200
 */
class Privacy_Settings_Page {

	/**
	 * Render the privacy settings page
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'wpshadow' ) );
		}

		?>
		<div class="wrap wps-page-container">
			<?php
			wpshadow_render_page_header(
				__( 'Privacy & Data', 'wpshadow' ),
				__( 'Manage data collection, anonymous reporting, and privacy preferences.', 'wpshadow' ),
				'dashicons-lock'
			);
			?>

			<!-- Privacy Philosophy Notice -->
			<?php
			wpshadow_render_card(
				array(
					'card_class' => 'wps-card--info',
					'body'       => '<p><strong>' . esc_html__( 'Privacy First:', 'wpshadow' ) . '</strong> ' . esc_html__( 'We never collect personal data without your consent. All settings below default to OFF (privacy-first). You can opt-in to help us improve WPShadow.', 'wpshadow' ) . '</p>',
				)
			);
			?>

			<form method="post" action="options.php" class="wps-settings-form">
				<?php settings_fields( 'wpshadow_privacy_settings' ); ?>

				<!-- Data Collection Section -->
				<?php
				wpshadow_render_card(
					array(
						'title'       => __( 'Data Collection', 'wpshadow' ),
						'description' => __( 'Help us improve WPShadow by sharing anonymous usage data.', 'wpshadow' ),
						'icon'        => 'dashicons-data',
						'body'        => function() {
							?>
							<div class="wps-form-group">
								<label class="wps-toggle" for="wpshadow_privacy_telemetry_enabled">
									<input
										type="checkbox"
										id="wpshadow_privacy_telemetry_enabled"
										name="wpshadow_privacy_telemetry_enabled"
										value="1"
										<?php checked( get_option( 'wpshadow_privacy_telemetry_enabled', false ) ); ?>
									/>
									<span class="wps-toggle-slider"></span>
									<?php esc_html_e( 'Allow Telemetry', 'wpshadow' ); ?>
								</label>
								<p class="wps-form-description">
									<?php esc_html_e( 'Share anonymous usage statistics (no personal data). Helps us identify the most useful features and prioritize improvements.', 'wpshadow' ); ?>
								</p>
							</div>

							<div class="wps-form-group wps-mt-4">
								<h4 class="wps-form-label"><?php esc_html_e( 'What We Collect (if enabled)', 'wpshadow' ); ?></h4>
								<ul style="margin-left: 20px;">
									<li><?php esc_html_e( '✓ Plugin version and PHP version', 'wpshadow' ); ?></li>
									<li><?php esc_html_e( '✓ Number of diagnostics run', 'wpshadow' ); ?></li>
									<li><?php esc_html_e( '✓ Features used (aggregated)', 'wpshadow' ); ?></li>
									<li><?php esc_html_e( '✗ Site name, URL, or any personal data', 'wpshadow' ); ?></li>
									<li><?php esc_html_e( '✗ User information or credentials', 'wpshadow' ); ?></li>
									<li><?php esc_html_e( '✗ Site content or configuration details', 'wpshadow' ); ?></li>
								</ul>
							</div>
							<?php
						},
					)
				);
				?>

				<!-- Error Reporting Section -->
				<?php
				wpshadow_render_card(
					array(
						'title'       => __( 'Error Reporting', 'wpshadow' ),
						'description' => __( 'Help us fix bugs by reporting errors that occur in WPShadow.', 'wpshadow' ),
						'icon'        => 'dashicons-warning',
						'body'        => function() {
							?>
							<div class="wps-form-group">
								<label class="wps-toggle" for="wpshadow_privacy_error_reporting">
									<input
										type="checkbox"
										id="wpshadow_privacy_error_reporting"
										name="wpshadow_privacy_error_reporting"
										value="1"
										<?php checked( get_option( 'wpshadow_privacy_error_reporting', false ) ); ?>
									/>
									<span class="wps-toggle-slider"></span>
									<?php esc_html_e( 'Allow Error Reporting', 'wpshadow' ); ?>
								</label>
								<p class="wps-form-description">
									<?php esc_html_e( 'Send error logs when something goes wrong. These help us diagnose and fix issues faster.', 'wpshadow' ); ?>
								</p>
							</div>

							<div class="wps-form-group wps-mt-4">
								<h4 class="wps-form-label"><?php esc_html_e( 'Error Reports Include', 'wpshadow' ); ?></h4>
								<ul style="margin-left: 20px;">
									<li><?php esc_html_e( '✓ Error message and stack trace', 'wpshadow' ); ?></li>
									<li><?php esc_html_e( '✓ Server PHP version and extensions', 'wpshadow' ); ?></li>
									<li><?php esc_html_e( '✗ Site content or sensitive settings', 'wpshadow' ); ?></li>
								</ul>
							</div>
							<?php
						},
					)
				);
				?>

				<!-- Data Retention Section -->
				<?php
				wpshadow_render_card(
					array(
						'title'       => __( 'Data Retention', 'wpshadow' ),
						'description' => __( 'Control how long WPShadow keeps activity logs and audit data.', 'wpshadow' ),
						'icon'        => 'dashicons-trash',
						'body'        => function() {
							?>
							<div class="wps-form-group">
								<label for="wpshadow_data_retention_days" class="wps-form-label">
									<?php esc_html_e( 'Keep Activity Logs For', 'wpshadow' ); ?>
								</label>
								<div class="wps-input-group">
									<select
										id="wpshadow_data_retention_days"
										name="wpshadow_data_retention_days"
										class="wps-input wps-w-48"
									>
										<option value="7" <?php selected( get_option( 'wpshadow_data_retention_days', 30 ), '7' ); ?>>
											<?php esc_html_e( '1 week', 'wpshadow' ); ?>
										</option>
										<option value="14" <?php selected( get_option( 'wpshadow_data_retention_days', 30 ), '14' ); ?>>
											<?php esc_html_e( '2 weeks', 'wpshadow' ); ?>
										</option>
										<option value="30" <?php selected( get_option( 'wpshadow_data_retention_days', 30 ), '30' ); ?>>
											<?php esc_html_e( '1 month', 'wpshadow' ); ?>
										</option>
										<option value="90" <?php selected( get_option( 'wpshadow_data_retention_days', 30 ), '90' ); ?>>
											<?php esc_html_e( '3 months', 'wpshadow' ); ?>
										</option>
										<option value="180" <?php selected( get_option( 'wpshadow_data_retention_days', 30 ), '180' ); ?>>
											<?php esc_html_e( '6 months', 'wpshadow' ); ?>
										</option>
										<option value="365" <?php selected( get_option( 'wpshadow_data_retention_days', 30 ), '365' ); ?>>
											<?php esc_html_e( '1 year', 'wpshadow' ); ?>
										</option>
									</select>
								</div>
								<p class="wps-form-description">
									<?php esc_html_e( 'Older logs are automatically deleted. Default: 1 month.', 'wpshadow' ); ?>
								</p>
							</div>
							<?php
						},
					)
				);
				?>

				<!-- GDPR Section -->
				<?php
				wpshadow_render_card(
					array(
						'title'       => __( 'GDPR & Data Rights', 'wpshadow' ),
						'description' => __( 'Your data rights and how to export or delete your data.', 'wpshadow' ),
						'icon'        => 'dashicons-shield-alt',
						'body'        => function() {
							?>
							<p>
								<?php esc_html_e( 'You can request:', 'wpshadow' ); ?>
							</p>
							<ul style="margin-left: 20px;">
								<li><?php esc_html_e( '→ Export your WPShadow activity data', 'wpshadow' ); ?></li>
								<li><?php esc_html_e( '→ Delete all WPShadow activity logs', 'wpshadow' ); ?></li>
								<li><?php esc_html_e( '→ Reset your privacy preferences', 'wpshadow' ); ?></li>
							</ul>
							<div class="wps-flex wps-gap-2 wps-mt-4">
								<button type="button" class="wps-btn wps-btn--secondary" id="wpshadow-export-data">
									<?php esc_html_e( 'Export My Data', 'wpshadow' ); ?>
								</button>
								<button type="button" class="wps-btn wps-btn--danger" id="wpshadow-delete-data" onclick="return confirm('<?php echo esc_attr__( 'This will permanently delete all WPShadow activity logs. This action cannot be undone. Continue?', 'wpshadow' ); ?>');">
									<?php esc_html_e( 'Delete All Logs', 'wpshadow' ); ?>
								</button>
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
