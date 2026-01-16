<?php
/**
 * Module Toggles Settings View
 *
 * Displays module enablement toggles for admin settings.
 *
 * @package wpshadow_SUPPORT
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap wps-module-settings" role="main" aria-label="<?php esc_attr_e( 'Module Configuration Settings', 'plugin-wpshadow' ); ?>">
	<h2><?php esc_html_e( 'Module Configuration', 'plugin-wpshadow' ); ?></h2>

	<form method="post" action="options.php">
		<?php
		settings_fields( 'wpshadow_modules' );
		do_settings_sections( 'wpshadow_modules' );
		submit_button();
		?>
	</form>

	<hr>

	<h3><?php esc_html_e( 'Installed Modules', 'plugin-wpshadow' ); ?></h3>
	<table class="wp-list-table widefat" role="table" aria-label="<?php esc_attr_e( 'Installed modules status', 'plugin-wpshadow' ); ?>">
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Module', 'plugin-wpshadow' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Status', 'plugin-wpshadow' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Version', 'plugin-wpshadow' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			$plugins = get_plugins();
			$modules = array( 'media-wpshadow', 'image-wpshadow', 'vault-wpshadow' );

			foreach ( $modules as $module ) {
				foreach ( $plugins as $plugin_file => $plugin_data ) {
					if ( strpos( $plugin_file, $module . '/' ) === 0 ) {
						$is_active = is_plugin_active( $plugin_file );
						$status    = $is_active ? '<span style="color:green;" aria-label="' . esc_attr__( 'Active', 'plugin-wpshadow' ) . '">' . esc_html__( 'Active', 'plugin-wpshadow' ) . '</span>' : '<span style="color:orange;" aria-label="' . esc_attr__( 'Installed but not active', 'plugin-wpshadow' ) . '">' . esc_html__( 'Installed', 'plugin-wpshadow' ) . '</span>';
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

