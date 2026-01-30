<?php
/**
 * WPShadow Vault Light Settings Page
 *
 * Controls lightweight backup behavior, scheduled snapshots, and retention policies.
 *
 * @package    WPShadow
 * @subpackage Settings
 * @since      1.26030.0232
 */

declare(strict_types=1);

namespace WPShadow\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPShadow Vault Light Settings Page
 *
 * @since 1.26030.0232
 */
class Vault_Light_Settings_Page {

	/**
	 * Render the Vault Light settings page
	 *
	 * @since  1.26030.0232
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
					<span class="dashicons dashicons-backup"></span>
					<?php esc_html_e( 'WPShadow Vault Light', 'wpshadow' ); ?>
				</h1>
				<p class="wps-page-subtitle">
					<?php esc_html_e( 'Configure lightweight scheduled backups and snapshots before treatments.', 'wpshadow' ); ?>
				</p>
			</div>

			<!-- Safety Notice -->
			<div class="wps-card wps-card--success">
				<div class="wps-card-body">
					<p>
						<strong><?php esc_html_e( 'Safety First:', 'wpshadow' ); ?></strong>
						<?php esc_html_e( 'WPShadow Vault Light creates scheduled snapshots and pre-treatment backups so you can recover fast. Vault upgrades are seamless later.', 'wpshadow' ); ?>
					</p>
				</div>
			</div>

			<form method="post" action="options.php" class="wps-settings-form">
				<?php settings_fields( 'wpshadow_settings' ); ?>

				<!-- Pre-Treatment Snapshots -->
				<div class="wps-card">
					<div class="wps-card-header">
						<h3 class="wps-card-title">
							<span class="dashicons dashicons-shield-alt"></span>
							<?php esc_html_e( 'Pre-Treatment Snapshots', 'wpshadow' ); ?>
						</h3>
						<p class="wps-card-description">
							<?php esc_html_e( 'Enable or disable Vault Light snapshots before treatments are applied.', 'wpshadow' ); ?>
						</p>
					</div>
					<div class="wps-card-body">
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
					</div>
				</div>

				<!-- Scheduled Backups -->
				<div class="wps-card">
					<div class="wps-card-header">
						<h3 class="wps-card-title">
							<span class="dashicons dashicons-calendar"></span>
							<?php esc_html_e( 'Scheduled Backups', 'wpshadow' ); ?>
						</h3>
						<p class="wps-card-description">
							<?php esc_html_e( 'Run Vault Light backups on a schedule (daily, weekly, or monthly).', 'wpshadow' ); ?>
						</p>
					</div>
					<div class="wps-card-body">
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
								<?php esc_html_e( 'Enable scheduled backups', 'wpshadow' ); ?>
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
								<?php esc_html_e( 'How often to run scheduled backups.', 'wpshadow' ); ?>
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
								<?php esc_html_e( 'We run the backup at this time in your WordPress timezone.', 'wpshadow' ); ?>
							</p>
						</div>
					</div>
				</div>

				<!-- Backup Storage -->
				<div class="wps-card">
					<div class="wps-card-header">
						<h3 class="wps-card-title">
							<span class="dashicons dashicons-folder-open"></span>
							<?php esc_html_e( 'Snapshot Storage', 'wpshadow' ); ?>
						</h3>
						<p class="wps-card-description">
							<?php esc_html_e( 'Control where snapshots are stored and how long to keep them.', 'wpshadow' ); ?>
						</p>
					</div>
					<div class="wps-card-body">
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
					</div>
				</div>

				<!-- Advanced Backup Options -->
				<div class="wps-card">
					<div class="wps-card-header">
						<h3 class="wps-card-title">
							<span class="dashicons dashicons-admin-tools"></span>
							<?php esc_html_e( 'Advanced Options', 'wpshadow' ); ?>
						</h3>
						<p class="wps-card-description">
							<?php esc_html_e( 'Fine-tune snapshot behavior for power users.', 'wpshadow' ); ?>
						</p>
					</div>
					<div class="wps-card-body">
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
					</div>
				</div>

				<!-- Backup Management -->
				<div class="wps-card">
					<div class="wps-card-header">
						<h3 class="wps-card-title">
							<span class="dashicons dashicons-list-view"></span>
							<?php esc_html_e( 'Snapshot Management', 'wpshadow' ); ?>
						</h3>
						<p class="wps-card-description">
							<?php esc_html_e( 'View and manage existing snapshots.', 'wpshadow' ); ?>
						</p>
					</div>
					<div class="wps-card-body">
						<p><?php esc_html_e( 'You can view, download, and delete Vault Light snapshots from the Activity Log.', 'wpshadow' ); ?></p>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-settings' ) ); ?>" class="wps-btn wps-btn--secondary wps-mt-2">
							<span class="dashicons dashicons-archive"></span>
							<?php esc_html_e( 'Back to Settings', 'wpshadow' ); ?>
						</a>
					</div>
				</div>

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
