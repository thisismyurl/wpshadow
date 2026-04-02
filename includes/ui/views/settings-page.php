<?php
/**
 * Settings Page
 *
 * Full settings UI for WPShadow: general options, scan schedule,
 * backup configuration, per-diagnostic toggles and frequency, and privacy.
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
$valid_tabs = array( 'general', 'scanning', 'backups', 'diagnostics', 'privacy' );
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

$freq_overrides = get_option( 'wpshadow_diagnostic_frequency_overrides', array() );
if ( ! is_array( $freq_overrides ) ) {
	$freq_overrides = array();
}

$disabled_diagnostics = get_option( 'wpshadow_disabled_diagnostic_classes', array() );
if ( ! is_array( $disabled_diagnostics ) ) {
	$disabled_diagnostics = array();
}

/**
 * Helper: checked/selected state for a boolean option.
 *
 * @param  string $option  Option name (with or without wpshadow_ prefix).
 * @param  bool   $default Default value.
 * @return bool
 */
function wpshadow_settings_bool( string $option, bool $default = true ): bool {
	$key    = ( 0 === strpos( $option, 'wpshadow_' ) ) ? $option : 'wpshadow_' . $option;
	$stored = get_option( $key );
	return false === $stored ? $default : (bool) $stored;
}

/**
 * Helper: string option value.
 *
 * @param  string $option  Option name.
 * @param  string $default Default value.
 * @return string
 */
function wpshadow_settings_str( string $option, string $default = '' ): string {
	$key    = ( 0 === strpos( $option, 'wpshadow_' ) ) ? $option : 'wpshadow_' . $option;
	$stored = get_option( $key );
	return false === $stored ? $default : (string) $stored;
}

/**
 * Helper: integer option value.
 *
 * @param  string $option  Option name.
 * @param  int    $default Default value.
 * @return int
 */
