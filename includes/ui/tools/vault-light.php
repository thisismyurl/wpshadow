<?php
/**
 * WPShadow Vault Light Tool
 *
 * @package WPShadow
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPShadow\Views\Tool_View_Base;

require WPSHADOW_PATH . 'includes/views/class-tool-view-base.php';

// Verify access
Tool_View_Base::verify_access( 'manage_options' );

// Enqueue assets
Tool_View_Base::enqueue_assets( 'vault-light' );

// Render header
Tool_View_Base::render_header( __( 'WPShadow Vault Light', 'wpshadow' ) );

// Get Vault Light settings
$backup_enabled          = get_option( 'wpshadow_backup_enabled', true );
$backup_include_database = get_option( 'wpshadow_backup_include_database', true );
$backup_retention_days   = get_option( 'wpshadow_backup_retention_days', 7 );
$backup_max_size_mb      = get_option( 'wpshadow_backup_max_size_mb', 500 );
$backup_compress         = get_option( 'wpshadow_backup_compress', true );
$backup_exclude_uploads  = get_option( 'wpshadow_backup_exclude_uploads', false );
$backup_verify           = get_option( 'wpshadow_backup_verify', true );
$backup_schedule_enabled = get_option( 'wpshadow_backup_schedule_enabled', false );
$backup_schedule_time    = get_option( 'wpshadow_backup_schedule_time', '02:00' );
$backup_schedule_freq    = get_option( 'wpshadow_backup_schedule_frequency', 'weekly' );
$backup_last_run         = get_option( 'wpshadow_backup_last_run', 0 );
$backup_next_run         = wp_next_scheduled( 'wpshadow_scheduled_backup' );

// Get backup directory info
$backup_dir      = WP_CONTENT_DIR . '/wpshadow-backups';
$backup_size     = 0;
$backup_count    = 0;
$backup_writable = is_dir( $backup_dir ) && is_writable( $backup_dir );

if ( is_dir( $backup_dir ) ) {
	$files = glob( $backup_dir . '/*.zip' );
	$backup_count = is_array( $files ) ? count( $files ) : 0;
	foreach ( $files as $file ) {
		$backup_size += filesize( $file );
	}
}
?>

	<p><?php esc_html_e( 'Vault Light gives you scheduled snapshots to help protect your site and simplify recovery when changes go wrong.', 'wpshadow' ); ?></p>

	<!-- Backup Status Overview -->
	<div class="wpshadow-tool-section">
		<h3><?php esc_html_e( 'Vault Light Status', 'wpshadow' ); ?></h3>
		<table class="widefat">
			<tr>
				<td><strong><?php esc_html_e( 'Vault Light Snapshot Before Treatments', 'wpshadow' ); ?></strong></td>
				<td><?php echo $backup_enabled ? '<span style="color: green;">✓ ' . esc_html__( 'Enabled', 'wpshadow' ) . '</span>' : '<span style="color: red;">✗ ' . esc_html__( 'Disabled', 'wpshadow' ) . '</span>'; ?></td>
			</tr>
			<tr>
				<td><strong><?php esc_html_e( 'Scheduled Vault Light Snapshots', 'wpshadow' ); ?></strong></td>
				<td><?php echo $backup_schedule_enabled ? '<span style="color: green;">✓ ' . esc_html__( 'Enabled', 'wpshadow' ) . '</span>' : '<span style="color: red;">✗ ' . esc_html__( 'Disabled', 'wpshadow' ) . '</span>'; ?></td>
			</tr>
			<tr>
				<td><strong><?php esc_html_e( 'Total Snapshots', 'wpshadow' ); ?></strong></td>
				<td><?php echo esc_html( number_format_i18n( $backup_count ) ); ?></td>
			</tr>
			<tr>
				<td><strong><?php esc_html_e( 'Total Size', 'wpshadow' ); ?></strong></td>
				<td><?php echo esc_html( size_format( $backup_size, 2 ) ); ?></td>
			</tr>
			<tr>
				<td><strong><?php esc_html_e( 'Snapshot Directory', 'wpshadow' ); ?></strong></td>
				<td>
					<?php
					if ( ! is_dir( $backup_dir ) ) {
						echo '<span style="color: #d98300;">' . esc_html__( 'Will be created on first backup', 'wpshadow' ) . '</span>';
					} elseif ( $backup_writable ) {
						echo '<span style="color: green;">' . esc_html__( 'Writable', 'wpshadow' ) . '</span>';
					} else {
						echo '<span style="color: red;">' . esc_html__( 'Not writable - check permissions', 'wpshadow' ) . '</span>';
					}
					?>
				</td>
			</tr>
			<tr>
				<td><strong><?php esc_html_e( 'Snapshot Retention', 'wpshadow' ); ?></strong></td>
				<td><?php echo esc_html( sprintf( _n( '%d day', '%d days', $backup_retention_days, 'wpshadow' ), $backup_retention_days ) ); ?></td>
			</tr>
			<tr>
				<td><strong><?php esc_html_e( 'Last Scheduled Run', 'wpshadow' ); ?></strong></td>
				<td>
					<?php
					if ( $backup_last_run ) {
						echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), (int) $backup_last_run ) );
					} else {
						echo esc_html__( 'Not run yet', 'wpshadow' );
					}
					?>
				</td>
			</tr>
			<tr>
				<td><strong><?php esc_html_e( 'Next Scheduled Run', 'wpshadow' ); ?></strong></td>
				<td>
					<?php
					if ( $backup_next_run ) {
						echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), (int) $backup_next_run ) );
					} else {
						echo esc_html__( 'Not scheduled', 'wpshadow' );
					}
					?>
				</td>
			</tr>
		</table>
	</div>

	<!-- Snapshot Settings Form -->
	<form method="post" action="options.php" class="wpshadow-tool-section">
		<?php settings_fields( 'wpshadow_settings' ); ?>

		<h3><?php esc_html_e( 'WPShadow Vault Light Settings', 'wpshadow' ); ?></h3>

		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Form_Controls outputs escaped HTML.
		echo \WPShadow\Helpers\Form_Controls::toggle_switch(
			array(
				'id'          => 'wpshadow_backup_enabled',
				'name'        => 'wpshadow_backup_enabled',
				'label'       => __( 'Create a Vault Light snapshot before treatments', 'wpshadow' ),
				'helper_text' => __( 'Capture critical configuration and WPShadow settings before treatments, so you can roll back quickly if needed.', 'wpshadow' ),
				'checked'     => (bool) $backup_enabled,
			)
		);
		?>

		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Form_Controls outputs escaped HTML.
		echo \WPShadow\Helpers\Form_Controls::toggle_switch(
			array(
				'id'          => 'wpshadow_backup_schedule_enabled',
				'name'        => 'wpshadow_backup_schedule_enabled',
				'label'       => __( 'Enable scheduled WPShadow Vault Light snapshots', 'wpshadow' ),
				'helper_text' => __( 'Run lightweight snapshots on a schedule so you always have a recent restore point.', 'wpshadow' ),
				'checked'     => (bool) $backup_schedule_enabled,
			)
		);
		?>

		<div class="wps-form-group">
			<label for="wpshadow_backup_schedule_frequency"><?php esc_html_e( 'Snapshot frequency', 'wpshadow' ); ?></label>
			<select id="wpshadow_backup_schedule_frequency" name="wpshadow_backup_schedule_frequency">
				<option value="daily" <?php selected( $backup_schedule_freq, 'daily' ); ?>><?php esc_html_e( 'Daily', 'wpshadow' ); ?></option>
				<option value="weekly" <?php selected( $backup_schedule_freq, 'weekly' ); ?>><?php esc_html_e( 'Weekly', 'wpshadow' ); ?></option>
				<option value="monthly" <?php selected( $backup_schedule_freq, 'monthly' ); ?>><?php esc_html_e( 'Monthly', 'wpshadow' ); ?></option>
			</select>
			<p class="description"><?php esc_html_e( 'How often WPShadow Vault Light snapshots should run.', 'wpshadow' ); ?></p>
		</div>

		<div class="wps-form-group">
			<label for="wpshadow_backup_schedule_time"><?php esc_html_e( 'Snapshot time (24h)', 'wpshadow' ); ?></label>
			<input type="text" id="wpshadow_backup_schedule_time" name="wpshadow_backup_schedule_time" value="<?php echo esc_attr( $backup_schedule_time ); ?>" placeholder="02:00" style="width: 120px;" />
			<p class="description"><?php esc_html_e( 'We will run the snapshot at this time in your WordPress timezone.', 'wpshadow' ); ?></p>
		</div>

		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Form_Controls outputs escaped HTML.
		echo \WPShadow\Helpers\Form_Controls::toggle_switch(
			array(
				'id'          => 'wpshadow_backup_include_database',
				'name'        => 'wpshadow_backup_include_database',
				'label'       => __( 'Include database in snapshots', 'wpshadow' ),
				'helper_text' => __( 'Include database settings in your Vault Light snapshot for faster recovery.', 'wpshadow' ),
				'checked'     => (bool) $backup_include_database,
			)
		);
		?>

		<div class="wps-form-group">
			<label for="wpshadow_backup_retention_days"><?php esc_html_e( 'Keep snapshots for', 'wpshadow' ); ?></label>
			<select id="wpshadow_backup_retention_days" name="wpshadow_backup_retention_days">
				<option value="3" <?php selected( $backup_retention_days, '3' ); ?>><?php esc_html_e( '3 days', 'wpshadow' ); ?></option>
				<option value="7" <?php selected( $backup_retention_days, '7' ); ?>><?php esc_html_e( '1 week', 'wpshadow' ); ?></option>
				<option value="14" <?php selected( $backup_retention_days, '14' ); ?>><?php esc_html_e( '2 weeks', 'wpshadow' ); ?></option>
				<option value="30" <?php selected( $backup_retention_days, '30' ); ?>><?php esc_html_e( '1 month', 'wpshadow' ); ?></option>
				<option value="90" <?php selected( $backup_retention_days, '90' ); ?>><?php esc_html_e( '3 months', 'wpshadow' ); ?></option>
			</select>
			<p class="description"><?php esc_html_e( 'Snapshots older than this are automatically deleted to save disk space.', 'wpshadow' ); ?></p>
		</div>

		<div class="wps-form-group">
			<label for="wpshadow_backup_max_size_mb"><?php esc_html_e( 'Maximum total snapshot size (MB)', 'wpshadow' ); ?></label>
			<?php
			$available_backup_space_mb = absint( $backup_max_size_mb );
			if ( $available_backup_space_mb < 100 ) {
				$available_backup_space_mb = 500;
			}

			$disk_probe_path = is_dir( WP_CONTENT_DIR ) ? WP_CONTENT_DIR : ABSPATH;
			$disk_free_bytes = disk_free_space( $disk_probe_path );
			$disk_free_label = ( false !== $disk_free_bytes )
				? size_format( (float) $disk_free_bytes, 2 )
				: __( 'unavailable', 'wpshadow' );
			?>
			<input type="number" id="wpshadow_backup_max_size_mb" name="wpshadow_backup_max_size_mb" value="<?php echo esc_attr( $available_backup_space_mb ); ?>" min="100" step="50" style="width: 150px;" />
			<p class="description">
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

		<h3><?php esc_html_e( 'Advanced Options', 'wpshadow' ); ?></h3>

		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Form_Controls outputs escaped HTML.
		echo \WPShadow\Helpers\Form_Controls::toggle_switch(
			array(
				'id'          => 'wpshadow_backup_compress',
				'name'        => 'wpshadow_backup_compress',
				'label'       => __( 'Compress snapshots', 'wpshadow' ),
				'helper_text' => __( 'Compress snapshot files to save disk space (slower but saves ~70% space).', 'wpshadow' ),
				'checked'     => (bool) $backup_compress,
			)
		);
		?>

		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Form_Controls outputs escaped HTML.
		echo \WPShadow\Helpers\Form_Controls::toggle_switch(
			array(
				'id'          => 'wpshadow_backup_exclude_uploads',
				'name'        => 'wpshadow_backup_exclude_uploads',
				'label'       => __( 'Exclude /uploads folder', 'wpshadow' ),
				'helper_text' => __( 'Skip the /wp-content/uploads folder to reduce snapshot size (only backup files that might be modified by treatments).', 'wpshadow' ),
				'checked'     => (bool) $backup_exclude_uploads,
			)
		);
		?>

		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Form_Controls outputs escaped HTML.
		echo \WPShadow\Helpers\Form_Controls::toggle_switch(
			array(
				'id'          => 'wpshadow_backup_verify',
				'name'        => 'wpshadow_backup_verify',
				'label'       => __( 'Verify snapshots after creation', 'wpshadow' ),
				'helper_text' => __( 'Test each snapshot for integrity (slower but ensures you can rollback if needed).', 'wpshadow' ),
				'checked'     => (bool) $backup_verify,
			)
		);
		?>

		<p class="submit">
			<button type="submit" class="wps-btn wps-btn-primary">
				<?php esc_html_e( 'Save Settings', 'wpshadow' ); ?>
			</button>
		</p>
	</form>

	<!-- Snapshot Management section intentionally removed -->

</div>

<?php Tool_View_Base::render_footer(); ?>
