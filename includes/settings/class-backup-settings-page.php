<?php
/**
 * Backup & Recovery Settings Page
 *
 * Controls backup behavior before applying treatments, recovery options,
 * and backup retention policies.
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
 * Backup & Recovery Settings Page
 *
 * @since 1.2601.2148
 */
class Backup_Settings_Page {

	/**
	 * Render the backup settings page
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
					<span class="dashicons dashicons-backup"></span>
					<?php esc_html_e( 'Backup & Recovery', 'wpshadow' ); ?>
				</h1>
				<p class="wps-page-subtitle">
					<?php esc_html_e( 'Configure automatic backups before applying treatments.', 'wpshadow' ); ?>
				</p>
			</div>

			<!-- Safety Notice -->
			<div class="wps-card wps-card--success">
				<div class="wps-card-body">
					<p>
						<strong><?php esc_html_e( 'Safety First:', 'wpshadow' ); ?></strong>
						<?php esc_html_e( 'WPShadow creates automatic backups before applying any treatment. If something goes wrong, you can easily rollback to the previous state.', 'wpshadow' ); ?>
					</p>
				</div>
			</div>

			<form method="post" action="options.php" class="wps-settings-form">
				<?php settings_fields( 'wpshadow_settings' ); ?>

				<!-- Automatic Backups -->
				<div class="wps-card">
					<div class="wps-card-header">
						<h3 class="wps-card-title">
							<span class="dashicons dashicons-shield-alt"></span>
							<?php esc_html_e( 'Automatic Backups', 'wpshadow' ); ?>
						</h3>
						<p class="wps-card-description">
							<?php esc_html_e( 'Enable or disable automatic backups before treatments are applied.', 'wpshadow' ); ?>
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
								<?php esc_html_e( 'Create Backup Before Each Treatment', 'wpshadow' ); ?>
							</label>
							<p class="wps-form-description">
								<?php esc_html_e( 'Always create a backup snapshot before applying any treatment. This allows you to rollback if needed.', 'wpshadow' ); ?>
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
								<?php esc_html_e( 'Include Database in Backups', 'wpshadow' ); ?>
							</label>
							<p class="wps-form-description">
								<?php esc_html_e( 'Backup the WordPress database in addition to modified files. Requires more disk space but enables complete recovery.', 'wpshadow' ); ?>
							</p>
						</div>
					</div>
				</div>

				<!-- Backup Storage -->
				<div class="wps-card">
					<div class="wps-card-header">
						<h3 class="wps-card-title">
							<span class="dashicons dashicons-folder-open"></span>
							<?php esc_html_e( 'Backup Storage', 'wpshadow' ); ?>
						</h3>
						<p class="wps-card-description">
							<?php esc_html_e( 'Control where backups are stored and how long to keep them.', 'wpshadow' ); ?>
						</p>
					</div>
					<div class="wps-card-body">
						<div class="wps-form-group">
							<label for="wpshadow_backup_retention_days" class="wps-form-label">
								<?php esc_html_e( 'Keep Backups For', 'wpshadow' ); ?>
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
								<?php esc_html_e( 'Backups older than this are automatically deleted to save disk space.', 'wpshadow' ); ?>
							</p>
						</div>

						<div class="wps-form-group wps-mt-4">
							<label for="wpshadow_backup_max_size_mb" class="wps-form-label">
								<?php esc_html_e( 'Maximum Total Backup Size', 'wpshadow' ); ?>
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
								<?php esc_html_e( 'When backups exceed this size, oldest backups are deleted. Default: 500MB.', 'wpshadow' ); ?>
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
							<?php esc_html_e( 'Fine-tune backup behavior for power users.', 'wpshadow' ); ?>
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
								<?php esc_html_e( 'Compress Backups', 'wpshadow' ); ?>
							</label>
							<p class="wps-form-description">
								<?php esc_html_e( 'Compress backup files to save disk space (slower but saves ~70% space).', 'wpshadow' ); ?>
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
								<?php esc_html_e( 'Skip the /wp-content/uploads folder to reduce backup size (only backup files that might be modified by treatments).', 'wpshadow' ); ?>
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
								<?php esc_html_e( 'Verify Backups After Creation', 'wpshadow' ); ?>
							</label>
							<p class="wps-form-description">
								<?php esc_html_e( 'Test each backup for integrity (slower but ensures you can rollback if needed).', 'wpshadow' ); ?>
							</p>
						</div>
					</div>
				</div>

				<!-- Backup Management -->
				<div class="wps-card">
					<div class="wps-card-header">
						<h3 class="wps-card-title">
							<span class="dashicons dashicons-list-view"></span>
							<?php esc_html_e( 'Backup Management', 'wpshadow' ); ?>
						</h3>
						<p class="wps-card-description">
							<?php esc_html_e( 'View and manage existing backups.', 'wpshadow' ); ?>
						</p>
					</div>
					<div class="wps-card-body">
						<p><?php esc_html_e( 'You can view, download, and delete backups from the Activity Log.', 'wpshadow' ); ?></p>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-utilities&tool=activity-history' ) ); ?>" class="wps-btn wps-btn--secondary wps-mt-2">
							<span class="dashicons dashicons-archive"></span>
							<?php esc_html_e( 'View All Backups', 'wpshadow' ); ?>
						</a>
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
