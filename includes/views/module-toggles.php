<?php
/**
 * Module Toggles Settings View
 *
 * Displays module enablement toggles for admin settings.
 *
 * @package wp_support_SUPPORT
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap wps-module-settings" role="main" aria-label="<?php esc_attr_e( 'Module Configuration Settings', 'plugin-wp-support-thisismyurl' ); ?>">
	<h2><?php esc_html_e( 'Module Configuration', 'plugin-wp-support-thisismyurl' ); ?></h2>

	<form method="post" action="options.php">
		<?php
		settings_fields( 'wp_support_modules' );
		do_settings_sections( 'wp_support_modules' );
		submit_button();
		?>
	</form>

	<hr>

	<h3><?php esc_html_e( 'Installed Modules', 'plugin-wp-support-thisismyurl' ); ?></h3>
	<table class="wp-list-table widefat" role="table" aria-label="<?php esc_attr_e( 'Installed modules status', 'plugin-wp-support-thisismyurl' ); ?>">
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Module', 'plugin-wp-support-thisismyurl' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Status', 'plugin-wp-support-thisismyurl' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Version', 'plugin-wp-support-thisismyurl' ); ?></th>
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
						$status    = $is_active ? '<span style="color:green;" aria-label="' . esc_attr__( 'Active', 'plugin-wp-support-thisismyurl' ) . '">' . esc_html__( 'Active', 'plugin-wp-support-thisismyurl' ) . '</span>' : '<span style="color:orange;" aria-label="' . esc_attr__( 'Installed but not active', 'plugin-wp-support-thisismyurl' ) . '">' . esc_html__( 'Installed', 'plugin-wp-support-thisismyurl' ) . '</span>';
						?>
						<tr>
							<td scope="row"><?php echo esc_html( $plugin_data['Name'] ?? $module ); ?></td>
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

