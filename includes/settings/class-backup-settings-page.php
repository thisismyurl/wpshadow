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
					<?php esc_html_e( 'Vault Light Backups', 'wpshadow' ); ?>
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
						<?php esc_html_e( 'Vault Light creates scheduled snapshots and pre-treatment backups so you can recover fast. Vault upgrades are seamless later.', 'wpshadow' ); ?>
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
									<?php
									/**
									 * Legacy Backup Settings Page Wrapper
									 *
									 * @package WPShadow
									 * @subpackage Settings
									 * @since 1.26030.0232
									 */

									declare(strict_types=1);

									namespace WPShadow\Settings;

									if ( ! defined( 'ABSPATH' ) ) {
										exit;
									}

									if ( file_exists( WPSHADOW_PATH . 'includes/settings/class-vault-light-settings-page.php' ) ) {
										require_once WPSHADOW_PATH . 'includes/settings/class-vault-light-settings-page.php';
									}

									if ( class_exists( 'WPShadow\\Settings\\Vault_Light_Settings_Page' ) && ! class_exists( 'WPShadow\\Settings\\Backup_Settings_Page' ) ) {
										class_alias( 'WPShadow\\Settings\\Vault_Light_Settings_Page', 'WPShadow\\Settings\\Backup_Settings_Page' );
									}
							<p class="wps-form-description">
