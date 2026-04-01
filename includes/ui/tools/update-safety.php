<?php
/**
 * Update Safety Check Utility Tool
 *
 * Pre-update safety checks and Vault Light backup snapshots.
 *
 * @package WPShadow
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPShadow\Views\Tool_View_Base;

require WPSHADOW_PATH . 'includes/views/class-tool-view-base.php';

Tool_View_Base::verify_access( 'manage_options' );
Tool_View_Base::enqueue_assets( 'update-safety' );
Tool_View_Base::render_header( __( 'Update Safety Check', 'wpshadow' ) );

// Get update and backup info
$backup_enabled = get_option( 'wpshadow_backup_enabled', true );
$wp_version = get_bloginfo( 'version' );
$updates = wp_get_update_data();
$core_updates = $updates['counts']['wordpress'] ?? 0;
$plugin_updates = $updates['counts']['plugins'] ?? 0;
$theme_updates = $updates['counts']['themes'] ?? 0;
?>

<p><?php esc_html_e( 'Before updating WordPress, plugins, or themes, ensure you have a recent backup snapshot so you can quickly roll back if something breaks.', 'wpshadow' ); ?></p>

<div class="wpshadow-tool-section">
	<h3><?php esc_html_e( 'Pre-Update Safety Status', 'wpshadow' ); ?></h3>

	<div style="padding: 15px; background: <?php echo $backup_enabled ? '#e8f5e9' : '#fff3cd'; ?>; border-radius: 4px; margin-bottom: 15px;">
		<strong>
			<?php
			if ( $backup_enabled ) {
				echo '<span style="color: #2e7d32;">✓ ' . esc_html__( 'Pre-Treatment Snapshots Enabled', 'wpshadow' ) . '</span>';
			} else {
				echo '<span style="color: #f57c00;">⚠ ' . esc_html__( 'Pre-Treatment Snapshots Disabled', 'wpshadow' ) . '</span>';
			}
			?>
		</strong>
		<p style="margin: 5px 0 0 0; font-size: 13px; color: #666;">
			<?php
			if ( $backup_enabled ) {
				esc_html_e( 'A Vault Light snapshot will be created automatically before each update.', 'wpshadow' );
			} else {
				esc_html_e( 'Enable pre-treatment snapshots to create a restore point before updates.', 'wpshadow' );
			}
			?>
		</p>
	</div>

	<?php if ( ! $backup_enabled ) : ?>
		<form method="post" action="options.php" style="margin-bottom: 20px;">
			<?php settings_fields( 'wpshadow_settings' ); ?>
			<input type="hidden" name="wpshadow_backup_enabled" value="1" />
			<button type="submit" class="wps-btn wps-btn--primary">
				<?php esc_html_e( 'Enable Pre-Treatment Snapshots', 'wpshadow' ); ?>
			</button>
		</form>
	<?php endif; ?>
</div>

<div class="wpshadow-tool-section">
	<h3><?php esc_html_e( 'Available Updates', 'wpshadow' ); ?></h3>

	<table class="widefat">
		<tr>
			<td><strong><?php esc_html_e( 'WordPress Core', 'wpshadow' ); ?></strong></td>
			<td>
				<?php
				if ( $core_updates > 0 ) {
					echo '<span style="background: #fff3cd; padding: 2px 6px; border-radius: 3px; color: #856404;">' . esc_html( number_format_i18n( $core_updates ) ) . ' ' . esc_html__( 'available', 'wpshadow' ) . '</span>';
				} else {
					echo '<span style="color: #2e7d32;">✓ ' . esc_html__( 'Up to date', 'wpshadow' ) . '</span>';
				}
				?>
			</td>
		</tr>
		<tr>
			<td><strong><?php esc_html_e( 'Plugins', 'wpshadow' ); ?></strong></td>
			<td>
				<?php
				if ( $plugin_updates > 0 ) {
					echo '<span style="background: #fff3cd; padding: 2px 6px; border-radius: 3px; color: #856404;">' . esc_html( number_format_i18n( $plugin_updates ) ) . ' ' . esc_html__( 'available', 'wpshadow' ) . '</span>';
				} else {
					echo '<span style="color: #2e7d32;">✓ ' . esc_html__( 'All up to date', 'wpshadow' ) . '</span>';
				}
				?>
			</td>
		</tr>
		<tr>
			<td><strong><?php esc_html_e( 'Themes', 'wpshadow' ); ?></strong></td>
			<td>
				<?php
				if ( $theme_updates > 0 ) {
					echo '<span style="background: #fff3cd; padding: 2px 6px; border-radius: 3px; color: #856404;">' . esc_html( number_format_i18n( $theme_updates ) ) . ' ' . esc_html__( 'available', 'wpshadow' ) . '</span>';
				} else {
					echo '<span style="color: #2e7d32;">✓ ' . esc_html__( 'All up to date', 'wpshadow' ) . '</span>';
				}
				?>
			</td>
		</tr>
	</table>
</div>

<div class="wpshadow-tool-section">
	<h3><?php esc_html_e( 'Pre-Update Checklist', 'wpshadow' ); ?></h3>

	<ul style="margin-left: 0; list-style: none;">
		<li style="padding: 8px 0; border-bottom: 1px solid #eee;">
			<?php echo $backup_enabled ? '✓' : '○'; ?>
			<strong><?php esc_html_e( 'Pre-treatment backups enabled', 'wpshadow' ); ?></strong>
		</li>
		<li style="padding: 8px 0; border-bottom: 1px solid #eee;">
			<?php echo wp_next_scheduled( 'wpshadow_scheduled_backup' ) ? '✓' : '○'; ?>
			<strong><?php esc_html_e( 'Scheduled Vault Light snapshots active', 'wpshadow' ); ?></strong>
		</li>
		<li style="padding: 8px 0; border-bottom: 1px solid #eee;">
			<strong><?php esc_html_e( 'Review changes before updating', 'wpshadow' ); ?></strong>
			<p style="margin: 4px 0 0 0; font-size: 12px; color: #666;">
				<?php esc_html_e( 'Test updates in staging first, if possible.', 'wpshadow' ); ?>
			</p>
		</li>
	</ul>
</div>

<div class="notice notice-success">
	<p>
		<strong><?php esc_html_e( 'Ready to update?', 'wpshadow' ); ?></strong>
		<?php esc_html_e( 'Your Vault Light snapshot will be created automatically. If anything breaks, restore from the Tools menu anytime within your retention period.', 'wpshadow' ); ?>
	</p>
</div>

<?php Tool_View_Base::render_footer(); ?>
