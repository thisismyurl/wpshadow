<?php
/**
 * Settings Page
 *
 * Full settings UI for WPShadow: general options, scan schedule,
 * and per-diagnostic toggles and frequency.
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

// ── Tab detection ─────────────────────────────────────────────────────────
$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'general'; // phpcs:ignore WordPress.Security.NonceVerification
$valid_tabs = array( 'general', 'scanning', 'backups', 'accessibility' );
if ( ! in_array( $active_tab, $valid_tabs, true ) ) {
	$active_tab = 'general';
}

// ── Retrieve option values ─────────────────────────────────────────────────
$scan_config = class_exists( '\WPShadow\Admin\Pages\Scan_Frequency_Manager' )
	? \WPShadow\Admin\Pages\Scan_Frequency_Manager::get_scan_config()
	: get_option( 'wpshadow_scan_frequency_settings', array() );

if ( ! is_array( $scan_config ) ) {
	$scan_config = array();
}

$scan_config = wp_parse_args(
	$scan_config,
	array(
		'frequency'             => 'daily',
		'scan_time'             => '02:00',
		'run_diagnostics'       => true,
		'run_treatments'        => true,
		'scan_on_plugin_update' => true,
		'scan_on_theme_update'  => true,
	)
);



$backup_status = class_exists( '\WPShadow\Guardian\Backup_Manager' )
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

$next_backup_display = class_exists( '\WPShadow\Guardian\Backup_Scheduler' )
	? \WPShadow\Guardian\Backup_Scheduler::get_next_scheduled_display()
	: __( 'Scheduler unavailable', 'wpshadow' );

/**
 * Helper: read a WPShadow option using the shared settings registry when available.
 *
 * @param  string $option  Option name.
 * @param  mixed  $default Default value.
 * @return mixed
 */
function wpshadow_settings_value( string $option, $default = '' ) {
	if ( class_exists( '\\WPShadow\\Core\\Settings_Registry' ) ) {
		return \WPShadow\Core\Settings_Registry::get( $option, $default );
	}

	$key = 0 === strpos( $option, 'wpshadow_' ) ? $option : 'wpshadow_' . $option;
	return get_option( $key, $default );
}

/**
 * Helper: checked/selected state for a boolean option.
 *
 * @param  string $option  Option name (with or without wpshadow_ prefix).
 * @param  bool   $default Default value.
 * @return bool
 */
function wpshadow_settings_bool( string $option, bool $default = true ): bool {
	return (bool) wpshadow_settings_value( $option, $default );
}

/**
 * Helper: string option value.
 *
 * @param  string $option  Option name.
 * @param  string $default Default value.
 * @return string
 */
function wpshadow_settings_str( string $option, string $default = '' ): string {
	return (string) wpshadow_settings_value( $option, $default );
}

/**
 * Helper: integer option value.
 *
 * @param  string $option  Option name.
 * @param  int    $default Default value.
 * @return int
 */
function wpshadow_settings_int( string $option, int $default = 0 ): int {
	return (int) wpshadow_settings_value( $option, $default );
}

// Base settings page URL.
$settings_url = admin_url( 'admin.php?page=wpshadow-settings' );

?>
<div class="wrap wps-settings-page">

