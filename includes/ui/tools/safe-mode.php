<?php
/**
 * Safe Mode Utility Tool
 *
 * Per-user plugin/theme isolation for troubleshooting without affecting live site.
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
Tool_View_Base::enqueue_assets( 'safe-mode' );
Tool_View_Base::render_header( __( 'Safe Mode', 'wpshadow' ) );

$current_user_id = get_current_user_id();
$safe_mode_enabled = get_user_meta( $current_user_id, 'wpshadow_safe_mode_enabled', true );
$disabled_plugins = get_user_meta( $current_user_id, 'wpshadow_safe_mode_plugins', true ) ?: array();
$disabled_themes = get_user_meta( $current_user_id, 'wpshadow_safe_mode_themes', true ) ?: array();
?>

<p><?php esc_html_e( 'Safe Mode lets you disable plugins and themes temporarily for only your session, without affecting other users or the live site. Perfect for troubleshooting conflicts.', 'wpshadow' ); ?></p>

<div class="wpshadow-tool-section">
	<h3><?php esc_html_e( 'Safe Mode Status', 'wpshadow' ); ?></h3>
	
	<div style="padding: 15px; background: <?php echo $safe_mode_enabled ? '#e8f5e9' : '#fff3cd'; ?>; border-radius: 4px; margin-bottom: 20px;">
		<strong>
			<?php
			if ( $safe_mode_enabled ) {
				esc_html_e( '✓ Safe Mode Active', 'wpshadow' );
			} else {
				esc_html_e( '⊘ Safe Mode Inactive', 'wpshadow' );
			}
			?>
		</strong>
		<p style="margin: 5px 0 0 0; font-size: 13px; color: #666;">
			<?php
			if ( $safe_mode_enabled ) {
				esc_html_e( 'Your session is running with safe mode active. Only your admin view is affected.', 'wpshadow' );
			} else {
				esc_html_e( 'Safe mode is not active. All plugins and themes are loading normally.', 'wpshadow' );
			}
			?>
		</p>
	</div>

	<form method="post" action="admin-ajax.php" style="margin-bottom: 20px;">
		<?php wp_nonce_field( 'wpshadow_safe_mode', 'nonce' ); ?>
		<input type="hidden" name="action" value="wpshadow_toggle_safe_mode" />
		
		<button type="submit" class="wps-btn <?php echo $safe_mode_enabled ? 'wps-btn--warning' : 'wps-btn--primary'; ?>">
			<?php echo $safe_mode_enabled ? esc_html__( 'Exit Safe Mode', 'wpshadow' ) : esc_html__( 'Enter Safe Mode', 'wpshadow' ); ?>
		</button>
		<p class="description" style="margin-top: 8px;">
			<?php esc_html_e( 'Toggling safe mode only affects your admin session, not the live site.', 'wpshadow' ); ?>
		</p>
	</form>
</div>

<?php if ( $safe_mode_enabled ) : ?>
	<div class="wpshadow-tool-section">
		<h3><?php esc_html_e( 'Disabled in Your Session', 'wpshadow' ); ?></h3>
		
		<h4><?php esc_html_e( 'Plugins', 'wpshadow' ); ?></h4>
		<?php if ( ! empty( $disabled_plugins ) ) : ?>
			<ul style="list-style: disc; margin-left: 20px;">
				<?php foreach ( $disabled_plugins as $plugin_file ) : ?>
					<li><?php echo esc_html( basename( dirname( $plugin_file ) ) ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php else : ?>
			<p><em><?php esc_html_e( 'No plugins disabled', 'wpshadow' ); ?></em></p>
		<?php endif; ?>

		<h4><?php esc_html_e( 'Themes', 'wpshadow' ); ?></h4>
		<?php if ( ! empty( $disabled_themes ) ) : ?>
			<ul style="list-style: disc; margin-left: 20px;">
				<?php foreach ( $disabled_themes as $theme_slug ) : ?>
					<li><?php echo esc_html( $theme_slug ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php else : ?>
			<p><em><?php esc_html_e( 'No themes disabled', 'wpshadow' ); ?></em></p>
		<?php endif; ?>
	</div>

	<div class="wpshadow-tool-section">
		<h3><?php esc_html_e( 'Disable in Safe Mode', 'wpshadow' ); ?></h3>
		<p><?php esc_html_e( 'Coming soon: granular controls to disable specific plugins/themes for your session only.', 'wpshadow' ); ?></p>
	</div>
<?php endif; ?>

<?php Tool_View_Base::render_footer(); ?>
