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

// Get backup settings
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

	<p><?php esc_html_e( 'Vault Light gives you scheduled snapshots inspired by Vault. It keeps your site safer today and makes a seamless upgrade to Vault later.', 'wpshadow' ); ?></p>

	<!-- Free Offsite Storage Notice -->
	<div class="notice notice-success" style="margin-top:10px;">
		<p><strong><?php esc_html_e( 'Free Offsite Storage for Registered Users', 'wpshadow' ); ?></strong></p>
		<p><?php esc_html_e( 'When you register for WPShadow (free!), you get secure offsite storage for your last three Vault Light snapshots and free restores whenever you need them.', 'wpshadow' ); ?> <a href="https://wpshadow.com/features/offsite-backup/" target="_blank"><?php esc_html_e( 'Learn more', 'wpshadow' ); ?></a></p>
	</div>

	<!-- Backup Status Overview -->
	<div class="wpshadow-tool-section">
		<h3><?php esc_html_e( 'Vault Light Status', 'wpshadow' ); ?></h3>
		<table class="widefat">
			<tr>
				<td><strong><?php esc_html_e( 'Backup Before Treatments', 'wpshadow' ); ?></strong></td>
				<td><?php echo $backup_enabled ? '<span style="color: green;">✓ ' . esc_html__( 'Enabled', 'wpshadow' ) . '</span>' : '<span style="color: red;">✗ ' . esc_html__( 'Disabled', 'wpshadow' ) . '</span>'; ?></td>
			</tr>
			<tr>
				<td><strong><?php esc_html_e( 'Scheduled Backups', 'wpshadow' ); ?></strong></td>
				<td><?php echo $backup_schedule_enabled ? '<span style="color: green;">✓ ' . esc_html__( 'Enabled', 'wpshadow' ) . '</span>' : '<span style="color: red;">✗ ' . esc_html__( 'Disabled', 'wpshadow' ) . '</span>'; ?></td>
			</tr>
			<tr>
				<td><strong><?php esc_html_e( 'Total Backups', 'wpshadow' ); ?></strong></td>
				<td><?php echo esc_html( number_format_i18n( $backup_count ) ); ?></td>
			</tr>
			<tr>
				<td><strong><?php esc_html_e( 'Total Size', 'wpshadow' ); ?></strong></td>
				<td><?php echo esc_html( size_format( $backup_size, 2 ) ); ?></td>
			</tr>
			<tr>
				<td><strong><?php esc_html_e( 'Backup Directory', 'wpshadow' ); ?></strong></td>
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
				<td><strong><?php esc_html_e( 'Retention Period', 'wpshadow' ); ?></strong></td>
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

	<!-- Backup Settings Form -->
	<form method="post" action="options.php" class="wpshadow-tool-section">
		<?php settings_fields( 'wpshadow_settings' ); ?>
		
		<h3><?php esc_html_e( 'WPShadow Vault Light Settings', 'wpshadow' ); ?></h3>

		<div class="wps-form-group">
			<label>
				<input type="checkbox" id="wpshadow_backup_enabled" name="wpshadow_backup_enabled" value="1" <?php checked( $backup_enabled ); ?> />
				<?php esc_html_e( 'Create a Vault Light snapshot before treatments', 'wpshadow' ); ?>
			</label>
			<p class="description">
				<?php esc_html_e( 'Capture critical configuration and WPShadow settings before treatments, so you can roll back quickly if needed.', 'wpshadow' ); ?>
			</p>
		</div>

		<div class="wps-form-group">
			<label>
				<input type="checkbox" id="wpshadow_backup_schedule_enabled" name="wpshadow_backup_schedule_enabled" value="1" <?php checked( $backup_schedule_enabled ); ?> />
				<?php esc_html_e( 'Enable scheduled WPShadow Vault Light backups', 'wpshadow' ); ?>
			</label>
			<p class="description">
				<?php esc_html_e( 'Run lightweight snapshots on a schedule so you always have a recent restore point.', 'wpshadow' ); ?>
			</p>
		</div>

		<div class="wps-form-group">
			<label for="wpshadow_backup_schedule_frequency"><?php esc_html_e( 'Backup frequency', 'wpshadow' ); ?></label>
			<select id="wpshadow_backup_schedule_frequency" name="wpshadow_backup_schedule_frequency">
				<option value="daily" <?php selected( $backup_schedule_freq, 'daily' ); ?>><?php esc_html_e( 'Daily', 'wpshadow' ); ?></option>
				<option value="weekly" <?php selected( $backup_schedule_freq, 'weekly' ); ?>><?php esc_html_e( 'Weekly', 'wpshadow' ); ?></option>
				<option value="monthly" <?php selected( $backup_schedule_freq, 'monthly' ); ?>><?php esc_html_e( 'Monthly', 'wpshadow' ); ?></option>
			</select>
			<p class="description"><?php esc_html_e( 'How often WPShadow Vault Light snapshots should run.', 'wpshadow' ); ?></p>
		</div>

		<div class="wps-form-group">
			<label for="wpshadow_backup_schedule_time"><?php esc_html_e( 'Backup time (24h)', 'wpshadow' ); ?></label>
			<input type="text" id="wpshadow_backup_schedule_time" name="wpshadow_backup_schedule_time" value="<?php echo esc_attr( $backup_schedule_time ); ?>" placeholder="02:00" style="width: 120px;" />
			<p class="description"><?php esc_html_e( 'We will run the snapshot at this time in your WordPress timezone.', 'wpshadow' ); ?></p>
		</div>

		<div class="wps-form-group">
			<label>
				<input type="checkbox" id="wpshadow_backup_include_database" name="wpshadow_backup_include_database" value="1" <?php checked( $backup_include_database ); ?> />
				<?php esc_html_e( 'Include database in snapshots', 'wpshadow' ); ?>
			</label>
			<p class="description">
				<?php esc_html_e( 'Include database settings in your Vault Light snapshot for faster recovery.', 'wpshadow' ); ?>
			</p>
		</div>

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
			<input type="number" id="wpshadow_backup_max_size_mb" name="wpshadow_backup_max_size_mb" value="<?php echo esc_attr( $backup_max_size_mb ); ?>" min="100" step="50" style="width: 150px;" />
			<p class="description"><?php esc_html_e( 'When snapshots exceed this size, oldest snapshots are deleted. Default: 500MB.', 'wpshadow' ); ?></p>
		</div>

		<h3><?php esc_html_e( 'Advanced Options', 'wpshadow' ); ?></h3>

		<div class="wps-form-group">
			<label>
				<input type="checkbox" id="wpshadow_backup_compress" name="wpshadow_backup_compress" value="1" <?php checked( $backup_compress ); ?> />
				<?php esc_html_e( 'Compress snapshots', 'wpshadow' ); ?>
			</label>
			<p class="description">
				<?php esc_html_e( 'Compress snapshot files to save disk space (slower but saves ~70% space).', 'wpshadow' ); ?>
			</p>
		</div>

		<div class="wps-form-group">
			<label>
				<input type="checkbox" id="wpshadow_backup_exclude_uploads" name="wpshadow_backup_exclude_uploads" value="1" <?php checked( $backup_exclude_uploads ); ?> />
				<?php esc_html_e( 'Exclude /uploads folder', 'wpshadow' ); ?>
			</label>
			<p class="description">
				<?php esc_html_e( 'Skip the /wp-content/uploads folder to reduce snapshot size (only backup files that might be modified by treatments).', 'wpshadow' ); ?>
			</p>
		</div>

		<div class="wps-form-group">
			<label>
				<input type="checkbox" id="wpshadow_backup_verify" name="wpshadow_backup_verify" value="1" <?php checked( $backup_verify ); ?> />
				<?php esc_html_e( 'Verify snapshots after creation', 'wpshadow' ); ?>
			</label>
			<p class="description">
				<?php esc_html_e( 'Test each snapshot for integrity (slower but ensures you can rollback if needed).', 'wpshadow' ); ?>
			</p>
		</div>

		<p class="submit">
			<button type="submit" class="wps-btn wps-btn-primary">
				<?php esc_html_e( 'Save Settings', 'wpshadow' ); ?>
			</button>
		</p>
	</form>

	<!-- Backup Management -->
	<div class="wpshadow-tool-section">
		<h3><?php esc_html_e( 'Snapshot Management', 'wpshadow' ); ?></h3>
		<p><?php esc_html_e( 'You can view, download, and delete Vault Light snapshots from the Activity Log.', 'wpshadow' ); ?></p>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-utilities&tab=activity-history' ) ); ?>" class="wps-btn wps-btn-secondary">
			<span class="dashicons dashicons-archive"></span>
			<?php esc_html_e( 'View All Snapshots', 'wpshadow' ); ?>
		</a>
	</div>

</div>

<?php
// Load and render sales widget
require_once WPSHADOW_PATH . 'includes/views/components/sales-widget.php';

wpshadow_render_sales_widget(
	array(
		'title'       => __( 'Upgrade to WPShadow Vault', 'wpshadow' ),
		'description' => __( 'WPShadow Vault adds continuous protection, encryption, journaling, and offsite storage with one-click restore.', 'wpshadow' ),
		'features'    => array(
			__( 'Continuous Vault protection', 'wpshadow' ),
			__( 'Encryption and journaling', 'wpshadow' ),
			__( 'Offsite storage with one-click restore', 'wpshadow' ),
			__( 'Priority support for recovery issues', 'wpshadow' ),
		),
		'cta_text'    => __( 'Learn More About WPShadow Vault', 'wpshadow' ),
		'cta_url'     => 'https://wpshadow.com/pro',
		'icon'        => 'dashicons-backup',
		'style'       => 'default',
	)
);
?>

<?php Tool_View_Base::render_footer(); ?>
