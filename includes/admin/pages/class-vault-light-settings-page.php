<?php
/**
 * WPShadow Vault Light Settings Page
			<?php
			wpshadow_render_page_header(
				__( 'Vault Light', 'wpshadow' ),
				__( 'Basic backup and restore functionality for your WordPress site.', 'wpshadow' ),
				'dashicons-backup'
			);
			?>

namespace WPShadow\Admin\Pages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPShadow Vault Light Settings Page
 *
 * @since 1.6030.0232
 */
class Vault_Light_Settings_Page {

	/**
	 * Render the Vault Light settings page
	 *
	 * @since  1.6030.0232
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
				__( 'WPShadow Vault Light', 'wpshadow' ),
				__( 'Configure WPShadow Vault Light snapshots before treatments.', 'wpshadow' ),
				'dashicons-backup'
			);
			?>

			<!-- Safety Notice -->
			<?php
			wpshadow_render_card(
				array(
					'card_class' => 'wps-card--success',
					'body'       => '<p><strong>' . esc_html__( 'Safety First:', 'wpshadow' ) . '</strong> ' . esc_html__( 'WPShadow Vault Light creates scheduled snapshots and pre-treatment safety points so you can recover fast. Vault upgrades are seamless later.', 'wpshadow' ) . '</p>',
				)
			);
			?>

			<form method="post" action="options.php" class="wps-settings-form">
				<?php settings_fields( 'wpshadow_settings' ); ?>

				<!-- Pre-Treatment Snapshots -->
				<?php
				wpshadow_render_card(
					array(
						'title'       => __( 'Pre-Treatment Snapshots', 'wpshadow' ),
						'description' => __( 'Enable or disable Vault Light snapshots before treatments are applied.', 'wpshadow' ),
						'icon'        => 'dashicons-shield-alt',
						'body'        => function() {
							?>
							<div class="wps-form-group">
								<label class="wps-toggle" for="wpshadow_backup_enabled">
									<input 
										type="checkbox" 
										id="wpshadow_backup_enabled" 
										name="wpshadow_backup_enabled" 
										value="1"
										<?php checked( get_option( 'wpshadow_backup_enabled', true ) ); ?>
									/>
									<span class="wps-toggle-slider"></span>
									<?php esc_html_e( 'Create Snapshot Before Each Treatment', 'wpshadow' ); ?>
								</label>
								<p class="wps-form-description">
									<?php esc_html_e( 'Capture critical settings before treatments so you can roll back quickly.', 'wpshadow' ); ?>
								</p>
							</div>

							<div class="wps-form-group wps-mt-4">
								<label class="wps-toggle" for="wpshadow_backup_include_database">
									<input 
										type="checkbox" 
										id="wpshadow_backup_include_database" 
										name="wpshadow_backup_include_database" 
										value="1"
										<?php checked( get_option( 'wpshadow_backup_include_database', true ) ); ?>
									/>
									<span class="wps-toggle-slider"></span>
									<?php esc_html_e( 'Include Database in Snapshots', 'wpshadow' ); ?>
								</label>
								<p class="wps-form-description">
									<?php esc_html_e( 'Include database settings in Vault Light snapshots for faster recovery.', 'wpshadow' ); ?>
								</p>
							</div>
							<?php
						},
					)
				);
				?>

				<!-- Scheduled Vault Light Snapshots -->
				<?php
				wpshadow_render_card(
					array(
						'title'       => __( 'Scheduled Vault Light Snapshots', 'wpshadow' ),
						'description' => __( 'Run WPShadow Vault Light snapshots on a schedule (daily, weekly, or monthly).', 'wpshadow' ),
						'icon'        => 'dashicons-calendar',
						'body'        => function() {
							?>
						<div class="wps-form-group">
							<label class="wps-toggle" for="wpshadow_backup_schedule_enabled">
								<input
									type="checkbox"
									id="wpshadow_backup_schedule_enabled"
									name="wpshadow_backup_schedule_enabled"
									value="1"
									<?php checked( get_option( 'wpshadow_backup_schedule_enabled', false ) ); ?>
								/>
								<span class="wps-toggle-slider"></span>
								<?php esc_html_e( 'Enable scheduled Vault Light snapshots', 'wpshadow' ); ?>
							</label>
							<p class="wps-form-description">
								<?php esc_html_e( 'Run lightweight snapshots automatically on your schedule.', 'wpshadow' ); ?>
							</p>
						</div>

						<div class="wps-form-group wps-mt-4">
							<label for="wpshadow_backup_schedule_frequency" class="wps-form-label">
								<?php esc_html_e( 'Frequency', 'wpshadow' ); ?>
							</label>
							<div class="wps-input-group">
								<select
									id="wpshadow_backup_schedule_frequency"
									name="wpshadow_backup_schedule_frequency"
									class="wps-input wps-w-48"
								>
									<option value="daily" <?php selected( get_option( 'wpshadow_backup_schedule_frequency', 'weekly' ), 'daily' ); ?>>
										<?php esc_html_e( 'Daily', 'wpshadow' ); ?>
									</option>
									<option value="weekly" <?php selected( get_option( 'wpshadow_backup_schedule_frequency', 'weekly' ), 'weekly' ); ?>>
										<?php esc_html_e( 'Weekly', 'wpshadow' ); ?>
									</option>
									<option value="monthly" <?php selected( get_option( 'wpshadow_backup_schedule_frequency', 'weekly' ), 'monthly' ); ?>>
										<?php esc_html_e( 'Monthly', 'wpshadow' ); ?>
									</option>
								</select>
							</div>
							<p class="wps-form-description">
								<?php esc_html_e( 'How often to run scheduled Vault Light snapshots.', 'wpshadow' ); ?>
							</p>
						</div>

						<div class="wps-form-group wps-mt-4">
							<label for="wpshadow_backup_schedule_time" class="wps-form-label">
								<?php esc_html_e( 'Time (24h)', 'wpshadow' ); ?>
							</label>
							<div class="wps-input-group">
								<input
									type="text"
									id="wpshadow_backup_schedule_time"
									name="wpshadow_backup_schedule_time"
									value="<?php echo esc_attr( get_option( 'wpshadow_backup_schedule_time', '02:00' ) ); ?>"
									class="wps-input wps-w-32"
								/>
							</div>
							<p class="wps-form-description">
								<?php esc_html_e( 'We run the snapshot at this time in your WordPress timezone.', 'wpshadow' ); ?>
							</p>
						</div>
						<?php
						},
					)
				);
				?>

				<!-- Snapshot Storage -->
				<?php
				wpshadow_render_card(
					array(
						'title'       => __( 'Snapshot Storage', 'wpshadow' ),
						'description' => __( 'Control where snapshots are stored and how long to keep them.', 'wpshadow' ),
						'icon'        => 'dashicons-folder-open',
						'body'        => function() {
							?>
						<div class="wps-form-group">
							<label for="wpshadow_backup_retention_days" class="wps-form-label">
								<?php esc_html_e( 'Keep Snapshots For', 'wpshadow' ); ?>
							</label>
							<div class="wps-input-group">
								<select 
									id="wpshadow_backup_retention_days" 
									name="wpshadow_backup_retention_days"
									class="wps-input wps-w-48"
								>
									<option value="3" <?php selected( get_option( 'wpshadow_backup_retention_days', 7 ), '3' ); ?>>
										<?php esc_html_e( '3 days', 'wpshadow' ); ?>
									</option>
									<option value="7" <?php selected( get_option( 'wpshadow_backup_retention_days', 7 ), '7' ); ?>>
										<?php esc_html_e( '1 week', 'wpshadow' ); ?>
									</option>
									<option value="14" <?php selected( get_option( 'wpshadow_backup_retention_days', 7 ), '14' ); ?>>
										<?php esc_html_e( '2 weeks', 'wpshadow' ); ?>
									</option>
									<option value="30" <?php selected( get_option( 'wpshadow_backup_retention_days', 7 ), '30' ); ?>>
										<?php esc_html_e( '1 month', 'wpshadow' ); ?>
									</option>
									<option value="90" <?php selected( get_option( 'wpshadow_backup_retention_days', 7 ), '90' ); ?>>
										<?php esc_html_e( '3 months', 'wpshadow' ); ?>
									</option>
								</select>
							</div>
							<p class="wps-form-description">
								<?php esc_html_e( 'Snapshots older than this are automatically deleted to save disk space.', 'wpshadow' ); ?>
							</p>
						</div>

						<div class="wps-form-group wps-mt-4">
							<label for="wpshadow_backup_max_size_mb" class="wps-form-label">
								<?php esc_html_e( 'Maximum Total Snapshot Size', 'wpshadow' ); ?>
							</label>
							<div class="wps-input-group">
								<input 
									type="number" 
									id="wpshadow_backup_max_size_mb" 
									name="wpshadow_backup_max_size_mb" 
									value="<?php echo esc_attr( get_option( 'wpshadow_backup_max_size_mb', 500 ) ); ?>"
									min="100"
									step="50"
									class="wps-input wps-w-32"
								/>
								<span class="wps-input-addon">MB</span>
							</div>
							<p class="wps-form-description">
								<?php esc_html_e( 'When snapshots exceed this size, oldest snapshots are deleted. Default: 500MB.', 'wpshadow' ); ?>
							</p>
						</div>
						<?php
						},
					)
				);
				?>

				<!-- Advanced Snapshot Options -->
				<?php
				wpshadow_render_card(
					array(
						'title'       => __( 'Advanced Options', 'wpshadow' ),
						'description' => __( 'Fine-tune snapshot behavior for power users.', 'wpshadow' ),
						'icon'        => 'dashicons-admin-tools',
						'body'        => function() {
							?>
						<div class="wps-form-group">
							<label class="wps-toggle" for="wpshadow_backup_compress">
								<input 
									type="checkbox" 
									id="wpshadow_backup_compress" 
									name="wpshadow_backup_compress" 
									value="1"
									<?php checked( get_option( 'wpshadow_backup_compress', true ) ); ?>
								/>
								<span class="wps-toggle-slider"></span>
								<?php esc_html_e( 'Compress Snapshots', 'wpshadow' ); ?>
							</label>
							<p class="wps-form-description">
								<?php esc_html_e( 'Compress snapshot files to save disk space (slower but saves ~70% space).', 'wpshadow' ); ?>
							</p>
						</div>

						<div class="wps-form-group wps-mt-4">
							<label class="wps-toggle" for="wpshadow_backup_exclude_uploads">
								<input 
									type="checkbox" 
									id="wpshadow_backup_exclude_uploads" 
									name="wpshadow_backup_exclude_uploads" 
									value="1"
									<?php checked( get_option( 'wpshadow_backup_exclude_uploads', false ) ); ?>
								/>
								<span class="wps-toggle-slider"></span>
								<?php esc_html_e( 'Exclude /uploads Folder', 'wpshadow' ); ?>
							</label>
							<p class="wps-form-description">
								<?php esc_html_e( 'Skip the /wp-content/uploads folder to reduce snapshot size (only backup files that might be modified by treatments).', 'wpshadow' ); ?>
							</p>
						</div>

						<div class="wps-form-group wps-mt-4">
							<label class="wps-toggle" for="wpshadow_backup_verify">
								<input 
									type="checkbox" 
									id="wpshadow_backup_verify" 
									name="wpshadow_backup_verify" 
									value="1"
									<?php checked( get_option( 'wpshadow_backup_verify', true ) ); ?>
								/>
								<span class="wps-toggle-slider"></span>
								<?php esc_html_e( 'Verify Snapshots After Creation', 'wpshadow' ); ?>
							</label>
							<p class="wps-form-description">
								<?php esc_html_e( 'Test each snapshot for integrity (slower but ensures you can rollback if needed).', 'wpshadow' ); ?>
							</p>
						</div>
						<?php
						},
					)
				);
				?>

				<!-- Snapshot Management -->
				<?php
				wpshadow_render_card(
					array(
						'title'       => __( 'Snapshot Management', 'wpshadow' ),
						'description' => __( 'View and manage existing snapshots.', 'wpshadow' ),
						'icon'        => 'dashicons-list-view',
						'body'        => function() {
							?>
							<p><?php esc_html_e( 'You can view, download, and delete Vault Light snapshots from the Activity Log.', 'wpshadow' ); ?></p>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-settings' ) ); ?>" class="wps-btn wps-btn--secondary wps-mt-2">
								<span class="dashicons dashicons-archive"></span>
								<?php esc_html_e( 'Back to Settings', 'wpshadow' ); ?>
							</a>
							<?php
						},
					)
				);
				?>

				<p class="submit">
					<button type="submit" class="wps-btn wps-btn--primary">
						<?php esc_html_e( 'Save Settings', 'wpshadow' ); ?>
					</button>
				</p>
			</form>
		</div>
		<?php
	}
}
