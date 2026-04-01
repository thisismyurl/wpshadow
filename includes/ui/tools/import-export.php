<?php
/**
 * Settings Import/Export Tool
 *
 * Backup, restore, or sync WPShadow configuration across sites.
 *
 * @package WPShadow
 * @since 0.6093.1200
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPShadow\Views\Tool_View_Base;
use WPShadow\Core\WPShadow_Account_API;

require WPSHADOW_PATH . 'includes/views/class-tool-view-base.php';

// Verify access.
Tool_View_Base::verify_access( 'manage_options' );

// Enqueue assets.
Tool_View_Base::enqueue_assets( 'import-export' );

// Render header.
Tool_View_Base::render_header(
	__( 'Settings Backup & Restore', 'wpshadow' ),
	__( 'Save your WPShadow settings to a file and copy them to other sites.', 'wpshadow' )
);

$is_registered     = WPShadow_Account_API::is_registered();
$account_info      = $is_registered ? WPShadow_Account_API::get_account_info() : null;
$last_export       = get_option( 'wpshadow_last_export_date', '' );
$last_import       = get_option( 'wpshadow_last_import_date', '' );
$cloud_sync_enabled = get_option( 'wpshadow_cloud_sync_enabled', false );
$last_cloud_sync   = get_option( 'wpshadow_last_cloud_sync', '' );
?>

<style>
	.wps-import-export-card {
		background: #fff;
		border: 1px solid #dcdcde;
		border-radius: 4px;
		padding: 20px;
		margin-bottom: 20px;
	}
	.wps-import-export-card h3 {
		margin-top: 0;
		display: flex;
		align-items: center;
		gap: 10px;
		font-size: 16px;
	}
	.wps-import-export-card h3 .dashicons {
		color: #2271b1;
		font-size: 24px;
		width: 24px;
		height: 24px;
	}
	.wps-import-export-card p {
		margin: 10px 0;
		color: #3c434a;
	}
	.wps-import-export-card ul {
		margin: 10px 0;
		padding-left: 30px;
		color: #646970;
	}
	.wps-import-export-card .button {
		margin-top: 10px;
	}
	.wps-alert {
		padding: 12px;
		border-left: 4px solid;
		margin: 15px 0;
		background: #fff;
	}
	.wps-alert--info {
		border-color: #72aee6;
		background: #f0f6fc;
	}
	.wps-alert--success {
		border-color: #46b450;
		background: #f0f6f0;
	}
	.wps-alert--warning {
		border-color: #dba617;
		background: #fcf9e8;
	}
	.wps-alert .dashicons {
		float: left;
		margin-right: 8px;
	}
</style>

<!-- Export Section -->
<div class="wps-import-export-card">
	<h3>
		<span class="dashicons dashicons-download"></span>
		<?php esc_html_e( 'Export Settings', 'wpshadow' ); ?>
	</h3>
	<p><?php esc_html_e( 'Download your complete WPShadow configuration as a JSON file.', 'wpshadow' ); ?></p>
	<p class="description"><?php esc_html_e( 'This will export all your WPShadow settings including:', 'wpshadow' ); ?></p>
	<ul>
		<li><?php esc_html_e( 'Guardian settings and auto-fix preferences', 'wpshadow' ); ?></li>
		<li><?php esc_html_e( 'Email notification configuration', 'wpshadow' ); ?></li>
		<li><?php esc_html_e( 'Privacy and telemetry preferences', 'wpshadow' ); ?></li>
		<li><?php esc_html_e( 'Cache settings and performance options', 'wpshadow' ); ?></li>
		<li><?php esc_html_e( 'Workflow automation rules', 'wpshadow' ); ?></li>
		<li><?php esc_html_e( 'Advanced configuration options', 'wpshadow' ); ?></li>
	</ul>
	<p class="description">
		<strong><?php esc_html_e( 'Note:', 'wpshadow' ); ?></strong>
		<?php esc_html_e( 'Sensitive data like API keys are excluded from exports for security.', 'wpshadow' ); ?>
	</p>

	<?php if ( $last_export ) : ?>
		<div class="wps-alert wps-alert--info">
			<span class="dashicons dashicons-info"></span>
			<div>
				<strong><?php esc_html_e( 'Last Export:', 'wpshadow' ); ?></strong>
				<?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $last_export ) ) ); ?>
			</div>
		</div>
	<?php endif; ?>

	<button
		type="button"
		class="button button-primary"
		id="wpshadow-export-settings"
		data-nonce="<?php echo esc_attr( wp_create_nonce( 'wpshadow_export_settings' ) ); ?>"
	>
		<span class="dashicons dashicons-download" style="margin-top: 3px;"></span>
		<?php esc_html_e( 'Export Settings', 'wpshadow' ); ?>
	</button>
</div>

<!-- Import Section -->
<div class="wps-import-export-card">
	<h3>
		<span class="dashicons dashicons-upload"></span>
		<?php esc_html_e( 'Import Settings', 'wpshadow' ); ?>
	</h3>
	<p><?php esc_html_e( 'Restore your WPShadow configuration from a previously exported JSON file.', 'wpshadow' ); ?></p>

	<div style="margin: 15px 0;">
		<label for="wpshadow-import-file" style="display: block; margin-bottom: 5px; font-weight: 600;">
			<?php esc_html_e( 'Select Settings File', 'wpshadow' ); ?>
		</label>
		<input
			type="file"
			id="wpshadow-import-file"
			accept=".json,application/json"
			style="margin-bottom: 5px;"
		/>
		<p class="description">
			<?php esc_html_e( 'Choose a JSON file exported from WPShadow.', 'wpshadow' ); ?>
		</p>
	</div>

	<?php if ( $last_import ) : ?>
		<div class="wps-alert wps-alert--success">
			<span class="dashicons dashicons-yes"></span>
			<div>
				<strong><?php esc_html_e( 'Last Import:', 'wpshadow' ); ?></strong>
				<?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $last_import ) ) ); ?>
			</div>
		</div>
	<?php endif; ?>

	<div class="wps-alert wps-alert--warning">
		<span class="dashicons dashicons-warning"></span>
		<div>
			<strong><?php esc_html_e( 'Caution:', 'wpshadow' ); ?></strong>
			<?php esc_html_e( 'Importing will overwrite your current settings. Consider exporting first as a backup.', 'wpshadow' ); ?>
		</div>
	</div>

	<button
		type="button"
		class="button button-primary"
		id="wpshadow-import-settings"
		data-nonce="<?php echo esc_attr( wp_create_nonce( 'wpshadow_import_settings' ) ); ?>"
		disabled
	>
		<span class="dashicons dashicons-upload" style="margin-top: 3px;"></span>
		<?php esc_html_e( 'Import Settings', 'wpshadow' ); ?>
	</button>
</div>

<!-- Cloud Sync Section (Paid Feature) -->
<?php if ( $is_registered ) : ?>
	<div class="wps-import-export-card">
		<h3>
			<span class="dashicons dashicons-cloud"></span>
			<?php esc_html_e( 'Cloud Sync', 'wpshadow' ); ?>
		</h3>
		<p><?php esc_html_e( 'Automatically sync your settings across multiple sites using WPShadow Cloud.', 'wpshadow' ); ?></p>

		<div style="margin: 15px 0;">
			<label>
				<input
					type="checkbox"
					id="wpshadow-cloud-sync-enabled"
					<?php checked( $cloud_sync_enabled ); ?>
				/>
				<?php esc_html_e( 'Enable Cloud Sync', 'wpshadow' ); ?>
			</label>
			<p class="description">
				<?php esc_html_e( 'Your settings will be automatically backed up to WPShadow Cloud and can be synced to other sites.', 'wpshadow' ); ?>
			</p>
		</div>

		<?php if ( $last_cloud_sync ) : ?>
			<div class="wps-alert wps-alert--info">
				<span class="dashicons dashicons-yes"></span>
				<div>
					<strong><?php esc_html_e( 'Last Cloud Sync:', 'wpshadow' ); ?></strong>
					<?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $last_cloud_sync ) ) ); ?>
				</div>
			</div>
		<?php endif; ?>

		<button
			type="button"
			class="button button-secondary"
			id="wpshadow-sync-now"
			data-nonce="<?php echo esc_attr( wp_create_nonce( 'wpshadow_cloud_sync' ) ); ?>"
			<?php disabled( ! $cloud_sync_enabled ); ?>
		>
			<span class="dashicons dashicons-update" style="margin-top: 3px;"></span>
			<?php esc_html_e( 'Sync Now', 'wpshadow' ); ?>
		</button>
	</div>
<?php else : ?>
	<div class="wps-import-export-card" style="background: #f0f6fc; border-left: 4px solid #72aee6;">
		<h3>
			<span class="dashicons dashicons-cloud"></span>
			<?php esc_html_e( 'Cloud Sync (Paid Feature)', 'wpshadow' ); ?>
		</h3>
		<p><?php esc_html_e( 'Automatically sync your settings across multiple sites using WPShadow Cloud.', 'wpshadow' ); ?></p>
		<p><strong><?php esc_html_e( 'Register for free to:', 'wpshadow' ); ?></strong></p>
		<ul>
			<li><?php esc_html_e( 'Backup settings to the cloud automatically', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Sync settings across multiple sites', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Restore from any device', 'wpshadow' ); ?></li>
		</ul>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-utilities&tab=cloud-registration' ) ); ?>" class="button button-primary">
			<?php esc_html_e( 'Register Free', 'wpshadow' ); ?>
		</a>
	</div>
<?php endif; ?>

<script>
jQuery(function($) {
	// Enable import button when file is selected
	$('#wpshadow-import-file').on('change', function() {
		$('#wpshadow-import-settings').prop('disabled', !this.files.length);
	});

	// Export settings
	$('#wpshadow-export-settings').on('click', function() {
		var $btn = $(this);
		var nonce = $btn.data('nonce');

		$btn.prop('disabled', true).text('<?php echo esc_js( __( 'Exporting...', 'wpshadow' ) ); ?>');

		$.ajax({
			url: ajaxurl,
			method: 'POST',
			data: {
				action: 'wpshadow_export_settings',
				nonce: nonce
			},
			success: function(response) {
				if (response.success && response.data.file_content) {
					// Create download
					var blob = new Blob([response.data.file_content], { type: 'application/json' });
					var url = URL.createObjectURL(blob);
					var a = document.createElement('a');
					a.href = url;
					a.download = response.data.filename || 'wpshadow-settings-export.json';
					document.body.appendChild(a);
					a.click();
					document.body.removeChild(a);
					URL.revokeObjectURL(url);

					alert('<?php echo esc_js( __( 'Settings exported successfully!', 'wpshadow' ) ); ?>');
					location.reload();
				} else {
					alert(response.data.message || '<?php echo esc_js( __( 'Export failed.', 'wpshadow' ) ); ?>');
				}
			},
			error: function() {
				alert('<?php echo esc_js( __( 'Network error during export.', 'wpshadow' ) ); ?>');
			},
			complete: function() {
				$btn.prop('disabled', false).html('<span class="dashicons dashicons-download" style="margin-top: 3px;"></span> <?php echo esc_js( __( 'Export Settings', 'wpshadow' ) ); ?>');
			}
		});
	});

	// Import settings
	$('#wpshadow-import-settings').on('click', function() {
		var $btn = $(this);
		var fileInput = $('#wpshadow-import-file')[0];
		var nonce = $btn.data('nonce');

		if (!fileInput.files.length) {
			alert('<?php echo esc_js( __( 'Please select a file first.', 'wpshadow' ) ); ?>');
			return;
		}

		if (!confirm('<?php echo esc_js( __( 'This will overwrite your current settings. Are you sure?', 'wpshadow' ) ); ?>')) {
			return;
		}

		$btn.prop('disabled', true).text('<?php echo esc_js( __( 'Importing...', 'wpshadow' ) ); ?>');

		var reader = new FileReader();
		reader.onload = function(e) {
			$.ajax({
				url: ajaxurl,
				method: 'POST',
				data: {
					action: 'wpshadow_import_settings',
					nonce: nonce,
					settings_data: e.target.result
				},
				success: function(response) {
					if (response.success) {
						alert('<?php echo esc_js( __( 'Settings imported successfully!', 'wpshadow' ) ); ?>');
						location.reload();
					} else {
						alert(response.data.message || '<?php echo esc_js( __( 'Import failed.', 'wpshadow' ) ); ?>');
						$btn.prop('disabled', false).html('<span class="dashicons dashicons-upload" style="margin-top: 3px;"></span> <?php echo esc_js( __( 'Import Settings', 'wpshadow' ) ); ?>');
					}
				},
				error: function() {
					alert('<?php echo esc_js( __( 'Network error during import.', 'wpshadow' ) ); ?>');
					$btn.prop('disabled', false).html('<span class="dashicons dashicons-upload" style="margin-top: 3px;"></span> <?php echo esc_js( __( 'Import Settings', 'wpshadow' ) ); ?>');
				}
			});
		};
		reader.readAsText(fileInput.files[0]);
	});

	// Cloud sync toggle
	$('#wpshadow-cloud-sync-enabled').on('change', function() {
		var enabled = $(this).is(':checked');
		$.ajax({
			url: ajaxurl,
			method: 'POST',
			data: {
				action: 'wpshadow_toggle_cloud_sync',
				nonce: '<?php echo esc_js( wp_create_nonce( 'wpshadow_cloud_sync_toggle' ) ); ?>',
				enabled: enabled ? '1' : '0'
			},
			success: function(response) {
				if (response.success) {
					$('#wpshadow-sync-now').prop('disabled', !enabled);
					alert(enabled ? '<?php echo esc_js( __( 'Cloud sync enabled!', 'wpshadow' ) ); ?>' : '<?php echo esc_js( __( 'Cloud sync disabled.', 'wpshadow' ) ); ?>');
				}
			}
		});
	});

	// Sync now
	$('#wpshadow-sync-now').on('click', function() {
		var $btn = $(this);
		$btn.prop('disabled', true).text('<?php echo esc_js( __( 'Syncing...', 'wpshadow' ) ); ?>');

		$.ajax({
			url: ajaxurl,
			method: 'POST',
			data: {
				action: 'wpshadow_cloud_sync_now',
				nonce: $btn.data('nonce')
			},
			success: function(response) {
				if (response.success) {
					alert('<?php echo esc_js( __( 'Settings synced to cloud successfully!', 'wpshadow' ) ); ?>');
					location.reload();
				} else {
					alert(response.data.message || '<?php echo esc_js( __( 'Cloud sync failed.', 'wpshadow' ) ); ?>');
				}
			},
			error: function() {
				alert('<?php echo esc_js( __( 'Network error during sync.', 'wpshadow' ) ); ?>');
			},
			complete: function() {
				$btn.prop('disabled', false).html('<span class="dashicons dashicons-update" style="margin-top: 3px;"></span> <?php echo esc_js( __( 'Sync Now', 'wpshadow' ) ); ?>');
			}
		});
	});
});
</script>

<?php
// Render footer.
Tool_View_Base::render_footer();
