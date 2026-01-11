<?php
/**
 * Settings view for Core Support Vault.
 *
 * @package wp_support_SUPPORT
 */

use WPS\CoreSupport\WPS_Vault;
use WPS\CoreSupport\WPS_License;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$is_network      = is_network_admin();
$action_url      = $is_network ? network_admin_url( 'admin.php?page=wps-core-network-settings' ) : admin_url( 'admin.php?page=wp-support&WPS_tab=settings' );
$settings        = WPS_Vault::get_settings();
$saved           = isset( $_GET['WPS_vault_saved'] );
$locked          = ! $is_network && ! WPS_Vault::site_override_allowed();
$tool_notice     = isset( $_GET['WPS_vault_tool'] ) ? sanitize_text_field( wp_unslash( $_GET['WPS_vault_tool'] ) ) : '';
$tool_ok         = isset( $_GET['ok'] ) ? (int) $_GET['ok'] : 0;
$tool_fail       = isset( $_GET['fail'] ) ? (int) $_GET['fail'] : 0;
$tool_skipped    = isset( $_GET['skipped'] ) ? (int) $_GET['skipped'] : 0;
$tool_missing    = isset( $_GET['missing'] ) ? (int) $_GET['missing'] : 0;
$license_state   = isset( $license_state ) && is_array( $license_state ) ? $license_state : WPS_License::get_state( false );
$license_locked  = $is_network;
$license_status  = $license_state['status'] ?? 'none';
$license_message = $license_state['message'] ?? '';
$license_checked = ! empty( $license_state['checked_at'] ) ? date_i18n( 'M j, Y g:i a', (int) $license_state['checked_at'] ) : '—';
?>
<div class="wrap wps-core-wrap">
	<h1><?php echo esc_html__( 'Core Support - Vault Settings', 'plugin-wp-support-thisismyurl' ); ?></h1>

	<?php if ( $is_network ) : ?>
		<div class="notice notice-info"><p><?php echo esc_html__( 'Network Admin: Register your license once, then broadcast it to all sub-sites below.', 'plugin-wp-support-thisismyurl' ); ?></p></div>
	<?php endif; ?>

	<?php if ( isset( $_GET['WPS_license_status'] ) ) : ?>
		<?php
			$status       = sanitize_text_field( wp_unslash( $_GET['WPS_license_status'] ) );
			$message      = isset( $_GET['WPS_license_message'] ) ? sanitize_text_field( wp_unslash( $_GET['WPS_license_message'] ) ) : '';
			$notice_class = 'notice-info';
		if ( 'valid' === $status ) {
			$notice_class = 'notice-success';
		} elseif ( in_array( $status, array( 'invalid', 'error' ), true ) ) {
			$notice_class = 'notice-error';
		}
		?>
		<div class="notice <?php echo esc_attr( $notice_class ); ?> is-dismissible"><p><?php echo esc_html( $message ? $message : __( 'License response received.', 'plugin-wp-support-thisismyurl' ) ); ?></p></div>
	<?php endif; ?>

	<div class="wps-license-card" style="margin-top:10px; padding:16px; background:#fff; border:1px solid #ccd0d4;">
		<h2 style="margin-top:0;"><?php echo esc_html__( 'Suite Registration', 'plugin-wp-support-thisismyurl' ); ?></h2>
		<p class="description"><?php echo esc_html__( 'Applies across all WPS hub and spoke plugins on this site.', 'plugin-wp-support-thisismyurl' ); ?></p>
		<p>
			<strong><?php echo esc_html__( 'Status:', 'plugin-wp-support-thisismyurl' ); ?></strong>
			<span style="display:inline-block; padding:2px 8px; border-radius:4px; background:#f1f1f1; margin-left:6px;">
				<?php echo esc_html( ucfirst( $license_status ) ); ?>
			</span>
			<?php if ( 'valid' === $license_status ) : ?>
				<span class="description" style="margin-left:8px; color:#123456;"><?php echo esc_html__( 'Registered for all plugins', 'plugin-wp-support-thisismyurl' ); ?></span>
			<?php endif; ?>
		</p>
		<p class="description"><?php echo esc_html( sprintf( __( 'Last checked: %s', 'plugin-wp-support-thisismyurl' ), $license_checked ) ); ?></p>

		<?php $license_button_attr = $license_locked ? 'disabled="disabled"' : ''; ?>
		<form method="post" action="<?php echo esc_url( $action_url ); ?>">
			<?php wp_nonce_field( 'WPS_license_settings', 'WPS_license_nonce' ); ?>
			<input type="hidden" name="WPS_license_action" value="save" />
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="WPS_license_key"><?php echo esc_html__( 'Registration Key', 'plugin-wp-support-thisismyurl' ); ?></label></th>
					<td>
						<input type="text" name="WPS_license_key" id="WPS_license_key" value="<?php echo esc_attr( $license_state['key'] ?? '' ); ?>" class="regular-text" <?php disabled( $license_locked ); ?> />
						<p class="description"><?php echo esc_html__( 'Validate once to unlock support and updates across the suite.', 'plugin-wp-support-thisismyurl' ); ?></p>
						<?php if ( $license_locked ) : ?>
							<p class="description" style="color:#a00;">
								<?php echo esc_html__( 'Licenses are site-specific. Enter the registration key from an individual site dashboard; Network Admin cannot modify it here.', 'plugin-wp-support-thisismyurl' ); ?>
							</p>
						<?php endif; ?>
					</td>
				</tr>
			</table>
			<?php submit_button( esc_html__( 'Validate & Save', 'plugin-wp-support-thisismyurl' ), 'primary', 'submit', false, 'style="margin-top:0;" ' . $license_button_attr ); ?>
		</form>
	</div>

	<?php if ( $is_network && is_multisite() ) : ?>
		<div class="wps-broadcast-card" style="margin-top:16px; padding:16px; background:#fff; border:1px solid #ccd0d4;">
			<h2 style="margin-top:0;"><?php echo esc_html__( 'Network License Broadcast', 'plugin-wp-support-thisismyurl' ); ?></h2>
			<p class="description"><?php echo esc_html__( 'Push your registered license key to sub-sites in this network. Each site keeps its own copy for validation.', 'plugin-wp-support-thisismyurl' ); ?></p>

			<?php if ( ! empty( $license_state['key'] ) && 'valid' === $license_status ) : ?>
				<form method="post" action="<?php echo esc_url( $action_url ); ?>" class="wps-broadcast-form">
					<?php wp_nonce_field( 'WPS_license_broadcast', 'WPS_license_broadcast_nonce' ); ?>
					<input type="hidden" name="WPS_license_action" value="broadcast" />
					<input type="hidden" name="WPS_license_key" value="<?php echo esc_attr( $license_state['key'] ?? '' ); ?>" />

					<div style="margin-bottom:12px;">
						<label for="WPS_broadcast_sites">
							<strong><?php echo esc_html__( 'Target Sites:', 'plugin-wp-support-thisismyurl' ); ?></strong>
						</label>
					</div>

					<?php
						$blogs = get_sites( array( 'fields' => 'ids' ) );
					if ( ! empty( $blogs ) ) :
						?>
							<div style="background:#f9f9f9; padding:12px; border:1px solid #e0e0e0; border-radius:4px; max-height:200px; overflow-y:auto;">
							<?php foreach ( $blogs as $blog_id ) : ?>
									<?php
										$blog_id   = absint( $blog_id );
										$blog_info = get_blog_details( $blog_id );
									if ( ! $blog_info ) {
										continue;
									}
									?>
									<label style="display:block; margin-bottom:6px;">
										<input type="checkbox" name="WPS_broadcast_site_ids[]" value="<?php echo absint( $blog_id ); ?>" checked="checked" />
										<?php echo esc_html( $blog_info->blogname ); ?> <span class="description">(<?php echo esc_html( $blog_info->domain . $blog_info->path ); ?>)</span>
									</label>
								<?php endforeach; ?>
							</div>
							<p class="description" style="margin-top:8px;"><?php echo esc_html__( 'All sites are selected by default. Uncheck any site to exclude it from this broadcast.', 'plugin-wp-support-thisismyurl' ); ?></p>
						<?php else : ?>
							<p class="description"><?php echo esc_html__( 'No sub-sites available to broadcast to.', 'plugin-wp-support-thisismyurl' ); ?></p>
						<?php endif; ?>
					</div>

					<div style="margin-top:16px; margin-bottom:12px;">
						<label>
							<input type="checkbox" name="WPS_auto_broadcast" id="WPS_auto_broadcast" value="1" checked="checked" />
							<?php echo esc_html__( 'Auto-apply to new sites created after this broadcast', 'plugin-wp-support-thisismyurl' ); ?>
						</label>
					</div>

					<?php submit_button( esc_html__( 'Broadcast License to Selected Sites', 'plugin-wp-support-thisismyurl' ), 'primary', 'submit', false, 'id="wps-broadcast-btn"' ); ?>
				</form>

				<div id="wps-broadcast-result" style="margin-top:16px; display:none;"></div>

				<script type="text/javascript">
					(function() {
						const form = document.querySelector('.wps-broadcast-form');
						if (!form) return;

						form.addEventListener('submit', function(e) {
							e.preventDefault();
							const btn = form.querySelector('#wps-broadcast-btn');
							const resultDiv = document.querySelector('#wps-broadcast-result');
							const key = form.querySelector('input[name="WPS_license_key"]').value;
							const siteIds = [];
							form.querySelectorAll('input[name="WPS_broadcast_site_ids[]"]:checked').forEach(input => {
								siteIds.push(parseInt(input.value, 10));
							});
							const autoBroadcast = form.querySelector('input[name="WPS_auto_broadcast"]').checked ? 1 : 0;

							btn.disabled = true;
							btn.textContent = '<?php echo esc_html__( 'Broadcasting...', 'plugin-wp-support-thisismyurl' ); ?>';
							resultDiv.style.display = 'none';

							const xhr = new XMLHttpRequest();
							xhr.open('POST', ajaxurl, true);
							xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
							xhr.onload = function() {
								btn.disabled = false;
								btn.textContent = '<?php echo esc_html__( 'Broadcast License to Selected Sites', 'plugin-wp-support-thisismyurl' ); ?>';

								try {
									const response = JSON.parse(xhr.responseText);
									let html = '<div class="notice ' + (response.success > 0 && response.failed === 0 ? 'notice-success' : response.failed > 0 ? 'notice-warning' : 'notice-error') + '"><p>';
									html += '<strong><?php echo esc_html__( 'Broadcast Result:', 'plugin-wp-support-thisismyurl' ); ?></strong> ';
									html += '<?php echo esc_html__( 'Success: %1$d, Failed: %2$d', 'plugin-wp-support-thisismyurl' ); ?>'.replace('%d', response.success).replace('%d', response.failed);
									if (response.errors && response.errors.length > 0) {
										html += '<ul style="margin-top:8px;">';
										response.errors.forEach(err => {
											html += '<li>' + err + '</li>';
										});
										html += '</ul>';
									}
									html += '</p></div>';
									resultDiv.innerHTML = html;
									resultDiv.style.display = 'block';
								} catch (e) {
									resultDiv.innerHTML = '<div class="notice notice-error"><p><?php echo esc_html__( 'Server error. Check debug log.', 'plugin-wp-support-thisismyurl' ); ?></p></div>';
									resultDiv.style.display = 'block';
								}
							};
							xhr.onerror = function() {
								btn.disabled = false;
								btn.textContent = '<?php echo esc_html__( 'Broadcast License to Selected Sites', 'plugin-wp-support-thisismyurl' ); ?>';
								resultDiv.innerHTML = '<div class="notice notice-error"><p><?php echo esc_html__( 'Network error. Check your connection.', 'plugin-wp-support-thisismyurl' ); ?></p></div>';
								resultDiv.style.display = 'block';
							};
							const data = 'action=WPS_broadcast_license&nonce=<?php echo esc_js( wp_create_nonce( 'WPS_broadcast_license' ) ); ?>&key=' + encodeURIComponent(key) + '&site_ids=' + encodeURIComponent(JSON.stringify(siteIds)) + '&auto_broadcast=' + autoBroadcast;
							xhr.send(data);
						});
					})();
				</script>
			<?php else : ?>
				<p class="description"><?php echo esc_html__( 'Register and validate a license key above before broadcasting to sub-sites.', 'plugin-wp-support-thisismyurl' ); ?></p>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php
		// Small summary banner: last job stats + quick export link.
		$queue_state   = WPS_Vault::get_queue_state();
		$export_nonce  = wp_create_nonce( 'WPS_vault_export' );
		$ledger_nonce  = wp_create_nonce( 'WPS_vault_export_ledger' );
		$journal_nonce = wp_create_nonce( 'WPS_vault_export_journal' );
		$bundle_nonce  = wp_create_nonce( 'WPS_vault_export_bundle' );

		$export_url = add_query_arg(
			array(
				'action'                 => 'WPS_vault_export_logs',
				'WPS_vault_export_nonce' => $export_nonce,
			),
			admin_url( 'admin-post.php' )
		);

		$ledger_url = add_query_arg(
			array(
				'action'                        => 'WPS_vault_export_ledger',
				'WPS_vault_export_ledger_nonce' => $ledger_nonce,
				'limit'                         => 500,
			),
			admin_url( 'admin-post.php' )
		);

		$journal_base_url = add_query_arg(
			array(
				'action'                         => 'WPS_vault_export_journal',
				'WPS_vault_export_journal_nonce' => $journal_nonce,
			),
			admin_url( 'admin-post.php' )
		);
		if ( ! empty( $queue_state ) ) :
			$qs_type      = isset( $queue_state['type'] ) ? (string) $queue_state['type'] : '—';
			$qs_ok        = (int) ( $queue_state['ok'] ?? 0 );
			$qs_fail      = (int) ( $queue_state['fail'] ?? 0 );
			$qs_missing   = (int) ( $queue_state['missing'] ?? 0 );
			$qs_skipped   = (int) ( $queue_state['skipped'] ?? 0 );
			$qs_processed = (int) ( $queue_state['processed'] ?? 0 );
			$qs_total     = isset( $queue_state['total'] ) ? (string) $queue_state['total'] : '∞';
			$qs_status    = isset( $queue_state['status'] ) ? (string) $queue_state['status'] : '';
			$qs_last_run  = isset( $queue_state['last_run'] ) ? (int) $queue_state['last_run'] : 0;
			$qs_last_txt  = $qs_last_run ? date_i18n( 'M j, Y g:i a', $qs_last_run ) : '—';
			?>
		<div class="notice notice-info" style="margin-top:10px;">
			<p>
				<strong><?php echo esc_html__( 'Vault Job Summary', 'plugin-wp-support-thisismyurl' ); ?>:</strong>
				<?php echo esc_html( sprintf( __( '%1$s (%2$s). Processed %3$d of %4$s. OK %5$d, Fail %6$d, Missing %7$d, Skipped %8$d. Last run: %9$s.', 'plugin-wp-support-thisismyurl' ), $qs_type, $qs_status ?: '—', $qs_processed, $qs_total, $qs_ok, $qs_fail, $qs_missing, $qs_skipped, $qs_last_txt ) ); ?>
				<a class="button button-link" style="margin-left:10px;" href="<?php echo esc_url( $export_url ); ?>"><?php echo esc_html__( 'Export logs', 'plugin-wp-support-thisismyurl' ); ?></a>
				<a class="button button-link" style="margin-left:10px;" href="<?php echo esc_url( $ledger_url ); ?>"><?php echo esc_html__( 'Export ledger (CSV)', 'plugin-wp-support-thisismyurl' ); ?></a>
			</p>
		</div>
	<?php endif; ?>

	<?php if ( $saved ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php echo esc_html__( 'Vault settings saved.', 'plugin-wp-support-thisismyurl' ); ?></p></div>
	<?php endif; ?>

	<?php if ( $locked ) : ?>
		<div class="notice notice-warning"><p><?php echo esc_html__( 'Vault settings are managed at the network level. Site overrides are disabled.', 'plugin-wp-support-thisismyurl' ); ?></p></div>
	<?php endif; ?>

	<?php if ( ! $is_network && is_multisite() && ! WPS_Vault::site_override_allowed() ) : ?>
		<div class="notice notice-info"><p><?php echo esc_html__( 'The Super Admin has marked these settings as final. Your site cannot override them.', 'plugin-wp-support-thisismyurl' ); ?></p></div>
	<?php endif; ?>

	<?php if ( isset( $_GET['WPS_vault_locked'] ) ) : ?>
		<div class="notice notice-warning is-dismissible"><p><?php echo esc_html__( 'Network has disabled site overrides. No changes were saved.', 'plugin-wp-support-thisismyurl' ); ?></p></div>
	<?php endif; ?>

	<?php if ( 'rehydrate' === $tool_notice ) : ?>
		<div class="notice notice-info is-dismissible"><p><?php echo esc_html( sprintf( __( 'Rehydrate completed. Restored: %1$d, Failed: %2$d, Skipped: %3$d.', 'plugin-wp-support-thisismyurl' ), $tool_ok, $tool_fail, $tool_skipped ) ); ?></p></div>
	<?php elseif ( 'verify' === $tool_notice ) : ?>
		<div class="notice notice-info is-dismissible"><p><?php echo esc_html( sprintf( __( 'Verify sample completed. OK: %1$d, Failed: %2$d, Missing: %3$d.', 'plugin-wp-support-thisismyurl' ), $tool_ok, $tool_fail, $tool_missing ) ); ?></p></div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( $action_url ); ?>">
		<?php wp_nonce_field( 'WPS_vault_settings', 'WPS_vault_settings_nonce' ); ?>

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">
					<label for="WPS_vault_encrypt"><?php echo esc_html__( 'Enable Encryption (AES-256-GCM)', 'plugin-wp-support-thisismyurl' ); ?></label>
				</th>
				<td>
					<label>
						<input type="checkbox" name="WPS_vault_encrypt" id="WPS_vault_encrypt" value="1" <?php checked( ! empty( $settings['encrypt'] ) ); ?> <?php disabled( $locked ); ?> />
						<?php echo esc_html__( 'Encrypt vaulted copies at rest with authenticated AES-GCM.', 'plugin-wp-support-thisismyurl' ); ?>
					</label>
					<p class="description"><?php echo esc_html__( 'Requires OpenSSL. Decryption occurs automatically when rehydrating.', 'plugin-wp-support-thisismyurl' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="WPS_vault_enabled"><?php echo esc_html__( 'Enable Vault Ingest', 'plugin-wp-support-thisismyurl' ); ?></label>
				</th>
				<td>
					<label>
						<input type="checkbox" name="WPS_vault_enabled" id="WPS_vault_enabled" value="1" <?php checked( ! empty( $settings['enabled'] ) ); ?> <?php disabled( $locked ); ?> />
						<?php echo esc_html__( 'Store originals into the Vault when attachments are added.', 'plugin-wp-support-thisismyurl' ); ?>
					</label>
				</td>
			</tr>

			<tr>
				<th scope="row"><?php echo esc_html__( 'Storage Mode', 'plugin-wp-support-thisismyurl' ); ?></th>
				<td>
					<fieldset>
						<label>
							<input type="radio" name="WPS_vault_mode" value="raw" <?php checked( 'raw' === $settings['mode'] ); ?> <?php disabled( $locked ); ?> />
							<?php echo esc_html__( 'Raw (bit-identical copy)', 'plugin-wp-support-thisismyurl' ); ?>
						</label><br />
						<label>
							<input type="radio" name="WPS_vault_mode" value="zip" <?php checked( 'zip' === $settings['mode'] ); ?> <?php disabled( $locked ); ?> />
							<?php echo esc_html__( 'Zip (single-file archive)', 'plugin-wp-support-thisismyurl' ); ?>
						</label>
						<p class="description"><?php echo esc_html__( 'Zip keeps originals together; Raw is fastest and bit-identical.', 'plugin-wp-support-thisismyurl' ); ?></p>
					</fieldset>
				</td>
			</tr>

			<tr>
				<th scope="row"><?php echo esc_html__( 'Zip Compression', 'plugin-wp-support-thisismyurl' ); ?></th>
				<td>
					<select name="WPS_vault_compression" <?php disabled( $locked ); ?> >
						<option value="store" <?php selected( $settings['compression'], 'store' ); ?>><?php echo esc_html__( 'Store (no compression, bit-identical)', 'plugin-wp-support-thisismyurl' ); ?></option>
						<option value="deflate" <?php selected( $settings['compression'], 'deflate' ); ?>><?php echo esc_html__( 'Deflate (smaller, slower)', 'plugin-wp-support-thisismyurl' ); ?></option>
					</select>
					<p class="description"><?php echo esc_html__( 'Only applies when Storage Mode is Zip.', 'plugin-wp-support-thisismyurl' ); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="WPS_vault_download_ttl"><?php echo esc_html__( 'Download Link TTL (seconds)', 'plugin-wp-support-thisismyurl' ); ?></label>
				</th>
				<td>
					<input type="number" name="WPS_vault_download_ttl" id="WPS_vault_download_ttl" min="60" step="30" value="<?php echo esc_attr( (int) $settings['download_ttl'] ); ?>" <?php disabled( $locked ); ?> />
					<p class="description"><?php echo esc_html__( 'Signed admin download links expire after this many seconds.', 'plugin-wp-support-thisismyurl' ); ?></p>
				</td>
			</tr>

			<?php if ( $is_network ) : ?>
			<tr>
				<th scope="row">
					<label for="WPS_vault_allow_override"><?php echo esc_html__( 'Allow Site Overrides', 'plugin-wp-support-thisismyurl' ); ?></label>
				</th>
				<td>
					<label>
						<input type="checkbox" name="WPS_vault_allow_override" id="WPS_vault_allow_override" value="1" <?php checked( ! empty( $settings['allow_site_override'] ) ); ?> />
						<?php echo esc_html__( 'Let individual sites change Vault settings.', 'plugin-wp-support-thisismyurl' ); ?>
					</label>
					<p class="description"><?php echo esc_html__( 'Uncheck to enforce these settings across the network.', 'plugin-wp-support-thisismyurl' ); ?></p>
				</td>
			</tr>
			<?php endif; ?>
		</table>

		<h3><?php echo esc_html__( 'Alerts & Resilience', 'plugin-wp-support-thisismyurl' ); ?></h3>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="WPS_vault_max_size_mb"><?php echo esc_html__( 'Max Vault Size (MB)', 'plugin-wp-support-thisismyurl' ); ?></label></th>
				<td>
					<input type="number" min="0" step="50" name="WPS_vault_max_size_mb" id="WPS_vault_max_size_mb" value="<?php echo esc_attr( (int) ( $settings['max_size_mb'] ?? 0 ) ); ?>" <?php disabled( $locked ); ?> />
					<p class="description"><?php echo esc_html__( 'Send an email alert when the vault exceeds this size. 0 disables alerts.', 'plugin-wp-support-thisismyurl' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="WPS_vault_alert_email"><?php echo esc_html__( 'Alert Email', 'plugin-wp-support-thisismyurl' ); ?></label></th>
				<td>
					<input type="email" name="WPS_vault_alert_email" id="WPS_vault_alert_email" class="regular-text" value="<?php echo esc_attr( (string) ( $settings['alert_email'] ?? '' ) ); ?>" <?php disabled( $locked ); ?> />
					<p class="description"><?php echo esc_html__( 'Leave blank to use the site admin email.', 'plugin-wp-support-thisismyurl' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="WPS_vault_mirror_logs"><?php echo esc_html__( 'Mirror Logs to Disk', 'plugin-wp-support-thisismyurl' ); ?></label></th>
				<td>
					<label>
						<input type="checkbox" name="WPS_vault_mirror_logs" id="WPS_vault_mirror_logs" value="1" <?php checked( ! empty( $settings['mirror_logs'] ) ); ?> <?php disabled( $locked ); ?> />
						<?php echo esc_html__( 'Write Vault logs and journals to files inside the Vault for crash resilience.', 'plugin-wp-support-thisismyurl' ); ?>
					</label>
				</td>
			</tr>
		</table>

		<h3><?php echo esc_html__( 'Cloud Offload (Google Drive)', 'plugin-wp-support-thisismyurl' ); ?></h3>
		<p class="description"><?php echo esc_html__( 'Configure a connection to Google Drive to offload vaulted originals and free up disk space.', 'plugin-wp-support-thisismyurl' ); ?></p>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="WPS_vault_offload_enabled"><?php echo esc_html__( 'Enable Offload', 'plugin-wp-support-thisismyurl' ); ?></label></th>
				<td>
					<label>
						<input type="checkbox" name="WPS_vault_offload_enabled" id="WPS_vault_offload_enabled" value="1" <?php checked( ! empty( $settings['offload_enabled'] ) ); ?> <?php disabled( $locked ); ?> />
						<?php echo esc_html__( 'Allow offloading vaulted items to Google Drive when connected.', 'plugin-wp-support-thisismyurl' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="WPS_gdrive_client_id"><?php echo esc_html__( 'Google Client ID', 'plugin-wp-support-thisismyurl' ); ?></label></th>
				<td>
					<input type="text" name="WPS_gdrive_client_id" id="WPS_gdrive_client_id" class="regular-text" value="<?php echo esc_attr( (string) ( $settings['gdrive_client_id'] ?? '' ) ); ?>" <?php disabled( $locked ); ?> />

				</td>
			</tr>
			<tr>
				<th scope="row"><label for="WPS_gdrive_client_secret"><?php echo esc_html__( 'Google Client Secret', 'plugin-wp-support-thisismyurl' ); ?></label></th>
				<td>
					<input type="password" name="WPS_gdrive_client_secret" id="WPS_gdrive_client_secret" class="regular-text" value="<?php echo esc_attr( (string) ( $settings['gdrive_client_secret'] ?? '' ) ); ?>" autocomplete="new-password" <?php disabled( $locked ); ?> />
					<p class="description"><?php echo esc_html__( 'Stored securely in WordPress options. Used to initiate OAuth connection.', 'plugin-wp-support-thisismyurl' ); ?></p>
				</td>
			</tr>
		</table>

		<?php
			$gdrive_status = isset( $_GET['WPS_gdrive_status'] ) ? sanitize_text_field( wp_unslash( $_GET['WPS_gdrive_status'] ) ) : '';
			$gdrive_error  = isset( $_GET['WPS_gdrive_error'] ) ? sanitize_text_field( wp_unslash( $_GET['WPS_gdrive_error'] ) ) : '';
			$connected     = (bool) get_option( 'WPS_gdrive_token_blob', false );
		?>

		<?php if ( $gdrive_status ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php echo esc_html__( 'Google Drive connection updated.', 'plugin-wp-support-thisismyurl' ); ?></p></div>
		<?php endif; ?>
		<?php if ( $gdrive_error ) : ?>
			<div class="notice notice-error is-dismissible"><p><?php echo esc_html__( 'Google Drive error. Check credentials and try again.', 'plugin-wp-support-thisismyurl' ); ?></p></div>
		<?php endif; ?>

		<div style="margin:8px 0 16px;">
			<?php if ( ! empty( $settings['gdrive_client_id'] ) && ! empty( $settings['gdrive_client_secret'] ) ) : ?>
				<?php if ( ! $connected ) : ?>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline-block; margin-right:8px;">
						<?php wp_nonce_field( 'WPS_gdrive_connect', 'WPS_gdrive_nonce' ); ?>
						<input type="hidden" name="action" value="WPS_vault_gdrive_connect" />
						<button class="button" type="submit" <?php disabled( $locked ); ?>><?php echo esc_html__( 'Connect Google Drive', 'plugin-wp-support-thisismyurl' ); ?></button>
					</form>
				<?php else : ?>
					<span style="display:inline-block; padding:2px 8px; border-radius:4px; background:#e8f5e9; color:#2e7d32; margin-right:8px;">
						<?php echo esc_html__( 'Connected', 'plugin-wp-support-thisismyurl' ); ?>
					</span>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline-block;">
						<?php wp_nonce_field( 'WPS_gdrive_disconnect', 'WPS_gdrive_nonce' ); ?>
						<input type="hidden" name="action" value="WPS_vault_gdrive_disconnect" />
						<button class="button" type="submit" onclick="return confirm('<?php echo esc_attr__( 'Disconnect Google Drive?', 'plugin-wp-support-thisismyurl' ); ?>');"><?php echo esc_html__( 'Disconnect', 'plugin-wp-support-thisismyurl' ); ?></button>
					</form>
				<?php endif; ?>
			<?php else : ?>
				<p class="description" style="color:#a00;">
					<?php echo esc_html__( 'Enter a valid Client ID and Secret, save settings, then connect.', 'plugin-wp-support-thisismyurl' ); ?>
				</p>
			<?php endif; ?>
		</div>

		<h4><?php echo esc_html__( 'Offload Oldest Files', 'plugin-wp-support-thisismyurl' ); ?></h4>
		<p class="description"><?php echo esc_html__( 'Uploads the oldest N files from the Vault to the connected Drive folder. Optional: remove local copies after upload to free space.', 'plugin-wp-support-thisismyurl' ); ?></p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'WPS_gdrive_offload', 'WPS_gdrive_nonce' ); ?>
			<input type="hidden" name="action" value="WPS_vault_gdrive_offload" />
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="WPS_gdrive_folder"><?php echo esc_html__( 'Folder Name', 'plugin-wp-support-thisismyurl' ); ?></label></th>
					<td>
						<input type="text" name="WPS_gdrive_folder" id="WPS_gdrive_folder" value="WPS Vault" class="regular-text" />
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="WPS_gdrive_count"><?php echo esc_html__( 'Number of Files', 'plugin-wp-support-thisismyurl' ); ?></label></th>
					<td>
						<input type="number" name="WPS_gdrive_count" id="WPS_gdrive_count" min="1" max="100" value="10" />
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="WPS_gdrive_delete_local"><?php echo esc_html__( 'Delete Local After Upload', 'plugin-wp-support-thisismyurl' ); ?></label></th>
					<td>
						<label>
							<input type="checkbox" name="WPS_gdrive_delete_local" id="WPS_gdrive_delete_local" value="1" />
							<?php echo esc_html__( 'Remove files locally after successful upload to free space (irreversible).', 'plugin-wp-support-thisismyurl' ); ?>
						</label>
					</td>
				</tr>
			</table>
			<?php submit_button( esc_html__( 'Offload Oldest Files', 'plugin-wp-support-thisismyurl' ), 'secondary' ); ?>
		</form>

		<?php submit_button( esc_html__( 'Save Vault Settings', 'plugin-wp-support-thisismyurl' ) ); ?>
	</form>

	<h2><?php echo esc_html__( 'Encryption & Keys', 'plugin-wp-support-thisismyurl' ); ?></h2>
	<p><?php echo esc_html__( 'Manage encryption keys without WP-CLI.', 'plugin-wp-support-thisismyurl' ); ?></p>
	<?php
		$current_key_id = (string) get_option( 'WPS_vault_key_id', '' );
		$prev_key_id    = (string) get_option( 'WPS_vault_prev_key_id', '' );
		$vault_dirname  = (string) get_option( 'WPS_vault_dirname', '' );
	?>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row"><?php echo esc_html__( 'Vault Directory', 'plugin-wp-support-thisismyurl' ); ?></th>
			<td><code><?php echo esc_html( $vault_dirname ? $vault_dirname : '—' ); ?></code></td>
		</tr>
		<tr>
			<th scope="row"><?php echo esc_html__( 'Current Key ID', 'plugin-wp-support-thisismyurl' ); ?></th>
			<td><code><?php echo esc_html( $current_key_id ? $current_key_id : '—' ); ?></code></td>
		</tr>
		<tr>
			<th scope="row"><?php echo esc_html__( 'Previous Key ID', 'plugin-wp-support-thisismyurl' ); ?></th>
			<td><code><?php echo esc_html( $prev_key_id ? $prev_key_id : '—' ); ?></code></td>
		</tr>
	</table>

	<?php if ( isset( $_GET['WPS_vault_key_rotated'] ) ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php echo esc_html__( 'Encryption key rotated. New attachments will use the current key.', 'plugin-wp-support-thisismyurl' ); ?></p></div>
	<?php endif; ?>
	<?php if ( isset( $_GET['WPS_vault_reencrypt'] ) ) : ?>
		<div class="notice notice-info is-dismissible"><p><?php echo esc_html( sprintf( __( 'Re-encrypt sample completed. OK: %1$d, Failed: %2$d.', 'plugin-wp-support-thisismyurl' ), (int) $_GET['ok'], (int) $_GET['fail'] ) ); ?></p></div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'WPS_vault_key_action', 'WPS_vault_key_nonce' ); ?>
		<input type="hidden" name="action" value="WPS_vault_key_action" />
		<p>
			<button class="button" type="submit" name="WPS_vault_key_cmd" value="rotate" onclick="return confirm( '<?php echo esc_attr__( 'Rotate the encryption key? New items will use the new key.', 'plugin-wp-support-thisismyurl' ); ?>' );"><?php echo esc_html__( 'Rotate Key', 'plugin-wp-support-thisismyurl' ); ?></button>
			<span class="description"><?php echo esc_html__( 'Preserves previous key for decryption. Does not re-encrypt existing items.', 'plugin-wp-support-thisismyurl' ); ?></span>
		</p>
		<p>
			<button class="button" type="submit" name="WPS_vault_key_cmd" value="reencrypt_sample" onclick="return confirm( '<?php echo esc_attr__( 'Re-encrypt a recent sample (25 items)?', 'plugin-wp-support-thisismyurl' ); ?>' );"><?php echo esc_html__( 'Re-encrypt Recent Sample (25)', 'plugin-wp-support-thisismyurl' ); ?></button>
			<span class="description"><?php echo esc_html__( 'Decrypts and re-encrypts a small batch to the current key.', 'plugin-wp-support-thisismyurl' ); ?></span>
		</p>
	</form>

	<h2><?php echo esc_html__( 'Vault Tools', 'plugin-wp-support-thisismyurl' ); ?></h2>
	<p><?php echo esc_html__( 'Run light, safe maintenance tasks without WP-CLI.', 'plugin-wp-support-thisismyurl' ); ?></p>
	<form method="post" action="<?php echo esc_url( $action_url ); ?>">
		<?php wp_nonce_field( 'WPS_vault_tools', 'WPS_vault_tools_nonce' ); ?>
		<input type="hidden" name="page" value="<?php echo $is_network ? 'wps-core-network-settings' : 'wp-support'; ?>" />
		<p>
			<button class="button" type="submit" name="WPS_vault_tool_action" value="rehydrate_missing"><?php echo esc_html__( 'Rehydrate missing attachments (up to 25)', 'plugin-wp-support-thisismyurl' ); ?></button>
			<span class="description"><?php echo esc_html__( 'Attempts to restore missing files from the Vault.', 'plugin-wp-support-thisismyurl' ); ?></span>
		</p>
		<p>
			<button class="button" type="submit" name="WPS_vault_tool_action" value="verify_sample"><?php echo esc_html__( 'Verify Vault integrity (sample 10)', 'plugin-wp-support-thisismyurl' ); ?></button>
			<span class="description"><?php echo esc_html__( 'Checks stored hashes against disk for a small sample.', 'plugin-wp-support-thisismyurl' ); ?></span>
		</p>
	</form>

	<h2><?php echo esc_html__( 'Vault Backups', 'plugin-wp-support-thisismyurl' ); ?></h2>
	<p><?php echo esc_html__( 'Create and download a ZIP of the entire Vault. Large sites may take a long time and impact server resources.', 'plugin-wp-support-thisismyurl' ); ?></p>
	<form method="get" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="WPS_vault_export_full" />
		<input type="hidden" name="WPS_vault_export_full_nonce" value="<?php echo esc_attr( wp_create_nonce( 'WPS_vault_export_full' ) ); ?>" />
		<p>
			<button class="button button-secondary" type="submit" onclick="return confirm('<?php echo esc_attr__( 'This may be very large and take significant time. Continue?', 'plugin-wp-support-thisismyurl' ); ?>');"><?php echo esc_html__( 'Download Full Vault (ZIP)', 'plugin-wp-support-thisismyurl' ); ?></button>
		</p>
	</form>

	<h2><?php echo esc_html__( 'Background Jobs (Bulk)', 'plugin-wp-support-thisismyurl' ); ?></h2>
	<p><?php echo esc_html__( 'Launch a background job to process the full library in batches.', 'plugin-wp-support-thisismyurl' ); ?></p>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'WPS_vault_queue_action', 'WPS_vault_queue_nonce' ); ?>
		<input type="hidden" name="action" value="WPS_vault_queue_action" />
		<p>
			<button class="button" type="submit" name="WPS_vault_queue_cmd" value="start_rehydrate"><?php echo esc_html__( 'Start bulk rehydrate', 'plugin-wp-support-thisismyurl' ); ?></button>
			<span class="description"><?php echo esc_html__( 'Background job: restore missing attachments across the library.', 'plugin-wp-support-thisismyurl' ); ?></span>
		</p>
		<p>
			<button class="button" type="submit" name="WPS_vault_queue_cmd" value="start_verify"><?php echo esc_html__( 'Start bulk verify', 'plugin-wp-support-thisismyurl' ); ?></button>
			<span class="description"><?php echo esc_html__( 'Background job: verify stored hashes for vaulted items.', 'plugin-wp-support-thisismyurl' ); ?></span>
		</p>
		<p>
			<button class="button" type="submit" name="WPS_vault_queue_cmd" value="start_reencrypt"><?php echo esc_html__( 'Start bulk re-encrypt', 'plugin-wp-support-thisismyurl' ); ?></button>
			<label style="margin-left:8px;">
				<input type="checkbox" name="WPS_reencrypt_only_old" value="1" />
				<?php echo esc_html__( 'Only re-encrypt older-key items', 'plugin-wp-support-thisismyurl' ); ?>
			</label>
			<span class="description"><?php echo esc_html__( 'Background job: re-encrypt encrypted items to the current key. Use the checkbox to skip items already on the current key.', 'plugin-wp-support-thisismyurl' ); ?></span>
		</p>
		<p>			<button class="button button-primary" type="submit" name="WPS_vault_queue_cmd" value="start_migrate"><?php echo esc_html__( 'Start bulk migrate', 'plugin-wp-support-thisismyurl' ); ?></button>
			<span class="description"><?php echo esc_html__( 'Background job: migrate existing attachments into the Vault (50 per batch).', 'plugin-wp-support-thisismyurl' ); ?></span>
		</p>
		<p>			<button class="button button-primary" type="submit" name="WPS_vault_queue_cmd" value="start_migrate"><?php echo esc_html__( 'Start bulk migrate', 'plugin-wp-support-thisismyurl' ); ?></button>
			<span class="description"><?php echo esc_html__( 'Background job: migrate existing attachments into the Vault (50 per batch).', 'plugin-wp-support-thisismyurl' ); ?></span>
		</p>
		<p>
			<button class="button" type="submit" name="WPS_vault_queue_cmd" value="stop"><?php echo esc_html__( 'Stop job', 'plugin-wp-support-thisismyurl' ); ?></button>
			<span class="description"><?php echo esc_html__( 'Cancel the running job and clear its status.', 'plugin-wp-support-thisismyurl' ); ?></span>
		</p>
	</form>

	<h2><?php echo esc_html__( 'Vault Event Logs', 'plugin-wp-support-thisismyurl' ); ?></h2>
	<p><?php echo esc_html__( 'Recent errors and warnings from Vault operations.', 'plugin-wp-support-thisismyurl' ); ?></p>
	<p>
		<a class="button" href="<?php echo esc_url( $export_url ); ?>"><?php echo esc_html__( 'Download logs (CSV)', 'plugin-wp-support-thisismyurl' ); ?></a>
		<a class="button" href="<?php echo esc_url( $ledger_url ); ?>"><?php echo esc_html__( 'Download ledger (CSV)', 'plugin-wp-support-thisismyurl' ); ?></a>
	</p>
	<p class="description"><?php echo esc_html__( 'When enabled, logs and journals are mirrored to files inside the Vault for added resilience.', 'plugin-wp-support-thisismyurl' ); ?></p>

	<div style="margin:10px 0; padding:12px; background:#fff; border:1px solid #ccd0d4;">
		<h3 style="margin-top:0;"><?php echo esc_html__( 'Export Options', 'plugin-wp-support-thisismyurl' ); ?></h3>
		<div style="display:flex; gap:24px; flex-wrap:wrap;">
			<div>
				<form method="get" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="WPS_vault_export_ledger" />
					<input type="hidden" name="WPS_vault_export_ledger_nonce" value="<?php echo esc_attr( $ledger_nonce ); ?>" />
					<p style="margin:0 0 8px;"><strong><?php echo esc_html__( 'Ledger (CSV) filters', 'plugin-wp-support-thisismyurl' ); ?></strong></p>
					<p style="margin:0 0 8px;">
						<label>
							<?php echo esc_html__( 'Operation (optional)', 'plugin-wp-support-thisismyurl' ); ?>
							<input type="text" name="op" placeholder="migrate|rehydrate|verify|..." style="margin-left:6px;" />
						</label>
					</p>
					<p style="margin:0 0 8px;">
						<label>
							<?php echo esc_html__( 'Attachment ID (optional)', 'plugin-wp-support-thisismyurl' ); ?>
							<input type="number" name="attachment_id" min="1" style="margin-left:6px; width:120px;" />
						</label>
					</p>
					<p style="margin:0 0 8px;">
						<label>
							<?php echo esc_html__( 'Limit', 'plugin-wp-support-thisismyurl' ); ?>
							<input type="number" name="limit" value="500" min="1" max="100000" style="margin-left:6px; width:120px;" />
						</label>
					</p>
					<p style="margin:8px 0 0;">
						<button class="button" type="submit"><?php echo esc_html__( 'Download filtered ledger (CSV)', 'plugin-wp-support-thisismyurl' ); ?></button>
					</p>
				</form>
			</div>
			<div>
				<form method="get" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="WPS_vault_export_journal" />
					<input type="hidden" name="WPS_vault_export_journal_nonce" value="<?php echo esc_attr( $journal_nonce ); ?>" />
					<p style="margin:0 0 8px;"><strong><?php echo esc_html__( 'Attachment Journal (JSON)', 'plugin-wp-support-thisismyurl' ); ?></strong></p>
					<p style="margin:0 0 8px;">
						<label>
							<?php echo esc_html__( 'Attachment ID', 'plugin-wp-support-thisismyurl' ); ?>
							<input type="number" name="attachment_id" min="1" required style="margin-left:6px; width:120px;" />
						</label>
					</p>
					<p style="margin:8px 0 0;">
						<button class="button" type="submit"><?php echo esc_html__( 'Download journal (JSON)', 'plugin-wp-support-thisismyurl' ); ?></button>
					</p>
				</form>
			</div>
			<div>
				<form method="get" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="WPS_vault_export_bundle" />
					<input type="hidden" name="WPS_vault_export_bundle_nonce" value="<?php echo esc_attr( $bundle_nonce ); ?>" />
					<p style="margin:0 0 8px;"><strong><?php echo esc_html__( 'Support Bundle (ZIP)', 'plugin-wp-support-thisismyurl' ); ?></strong></p>
					<p style="margin:0 0 8px;">
						<label>
							<?php echo esc_html__( 'Ledger limit', 'plugin-wp-support-thisismyurl' ); ?>
							<input type="number" name="limit" value="1000" min="1" max="100000" style="margin-left:6px; width:120px;" />
						</label>
					</p>
					<p style="margin:0 0 8px;">
						<label>
							<?php echo esc_html__( 'Operation filter (optional)', 'plugin-wp-support-thisismyurl' ); ?>
							<input type="text" name="op" placeholder="migrate|rehydrate|verify|..." style="margin-left:6px;" />
						</label>
					</p>
					<p style="margin:0 0 8px;">
						<label>
							<?php echo esc_html__( 'Include journal for Attachment ID (optional)', 'plugin-wp-support-thisismyurl' ); ?>
							<input type="number" name="attachment_id" min="1" style="margin-left:6px; width:160px;" />
						</label>
					</p>
					<p style="margin:8px 0 0;">
						<button class="button button-primary" type="submit"><?php echo esc_html__( 'Download support bundle (ZIP)', 'plugin-wp-support-thisismyurl' ); ?></button>
					</p>
				</form>
			</div>
		</div>
	</div>

	<?php if ( isset( $_GET['WPS_vault_logs_cleared'] ) ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php echo esc_html__( 'All logs cleared.', 'plugin-wp-support-thisismyurl' ); ?></p></div>
	<?php endif; ?>

	<?php if ( isset( $_GET['WPS_vault_journal_empty'] ) ) : ?>
		<div class="notice notice-info is-dismissible"><p><?php echo esc_html__( 'No journal entries found for that attachment.', 'plugin-wp-support-thisismyurl' ); ?></p></div>
	<?php endif; ?>

	<?php if ( isset( $_GET['WPS_vault_bundle_error'] ) ) : ?>
		<?php $err = sanitize_text_field( wp_unslash( $_GET['WPS_vault_bundle_error'] ) ); ?>
		<div class="notice notice-error is-dismissible">
			<p>
				<?php echo esc_html__( 'Support bundle could not be created.', 'plugin-wp-support-thisismyurl' ); ?>
				<?php if ( 'zip_missing' === $err ) : ?>
					<?php echo esc_html__( 'The ZipArchive PHP extension is not available.', 'plugin-wp-support-thisismyurl' ); ?>
				<?php elseif ( 'zip_open' === $err ) : ?>
					<?php echo esc_html__( 'Failed to open a ZIP archive for writing.', 'plugin-wp-support-thisismyurl' ); ?>
				<?php endif; ?>
			</p>
		</div>
	<?php endif; ?>

	<?php
	// Get level filter and search query from parameters.
	$level_filter = isset( $_GET['WPS_log_level'] ) ? sanitize_text_field( wp_unslash( $_GET['WPS_log_level'] ) ) : '';
	$level_filter = in_array( $level_filter, array( 'error', 'warning', 'info' ), true ) ? $level_filter : '';

	$search_query = isset( $_GET['WPS_log_search'] ) ? sanitize_text_field( wp_unslash( $_GET['WPS_log_search'] ) ) : '';

	$logs_total  = WPS_Vault::get_log_count( $level_filter, $search_query );
	$logs_page   = isset( $_GET['WPS_log_page'] ) ? max( 1, (int) $_GET['WPS_log_page'] ) : 1;
	$logs_limit  = 20;
	$logs_offset = ( $logs_page - 1 ) * $logs_limit;
	$logs        = WPS_Vault::get_logs( $logs_offset, $logs_limit, $level_filter, $search_query );
	$logs_pages  = ceil( $logs_total / $logs_limit );
	?>

	<?php if ( $logs_total > 0 ) : ?>
		<div class="wps-card" id="wps-vault-logs">
			<div class="wps-card-header">
				<h2 style="margin: 0;"><?php echo esc_html__( 'Vault Activity Log', 'plugin-wp-support-thisismyurl' ); ?></h2>
			</div>
			<div class="wps-card-body">
				<!-- Activity Log Filter & Search -->
				<form method="get" action="" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--wps-space-md); margin-bottom: var(--wps-space-lg);">
					<?php wp_nonce_field( 'WPS_log_filter', 'WPS_log_filter_nonce' ); ?>
					<input type="hidden" name="page" value="<?php echo esc_attr( isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '' ); ?>" />

					<div class="wps-form-group">
						<label for="WPS_log_level_filter" class="wps-form-label">
							<?php echo esc_html__( 'Filter by Level', 'plugin-wp-support-thisismyurl' ); ?>
						</label>
						<select id="WPS_log_level_filter" name="WPS_log_level" class="wps-form-control">
							<option value=""><?php echo esc_html__( 'All Levels', 'plugin-wp-support-thisismyurl' ); ?></option>
							<option value="info" <?php selected( $level_filter, 'info' ); ?>><?php echo esc_html__( 'Info', 'plugin-wp-support-thisismyurl' ); ?></option>
							<option value="warning" <?php selected( $level_filter, 'warning' ); ?>><?php echo esc_html__( 'Warning', 'plugin-wp-support-thisismyurl' ); ?></option>
							<option value="error" <?php selected( $level_filter, 'error' ); ?>><?php echo esc_html__( 'Error', 'plugin-wp-support-thisismyurl' ); ?></option>
						</select>
					</div>

					<div class="wps-form-group">
						<label for="WPS_log_search" class="wps-form-label">
							<?php echo esc_html__( 'Search', 'plugin-wp-support-thisismyurl' ); ?>
						</label>
						<input type="text" id="WPS_log_search" name="WPS_log_search" value="<?php echo esc_attr( $search_query ); ?>" placeholder="<?php echo esc_attr__( 'File, ID, operation...', 'plugin-wp-support-thisismyurl' ); ?>" class="wps-form-control" />
					</div>

					<div style="display: flex; gap: var(--wps-space-sm); align-items: flex-end;">
						<button class="wps-btn wps-btn-primary" type="submit"><?php echo esc_html__( 'Search', 'plugin-wp-support-thisismyurl' ); ?></button>
						<?php if ( ! empty( $level_filter ) || ! empty( $search_query ) ) : ?>
							<a href="<?php echo esc_url( add_query_arg( 'page', isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '' ) ); ?>" class="wps-btn wps-btn-secondary">
								<?php echo esc_html__( 'Clear', 'plugin-wp-support-thisismyurl' ); ?>
							</a>
						<?php endif; ?>
					</div>
				</form>

				<div class="wps-table-responsive">
					<table class="wps-table">
						<thead>
							<tr>
								<th><?php echo esc_html__( 'Timestamp', 'plugin-wp-support-thisismyurl' ); ?></th>
								<th><?php echo esc_html__( 'Level', 'plugin-wp-support-thisismyurl' ); ?></th>
								<th><?php echo esc_html__( 'Attachment ID', 'plugin-wp-support-thisismyurl' ); ?></th>
								<th><?php echo esc_html__( 'Reason', 'plugin-wp-support-thisismyurl' ); ?></th>
								<th><?php echo esc_html__( 'Operation', 'plugin-wp-support-thisismyurl' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $logs as $entry ) : ?>
								<?php
									$badge_class = 'wps-badge-info';
								if ( 'error' === $entry['level'] ) {
									$badge_class = 'wps-badge-danger';
								} elseif ( 'warning' === $entry['level'] ) {
									$badge_class = 'wps-badge-warning';
								}
								?>
								<tr>
									<td><?php echo esc_html( $entry['timestamp'] ); ?></td>
									<td><span class="wps-badge <?php echo esc_attr( $badge_class ); ?>"><?php echo esc_html( ucfirst( $entry['level'] ) ); ?></span></td>
									<td><?php echo $entry['attachment_id'] > 0 ? esc_html( (string) $entry['attachment_id'] ) : '—'; ?></td>
									<td><?php echo esc_html( $entry['reason'] ); ?></td>
									<td><?php echo $entry['operation'] ? esc_html( $entry['operation'] ) : '—'; ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>

		<?php if ( $logs_pages > 1 ) : ?>
				<div style="margin-top: var(--wps-space-lg); padding-top: var(--wps-space-lg); border-top: 1px solid var(--wps-border-subtle);">
					<?php
					$pagination_args = array(
						'base'      => add_query_arg( 'WPS_log_page', '%#%' ),
						'format'    => '',
						'prev_text' => __( '← Previous', 'plugin-wp-support-thisismyurl' ),
						'next_text' => __( 'Next →', 'plugin-wp-support-thisismyurl' ),
						'total'     => $logs_pages,
						'current'   => $logs_page,
						'echo'      => false,
					);

					// Preserve level filter and search query in pagination links.
					if ( ! empty( $level_filter ) ) {
						$pagination_args['base'] = add_query_arg( 'WPS_log_level', $level_filter, $pagination_args['base'] );
					}
					if ( ! empty( $search_query ) ) {
						$pagination_args['base'] = add_query_arg( 'WPS_log_search', $search_query, $pagination_args['base'] );
					}

					$pagination = paginate_links( $pagination_args );
					echo wp_kses_post( $pagination );
					?>
				</div>
			<?php endif; ?>
		</div>
	</div>
<?php else : ?>
	<div class="wps-card" id="wps-vault-logs">
		<div class="wps-card-body">
			<p style="color: var(--wps-text-muted); font-style: italic; margin: 0;"><?php echo esc_html__( 'No logs yet.', 'plugin-wp-support-thisismyurl' ); ?></p>
		</div>
	</div>
	<?php endif; ?>

	<div class="wps-card" style="margin-top: var(--wps-space-xl);">
		<div class="wps-card-body">
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'WPS_vault_logs', 'WPS_vault_log_nonce' ); ?>
				<input type="hidden" name="action" value="WPS_vault_log_action" />
				<button class="wps-btn wps-btn-destructive" type="submit" name="WPS_vault_log_action" value="clear_all" onclick="return confirm( '<?php echo esc_attr__( 'Clear all logs? This cannot be undone.', 'plugin-wp-support-thisismyurl' ); ?>' );"><?php echo esc_html__( 'Clear All Logs', 'plugin-wp-support-thisismyurl' ); ?></button>
			</form>
		</div>
	</div>
</div>



