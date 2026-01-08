<?php
/**
 * Module Toggles Settings View
 *
 * Displays module enablement toggles for admin settings.
 *
 * @package TIMU_CORE_SUPPORT
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap timu-module-settings">
	<h2><?php esc_html_e( 'Module Configuration', 'core-support-thisismyurl' ); ?></h2>
	
	<form method="post" action="options.php">
		<?php
		settings_fields( 'timu_core_modules' );
		do_settings_sections( 'timu_core_modules' );
		submit_button();
		?>
	</form>

	<hr>

	<h3><?php esc_html_e( 'Installed Modules', 'core-support-thisismyurl' ); ?></h3>
	<table class="wp-list-table widefat">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Module', 'core-support-thisismyurl' ); ?></th>
				<th><?php esc_html_e( 'Status', 'core-support-thisismyurl' ); ?></th>
				<th><?php esc_html_e( 'Version', 'core-support-thisismyurl' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			$plugins = get_plugins();
			$modules = array( 'media-support-thisismyurl', 'image-support-thisismyurl', 'vault-support-thisismyurl' );

			foreach ( $modules as $module ) {
				foreach ( $plugins as $plugin_file => $plugin_data ) {
					if ( strpos( $plugin_file, $module . '/' ) === 0 ) {
						$is_active = is_plugin_active( $plugin_file );
						$status    = $is_active ? '<span style="color:green;">' . __( 'Active', 'core-support-thisismyurl' ) . '</span>' : '<span style="color:orange;">' . __( 'Installed', 'core-support-thisismyurl' ) . '</span>';
						?>
						<tr>
							<td><?php echo esc_html( $plugin_data['Name'] ?? $module ); ?></td>
							<td><?php echo wp_kses_post( $status ); ?></td>
							<td><?php echo esc_html( $plugin_data['Version'] ?? '—' ); ?></td>
						</tr>
						<?php
						break;
					}
				}
			}
			?>
		</tbody>
	</table>
</div>
