<?php
/**
 * Import/Export Settings Page
			<?php
			wpshadow_render_page_header(
				__( 'Import / Export Settings', 'wpshadow' ),
				__( 'Backup, restore, or sync your WPShadow configuration.', 'wpshadow' ),
				'dashicons-upload'
			);
			?>
 * @package    WPShadow
 * @subpackage Settings
 * @since      1.7035.1500
 */

declare(strict_types=1);

namespace WPShadow\Admin\Pages;

use WPShadow\Core\Settings_Registry;
use WPShadow\Core\WPShadow_Account_API;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Import/Export Settings Page
 *
 * @since 1.7035.1500
 */
class Import_Export_Settings_Page {

	/**
	 * Render the import/export settings page
	 *
	 * @since  1.7035.1500
	 * @return void
	 */
	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'wpshadow' ) );
		}

		$is_registered = WPShadow_Account_API::is_registered();
		$account_info = $is_registered ? WPShadow_Account_API::get_account_info() : null;
		$last_export = get_option( 'wpshadow_last_export_date', '' );
		$last_import = get_option( 'wpshadow_last_import_date', '' );
		$cloud_sync_enabled = get_option( 'wpshadow_cloud_sync_enabled', false );
		$last_cloud_sync = get_option( 'wpshadow_last_cloud_sync', '' );

		?>
		<div class="wrap wps-page-container">
			<?php
			wpshadow_render_page_header(
				__( 'Import / Export Settings', 'wpshadow' ),
				__( 'Backup, restore, or sync your WPShadow configuration across sites.', 'wpshadow' ),
				'dashicons-upload'
			);
			?>

			<!-- Export Section -->
			<?php
			wpshadow_render_card(
				array(
					'title'       => __( 'Export Settings', 'wpshadow' ),
					'description' => __( 'Download your complete WPShadow configuration as a JSON file.', 'wpshadow' ),
					'icon'        => 'dashicons-download',
					'body'        => function() use ( $last_export ) {
						?>
						<div class="wps-form-group">
							<p class="wps-text-muted">
								<?php esc_html_e( 'This will export all your WPShadow settings including:', 'wpshadow' ); ?>
							</p>
							<ul class="wps-list wps-list-disc wps-pl-6 wps-text-muted">
								<li><?php esc_html_e( 'Guardian settings and auto-fix preferences', 'wpshadow' ); ?></li>
								<li><?php esc_html_e( 'Email notification configuration', 'wpshadow' ); ?></li>
								<li><?php esc_html_e( 'Privacy and telemetry preferences', 'wpshadow' ); ?></li>
								<li><?php esc_html_e( 'Cache settings and performance options', 'wpshadow' ); ?></li>
								<li><?php esc_html_e( 'Workflow automation rules', 'wpshadow' ); ?></li>
								<li><?php esc_html_e( 'Advanced configuration options', 'wpshadow' ); ?></li>
							</ul>
							<p class="wps-text-muted wps-mt-3">
								<strong><?php esc_html_e( 'Note:', 'wpshadow' ); ?></strong>
								<?php esc_html_e( 'Sensitive data like API keys are excluded from exports for security.', 'wpshadow' ); ?>
							</p>
						</div>

						<?php if ( $last_export ) : ?>
							<div class="wps-alert wps-alert--info wps-mt-4">
								<span class="dashicons dashicons-info"></span>
								<div>
									<strong><?php esc_html_e( 'Last Export:', 'wpshadow' ); ?></strong>
									<?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $last_export ) ) ); ?>
								</div>
							</div>
						<?php endif; ?>

						<div class="wps-form-actions wps-mt-4">
							<button
								type="button"
								class="wps-btn wps-btn--primary"
								id="wpshadow-export-settings"
								data-nonce="<?php echo esc_attr( wp_create_nonce( 'wpshadow_export_settings' ) ); ?>"
							>
								<span class="dashicons dashicons-download"></span>
								<?php esc_html_e( 'Export Settings', 'wpshadow' ); ?>
							</button>
						</div>
						<?php
					},
				)
			);
			?>

			<!-- Import Section -->
			<?php
			wpshadow_render_card(
				array(
					'title'       => __( 'Import Settings', 'wpshadow' ),
					'description' => __( 'Restore your WPShadow configuration from a previously exported JSON file.', 'wpshadow' ),
					'icon'        => 'dashicons-upload',
					'card_class'  => 'wps-mt-6',
					'body'        => function() use ( $last_import ) {
						?>
						<div class="wps-form-group">
							<label for="wpshadow-import-file" class="wps-form-label">
								<?php esc_html_e( 'Select Settings File', 'wpshadow' ); ?>
							</label>
							<input
								type="file"
								id="wpshadow-import-file"
								accept=".json,application/json"
								class="wps-form-control"
							/>
							<span class="wps-help-text">
								<?php esc_html_e( 'Choose a JSON file exported from WPShadow.', 'wpshadow' ); ?>
							</span>
						</div>

						<?php if ( $last_import ) : ?>
							<div class="wps-alert wps-alert--success wps-mt-4">
								<span class="dashicons dashicons-yes"></span>
								<div>
									<strong><?php esc_html_e( 'Last Import:', 'wpshadow' ); ?></strong>
									<?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $last_import ) ) ); ?>
								</div>
							</div>
						<?php endif; ?>

						<div class="wps-alert wps-alert--warning wps-mt-4">
							<span class="dashicons dashicons-warning"></span>
							<div>
								<strong><?php esc_html_e( 'Heads up:', 'wpshadow' ); ?></strong>
								<?php esc_html_e( 'Importing will overwrite your current settings. Your existing configuration will be backed up automatically.', 'wpshadow' ); ?>
							</div>
						</div>

						<div class="wps-form-actions wps-mt-4">
							<button
								type="button"
								class="wps-btn wps-btn--secondary"
								id="wpshadow-import-settings"
								data-nonce="<?php echo esc_attr( wp_create_nonce( 'wpshadow_import_settings' ) ); ?>"
								disabled
							>
								<span class="dashicons dashicons-upload"></span>
								<?php esc_html_e( 'Import Settings', 'wpshadow' ); ?>
							</button>
						</div>
						<?php
					},
				)
			);
			?>

			<!-- Cloud Sync Section (for registered users) -->
			<?php
			if ( $is_registered ) {
				wpshadow_render_card(
					array(
						'title'       => __( 'Cloud Sync', 'wpshadow' ),
						'description' => __( 'Automatically sync your settings with wpshadow.com and access them from any site.', 'wpshadow' ),
						'icon'        => 'dashicons-cloud',
						'card_class'  => 'wps-mt-6',
						'body'        => function() use ( $account_info, $cloud_sync_enabled, $last_cloud_sync ) {
							?>
						<div class="wps-alert wps-alert--info wps-mb-4">
							<span class="dashicons dashicons-info"></span>
							<div>
								<strong><?php esc_html_e( 'Connected Account:', 'wpshadow' ); ?></strong>
								<?php echo esc_html( $account_info['email'] ?? __( 'Unknown', 'wpshadow' ) ); ?>
							</div>
						</div>

						<div class="wps-form-group">
							<div class="wps-flex wps-gap-6 wps-items-start wps-justify-between">
								<label class="wps-toggle" for="wpshadow_cloud_sync_enabled">
									<input
										type="checkbox"
										id="wpshadow_cloud_sync_enabled"
										name="wpshadow_cloud_sync_enabled"
										value="1"
										<?php checked( $cloud_sync_enabled ); ?>
										data-nonce="<?php echo esc_attr( wp_create_nonce( 'wpshadow_toggle_cloud_sync' ) ); ?>"
									/>
									<span class="wps-toggle-slider"></span>
									<?php esc_html_e( 'Enable Cloud Sync', 'wpshadow' ); ?>
								</label>
								<p class="wps-form-description wps-m-0">
									<?php esc_html_e( 'Automatically backup your settings to the cloud. Your data is encrypted and only accessible by you.', 'wpshadow' ); ?>
								</p>
							</div>
						</div>

						<?php if ( $last_cloud_sync ) : ?>
							<div class="wps-form-group wps-mt-4">
								<p class="wps-text-muted">
									<span class="dashicons dashicons-cloud-saved"></span>
									<strong><?php esc_html_e( 'Last Cloud Sync:', 'wpshadow' ); ?></strong>
									<?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $last_cloud_sync ) ) ); ?>
								</p>
							</div>
						<?php endif; ?>

						<div class="wps-form-actions wps-mt-4 wps-flex wps-gap-3">
							<button
								type="button"
								class="wps-btn wps-btn--primary"
								id="wpshadow-sync-to-cloud"
								data-nonce="<?php echo esc_attr( wp_create_nonce( 'wpshadow_sync_to_cloud' ) ); ?>"
								<?php echo $cloud_sync_enabled ? '' : 'disabled'; ?>
							>
								<span class="dashicons dashicons-cloud-upload"></span>
								<?php esc_html_e( 'Sync to Cloud', 'wpshadow' ); ?>
							</button>

							<button
								type="button"
								class="wps-btn wps-btn--secondary"
								id="wpshadow-restore-from-cloud"
								data-nonce="<?php echo esc_attr( wp_create_nonce( 'wpshadow_restore_from_cloud' ) ); ?>"
								<?php echo $cloud_sync_enabled ? '' : 'disabled'; ?>
							>
								<span class="dashicons dashicons-cloud-download"></span>
								<?php esc_html_e( 'Restore from Cloud', 'wpshadow' ); ?>
							</button>
						</div>

						<div class="wps-help-text wps-mt-3">
							<span class="dashicons dashicons-privacy"></span>
							<?php
							printf(
								/* translators: %s: link to privacy policy */
								esc_html__( 'Your settings are encrypted before being sent to our servers. Learn more in our %s.', 'wpshadow' ),
								'<a href="https://wpshadow.com/privacy?utm_source=plugin&utm_medium=settings&utm_campaign=cloud_sync" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Privacy Policy', 'wpshadow' ) . '</a>'
							);
							?>
							<?php
						},
					)
				);
			} else {
				// Cloud Sync Promo for Non-Registered Users
				wpshadow_render_card(
					array(
						'title'       => __( 'Cloud Sync Available', 'wpshadow' ),
						'description' => __( 'Register for a free WPShadow account to unlock cloud settings sync.', 'wpshadow' ),
						'icon'        => 'dashicons-cloud',
						'card_class'  => 'wps-mt-6 wps-card--highlight',
						'body'        => function() {
							?>
						<p>
							<?php esc_html_e( 'With a free WPShadow account, you can:', 'wpshadow' ); ?>
						</p>
						<ul class="wps-list wps-list-disc wps-pl-6">
							<li><?php esc_html_e( 'Automatically backup settings to the cloud', 'wpshadow' ); ?></li>
							<li><?php esc_html_e( 'Sync settings across multiple sites', 'wpshadow' ); ?></li>
							<li><?php esc_html_e( 'Restore settings from any device', 'wpshadow' ); ?></li>
							<li><?php esc_html_e( 'Access your configuration history', 'wpshadow' ); ?></li>
						</ul>

						<div class="wps-alert wps-alert--info wps-mt-4">
							<span class="dashicons dashicons-info"></span>
							<div>
								<strong><?php esc_html_e( 'Free & Privacy-First:', 'wpshadow' ); ?></strong>
								<?php esc_html_e( 'Your data is encrypted and never shared. No credit card required.', 'wpshadow' ); ?>
							</div>
						</div>

						<div class="wps-form-actions wps-mt-4">
							<a
								href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-account' ) ); ?>"
								class="wps-btn wps-btn--primary"
							>
								<span class="dashicons dashicons-admin-users"></span>
								<?php esc_html_e( 'Register Free Account', 'wpshadow' ); ?>
							</a>
							<a
								href="https://wpshadow.com/cloud-sync?utm_source=plugin&utm_medium=settings&utm_campaign=register_cta"
								target="_blank"
								rel="noopener noreferrer"
								class="wps-btn wps-btn--secondary"
							>
								<?php esc_html_e( 'Learn More', 'wpshadow' ); ?>
							</a>
						</div>
						<?php
					},
				)
			);
		}
		?>

			<!-- Status Messages -->
			<div id="wpshadow-import-export-messages" class="wps-mt-6"></div>
		</div>

		<?php
		// Enqueue JavaScript for import/export functionality
		wp_enqueue_script(
			'wpshadow-import-export',
			WPSHADOW_URL . 'assets/js/import-export.js',
			array( 'jquery' ),
			WPSHADOW_VERSION,
			true
		);

		wp_localize_script(
			'wpshadow-import-export',
			'wpShadowImportExport',
			array(
				'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
				'isRegistered'  => $is_registered,
				'i18n'          => array(
					'exporting'        => __( 'Exporting settings...', 'wpshadow' ),
					'exportSuccess'    => __( 'Settings exported successfully!', 'wpshadow' ),
					'exportError'      => __( 'Failed to export settings. Please try again.', 'wpshadow' ),
					'importing'        => __( 'Importing settings...', 'wpshadow' ),
					'importSuccess'    => __( 'Settings imported successfully! Reloading page...', 'wpshadow' ),
					'importError'      => __( 'Failed to import settings. Please check the file format.', 'wpshadow' ),
					'invalidFile'      => __( 'Please select a valid JSON file.', 'wpshadow' ),
					'syncing'          => __( 'Syncing to cloud...', 'wpshadow' ),
					'syncSuccess'      => __( 'Settings synced to cloud successfully!', 'wpshadow' ),
					'syncError'        => __( 'Failed to sync settings to cloud.', 'wpshadow' ),
					'restoring'        => __( 'Restoring from cloud...', 'wpshadow' ),
					'restoreSuccess'   => __( 'Settings restored from cloud successfully! Reloading page...', 'wpshadow' ),
					'restoreError'     => __( 'Failed to restore settings from cloud.', 'wpshadow' ),
					'confirmImport'    => __( 'This will overwrite your current settings. A backup will be created automatically. Continue?', 'wpshadow' ),
					'confirmRestore'   => __( 'This will replace your current settings with your cloud backup. Continue?', 'wpshadow' ),
					'retrying'         => __( 'Upload failed. Trying again (%1$d of %2$d)...', 'wpshadow' ),
					'retryingShort'    => __( 'Retrying upload...', 'wpshadow' ),
					'retryFailed'      => __( 'Upload failed after a few tries. Please check your connection and try again.', 'wpshadow' ),
				),
			)
		);
	}
}
