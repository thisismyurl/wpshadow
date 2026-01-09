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
<div class="wrap timu-module-settings" role="main" aria-label="<?php esc_attr_e( 'Module Configuration Settings', 'wordpress-support-thisismyurl' ); ?>">
	<h2><?php esc_html_e( 'Module Configuration', 'wordpress-support-thisismyurl' ); ?></h2>

	<form method="post" action="options.php">
		<?php
		settings_fields( 'timu_core_modules' );
		do_settings_sections( 'timu_core_modules' );
		submit_button();
		?>
	</form>

	<hr>

	<h3><?php esc_html_e( 'Installed Modules', 'wordpress-support-thisismyurl' ); ?></h3>
	<table class="wp-list-table widefat" role="table" aria-label="<?php esc_attr_e( 'Installed modules status', 'wordpress-support-thisismyurl' ); ?>">
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Module', 'wordpress-support-thisismyurl' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Status', 'wordpress-support-thisismyurl' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Version', 'wordpress-support-thisismyurl' ); ?></th>
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
						$status    = $is_active ? '<span style="color:green;" aria-label="' . esc_attr__( 'Active', 'wordpress-support-thisismyurl' ) . '">' . esc_html__( 'Active', 'wordpress-support-thisismyurl' ) . '</span>' : '<span style="color:orange;" aria-label="' . esc_attr__( 'Installed but not active', 'wordpress-support-thisismyurl' ) . '">' . esc_html__( 'Installed', 'wordpress-support-thisismyurl' ) . '</span>';
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
