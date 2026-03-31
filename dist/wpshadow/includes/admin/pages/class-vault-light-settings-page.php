<?php
/**
 * WPShadow Vault Light Settings Page
 *
 * Renders the Vault Light settings UI for local snapshot behavior.

namespace WPShadow\Admin\Pages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPShadow Vault Light Settings Page
 *
 * @since 1.6093.1200
 */
class Vault_Light_Settings_Page {

	/**
	 * Render the Vault Light settings page
	 *
	 * @since 1.6093.1200
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
<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo \WPShadow\Helpers\Form_Controls::toggle_switch(
							array(
								'id'          => 'wpshadow_backup_enabled',
								'name'        => 'wpshadow_backup_enabled',
								'label'       => __( 'Create Snapshot Before Each Treatment', 'wpshadow' ),
								'helper_text' => __( 'Capture critical settings before treatments so you can roll back quickly.', 'wpshadow' ),
								'checked'     => (bool) get_option( 'wpshadow_backup_enabled', true ),
							)
						); ?>

						<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo \WPShadow\Helpers\Form_Controls::toggle_switch(
							array(
								'id'          => 'wpshadow_backup_include_database',
								'name'        => 'wpshadow_backup_include_database',
								'label'       => __( 'Include Database in Snapshots', 'wpshadow' ),
								'helper_text' => __( 'Include database settings in Vault Light snapshots for faster recovery.', 'wpshadow' ),
								'checked'     => (bool) get_option( 'wpshadow_backup_include_database', true ),
							)
						); ?>
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
<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo \WPShadow\Helpers\Form_Controls::toggle_switch(
						array(
							'id'          => 'wpshadow_backup_schedule_enabled',
							'name'        => 'wpshadow_backup_schedule_enabled',
							'label'       => __( 'Enable scheduled Vault Light snapshots', 'wpshadow' ),
							'helper_text' => __( 'Run lightweight snapshots automatically on your schedule.', 'wpshadow' ),
							'checked'     => (bool) get_option( 'wpshadow_backup_schedule_enabled', false ),
						)
					); ?>

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
							<?php
							$available_backup_space_mb = absint( get_option( 'wpshadow_backup_max_size_mb', 500 ) );
							if ( $available_backup_space_mb < 100 ) {
								$available_backup_space_mb = 500;
							}

							$disk_probe_path = is_dir( WP_CONTENT_DIR ) ? WP_CONTENT_DIR : ABSPATH;
							$disk_free_bytes = disk_free_space( $disk_probe_path );
							$disk_free_label = ( false !== $disk_free_bytes )
								? size_format( (float) $disk_free_bytes, 2 )
								: __( 'unavailable', 'wpshadow' );
							?>
							<div class="wps-input-group">
								<input 
									type="number" 
									id="wpshadow_backup_max_size_mb" 
									name="wpshadow_backup_max_size_mb" 
									value="<?php echo esc_attr( $available_backup_space_mb ); ?>"
									min="100"
									step="50"
									class="wps-input wps-w-32"
								/>
								<span class="wps-input-addon">MB</span>
							</div>
							<p class="wps-form-description">
								<?php
								printf(
									/* translators: 1: configured backup limit in megabytes, 2: available free disk space */
									esc_html__( 'Vault Light can use up to %1$d MB for backups. Free space currently available on this server: %2$s.', 'wpshadow' ),
									$available_backup_space_mb,
									esc_html( $disk_free_label )
								);
								?>
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
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo \WPShadow\Helpers\Form_Controls::toggle_switch(
								array(
									'id'          => 'wpshadow_backup_compress',
									'name'        => 'wpshadow_backup_compress',
									'label'       => __( 'Compress Snapshots', 'wpshadow' ),
									'helper_text' => __( 'Compress snapshot files to save disk space (slower but saves ~70% space).', 'wpshadow' ),
									'checked'     => (bool) get_option( 'wpshadow_backup_compress', true ),
								)
							);
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo \WPShadow\Helpers\Form_Controls::toggle_switch(
								array(
									'id'          => 'wpshadow_backup_exclude_uploads',
									'name'        => 'wpshadow_backup_exclude_uploads',
									'label'       => __( 'Exclude /uploads Folder', 'wpshadow' ),
									'helper_text' => __( 'Skip the /wp-content/uploads folder to reduce snapshot size (only backup files that might be modified by treatments).', 'wpshadow' ),
									'checked'     => (bool) get_option( 'wpshadow_backup_exclude_uploads', false ),
								)
							);
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo \WPShadow\Helpers\Form_Controls::toggle_switch(
								array(
									'id'          => 'wpshadow_backup_verify',
									'name'        => 'wpshadow_backup_verify',
									'label'       => __( 'Verify Snapshots After Creation', 'wpshadow' ),
									'helper_text' => __( 'Test each snapshot for integrity (slower but ensures you can rollback if needed).', 'wpshadow' ),
									'checked'     => (bool) get_option( 'wpshadow_backup_verify', true ),
								)
							);
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
