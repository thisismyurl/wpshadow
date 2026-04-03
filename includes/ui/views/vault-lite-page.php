<?php
/**
 * Vault Lite Page
 *
 * Local-only backup dashboard and settings for WPShadow.
 *
 * @package    WPShadow
 * @subpackage Views
 * @since      0.6093.1200
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'You do not have permission to access this page.', 'wpshadow' ) );
}

require_once WPSHADOW_PATH . 'includes/ui/views/functions-page-layout.php';

$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'vault'; // phpcs:ignore WordPress.Security.NonceVerification
$valid_tabs = array( 'vault', 'settings' );
if ( ! in_array( $active_tab, $valid_tabs, true ) ) {
	$active_tab = 'vault';
}

$get_option_value = static function ( string $option, $default = '' ) {
	if ( class_exists( '\\WPShadow\\Core\\Settings_Registry' ) ) {
		return \WPShadow\Core\Settings_Registry::get( $option, $default );
	}

	$key = 0 === strpos( $option, 'wpshadow_' ) ? $option : 'wpshadow_' . $option;
	return get_option( $key, $default );
};

$get_bool = static function ( string $option, bool $default = true ) use ( $get_option_value ): bool {
	return (bool) $get_option_value( $option, $default );
};

$get_str = static function ( string $option, string $default = '' ) use ( $get_option_value ): string {
	return (string) $get_option_value( $option, $default );
};

$get_int = static function ( string $option, int $default = 0 ) use ( $get_option_value ): int {
	return (int) $get_option_value( $option, $default );
};

$backup_status = class_exists( '\\WPShadow\\Guardian\\Backup_Manager' )
	? \WPShadow\Guardian\Backup_Manager::get_status_summary()
	: array(
		'directory'              => '',
		'directory_public_label' => __( 'Private Vault Lite storage (hidden randomized path)', 'wpshadow' ),
		'count'                  => 0,
		'total_size_human'       => size_format( 0 ),
		'last_backup_label'      => __( 'No local backups yet', 'wpshadow' ),
		'last_backup_file'       => '',
		'last_backup_status'     => 'warning',
	);

$next_backup_display = class_exists( '\\WPShadow\\Guardian\\Backup_Scheduler' )
	? \WPShadow\Guardian\Backup_Scheduler::get_next_scheduled_display()
	: __( 'Scheduler unavailable', 'wpshadow' );

$backup_entries            = class_exists( '\\WPShadow\\Guardian\\Backup_Manager' )
	? \WPShadow\Guardian\Backup_Manager::get_backups()
	: array();
$current_backup_file       = isset( $_GET['wpshadow_backup_file'] ) ? sanitize_file_name( wp_unslash( $_GET['wpshadow_backup_file'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$current_backup_entry      = ( '' !== $current_backup_file && class_exists( '\\WPShadow\\Guardian\\Backup_Manager' ) )
	? \WPShadow\Guardian\Backup_Manager::get_backup_entry( $current_backup_file )
	: null;
$current_backup_desc       = is_array( $current_backup_entry ) && class_exists( '\\WPShadow\\Guardian\\Backup_Manager' )
	? \WPShadow\Guardian\Backup_Manager::describe_backup( $current_backup_entry )
	: '';
$backup_run_status         = isset( $_GET['wpshadow_backup_run'] ) ? sanitize_key( wp_unslash( $_GET['wpshadow_backup_run'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$backup_restored_status    = isset( $_GET['wpshadow_backup_restored'] ) ? sanitize_key( wp_unslash( $_GET['wpshadow_backup_restored'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$restore_message           = isset( $_GET['wpshadow_restore_message'] ) ? sanitize_text_field( rawurldecode( wp_unslash( $_GET['wpshadow_restore_message'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$backup_deleted_status     = isset( $_GET['wpshadow_backup_deleted'] ) ? sanitize_key( wp_unslash( $_GET['wpshadow_backup_deleted'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$delete_message            = isset( $_GET['wpshadow_delete_message'] ) ? sanitize_text_field( rawurldecode( wp_unslash( $_GET['wpshadow_delete_message'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$retention_days            = $get_int( 'wpshadow_backup_retention_days', 7 );
$max_size_human            = size_format( $get_int( 'wpshadow_backup_max_size_mb', 500 ) * 1024 * 1024 );
$vault_url = admin_url( 'admin.php?page=wpshadow-vault-lite' );
?>
<div class="wrap wps-settings-page">

<?php
wpshadow_render_page_header(
	__( 'Vault Lite', 'wpshadow' ),
	__( 'Manage local-only restore points and backup settings for this site.', 'wpshadow' ),
	'dashicons-backup'
);
?>

	<div id="wps-settings-notice" class="wps-settings-notice" aria-live="polite"></div>

	<nav class="wps-settings-tabs" aria-label="<?php esc_attr_e( 'Vault Lite sections', 'wpshadow' ); ?>">
		<?php
		$tabs = array(
			'vault'    => array( 'label' => __( 'Vault Lite', 'wpshadow' ), 'icon' => 'dashicons-backup' ),
			'settings' => array( 'label' => __( 'Settings', 'wpshadow' ), 'icon' => 'dashicons-admin-generic' ),
		);
		foreach ( $tabs as $tab_key => $tab ) :
			$href   = add_query_arg( 'tab', $tab_key, $vault_url );
			$active = $active_tab === $tab_key ? ' wps-settings-tab--active' : '';
			?>
			<a
				href="<?php echo esc_url( $href ); ?>"
				class="wps-settings-tab<?php echo esc_attr( $active ); ?>"
				aria-current="<?php echo esc_attr( $active_tab === $tab_key ? 'page' : 'false' ); ?>"
			>
				<span class="dashicons <?php echo esc_attr( $tab['icon'] ); ?>" aria-hidden="true"></span>
				<?php echo esc_html( $tab['label'] ); ?>
			</a>
		<?php endforeach; ?>
	</nav>

	<?php if ( '' !== $backup_run_status ) : ?>
		<div class="notice <?php echo 'success' === $backup_run_status ? 'notice-success' : 'notice-error'; ?>">
			<p>
				<?php if ( 'success' === $backup_run_status ) : ?>
					<strong><?php esc_html_e( 'Local backup created successfully.', 'wpshadow' ); ?></strong>
					<?php if ( '' !== $current_backup_desc ) : ?>
						<?php echo ' ' . esc_html( $current_backup_desc ); ?>
						<a
							href="#wps-vault-restore-dialog"
							class="button-link wps-vault-restore-trigger"
							data-backup-file="<?php echo esc_attr( $current_backup_file ); ?>"
							data-backup-description="<?php echo esc_attr( $current_backup_desc ); ?>"
						><?php esc_html_e( 'Restore this backup', 'wpshadow' ); ?></a>
					<?php endif; ?>
				<?php else : ?>
					<?php esc_html_e( 'Local backup could not be created.', 'wpshadow' ); ?>
				<?php endif; ?>
			</p>
		</div>
	<?php endif; ?>

	<?php if ( '' !== $backup_restored_status ) : ?>
		<div class="notice <?php echo 'success' === $backup_restored_status ? 'notice-success' : 'notice-error'; ?>">
			<p><?php echo esc_html( '' !== $restore_message ? $restore_message : __( 'Restore request finished.', 'wpshadow' ) ); ?></p>
		</div>
	<?php endif; ?>

	<?php if ( '' !== $backup_deleted_status ) : ?>
		<div class="notice <?php echo 'success' === $backup_deleted_status ? 'notice-success' : 'notice-error'; ?>">
			<p><?php echo esc_html( '' !== $delete_message ? $delete_message : __( 'Backup deletion finished.', 'wpshadow' ) ); ?></p>
		</div>
	<?php endif; ?>

	<?php if ( 'vault' === $active_tab ) : ?>
	<div class="wps-settings-body">

		<div class="wps-settings-section">
			<h2 class="wps-settings-section-title"><?php esc_html_e( 'Local Backup Status', 'wpshadow' ); ?></h2>
			<p class="wps-settings-section-desc"><?php esc_html_e( 'Vault Lite stores local-only restore points on this server. No cloud tools are included in this version.', 'wpshadow' ); ?></p>

			<div class="wps-settings-rows">
				<div class="wps-settings-row">
					<div class="wps-settings-row-label">
						<label><?php esc_html_e( 'Stored Backups', 'wpshadow' ); ?></label>
						<p class="wps-settings-row-hint"><?php echo esc_html( sprintf( _n( '%d local backup currently retained.', '%d local backups currently retained.', (int) $backup_status['count'], 'wpshadow' ), (int) $backup_status['count'] ) ); ?></p>
					</div>
					<div class="wps-settings-row-control">
						<div>
							<strong><?php echo esc_html( sprintf( _n( '%d backup retained', '%d backups retained', (int) $backup_status['count'], 'wpshadow' ), (int) $backup_status['count'] ) ); ?></strong>
							<div class="description"><?php echo esc_html( sprintf( __( 'Older backups are auto-trimmed after %1$d days or %2$s total.', 'wpshadow' ), $retention_days, $max_size_human ) ); ?></div>
						</div>
					</div>
				</div>

				<div class="wps-settings-row">
					<div class="wps-settings-row-label">
						<label><?php esc_html_e( 'Disk Usage', 'wpshadow' ); ?></label>
						<p class="wps-settings-row-hint"><?php esc_html_e( 'Combined size of all retained local backup archives.', 'wpshadow' ); ?></p>
					</div>
					<div class="wps-settings-row-control">
						<strong><?php echo esc_html( (string) $backup_status['total_size_human'] ); ?></strong>
					</div>
				</div>

				<div class="wps-settings-row">
					<div class="wps-settings-row-label">
						<label><?php esc_html_e( 'Last Backup', 'wpshadow' ); ?></label>
						<p class="wps-settings-row-hint"><?php echo esc_html( (string) $backup_status['last_backup_label'] ); ?></p>
					</div>
					<div class="wps-settings-row-control">
						<?php if ( ! empty( $backup_status['last_backup_file'] ) ) : ?>
							<code><?php echo esc_html( (string) $backup_status['last_backup_file'] ); ?></code>
						<?php else : ?>
							<span class="description"><?php esc_html_e( 'No backup file yet', 'wpshadow' ); ?></span>
						<?php endif; ?>
					</div>
				</div>

				<div class="wps-settings-row">
					<div class="wps-settings-row-label">
						<label><?php esc_html_e( 'Next Scheduled Backup', 'wpshadow' ); ?></label>
						<p class="wps-settings-row-hint"><?php esc_html_e( 'Shown when scheduled local backups are enabled.', 'wpshadow' ); ?></p>
					</div>
					<div class="wps-settings-row-control">
						<strong><?php echo esc_html( $next_backup_display ); ?></strong>
					</div>
				</div>

				<div class="wps-settings-row">
					<div class="wps-settings-row-label">
						<label><?php esc_html_e( 'Backup Location', 'wpshadow' ); ?></label>
						<p class="wps-settings-row-hint"><?php esc_html_e( 'Vault Lite keeps backups in a secret randomized local directory, and the exact path is intentionally hidden.', 'wpshadow' ); ?></p>
					</div>
					<div class="wps-settings-row-control">
						<strong><?php echo esc_html( (string) ( $backup_status['directory_public_label'] ?? __( 'Private Vault Lite storage (hidden randomized path)', 'wpshadow' ) ) ); ?></strong>
					</div>
				</div>

				<div class="wps-settings-row">
					<div class="wps-settings-row-label">
						<label><?php esc_html_e( 'Run Backup Now', 'wpshadow' ); ?></label>
						<p class="wps-settings-row-hint"><?php esc_html_e( 'Create a new local restore point immediately.', 'wpshadow' ); ?></p>
					</div>
					<div class="wps-settings-row-control">
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
							<input type="hidden" name="action" value="wpshadow_run_local_backup" />
							<?php wp_nonce_field( 'wpshadow_run_local_backup' ); ?>
							<button type="submit" class="button button-primary"><?php esc_html_e( 'Create Local Backup', 'wpshadow' ); ?></button>
						</form>
					</div>
				</div>
			</div>
		</div>

		<div class="wps-settings-section">
			<h2 class="wps-settings-section-title"><?php esc_html_e( 'Available Backups', 'wpshadow' ); ?></h2>
			<p class="wps-settings-section-desc"><?php esc_html_e( 'Download, restore, or delete any retained local backup from this list. WPShadow creates a fresh safety backup before restoring.', 'wpshadow' ); ?></p>

			<?php if ( empty( $backup_entries ) ) : ?>
				<div class="notice notice-info inline">
					<p><?php esc_html_e( 'No retained local backups are available yet.', 'wpshadow' ); ?></p>
				</div>
			<?php else : ?>
				<div class="wps-settings-table-wrap">
					<table class="widefat striped">
						<thead>
							<tr>
								<th scope="col"><?php esc_html_e( 'Backup', 'wpshadow' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Created', 'wpshadow' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Size', 'wpshadow' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Status', 'wpshadow' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Actions', 'wpshadow' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $backup_entries as $entry ) : ?>
								<?php
								$file_name    = isset( $entry['file'] ) ? (string) $entry['file'] : '';
								$description  = class_exists( '\\WPShadow\\Guardian\\Backup_Manager' ) ? \WPShadow\Guardian\Backup_Manager::describe_backup( $entry ) : '';
								$created_at   = isset( $entry['created_at'] ) ? date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), (int) $entry['created_at'] ) : __( 'Unknown', 'wpshadow' );
								$size_label   = isset( $entry['size'] ) ? size_format( (int) $entry['size'] ) : __( 'Unknown', 'wpshadow' );
								$is_verified  = ! isset( $entry['verified'] ) || ! empty( $entry['verified'] );
								$status_label = $is_verified ? __( 'Verified', 'wpshadow' ) : __( 'Needs Review', 'wpshadow' );
								$download_url = wp_nonce_url(
									add_query_arg(
										array(
											'action'      => 'wpshadow_download_local_backup',
											'backup_file' => rawurlencode( $file_name ),
										),
										admin_url( 'admin-post.php' )
									),
									'wpshadow_download_local_backup'
								);
								?>
								<tr>
									<td>
										<strong><?php echo esc_html( $file_name ); ?></strong>
										<?php if ( '' !== $description ) : ?>
											<div class="description"><?php echo esc_html( $description ); ?></div>
										<?php endif; ?>
									</td>
									<td><?php echo esc_html( $created_at ); ?></td>
									<td><?php echo esc_html( $size_label ); ?></td>
									<td><?php echo esc_html( $status_label ); ?></td>
									<td style="white-space:nowrap;">
										<a
											href="<?php echo esc_url( $download_url ); ?>"
											class="button button-link"
											title="<?php esc_attr_e( 'Download this backup archive', 'wpshadow' ); ?>"
										>
											<span class="dashicons dashicons-download" aria-hidden="true"></span>
											<?php esc_html_e( 'Download', 'wpshadow' ); ?>
										</a>
										<a
											href="#wps-vault-restore-dialog"
											class="button button-secondary wps-vault-restore-trigger"
											data-backup-file="<?php echo esc_attr( $file_name ); ?>"
											data-backup-description="<?php echo esc_attr( $description ); ?>"
										><?php esc_html_e( 'Restore', 'wpshadow' ); ?></a>
										<button
											type="button"
											class="button button-link-delete wps-vault-delete-trigger"
											data-backup-file="<?php echo esc_attr( $file_name ); ?>"
											data-backup-description="<?php echo esc_attr( $description ); ?>"
											title="<?php esc_attr_e( 'Delete this backup', 'wpshadow' ); ?>"
										>
											<span class="dashicons dashicons-trash" aria-hidden="true"></span>
											<?php esc_html_e( 'Delete', 'wpshadow' ); ?>
										</button>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>
		</div>

	</div>

	<dialog id="wps-vault-restore-dialog" class="wps-settings-dialog" aria-labelledby="wps-vault-restore-title">
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="wpshadow_restore_local_backup" />
			<input type="hidden" id="wps-vault-restore-file" name="backup_file" value="" />
			<?php wp_nonce_field( 'wpshadow_restore_local_backup' ); ?>

			<div class="wps-settings-dialog__header">
				<h2 id="wps-vault-restore-title"><?php esc_html_e( 'Restore Local Backup', 'wpshadow' ); ?></h2>
			</div>

			<div class="wps-settings-dialog__body">
				<p><?php esc_html_e( 'You are about to restore this local backup:', 'wpshadow' ); ?></p>
				<p id="wps-vault-restore-description"><strong></strong></p>
				<p class="description"><?php esc_html_e( 'WPShadow will create a fresh safety backup first when possible. Restoring may overwrite files and database content.', 'wpshadow' ); ?></p>
			</div>

			<div class="wps-settings-dialog__footer" style="display:flex; gap:12px; justify-content:flex-end;">
				<button type="button" class="button" data-wps-vault-close><?php esc_html_e( 'Cancel', 'wpshadow' ); ?></button>
				<button type="submit" class="button button-primary"><?php esc_html_e( 'Restore Backup', 'wpshadow' ); ?></button>
			</div>
		</form>
	</dialog>

	<dialog id="wps-vault-delete-dialog" class="wps-settings-dialog" aria-labelledby="wps-vault-delete-title">
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="wpshadow_delete_local_backup" />
			<input type="hidden" id="wps-vault-delete-file" name="backup_file" value="" />
			<?php wp_nonce_field( 'wpshadow_delete_local_backup' ); ?>

			<div class="wps-settings-dialog__header">
				<h2 id="wps-vault-delete-title"><?php esc_html_e( 'Delete Backup', 'wpshadow' ); ?></h2>
			</div>

			<div class="wps-settings-dialog__body">
				<p><?php esc_html_e( 'You are about to permanently delete this backup:', 'wpshadow' ); ?></p>
				<p id="wps-vault-delete-description"><strong></strong></p>
				<p class="description"><?php esc_html_e( 'This action cannot be undone.', 'wpshadow' ); ?></p>
			</div>

			<div class="wps-settings-dialog__footer" style="display:flex; gap:12px; justify-content:flex-end;">
				<button type="button" class="button" data-wps-vault-close><?php esc_html_e( 'Cancel', 'wpshadow' ); ?></button>
				<button type="submit" class="button button-primary" style="background:#b32d2e;border-color:#b32d2e;"><?php esc_html_e( 'Delete Backup', 'wpshadow' ); ?></button>
			</div>
		</form>
	</dialog>

			<div class="wps-settings-rows">
				<div class="wps-settings-row">
					<div class="wps-settings-row-label">
						<label for="wps-backup-enabled"><?php esc_html_e( 'Enable Backups', 'wpshadow' ); ?></label>
						<p class="wps-settings-row-hint"><?php esc_html_e( 'Create a backup before applying any treatment. Strongly recommended.', 'wpshadow' ); ?></p>
					</div>
					<div class="wps-settings-row-control">
						<label class="wps-toggle-switch">
							<input
								type="checkbox"
								id="wps-backup-enabled"
								class="wps-auto-save"
								data-option="wpshadow_backup_enabled"
								data-type="bool"
								<?php checked( $get_bool( 'wpshadow_backup_enabled', true ) ); ?>
							/>
							<span class="wps-toggle-slider" aria-hidden="true"></span>
						</label>
						<span class="wps-save-status" aria-live="polite"></span>
					</div>
				</div>

				<div class="wps-settings-row">
					<div class="wps-settings-row-label">
						<label for="wps-backup-db"><?php esc_html_e( 'Include Database', 'wpshadow' ); ?></label>
						<p class="wps-settings-row-hint"><?php esc_html_e( 'Include a database dump in each Vault Lite backup.', 'wpshadow' ); ?></p>
					</div>
					<div class="wps-settings-row-control">
						<label class="wps-toggle-switch">
							<input
								type="checkbox"
								id="wps-backup-db"
								class="wps-auto-save"
								data-option="wpshadow_backup_include_database"
								data-type="bool"
								<?php checked( $get_bool( 'wpshadow_backup_include_database', true ) ); ?>
							/>
							<span class="wps-toggle-slider" aria-hidden="true"></span>
						</label>
						<span class="wps-save-status" aria-live="polite"></span>
					</div>
				</div>

				<div class="wps-settings-row">
					<div class="wps-settings-row-label">
						<label for="wps-backup-compress"><?php esc_html_e( 'Compress Backups', 'wpshadow' ); ?></label>
						<p class="wps-settings-row-hint"><?php esc_html_e( 'Compress backup archives to save disk space.', 'wpshadow' ); ?></p>
					</div>
					<div class="wps-settings-row-control">
						<label class="wps-toggle-switch">
							<input
								type="checkbox"
								id="wps-backup-compress"
								class="wps-auto-save"
								data-option="wpshadow_backup_compress"
								data-type="bool"
								<?php checked( $get_bool( 'wpshadow_backup_compress', true ) ); ?>
							/>
							<span class="wps-toggle-slider" aria-hidden="true"></span>
						</label>
						<span class="wps-save-status" aria-live="polite"></span>
					</div>
				</div>

				<div class="wps-settings-row">
					<div class="wps-settings-row-label">
						<label for="wps-backup-uploads"><?php esc_html_e( 'Include Uploads Folder', 'wpshadow' ); ?></label>
						<p class="wps-settings-row-hint"><?php esc_html_e( 'Back up the /uploads directory along with the rest of your site files.', 'wpshadow' ); ?></p>
					</div>
					<div class="wps-settings-row-control">
						<label class="wps-toggle-switch">
							<input
								type="checkbox"
								id="wps-backup-uploads"
								class="wps-auto-save"
								data-option="wpshadow_backup_include_uploads"
								data-type="bool"
								<?php checked( $get_bool( 'wpshadow_backup_include_uploads', true ) ); ?>
							/>
							<span class="wps-toggle-slider" aria-hidden="true"></span>
						</label>
						<span class="wps-save-status" aria-live="polite"></span>
					</div>
				</div>

				<div class="wps-settings-row">
					<div class="wps-settings-row-label">
						<label for="wps-backup-verify"><?php esc_html_e( 'Verify Backups', 'wpshadow' ); ?></label>
						<p class="wps-settings-row-hint"><?php esc_html_e( 'Verify the integrity of each backup after creation.', 'wpshadow' ); ?></p>
					</div>
					<div class="wps-settings-row-control">
						<label class="wps-toggle-switch">
							<input
								type="checkbox"
								id="wps-backup-verify"
								class="wps-auto-save"
								data-option="wpshadow_backup_verify"
								data-type="bool"
								<?php checked( $get_bool( 'wpshadow_backup_verify', true ) ); ?>
							/>
							<span class="wps-toggle-slider" aria-hidden="true"></span>
						</label>
						<span class="wps-save-status" aria-live="polite"></span>
					</div>
				</div>

				<div class="wps-settings-row">
					<div class="wps-settings-row-label">
						<label for="wps-treatment-backup-exclude-uploads"><?php esc_html_e( 'Exclude Uploads from Treatment Backups', 'wpshadow' ); ?></label>
						<p class="wps-settings-row-hint"><?php esc_html_e( 'When a backup is triggered automatically before a treatment, skip the /uploads folder. Treatments never modify uploaded media, so including it only adds unnecessary size.', 'wpshadow' ); ?></p>
					</div>
					<div class="wps-settings-row-control">
						<label class="wps-toggle-switch">
							<input
								type="checkbox"
								id="wps-treatment-backup-exclude-uploads"
								class="wps-auto-save"
								data-option="wpshadow_treatment_backup_exclude_uploads"
								data-type="bool"
								<?php checked( $get_bool( 'wpshadow_treatment_backup_exclude_uploads', true ) ); ?>
							/>
							<span class="wps-toggle-slider" aria-hidden="true"></span>
						</label>
						<span class="wps-save-status" aria-live="polite"></span>
					</div>
				</div>

				<div class="wps-settings-row">
					<div class="wps-settings-row-label">
						<label for="wps-treatment-backup-window"><?php esc_html_e( 'Treatment Backup Deduplication Window', 'wpshadow' ); ?></label>
						<p class="wps-settings-row-hint"><?php esc_html_e( 'If a backup already exists within this many minutes, skip creating another one before the next treatment. Prevents N treatments in a session from generating N identical archives.', 'wpshadow' ); ?></p>
					</div>
					<div class="wps-settings-row-control">
						<select
							id="wps-treatment-backup-window"
							class="wps-auto-save"
							data-option="wpshadow_treatment_backup_window"
							data-type="integer"
						>
							<?php
							$window_options = array( 15 => '15 minutes', 30 => '30 minutes', 60 => '1 hour (recommended)', 120 => '2 hours', 240 => '4 hours', 480 => '8 hours' );
							$current_window = $get_int( 'wpshadow_treatment_backup_window', 60 );
							foreach ( $window_options as $mins => $label ) :
								echo '<option value="' . esc_attr( $mins ) . '"' . selected( $current_window, $mins, false ) . '>' . esc_html( $label ) . '</option>';
							endforeach;
							?>
						</select>
						<span class="wps-save-status" aria-live="polite"></span>
					</div>
				</div>

				<div class="wps-settings-row">
					<div class="wps-settings-row-label">
						<label for="wps-backup-retention"><?php esc_html_e( 'Retention Period', 'wpshadow' ); ?></label>
						<p class="wps-settings-row-hint"><?php esc_html_e( 'Number of days to keep backup files before they are automatically deleted.', 'wpshadow' ); ?></p>
					</div>
					<div class="wps-settings-row-control">
						<select
							id="wps-backup-retention"
							class="wps-auto-save"
							data-option="wpshadow_backup_retention_days"
							data-type="integer"
						>
							<?php
							$retention_options = array( 1 => '1 day', 3 => '3 days', 7 => '7 days (recommended)', 14 => '14 days', 30 => '30 days', 60 => '60 days', 90 => '90 days' );
							$current_retention = $get_int( 'wpshadow_backup_retention_days', 7 );
							foreach ( $retention_options as $days => $label ) :
								echo '<option value="' . esc_attr( $days ) . '"' . selected( $current_retention, $days, false ) . '>' . esc_html( $label ) . '</option>';
							endforeach;
							?>
						</select>
						<span class="wps-save-status" aria-live="polite"></span>
					</div>
				</div>

				<div class="wps-settings-row">
					<div class="wps-settings-row-label">
						<label for="wps-backup-max-size"><?php esc_html_e( 'Maximum Total Size', 'wpshadow' ); ?></label>
						<p class="wps-settings-row-hint"><?php esc_html_e( 'Maximum total disk space (MB) that all Vault Light backups may occupy. Oldest backups are pruned when exceeded.', 'wpshadow' ); ?></p>
					</div>
					<div class="wps-settings-row-control">
						<div class="wps-input-with-unit">
							<input
								type="number"
								id="wps-backup-max-size"
								class="wps-auto-save small-text"
								data-option="wpshadow_backup_max_size_mb"
								data-type="integer"
								min="50"
								max="10000"
								step="50"
								value="<?php echo esc_attr( $get_int( 'wpshadow_backup_max_size_mb', 500 ) ); ?>"
							/>
							<span class="wps-input-unit">MB</span>
						</div>
						<span class="wps-save-status" aria-live="polite"></span>
					</div>
				</div>
			</div>
		</div>

		<div class="wps-settings-section">
			<h2 class="wps-settings-section-title"><?php esc_html_e( 'Scheduled Backups', 'wpshadow' ); ?></h2>
			<p class="wps-settings-section-desc"><?php esc_html_e( 'Run regular automatic backups on a schedule, independent of treatment activity.', 'wpshadow' ); ?></p>

			<div class="wps-settings-rows">
				<div class="wps-settings-row">
					<div class="wps-settings-row-label">
						<label for="wps-backup-schedule"><?php esc_html_e( 'Enable Scheduled Backups', 'wpshadow' ); ?></label>
						<p class="wps-settings-row-hint"><?php esc_html_e( 'Run automatic backups on a regular schedule.', 'wpshadow' ); ?></p>
					</div>
					<div class="wps-settings-row-control">
						<label class="wps-toggle-switch">
							<input
								type="checkbox"
								id="wps-backup-schedule"
								class="wps-auto-save"
								data-option="wpshadow_backup_schedule_enabled"
								data-type="bool"
								<?php checked( $get_bool( 'wpshadow_backup_schedule_enabled', false ) ); ?>
							/>
							<span class="wps-toggle-slider" aria-hidden="true"></span>
						</label>
						<span class="wps-save-status" aria-live="polite"></span>
					</div>
				</div>

				<div class="wps-settings-row">
					<div class="wps-settings-row-label">
						<label for="wps-backup-freq"><?php esc_html_e( 'Backup Frequency', 'wpshadow' ); ?></label>
						<p class="wps-settings-row-hint"><?php esc_html_e( 'How often to create a scheduled backup.', 'wpshadow' ); ?></p>
					</div>
					<div class="wps-settings-row-control">
						<select
							id="wps-backup-freq"
							class="wps-auto-save"
							data-option="wpshadow_backup_schedule_frequency"
							data-type="string"
						>
							<?php
							$backup_freqs   = array( 'daily' => __( 'Daily (recommended)', 'wpshadow' ), 'weekly' => __( 'Weekly', 'wpshadow' ), 'monthly' => __( 'Monthly', 'wpshadow' ) );
							$current_bkfreq = $get_str( 'wpshadow_backup_schedule_frequency', 'daily' );
							foreach ( $backup_freqs as $bfk => $bfl ) :
								echo '<option value="' . esc_attr( $bfk ) . '"' . selected( $current_bkfreq, $bfk, false ) . '>' . esc_html( $bfl ) . '</option>';
							endforeach;
							?>
						</select>
						<span class="wps-save-status" aria-live="polite"></span>
					</div>
				</div>

				<div class="wps-settings-row">
					<div class="wps-settings-row-label">
						<label for="wps-backup-time"><?php esc_html_e( 'Backup Time', 'wpshadow' ); ?></label>
						<p class="wps-settings-row-hint"><?php esc_html_e( 'The time of day (24-hour) when scheduled backups run. Choose a low-traffic period.', 'wpshadow' ); ?></p>
					</div>
					<div class="wps-settings-row-control">
						<input
							type="time"
							id="wps-backup-time"
							class="wps-auto-save"
							data-option="wpshadow_backup_schedule_time"
							data-type="string"
							value="<?php echo esc_attr( $get_str( 'wpshadow_backup_schedule_time', '02:00' ) ); ?>"
						/>
						<span class="wps-save-status" aria-live="polite"></span>
					</div>
				</div>
			</div>
		</div>

	</div>
	<?php endif; ?>
</div>