function wpshadow_settings_int( string $option, int $default = 0 ): int {
	$key    = ( 0 === strpos( $option, 'wpshadow_' ) ) ? $option : 'wpshadow_' . $option;
	$stored = get_option( $key );
	return false === $stored ? $default : (int) $stored;
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
			'general'     => array( 'label' => __( 'General', 'wpshadow' ),     'icon' => 'dashicons-admin-generic' ),
			'scanning'    => array( 'label' => __( 'Scanning', 'wpshadow' ),    'icon' => 'dashicons-search' ),
			'backups'     => array( 'label' => __( 'Backups', 'wpshadow' ),     'icon' => 'dashicons-backup' ),
			'diagnostics' => array( 'label' => __( 'Diagnostics', 'wpshadow' ), 'icon' => 'dashicons-heart' ),
			'privacy'     => array( 'label' => __( 'Privacy', 'wpshadow' ),     'icon' => 'dashicons-shield' ),
		);
		foreach ( $tabs as $tab_key => $tab ) :
			$href   = esc_url( add_query_arg( 'tab', $tab_key, $settings_url ) );
			$active = $active_tab === $tab_key ? ' wps-settings-tab--active' : '';
			?>
			<a
				href="<?php echo $href; ?>"
				class="wps-settings-tab<?php echo esc_attr( $active ); ?>"
				aria-current="<?php echo $active_tab === $tab_key ? 'page' : 'false'; ?>"
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
							$backup_freqs    = array( 'daily' => __( 'Daily', 'wpshadow' ), 'weekly' => __( 'Weekly (recommended)', 'wpshadow' ), 'monthly' => __( 'Monthly', 'wpshadow' ) );
							$current_bkfreq  = wpshadow_settings_str( 'wpshadow_backup_schedule_frequency', 'weekly' );
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
	     TAB: DIAGNOSTICS
	     ═══════════════════════════════════════════════════════════════════════ -->
	<?php if ( 'diagnostics' === $active_tab ) : ?>
	<div class="wps-settings-body">

		<?php
		// ── Load diagnostics ──────────────────────────────────────────────────
		$all_diagnostics = array();
		$families        = array();

		if ( class_exists( '\WPShadow\Diagnostics\Diagnostic_Registry' ) ) {
			$file_map = \WPShadow\Diagnostics\Diagnostic_Registry::get_diagnostic_file_map();

			foreach ( $file_map as $short_class => $diagnostic_data ) {
				if ( ! is_string( $short_class ) || '' === $short_class ) {
					continue;
				}

				$class = 0 === strpos( $short_class, 'WPShadow\\Diagnostics\\' )
					? $short_class
					: 'WPShadow\\Diagnostics\\' . $short_class;

				$file = isset( $diagnostic_data['file'] ) ? (string) $diagnostic_data['file'] : '';
				if ( ! class_exists( $class ) && '' !== $file && file_exists( $file ) ) {
					require_once $file;
				}

				$class_loaded = class_exists( $class );

				$family_raw   = isset( $diagnostic_data['family'] ) ? (string) $diagnostic_data['family'] : '';
				$family       = $class_loaded && method_exists( $class, 'get_family' )       ? $class::get_family()       : $family_raw;
				$family_label = $class_loaded && method_exists( $class, 'get_family_label' ) ? $class::get_family_label() : '';
				$title        = $class_loaded && method_exists( $class, 'get_title' )        ? $class::get_title()        : '';
				$description  = $class_loaded && method_exists( $class, 'get_description' )  ? $class::get_description()  : '';
				$severity     = $class_loaded && method_exists( $class, 'get_severity' )     ? $class::get_severity()     : 'medium';
				$default_freq = $class_loaded && method_exists( $class, 'get_scan_frequency' ) ? $class::get_scan_frequency() : 'daily';
				$enabled      = ! in_array( $class, $disabled_diagnostics, true );
				$frequency    = isset( $freq_overrides[ $class ] ) ? $freq_overrides[ $class ] : 'default';

				if ( empty( $family_label ) ) {
					$family_label = ! empty( $family ) ? ucwords( str_replace( array( '-', '_' ), ' ', $family ) ) : __( 'General', 'wpshadow' );
				}

				if ( empty( $title ) ) {
					$short = str_replace( 'WPShadow\\Diagnostics\\Diagnostic_', '', $class );
					$title = ucwords( str_replace( '_', ' ', $short ) );
				}

				$all_diagnostics[] = array(
					'class'         => $class,
					'title'         => $title,
					'description'   => $description,
					'family'        => $family,
					'family_label'  => $family_label,
					'severity'      => $severity,
					'default_freq'  => $default_freq,
					'enabled'       => $enabled,
					'frequency'     => $frequency,
					'run_key'       => function_exists( 'wpshadow_get_diagnostic_run_key_from_class' )
						? wpshadow_get_diagnostic_run_key_from_class( $class )
						: sanitize_key( strtolower( str_replace( '_', '-', str_replace( 'WPShadow\\Diagnostics\\Diagnostic_', 'diagnostic-', $class ) ) ) ),
				);

				if ( ! empty( $family ) && ! isset( $families[ $family ] ) ) {
					$families[ $family ] = $family_label;
				}
			}

			// Group by family then sort alphabetically by title within each group.
			usort(
				$all_diagnostics,
				function ( $a, $b ) {
					$family_cmp = strcmp( $a['family'], $b['family'] );
					return 0 !== $family_cmp ? $family_cmp : strcmp( $a['title'], $b['title'] );
				}
			);

			asort( $families );
		}

		$total_diagnostics   = count( $all_diagnostics );
		$enabled_count       = count( array_filter( $all_diagnostics, fn( $d ) => $d['enabled'] ) );
		?>

		<div class="wps-diag-toolbar">
			<div class="wps-diag-stats">
				<strong><?php echo esc_html( $enabled_count ); ?></strong>
				<?php
				/* translators: %d: total diagnostic count */
				printf( esc_html__( 'of %d diagnostics active', 'wpshadow' ), $total_diagnostics );
				?>
			</div>

			<div class="wps-diag-filters">
				<input
					type="search"
					id="wps-diag-search"
					class="regular-text"
					placeholder="<?php esc_attr_e( 'Search diagnostics&hellip;', 'wpshadow' ); ?>"
					aria-label="<?php esc_attr_e( 'Search diagnostics', 'wpshadow' ); ?>"
				/>

				<select id="wps-diag-family-filter" aria-label="<?php esc_attr_e( 'Filter by category', 'wpshadow' ); ?>">
					<option value=""><?php esc_html_e( 'All categories', 'wpshadow' ); ?></option>
					<?php foreach ( $families as $fk => $fl ) : ?>
						<option value="<?php echo esc_attr( $fk ); ?>"><?php echo esc_html( $fl ); ?></option>
					<?php endforeach; ?>
				</select>

				<select id="wps-diag-status-filter" aria-label="<?php esc_attr_e( 'Filter by status', 'wpshadow' ); ?>">
					<option value=""><?php esc_html_e( 'All statuses', 'wpshadow' ); ?></option>
					<option value="enabled"><?php esc_html_e( 'Active', 'wpshadow' ); ?></option>
					<option value="disabled"><?php esc_html_e( 'Inactive', 'wpshadow' ); ?></option>
				</select>
			</div>
		</div><!-- .wps-diag-toolbar -->

		<?php if ( empty( $all_diagnostics ) ) : ?>
			<div class="wps-settings-section">
				<p><?php esc_html_e( 'No diagnostics found. The diagnostic registry may still be building.', 'wpshadow' ); ?></p>
			</div>
		<?php else : ?>

		<div class="wps-diag-table-wrap">
			<table
				class="wps-diag-table widefat"
				id="wps-diagnostics-table"
				aria-label="<?php esc_attr_e( 'Diagnostics list', 'wpshadow' ); ?>"
			>
				<thead>
					<tr>
						<th class="wps-diag-col-name"><?php esc_html_e( 'Diagnostic', 'wpshadow' ); ?></th>
						<th class="wps-diag-col-category"><?php esc_html_e( 'Category', 'wpshadow' ); ?></th>
						<th class="wps-diag-col-severity"><?php esc_html_e( 'Severity', 'wpshadow' ); ?></th>
						<th class="wps-diag-col-freq"><?php esc_html_e( 'Frequency', 'wpshadow' ); ?></th>
						<th class="wps-diag-col-toggle"><?php esc_html_e( 'Active', 'wpshadow' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( $all_diagnostics as $idx => $diag ) : ?>
					<tr
						class="wps-diag-row<?php echo ! $diag['enabled'] ? ' wps-diag-row--disabled' : ''; ?>"
						data-family="<?php echo esc_attr( $diag['family'] ); ?>"
						data-enabled="<?php echo $diag['enabled'] ? 'enabled' : 'disabled'; ?>"
					>
						<td class="wps-diag-col-name">
							<?php
							$detail_url = function_exists( 'wpshadow_get_diagnostic_detail_admin_url' )
								? wpshadow_get_diagnostic_detail_admin_url( $diag['run_key'] )
								: add_query_arg( array( 'page' => 'wpshadow-diagnostic', 'diagnostic' => urlencode( $diag['run_key'] ) ), admin_url( 'admin.php' ) );
							?>
							<a href="<?php echo esc_url( $detail_url ); ?>" class="wps-diag-title">
								<?php echo esc_html( $diag['title'] ); ?>
							</a>
							<?php if ( ! empty( $diag['description'] ) ) : ?>
								<span class="wps-diag-description"><?php echo esc_html( $diag['description'] ); ?></span>
							<?php endif; ?>
						</td>
						<td class="wps-diag-col-category">
							<?php if ( ! empty( $diag['family_label'] ) ) : ?>
								<span class="wps-diag-family-badge wps-diag-family-badge--<?php echo esc_attr( sanitize_html_class( $diag['family'] ) ); ?>">
									<?php echo esc_html( $diag['family_label'] ); ?>
								</span>
							<?php endif; ?>
						</td>
						<td class="wps-diag-col-severity">
							<span class="wps-diag-severity wps-diag-severity--<?php echo esc_attr( $diag['severity'] ); ?>">
								<?php echo esc_html( ucfirst( $diag['severity'] ) ); ?>
							</span>
						</td>
						<td class="wps-diag-col-freq">
							<select
								class="wps-diag-freq-select"
								data-class="<?php echo esc_attr( $diag['class'] ); ?>"
								aria-label="<?php echo esc_attr( sprintf( __( 'Frequency for %s', 'wpshadow' ), $diag['title'] ) ); ?>"
							>
								<option value="default" <?php selected( $diag['frequency'], 'default' ); ?>>
									<?php
									$default_label = sprintf(
										/* translators: %s: default frequency name */
										__( 'Default (%s)', 'wpshadow' ),
										ucfirst( $diag['default_freq'] )
									);
									echo esc_html( $default_label );
									?>
								</option>
								<option value="always"    <?php selected( $diag['frequency'], 'always' ); ?>><?php esc_html_e( 'Always', 'wpshadow' ); ?></option>
								<option value="on-change" <?php selected( $diag['frequency'], 'on-change' ); ?>><?php esc_html_e( 'On Change', 'wpshadow' ); ?></option>
								<option value="daily"     <?php selected( $diag['frequency'], 'daily' ); ?>><?php esc_html_e( 'Daily', 'wpshadow' ); ?></option>
								<option value="weekly"    <?php selected( $diag['frequency'], 'weekly' ); ?>><?php esc_html_e( 'Weekly', 'wpshadow' ); ?></option>
								<option value="monthly"   <?php selected( $diag['frequency'], 'monthly' ); ?>><?php esc_html_e( 'Monthly', 'wpshadow' ); ?></option>
							</select>
							<span class="wps-save-status" aria-live="polite"></span>
						</td>
						<td class="wps-diag-col-toggle">
							<label class="wps-toggle-switch" aria-label="<?php echo esc_attr( sprintf( __( 'Enable %s', 'wpshadow' ), $diag['title'] ) ); ?>">
								<input
									type="checkbox"
									class="wps-diag-toggle"
									data-class="<?php echo esc_attr( $diag['class'] ); ?>"
									<?php checked( $diag['enabled'] ); ?>
								/>
								<span class="wps-toggle-slider" aria-hidden="true"></span>
							</label>
							<span class="wps-save-status" aria-live="polite"></span>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div><!-- .wps-diag-table-wrap -->

		<p class="wps-diag-no-results" id="wps-diag-no-results" hidden>
			<?php esc_html_e( 'No diagnostics match the current filters.', 'wpshadow' ); ?>
		</p>

		<?php endif; ?>

	</div><!-- .wps-settings-body (diagnostics) -->
	<?php endif; ?>

	<!-- ═══════════════════════════════════════════════════════════════════════
	     TAB: PRIVACY
	     ═══════════════════════════════════════════════════════════════════════ -->
	<?php if ( 'privacy' === $active_tab ) : ?>
	<div class="wps-settings-body">

		<div class="wps-settings-section">
			<h2 class="wps-settings-section-title"><?php esc_html_e( 'Data Sharing', 'wpshadow' ); ?></h2>
			<p class="wps-settings-section-desc"><?php esc_html_e( 'All data sharing is opt-in and anonymous. WPShadow never collects personally identifiable information.', 'wpshadow' ); ?></p>

			<div class="wps-settings-rows">

				<div class="wps-settings-row">
					<div class="wps-settings-row-label">
						<label for="wps-telemetry"><?php esc_html_e( 'Anonymous Usage Data', 'wpshadow' ); ?></label>
						<p class="wps-settings-row-hint"><?php esc_html_e( 'Share anonymous feature usage data to help us improve WPShadow. No personally identifiable information is ever collected.', 'wpshadow' ); ?></p>
					</div>
					<div class="wps-settings-row-control">
						<label class="wps-toggle-switch">
							<input
								type="checkbox"
								id="wps-telemetry"
								class="wps-auto-save"
								data-option="wpshadow_telemetry_enabled"
								data-type="bool"
								<?php checked( wpshadow_settings_bool( 'wpshadow_telemetry_enabled', false ) ); ?>
							/>
							<span class="wps-toggle-slider" aria-hidden="true"></span>
						</label>
						<span class="wps-save-status" aria-live="polite"></span>
					</div>
				</div>

				<div class="wps-settings-row">
					<div class="wps-settings-row-label">
						<label for="wps-error-reporting"><?php esc_html_e( 'Error Reporting', 'wpshadow' ); ?></label>
						<p class="wps-settings-row-hint"><?php esc_html_e( 'Automatically send error reports to the WPShadow team when plugin errors occur. Helps us fix bugs faster.', 'wpshadow' ); ?></p>
					</div>
					<div class="wps-settings-row-control">
						<label class="wps-toggle-switch">
							<input
								type="checkbox"
								id="wps-error-reporting"
								class="wps-auto-save"
								data-option="wpshadow_error_reporting"
								data-type="bool"
								<?php checked( wpshadow_settings_bool( 'wpshadow_error_reporting', false ) ); ?>
							/>
							<span class="wps-toggle-slider" aria-hidden="true"></span>
						</label>
						<span class="wps-save-status" aria-live="polite"></span>
					</div>
				</div>

			</div><!-- .wps-settings-rows -->
		</div><!-- .wps-settings-section -->

	</div><!-- .wps-settings-body (privacy) -->
	<?php endif; ?>

</div><!-- .wps-settings-page -->