<?php
wpshadow_render_page_header(
	__( 'Settings', 'wpshadow' ),
	__( 'Configure WPShadow to match your workflow and site requirements.', 'wpshadow' ),
	'dashicons-admin-settings'
);
?>

	<!-- ── Global save indicator ──────────────────────────────────────────── -->
	<div id="wps-settings-notice" class="wps-settings-notice" aria-live="polite"></div>

	<!-- ── Tab navigation ─────────────────────────────────────────────────── -->
	<nav class="wps-settings-tabs" aria-label="<?php esc_attr_e( 'Settings sections', 'wpshadow' ); ?>">
		<?php
		$tabs = array(
			'general'       => array( 'label' => __( 'General', 'wpshadow' ),       'icon' => 'dashicons-admin-generic' ),
			'scanning'      => array( 'label' => __( 'Scanning', 'wpshadow' ),      'icon' => 'dashicons-search' ),
			'backups'       => array( 'label' => __( 'Backups', 'wpshadow' ),       'icon' => 'dashicons-backup' ),
			'accessibility' => array( 'label' => __( 'Accessibility', 'wpshadow' ), 'icon' => 'dashicons-universal-access-alt' ),
		);
		foreach ( $tabs as $tab_key => $tab ) :
			$href   = add_query_arg( 'tab', $tab_key, $settings_url );
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

	<!-- ═══════════════════════════════════════════════════════════════════════
	     TAB: GENERAL
	     ═══════════════════════════════════════════════════════════════════════ -->
	<?php if ( 'general' === $active_tab ) : ?>
	<div class="wps-settings-body">

		<div class="wps-settings-section">
			<h2 class="wps-settings-section-title"><?php esc_html_e( 'Caching', 'wpshadow' ); ?></h2>
			<p class="wps-settings-section-desc"><?php esc_html_e( 'Control how long diagnostic results are kept to avoid re-running expensive checks every visit.', 'wpshadow' ); ?></p>

			<div class="wps-settings-rows">

				<div class="wps-settings-row">
					<div class="wps-settings-row-label">
						<label for="wps-cache-enabled"><?php esc_html_e( 'Enable Result Caching', 'wpshadow' ); ?></label>
						<p class="wps-settings-row-hint"><?php esc_html_e( 'Cache diagnostic results to speed up page loads. Disable only when actively debugging.', 'wpshadow' ); ?></p>
					</div>
					<div class="wps-settings-row-control">
						<label class="wps-toggle-switch">
							<input
								type="checkbox"
								id="wps-cache-enabled"
								class="wps-auto-save"
								data-option="wpshadow_cache_enabled"
								data-type="bool"
								<?php checked( wpshadow_settings_bool( 'wpshadow_cache_enabled', true ) ); ?>
							/>
							<span class="wps-toggle-slider" aria-hidden="true"></span>
						</label>
						<span class="wps-save-status" aria-live="polite"></span>
					</div>
				</div>

				<div class="wps-settings-row">
					<div class="wps-settings-row-label">
						<label for="wps-cache-duration"><?php esc_html_e( 'Cache Duration', 'wpshadow' ); ?></label>
						<p class="wps-settings-row-hint"><?php esc_html_e( 'How long to keep cached diagnostic results before re-running checks.', 'wpshadow' ); ?></p>
					</div>
					<div class="wps-settings-row-control">
						<select
							id="wps-cache-duration"
							class="wps-auto-save"
							data-option="wpshadow_cache_duration"
							data-type="integer"
						>
							<?php
							$durations = array(
								3600   => __( '1 hour', 'wpshadow' ),
								21600  => __( '6 hours', 'wpshadow' ),
								43200  => __( '12 hours', 'wpshadow' ),
								86400  => __( '24 hours (recommended)', 'wpshadow' ),
								172800 => __( '48 hours', 'wpshadow' ),
							);
							$current_dur = wpshadow_settings_int( 'wpshadow_cache_duration', 86400 );
							foreach ( $durations as $secs => $label ) :
								echo '<option value="' . esc_attr( $secs ) . '"' . selected( $current_dur, $secs, false ) . '>' . esc_html( $label ) . '</option>';
							endforeach;
							?>
						</select>
						<span class="wps-save-status" aria-live="polite"></span>
					</div>
				</div>

			</div><!-- .wps-settings-rows -->
		</div><!-- .wps-settings-section -->

		<div class="wps-settings-section">
			<h2 class="wps-settings-section-title"><?php esc_html_e( 'WordPress File Editors', 'wpshadow' ); ?></h2>
			<p class="wps-settings-section-desc"><?php esc_html_e( 'Control access to the built-in WordPress theme and plugin file editors.', 'wpshadow' ); ?></p>

			<?php $wp_file_edit_locked = ( defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT ) || ( defined( 'DISALLOW_FILE_MODS' ) && DISALLOW_FILE_MODS ); ?>
			<div class="wps-settings-rows">

				<div class="wps-settings-row">
					<div class="wps-settings-row-label">
						<label for="wps-theme-editor"><?php esc_html_e( 'Enable Theme File Editor', 'wpshadow' ); ?></label>
						<p class="wps-settings-row-hint">
							<?php if ( $wp_file_edit_locked ) : ?>
								<?php esc_html_e( 'Disabled by WordPress. To re-enable, remove DISALLOW_FILE_EDIT and DISALLOW_FILE_MODS from wp-config.php.', 'wpshadow' ); ?>
							<?php else : ?>
								<?php esc_html_e( 'Allow admins to edit theme files directly from the WordPress admin. Disable to reduce attack surface.', 'wpshadow' ); ?>
							<?php endif; ?>
						</p>
					</div>
					<div class="wps-settings-row-control">
						<label class="wps-toggle-switch">
							<input
								type="checkbox"
								id="wps-theme-editor"
								<?php if ( $wp_file_edit_locked ) : ?>
								disabled
								<?php else : ?>
								class="wps-auto-save"
								data-option="wpshadow_enable_theme_file_editor"
								data-type="bool"
								<?php checked( wpshadow_settings_bool( 'wpshadow_enable_theme_file_editor', true ) ); ?>
								<?php endif; ?>
							/>
							<span class="wps-toggle-slider" aria-hidden="true"></span>
						</label>
						<span class="wps-save-status" aria-live="polite"></span>
					</div>
				</div>

				<div class="wps-settings-row">
					<div class="wps-settings-row-label">
						<label for="wps-plugin-editor"><?php esc_html_e( 'Enable Plugin File Editor', 'wpshadow' ); ?></label>
						<p class="wps-settings-row-hint">
							<?php if ( $wp_file_edit_locked ) : ?>
								<?php esc_html_e( 'Disabled by WordPress. To re-enable, remove DISALLOW_FILE_EDIT and DISALLOW_FILE_MODS from wp-config.php.', 'wpshadow' ); ?>
							<?php else : ?>
								<?php esc_html_e( 'Allow admins to edit plugin files directly from the WordPress admin. Disable to reduce attack surface.', 'wpshadow' ); ?>
							<?php endif; ?>
						</p>
					</div>
					<div class="wps-settings-row-control">
						<label class="wps-toggle-switch">
							<input
								type="checkbox"
								id="wps-plugin-editor"
								<?php if ( $wp_file_edit_locked ) : ?>
								disabled
								<?php else : ?>
								class="wps-auto-save"
								data-option="wpshadow_enable_plugin_file_editor"
								data-type="bool"
								<?php checked( wpshadow_settings_bool( 'wpshadow_enable_plugin_file_editor', true ) ); ?>
								<?php endif; ?>
							/>
							<span class="wps-toggle-slider" aria-hidden="true"></span>
						</label>
						<span class="wps-save-status" aria-live="polite"></span>
					</div>
				</div>

			</div><!-- .wps-settings-rows -->
		</div><!-- .wps-settings-section -->

		<div class="wps-settings-section">
			<h2 class="wps-settings-section-title"><?php esc_html_e( 'Debug', 'wpshadow' ); ?></h2>
			<p class="wps-settings-section-desc"><?php esc_html_e( 'Verbose logging for diagnosing plugin issues. Keep disabled in production.', 'wpshadow' ); ?></p>

			<div class="wps-settings-rows">

				<div class="wps-settings-row">
					<div class="wps-settings-row-label">
						<label for="wps-debug-mode"><?php esc_html_e( 'Debug Mode', 'wpshadow' ); ?></label>
						<p class="wps-settings-row-hint"><?php esc_html_e( 'Enable verbose logging of all WPShadow operations. Useful when reporting issues.', 'wpshadow' ); ?></p>
					</div>
					<div class="wps-settings-row-control">
						<label class="wps-toggle-switch">
							<input
								type="checkbox"
								id="wps-debug-mode"
								class="wps-auto-save"
								data-option="wpshadow_debug_mode"
								data-type="bool"
								<?php checked( wpshadow_settings_bool( 'wpshadow_debug_mode', false ) ); ?>
							/>
							<span class="wps-toggle-slider" aria-hidden="true"></span>
						</label>
						<span class="wps-save-status" aria-live="polite"></span>
					</div>
				</div>

			</div><!-- .wps-settings-rows -->
		</div><!-- .wps-settings-section -->

	</div><!-- .wps-settings-body (general) -->
	<?php endif; ?>

	<!-- ═══════════════════════════════════════════════════════════════════════
	     TAB: SCANNING
	     ═══════════════════════════════════════════════════════════════════════ -->
	<?php if ( 'scanning' === $active_tab ) : ?>
	<div class="wps-settings-body">

		<div class="wps-settings-section">
			<h2 class="wps-settings-section-title"><?php esc_html_e( 'Automatic Scan Schedule', 'wpshadow' ); ?></h2>
			<p class="wps-settings-section-desc"><?php esc_html_e( 'Control how often WPShadow runs diagnostic scans in the background.', 'wpshadow' ); ?></p>

			<div class="wps-settings-rows">

				<div class="wps-settings-row">
					<div class="wps-settings-row-label">
						<label for="wps-scan-frequency"><?php esc_html_e( 'Scan Frequency', 'wpshadow' ); ?></label>
						<p class="wps-settings-row-hint"><?php esc_html_e( 'How often to run the automatic background scan.', 'wpshadow' ); ?></p>
					</div>
					<div class="wps-settings-row-control">
						<select
							id="wps-scan-frequency"
							class="wps-save-scan-config"
							data-key="frequency"
						>
							<option value="manual"  <?php selected( $scan_config['frequency'], 'manual' ); ?>><?php esc_html_e( 'Manual only', 'wpshadow' ); ?></option>
							<option value="hourly"  <?php selected( $scan_config['frequency'], 'hourly' ); ?>><?php esc_html_e( 'Hourly', 'wpshadow' ); ?></option>
							<option value="daily"   <?php selected( $scan_config['frequency'], 'daily' ); ?>><?php esc_html_e( 'Daily (recommended)', 'wpshadow' ); ?></option>
							<option value="weekly"  <?php selected( $scan_config['frequency'], 'weekly' ); ?>><?php esc_html_e( 'Weekly', 'wpshadow' ); ?></option>
						</select>
						<span class="wps-save-status" aria-live="polite"></span>
					</div>
				</div>

				<div class="wps-settings-row" id="wps-scan-time-row">
					<div class="wps-settings-row-label">
						<label for="wps-scan-time"><?php esc_html_e( 'Preferred Scan Time', 'wpshadow' ); ?></label>
						<p class="wps-settings-row-hint"><?php esc_html_e( 'The time of day (24-hour) when automatic scans should run. Choose a low-traffic period.', 'wpshadow' ); ?></p>
					</div>
					<div class="wps-settings-row-control">
						<input
							type="time"
							id="wps-scan-time"
							class="wps-save-scan-config"
							data-key="scan_time"
							value="<?php echo esc_attr( $scan_config['scan_time'] ); ?>"
						/>
						<span class="wps-save-status" aria-live="polite"></span>
					</div>
				</div>

			</div><!-- .wps-settings-rows -->
		</div><!-- .wps-settings-section -->

		<div class="wps-settings-section">
			<h2 class="wps-settings-section-title"><?php esc_html_e( 'Scan Behaviour', 'wpshadow' ); ?></h2>
			<p class="wps-settings-section-desc"><?php esc_html_e( 'Choose what happens when a scan runs.', 'wpshadow' ); ?></p>

			<div class="wps-settings-rows">

				<div class="wps-settings-row">
					<div class="wps-settings-row-label">
						<label for="wps-run-diagnostics"><?php esc_html_e( 'Run Diagnostics', 'wpshadow' ); ?></label>
						<p class="wps-settings-row-hint"><?php esc_html_e( 'Execute enabled diagnostic checks during each scan. Strongly recommended.', 'wpshadow' ); ?></p>
					</div>
					<div class="wps-settings-row-control">
						<label class="wps-toggle-switch">
							<input
								type="checkbox"
								id="wps-run-diagnostics"
								class="wps-save-scan-config"
								data-key="run_diagnostics"
								data-type="bool"
								<?php checked( ! empty( $scan_config['run_diagnostics'] ) ); ?>
							/>
							<span class="wps-toggle-slider" aria-hidden="true"></span>
						</label>
						<span class="wps-save-status" aria-live="polite"></span>
					</div>
				</div>

				<div class="wps-settings-row">
					<div class="wps-settings-row-label">
						<label for="wps-run-treatments"><?php esc_html_e( 'Auto-Apply Safe Treatments', 'wpshadow' ); ?></label>
						<p class="wps-settings-row-hint"><?php esc_html_e( 'Automatically apply treatments that are marked safe and have admin approval. Requires backups enabled.', 'wpshadow' ); ?></p>
					</div>
					<div class="wps-settings-row-control">
						<label class="wps-toggle-switch">
							<input
								type="checkbox"
								id="wps-run-treatments"
								class="wps-save-scan-config"
								data-key="run_treatments"
								data-type="bool"
								<?php checked( ! empty( $scan_config['run_treatments'] ) ); ?>
							/>
							<span class="wps-toggle-slider" aria-hidden="true"></span>
						</label>
						<span class="wps-save-status" aria-live="polite"></span>
					</div>
				</div>

				<div class="wps-settings-row">
					<div class="wps-settings-row-label">
						<label for="wps-scan-plugin-update"><?php esc_html_e( 'Scan After Plugin Update', 'wpshadow' ); ?></label>
						<p class="wps-settings-row-hint"><?php esc_html_e( 'Trigger a scan automatically when a plugin is updated, activated, or deactivated.', 'wpshadow' ); ?></p>
					</div>
					<div class="wps-settings-row-control">
						<label class="wps-toggle-switch">
							<input
								type="checkbox"
								id="wps-scan-plugin-update"
								class="wps-save-scan-config"
								data-key="scan_on_plugin_update"
								data-type="bool"
								<?php checked( ! empty( $scan_config['scan_on_plugin_update'] ) ); ?>
							/>
							<span class="wps-toggle-slider" aria-hidden="true"></span>
						</label>
						<span class="wps-save-status" aria-live="polite"></span>
					</div>
				</div>

				<div class="wps-settings-row">
					<div class="wps-settings-row-label">
						<label for="wps-scan-theme-update"><?php esc_html_e( 'Scan After Theme Update', 'wpshadow' ); ?></label>
						<p class="wps-settings-row-hint"><?php esc_html_e( 'Trigger a scan automatically when a theme is updated, activated, or switched.', 'wpshadow' ); ?></p>
					</div>
					<div class="wps-settings-row-control">
						<label class="wps-toggle-switch">
							<input
								type="checkbox"
								id="wps-scan-theme-update"
								class="wps-save-scan-config"
								data-key="scan_on_theme_update"
								data-type="bool"
								<?php checked( ! empty( $scan_config['scan_on_theme_update'] ) ); ?>
							/>
							<span class="wps-toggle-slider" aria-hidden="true"></span>
						</label>
						<span class="wps-save-status" aria-live="polite"></span>
					</div>
				</div>

			</div><!-- .wps-settings-rows -->
		</div><!-- .wps-settings-section -->

	</div><!-- .wps-settings-body (scanning) -->
	<?php endif; ?>

	<!-- ═══════════════════════════════════════════════════════════════════════
	     TAB: BACKUPS
	     ═══════════════════════════════════════════════════════════════════════ -->
	<?php if ( 'backups' === $active_tab ) : ?>
	<div class="wps-settings-body">

		<?php if ( isset( $_GET['wpshadow_backup_run'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="notice <?php echo 'success' === sanitize_key( wp_unslash( $_GET['wpshadow_backup_run'] ) ) ? 'notice-success' : 'notice-error'; ?>">
				<p>
					<?php if ( 'success' === sanitize_key( wp_unslash( $_GET['wpshadow_backup_run'] ) ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
						<?php esc_html_e( 'Local backup created successfully.', 'wpshadow' ); ?>
					<?php else : ?>
						<?php esc_html_e( 'Local backup could not be created.', 'wpshadow' ); ?>
					<?php endif; ?>
				</p>
			</div>
		<?php endif; ?>

		<div class="wps-settings-section">
			<h2 class="wps-settings-section-title"><?php esc_html_e( 'Local Backup Status', 'wpshadow' ); ?></h2>
			<p class="wps-settings-section-desc"><?php esc_html_e( 'Vault Light stores local-only restore points on this server. No cloud tools are used in this lite version.', 'wpshadow' ); ?></p>

			<div class="wps-settings-rows">
				<div class="wps-settings-row">
					<div class="wps-settings-row-label">
						<label><?php esc_html_e( 'Stored Backups', 'wpshadow' ); ?></label>
						<p class="wps-settings-row-hint"><?php echo esc_html( sprintf( _n( '%d local backup currently stored.', '%d local backups currently stored.', (int) $backup_status['count'], 'wpshadow' ), (int) $backup_status['count'] ) ); ?></p>
					</div>
					<div class="wps-settings-row-control">
						<strong><?php echo esc_html( (string) $backup_status['count'] ); ?></strong>
					</div>
				</div>

				<div class="wps-settings-row">
					<div class="wps-settings-row-label">
						<label><?php esc_html_e( 'Disk Usage', 'wpshadow' ); ?></label>
						<p class="wps-settings-row-hint"><?php esc_html_e( 'Combined size of all local backup archives currently retained.', 'wpshadow' ); ?></p>
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
						<p class="wps-settings-row-hint"><?php esc_html_e( 'These archives remain on the local server only, inside a secret randomized Vault Lite directory.', 'wpshadow' ); ?></p>
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
			</div><!-- .wps-settings-rows -->
		</div><!-- .wps-settings-section -->

		<div class="wps-settings-section">
			<h2 class="wps-settings-section-title"><?php esc_html_e( 'Vault Light Backups', 'wpshadow' ); ?></h2>
			<p class="wps-settings-section-desc"><?php esc_html_e( 'Lightweight backups created automatically before each treatment is applied.', 'wpshadow' ); ?></p>

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
								<?php checked( wpshadow_settings_bool( 'wpshadow_backup_enabled', true ) ); ?>
							/>
							<span class="wps-toggle-slider" aria-hidden="true"></span>
						</label>
						<span class="wps-save-status" aria-live="polite"></span>
					</div>
				</div>

				<div class="wps-settings-row">
					<div class="wps-settings-row-label">
						<label for="wps-backup-db"><?php esc_html_e( 'Include Database', 'wpshadow' ); ?></label>
						<p class="wps-settings-row-hint"><?php esc_html_e( 'Include a database dump in each Vault Light backup.', 'wpshadow' ); ?></p>
					</div>
					<div class="wps-settings-row-control">
						<label class="wps-toggle-switch">
							<input
								type="checkbox"
								id="wps-backup-db"
								class="wps-auto-save"
								data-option="wpshadow_backup_include_database"
								data-type="bool"
								<?php checked( wpshadow_settings_bool( 'wpshadow_backup_include_database', true ) ); ?>
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
								<?php checked( wpshadow_settings_bool( 'wpshadow_backup_compress', true ) ); ?>
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
								<?php checked( wpshadow_settings_bool( 'wpshadow_backup_include_uploads', true ) ); ?>
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
								<?php checked( wpshadow_settings_bool( 'wpshadow_backup_verify', true ) ); ?>
							/>
							<span class="wps-toggle-slider" aria-hidden="true"></span>
						</label>
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
							$current_retention = wpshadow_settings_int( 'wpshadow_backup_retention_days', 7 );
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
								value="<?php echo esc_attr( wpshadow_settings_int( 'wpshadow_backup_max_size_mb', 500 ) ); ?>"
							/>
							<span class="wps-input-unit">MB</span>
						</div>
						<span class="wps-save-status" aria-live="polite"></span>
					</div>
				</div>

			</div><!-- .wps-settings-rows -->
		</div><!-- .wps-settings-section -->

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
								<?php checked( wpshadow_settings_bool( 'wpshadow_backup_schedule_enabled', false ) ); ?>
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
							$backup_freqs    = array( 'daily' => __( 'Daily (recommended)', 'wpshadow' ), 'weekly' => __( 'Weekly', 'wpshadow' ), 'monthly' => __( 'Monthly', 'wpshadow' ) );
							$current_bkfreq  = wpshadow_settings_str( 'wpshadow_backup_schedule_frequency', 'daily' );
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
							value="<?php echo esc_attr( wpshadow_settings_str( 'wpshadow_backup_schedule_time', '02:00' ) ); ?>"
						/>
						<span class="wps-save-status" aria-live="polite"></span>
					</div>
				</div>

			</div><!-- .wps-settings-rows -->
		</div><!-- .wps-settings-section -->

	</div><!-- .wps-settings-body (backups) -->
	<?php endif; ?>

	<!-- ═══════════════════════════════════════════════════════════════════════
	     TAB: ACCESSIBILITY
	     ═══════════════════════════════════════════════════════════════════════ -->
	<?php if ( 'accessibility' === $active_tab ) : ?>
	<div class="wps-settings-body">

		<?php
		$current_font_choice = wpshadow_settings_str( 'wpshadow_admin_font_family', 'default' );
		$current_font_scale  = sprintf( '%.2F', (float) get_option( 'wpshadow_font_size_multiplier', 1.0 ) );
		$current_focus_style = wpshadow_settings_str( 'wpshadow_focus_indicators', 'standard' );
		?>

		<div class="wps-settings-section">
			<h2 class="wps-settings-section-title"><?php esc_html_e( 'Reading Comfort & Focus', 'wpshadow' ); ?></h2>
			<p class="wps-settings-section-desc"><?php esc_html_e( 'These options can be applied across WordPress admin screens, not just WPShadow. They are optional comfort aids — what helps one person read or focus more easily may not help another.', 'wpshadow' ); ?></p>

			<div class="notice notice-info inline">
				<p><?php esc_html_e( 'The focus-friendly font option uses a local readable font stack inspired by styles some ADHD or dyslexic users report as easier to track. No external font files are loaded.', 'wpshadow' ); ?></p>
			</div>

			<div class="wps-settings-rows">

				<div class="wps-settings-row">
					<div class="wps-settings-row-label">
						<label for="wps-admin-font-family"><?php esc_html_e( 'Admin Reading Font', 'wpshadow' ); ?></label>
						<p class="wps-settings-row-hint"><?php esc_html_e( 'Choose a more readable font style for the WordPress admin. The focus-friendly option uses a Lexend / Atkinson-style stack with roomier letter spacing.', 'wpshadow' ); ?></p>
					</div>
					<div class="wps-settings-row-control">
						<select
							id="wps-admin-font-family"
							class="wps-auto-save"
							data-option="wpshadow_admin_font_family"
							data-type="string"
						>
							<option value="default" <?php selected( $current_font_choice, 'default' ); ?>><?php esc_html_e( 'WordPress default', 'wpshadow' ); ?></option>
							<option value="readable" <?php selected( $current_font_choice, 'readable' ); ?>><?php esc_html_e( 'Readable sans-serif', 'wpshadow' ); ?></option>
							<option value="lexend" <?php selected( $current_font_choice, 'lexend' ); ?>><?php esc_html_e( 'Focus-friendly reading stack', 'wpshadow' ); ?></option>
						</select>
						<span class="wps-save-status" aria-live="polite"></span>
					</div>
				</div>

				<div class="wps-settings-row">
					<div class="wps-settings-row-label">
						<label for="wps-font-size-multiplier"><?php esc_html_e( 'Text Size', 'wpshadow' ); ?></label>
						<p class="wps-settings-row-hint"><?php esc_html_e( 'Increase the text size used across the WordPress admin to reduce strain and improve readability.', 'wpshadow' ); ?></p>
					</div>
					<div class="wps-settings-row-control">
						<select
							id="wps-font-size-multiplier"
							class="wps-auto-save"
							data-option="wpshadow_font_size_multiplier"
							data-type="string"
						>
							<option value="1.00" <?php selected( $current_font_scale, '1.00' ); ?>><?php esc_html_e( '100% (default)', 'wpshadow' ); ?></option>
							<option value="1.10" <?php selected( $current_font_scale, '1.10' ); ?>><?php esc_html_e( '110%', 'wpshadow' ); ?></option>
							<option value="1.25" <?php selected( $current_font_scale, '1.25' ); ?>><?php esc_html_e( '125%', 'wpshadow' ); ?></option>
							<option value="1.40" <?php selected( $current_font_scale, '1.40' ); ?>><?php esc_html_e( '140%', 'wpshadow' ); ?></option>
						</select>
						<span class="wps-save-status" aria-live="polite"></span>
					</div>
				</div>

				<div class="wps-settings-row">
					<div class="wps-settings-row-label">
						<label for="wps-focus-indicators"><?php esc_html_e( 'Focus Indicator Strength', 'wpshadow' ); ?></label>
						<p class="wps-settings-row-hint"><?php esc_html_e( 'Make keyboard focus outlines easier to see when tabbing through buttons, links, and form fields.', 'wpshadow' ); ?></p>
					</div>
					<div class="wps-settings-row-control">
						<select
							id="wps-focus-indicators"
							class="wps-auto-save"
							data-option="wpshadow_focus_indicators"
							data-type="string"
						>
							<option value="standard" <?php selected( $current_focus_style, 'standard' ); ?>><?php esc_html_e( 'Standard', 'wpshadow' ); ?></option>
							<option value="enhanced" <?php selected( $current_focus_style, 'enhanced' ); ?>><?php esc_html_e( 'Enhanced', 'wpshadow' ); ?></option>
							<option value="maximum" <?php selected( $current_focus_style, 'maximum' ); ?>><?php esc_html_e( 'Maximum', 'wpshadow' ); ?></option>
						</select>
						<span class="wps-save-status" aria-live="polite"></span>
					</div>
				</div>

				<div class="wps-settings-row">
					<div class="wps-settings-row-label">
						<label for="wps-high-contrast-mode"><?php esc_html_e( 'High Contrast Mode', 'wpshadow' ); ?></label>
						<p class="wps-settings-row-hint"><?php esc_html_e( 'Increase contrast in admin text, controls, and notices to improve visibility.', 'wpshadow' ); ?></p>
					</div>
					<div class="wps-settings-row-control">
						<label class="wps-toggle-switch">
							<input
								type="checkbox"
								id="wps-high-contrast-mode"
								class="wps-auto-save"
								data-option="wpshadow_high_contrast_mode"
								data-type="bool"
								<?php checked( wpshadow_settings_bool( 'wpshadow_high_contrast_mode', false ) ); ?>
							/>
							<span class="wps-toggle-slider" aria-hidden="true"></span>
						</label>
						<span class="wps-save-status" aria-live="polite"></span>
					</div>
				</div>

				<div class="wps-settings-row">
					<div class="wps-settings-row-label">
						<label for="wps-reduce-motion"><?php esc_html_e( 'Reduce Motion', 'wpshadow' ); ?></label>
						<p class="wps-settings-row-hint"><?php esc_html_e( 'Reduce animations and transitions in the WordPress admin for people who are motion-sensitive or who focus better with calmer interfaces.', 'wpshadow' ); ?></p>
					</div>
					<div class="wps-settings-row-control">
						<label class="wps-toggle-switch">
							<input
								type="checkbox"
								id="wps-reduce-motion"
								class="wps-auto-save"
								data-option="wpshadow_reduce_motion"
								data-type="bool"
								<?php checked( wpshadow_settings_bool( 'wpshadow_reduce_motion', false ) ); ?>
							/>
							<span class="wps-toggle-slider" aria-hidden="true"></span>
						</label>
						<span class="wps-save-status" aria-live="polite"></span>
					</div>
				</div>

			</div><!-- .wps-settings-rows -->
		</div><!-- .wps-settings-section -->

		<div class="wps-settings-section">
			<h2 class="wps-settings-section-title"><?php esc_html_e( 'Clarity & Inclusion', 'wpshadow' ); ?></h2>
			<p class="wps-settings-section-desc"><?php esc_html_e( 'Accessibility is broader than one setting or one diagnosis. These options aim to reduce friction, improve clarity, and give you a calmer, more usable admin experience.', 'wpshadow' ); ?></p>

			<div class="wps-settings-rows">
				<div class="wps-settings-row">
					<div class="wps-settings-row-label">
						<label for="wps-screen-reader-optimization"><?php esc_html_e( 'Screen Reader Friendly Enhancements', 'wpshadow' ); ?></label>
						<p class="wps-settings-row-hint"><?php esc_html_e( 'Enhance skip-focus visibility and a few admin feedback patterns to work more smoothly with assistive technology.', 'wpshadow' ); ?></p>
					</div>
					<div class="wps-settings-row-control">
						<label class="wps-toggle-switch">
							<input
								type="checkbox"
								id="wps-screen-reader-optimization"
								class="wps-auto-save"
								data-option="wpshadow_screen_reader_optimization"
								data-type="bool"
								<?php checked( wpshadow_settings_bool( 'wpshadow_screen_reader_optimization', false ) ); ?>
							/>
							<span class="wps-toggle-slider" aria-hidden="true"></span>
						</label>
						<span class="wps-save-status" aria-live="polite"></span>
					</div>
				</div>

				<div class="wps-settings-row">
					<div class="wps-settings-row-label">
						<label for="wps-simplified-ui"><?php esc_html_e( 'Simplified Admin Feel', 'wpshadow' ); ?></label>
						<p class="wps-settings-row-hint"><?php esc_html_e( 'Reduce some visual clutter and use calmer spacing to make admin screens easier to scan.', 'wpshadow' ); ?></p>
					</div>
					<div class="wps-settings-row-control">
						<label class="wps-toggle-switch">
							<input
								type="checkbox"
								id="wps-simplified-ui"
								class="wps-auto-save"
								data-option="wpshadow_simplified_ui"
								data-type="bool"
								<?php checked( wpshadow_settings_bool( 'wpshadow_simplified_ui', false ) ); ?>
							/>
							<span class="wps-toggle-slider" aria-hidden="true"></span>
						</label>
						<span class="wps-save-status" aria-live="polite"></span>
					</div>
				</div>
			</div>
		</div>

	</div><!-- .wps-settings-body (accessibility) -->
	<?php endif; ?>

	<!-- Governance Report Section -->
	<div id="wps-governance-report" class="wps-settings-body" style="margin-top: 40px; padding: 24px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px;">
		<h2 style="margin-top: 0; margin-bottom: 20px; font-size: 18px; font-weight: 600;">
			<?php esc_html_e( 'Governance & Compliance Report', 'wpshadow' ); ?>
		</h2>
		<p style="margin: 0 0 24px 0; color: #374151; font-size: 14px;">
			<?php esc_html_e( 'Export a comprehensive readiness inventory of all diagnostics and treatments for compliance and audit purposes.', 'wpshadow' ); ?>
		</p>

		<!-- Readiness Summary --
>
		<div id="wps-readiness-summary-report" style="padding: 16px; background: white; border: 1px solid #d1d5db; border-radius: 6px; margin-bottom: 20px;">
			<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
				<div style="padding: 12px; background: #f0fdf4; border-left: 4px solid #00a32a;">
					<div style="font-size: 12px; font-weight: 600; color: #374151; text-transform: uppercase; margin-bottom: 4px;">
						<?php esc_html_e( 'Production Diagnostics', 'wpshadow' ); ?>
					</div>
					<div style="font-size: 24px; font-weight: 700; color: #00a32a;" data-count-prod-diag>0</div>
				</div>
				<div style="padding: 12px; background: #fef3c7; border-left: 4px solid #f57c00;">
					<div style="font-size: 12px; font-weight: 600; color: #374151; text-transform: uppercase; margin-bottom: 4px;">
						<?php esc_html_e( 'Beta Diagnostics', 'wpshadow' ); ?>
					</div>
					<div style="font-size: 24px; font-weight: 700; color: #f57c00;" data-count-beta-diag>0</div>
				</div>
				<div style="padding: 12px; background: #fee2e2; border-left: 4px solid #d32f2f;">
					<div style="font-size: 12px; font-weight: 600; color: #374151; text-transform: uppercase; margin-bottom: 4px;">
						<?php esc_html_e( 'Planned Diagnostics', 'wpshadow' ); ?>
					</div>
					<div style="font-size: 24px; font-weight: 700; color: #d32f2f;" data-count-planned-diag>0</div>
				</div>
				<div style="padding: 12px; background: #f0fdf4; border-left: 4px solid #00a32a;">
					<div style="font-size: 12px; font-weight: 600; color: #374151; text-transform: uppercase; margin-bottom: 4px;">
						<?php esc_html_e( 'Production Treatments', 'wpshadow' ); ?>
					</div>
					<div style="font-size: 24px; font-weight: 700; color: #00a32a;" data-count-prod-treat>0</div>
				</div>
				<div style="padding: 12px; background: #fef3c7; border-left: 4px solid #f57c00;">
					<div style="font-size: 12px; font-weight: 600; color: #374151; text-transform: uppercase; margin-bottom: 4px;">
						<?php esc_html_e( 'Beta Treatments', 'wpshadow' ); ?>
					</div>
					<div style="font-size: 24px; font-weight: 700; color: #f57c00;" data-count-beta-treat>0</div>
				</div>
				<div style="padding: 12px; background: #fee2e2; border-left: 4px solid #d32f2f;">
					<div style="font-size: 12px; font-weight: 600; color: #374151; text-transform: uppercase; margin-bottom: 4px;">
						<?php esc_html_e( 'Planned Treatments', 'wpshadow' ); ?>
					</div>
					<div style="font-size: 24px; font-weight: 700; color: #d32f2f;" data-count-planned-treat>0</div>
				</div>
			</div>
		</div>

		<!-- Treatment Maturity Coverage -->
		<div id="wps-treatment-maturity-summary" style="padding: 16px; background: white; border: 1px solid #d1d5db; border-radius: 6px; margin-bottom: 20px;">
			<div style="font-size: 13px; font-weight: 600; color: #374151; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 12px;">
				<?php esc_html_e( 'Treatment Maturity Coverage', 'wpshadow' ); ?>
			</div>
			<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; margin-bottom: 12px;">
				<div style="padding: 12px; background: #f0fdf4; border-left: 4px solid #00a32a;">
					<div style="font-size: 11px; font-weight: 600; color: #374151; text-transform: uppercase; margin-bottom: 4px;">
						<?php esc_html_e( 'Automated', 'wpshadow' ); ?>
					</div>
					<div style="font-size: 24px; font-weight: 700; color: #00a32a;" data-count-treat-shipped>—</div>
					<div style="font-size: 11px; color: #6b7280; margin-top: 2px;"><?php esc_html_e( 'apply + undo implemented', 'wpshadow' ); ?></div>
				</div>
				<div style="padding: 12px; background: #fff7ed; border-left: 4px solid #f57c00;">
					<div style="font-size: 11px; font-weight: 600; color: #374151; text-transform: uppercase; margin-bottom: 4px;">
						<?php esc_html_e( 'Guidance-Only', 'wpshadow' ); ?>
					</div>
					<div style="font-size: 24px; font-weight: 700; color: #f57c00;" data-count-treat-guidance>—</div>
					<div style="font-size: 11px; color: #6b7280; margin-top: 2px;"><?php esc_html_e( 'returns manual steps only', 'wpshadow' ); ?></div>
				</div>
				<div style="padding: 12px; background: #eff6ff; border-left: 4px solid #2563eb;">
					<div style="font-size: 11px; font-weight: 600; color: #374151; text-transform: uppercase; margin-bottom: 4px;">
						<?php esc_html_e( 'Reversible', 'wpshadow' ); ?>
					</div>
					<div style="font-size: 24px; font-weight: 700; color: #2563eb;" data-count-treat-reversible>—</div>
					<div style="font-size: 11px; color: #6b7280; margin-top: 2px;"><?php esc_html_e( 'undo() fully restores state', 'wpshadow' ); ?></div>
				</div>
			</div>
			<div style="font-size: 11px; font-weight: 600; color: #374151; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 8px;">
				<?php esc_html_e( 'Risk Distribution', 'wpshadow' ); ?>
			</div>
			<div style="display: flex; gap: 10px; flex-wrap: wrap;">
				<span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 9999px; background: #f0fdf4; border: 1px solid #bbf7d0; font-size: 12px; font-weight: 600; color: #166534;">
					<?php esc_html_e( 'Safe:', 'wpshadow' ); ?> <span data-count-treat-safe>—</span>
				</span>
				<span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 9999px; background: #fff7ed; border: 1px solid #fed7aa; font-size: 12px; font-weight: 600; color: #9a3412;">
					<?php esc_html_e( 'Moderate:', 'wpshadow' ); ?> <span data-count-treat-moderate>—</span>
				</span>
				<span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 9999px; background: #fef2f2; border: 1px solid #fecaca; font-size: 12px; font-weight: 600; color: #991b1b;">
					<?php esc_html_e( 'High:', 'wpshadow' ); ?> <span data-count-treat-high>—</span>
				</span>
				<span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 9999px; background: #f9fafb; border: 1px solid #d1d5db; font-size: 12px; font-weight: 600; color: #6b7280;">
					<?php esc_html_e( 'Guidance:', 'wpshadow' ); ?> <span data-count-treat-guidance-risk>—</span>
				</span>
			</div>
		</div>

		<!-- Export Buttons -->
		<div style="display: flex; gap: 12px; flex-wrap: wrap;">
			<button type="button" class="button button-primary" id="wps-export-inventory-json">
				<?php esc_html_e( 'Export as JSON', 'wpshadow' ); ?>
			</button>
			<button type="button" class="button" id="wps-export-inventory-csv">
				<?php esc_html_e( 'Export as CSV', 'wpshadow' ); ?>
			</button>
			<button type="button" class="button" id="wps-refresh-readiness-summary">
				<?php esc_html_e( 'Refresh Summary', 'wpshadow' ); ?>
			</button>
		</div>
		<p id="wps-export-status" style="margin-top: 12px; font-size: 13px; color: #374151;"></p>

		<!-- Expandable Inventory Sections -->
		<div style="margin-top: 24px;">
			<?php
			$states = array( 'production', 'beta', 'planned' );
			$state_labels = array(
				'production' => __( 'Production', 'wpshadow' ),
				'beta'       => __( 'Beta', 'wpshadow' ),
				'planned'    => __( 'Planned', 'wpshadow' ),
			);
			$state_colors = array(
				'production' => '#00a32a',
				'beta'       => '#f57c00',
				'planned'    => '#d32f2f',
			);
			?>
			<?php foreach ( $states as $state ) : ?>
				<div style="margin-bottom: 16px; border: 1px solid #d1d5db; border-radius: 6px; overflow: hidden;">
					<button
						type="button"
						class="wps-readiness-section-toggle"
						style="width: 100%; padding: 12px 16px; background: white; border: none; text-align: left; cursor: pointer; display: flex; align-items: center; justify-content: space-between; font-weight: 600; color: #1f2937; border-bottom: 1px solid #e5e7eb; hover:background: #f9fafb;"
						data-state="<?php echo esc_attr( $state ); ?>"
					>
						<span>
							<span style="display: inline-block; width: 12px; height: 12px; border-radius: 50%; background: <?php echo esc_attr( $state_colors[ $state ] ); ?>; margin-right: 8px;"></span>
							<?php echo esc_html( $state_labels[ $state ] ); ?>
						</span>
						<span class="wps-toggle-arrow" style="display: inline-block; transition: transform 0.2s;">▼</span>
					</button>
					<div class="wps-readiness-section-content" data-state="<?php echo esc_attr( $state ); ?>" style="display: none; padding: 12px 16px; background: #f9fafb; max-height: 400px; overflow-y: auto;">
						<div class="wps-inventory-list" style="font-size: 13px;">
							<?php esc_html_e( 'Loading...', 'wpshadow' ); ?>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

	<script>
		(function() {
			var ajaxUrl = (typeof wpshadowDashboardData !== 'undefined' && wpshadowDashboardData.ajaxUrl)
				? wpshadowDashboardData.ajaxUrl
				: ajaxurl;

			// Refresh readiness summary on page load
			refreshReadinessSummary();

			// Attach button handlers
			jQuery('#wps-export-inventory-json').on('click', function(e) {
				e.preventDefault();
				exportInventory('json');
			});

			jQuery('#wps-export-inventory-csv').on('click', function(e) {
				e.preventDefault();
				exportInventory('csv');
			});

			jQuery('#wps-refresh-readiness-summary').on('click', function(e) {
				e.preventDefault();
				refreshReadinessSummary();
			});

			// Attach toggle handlers for expandable sections
			jQuery('.wps-readiness-section-toggle').on('click', function(e) {
				e.preventDefault();
				var $btn = jQuery(this);
				var $content = $btn.closest('.wps-readiness-section-toggle').nextAll('.wps-readiness-section-content').first();
				var $arrow = $btn.find('.wps-toggle-arrow');

				if ($content.is(':visible')) {
					$content.slideUp(200);
					$arrow.css('transform', '');
				} else {
					// Load inventory for this state if not already loaded
					var state = $content.data('state');
					var $list = $content.find('.wps-inventory-list');

					if ($list.text().indexOf('Loading...') !== -1) {
						loadInventoryForState(state, $list);
					}

					$content.slideDown(200);
					$arrow.css('transform', 'rotate(180deg)');
				}
			});

			function refreshReadinessSummary() {
				var nonce = (typeof wpshadowDashboardData !== 'undefined' && wpshadowDashboardData.scan_settings_nonce)
					? wpshadowDashboardData.scan_settings_nonce
					: '';

				jQuery.post(ajaxUrl, {
					action: 'wpshadow_readiness_inventory',
					nonce: nonce
				}).done(function(response) {
					if (response && response.success && response.data) {
						var summary = response.data.summary || {};
						jQuery('[data-count-prod-diag]').text(summary.diagnostics?.production || 0);
						jQuery('[data-count-beta-diag]').text(summary.diagnostics?.beta || 0);
						jQuery('[data-count-planned-diag]').text(summary.diagnostics?.planned || 0);
						jQuery('[data-count-prod-treat]').text(summary.treatments?.production || 0);
						jQuery('[data-count-beta-treat]').text(summary.treatments?.beta || 0);
						jQuery('[data-count-planned-treat]').text(summary.treatments?.planned || 0);
					}
				}).fail(function() {
					jQuery('#wps-export-status').text('<?php echo esc_js( __( 'Failed to refresh summary.', 'wpshadow' ) ); ?>').css('color', '#d32f2f');
				});

				jQuery.post(ajaxUrl, {
					action: 'wpshadow_treatment_maturity',
					nonce: nonce
				}).done(function(response) {
					if (response && response.success && response.data) {
						var counts = response.data.counts || {};
						var byRisk  = counts.by_risk || {};
						jQuery('[data-count-treat-shipped]').text(counts.shipped ?? '—');
						jQuery('[data-count-treat-guidance]').text(counts.guidance ?? '—');
						jQuery('[data-count-treat-reversible]').text(counts.reversible ?? '—');
						jQuery('[data-count-treat-safe]').text(byRisk.safe ?? '—');
						jQuery('[data-count-treat-moderate]').text(byRisk.moderate ?? '—');
						jQuery('[data-count-treat-high]').text(byRisk.high ?? '—');
						jQuery('[data-count-treat-guidance-risk]').text(byRisk.guidance ?? '—');
					}
				});
			}

			function loadInventoryForState(state, $list) {
				jQuery.post(ajaxUrl, {
					action: 'wpshadow_readiness_inventory',
					nonce: (typeof wpshadowDashboardData !== 'undefined' && wpshadowDashboardData.scan_settings_nonce)
						? wpshadowDashboardData.scan_settings_nonce
						: ''
				}).done(function(response) {
					if (response && response.success && response.data) {
						var inventory = response.data.inventory || {};
						var items = [];

						// Collect diagnostics for this state
						if (inventory.diagnostics) {
							jQuery.each(inventory.diagnostics, function(idx, diag) {
								if ((diag.state || 'production') === state) {
									items.push({
										type: 'Diagnostic',
										name: diag.class || 'Unknown',
										class: diag.class || '',
										file: diag.file || ''
									});
								}
							});
						}

						// Collect treatments for this state
						if (inventory.treatments) {
							jQuery.each(inventory.treatments, function(idx, treat) {
								if ((treat.state || 'production') === state) {
									items.push({
										type: 'Treatment',
										name: treat.class || 'Unknown',
										class: treat.class || '',
										file: treat.file || ''
									});
								}
							});
						}

						// Render items
						if (items.length === 0) {
							$list.html('<em><?php echo esc_js( __( 'No items found.', 'wpshadow' ) ); ?></em>');
						} else {
							var html = '<ul style="list-style: none; margin: 0; padding: 0;">';
							jQuery.each(items, function(idx, item) {
								html += '<li style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;">';
								html += '<strong>' + item.type + ':</strong> ' + jQuery('<div/>').text(item.name).html();
								if (item.file) {
									html += '<br><small style="color: #6b7280;">' + jQuery('<div/>').text(item.file).html() + '</small>';
								}
								html += '</li>';
							});
							html += '</ul>';
							$list.html(html);
						}
					}
				}).fail(function() {
					$list.html('<em style="color: #d32f2f;"><?php echo esc_js( __( 'Failed to load inventory.', 'wpshadow' ) ); ?></em>');
				});
			}

			function exportInventory(format) {
				var $status = jQuery('#wps-export-status');
				$status.text('<?php echo esc_js( __( 'Exporting...', 'wpshadow' ) ); ?>').css('color', '#374151');

				var formData = new FormData();
				formData.append('action', 'wpshadow_export_readiness_inventory');
				formData.append('format', format);
				formData.append('nonce', (typeof wpshadowDashboardData !== 'undefined' && wpshadowDashboardData.scan_settings_nonce)
					? wpshadowDashboardData.scan_settings_nonce
					: '');

				fetch(ajaxUrl, {
					method: 'POST',
					body: formData
				}).then(function(response) {
					if (response.ok) {
						return response.blob().then(function(blob) {
							var fileName = 'wpshadow-readiness-inventory-' + new Date().toISOString().substring(0, 10) + '.' + format;
							var url = window.URL.createObjectURL(blob);
							var link = document.createElement('a');
							link.href = url;
							link.download = fileName;
							document.body.appendChild(link);
							link.click();
							document.body.removeChild(link);
							window.URL.revokeObjectURL(url);
							$status.text('<?php echo esc_js( __( 'Export complete.', 'wpshadow' ) ); ?>').css('color', '#00a32a');
						});
					} else {
						$status.text('<?php echo esc_js( __( 'Export failed.', 'wpshadow' ) ); ?>').css('color', '#d32f2f');
					}
				}).catch(function() {
					$status.text('<?php echo esc_js( __( 'Export error.', 'wpshadow' ) ); ?>').css('color', '#d32f2f');
				});
			}
		})();
	</script>

</div><!-- .wps-settings-page -->
