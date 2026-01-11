<?php
/**
 * Capabilities management view.
 *
 * @package wp_support_SUPPORT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPS\CoreSupport\WPS_Capabilities;

$map = WPS_Capabilities::get_map();
?>
<div class="wrap">
	<h1><?php echo esc_html__( 'Module Capabilities', 'plugin-wp-support-thisismyurl' ); ?></h1>
	<p><?php echo esc_html__( 'Map module capabilities to WordPress capabilities. This controls who can access module features.', 'plugin-wp-support-thisismyurl' ); ?></p>

	<?php settings_errors( 'WPS_capabilities' ); ?>

	<form method="post" action="">
		<?php wp_nonce_field( 'WPS_capabilities', 'WPS_capabilities_nonce' ); ?>
		<input type="hidden" name="WPS_capability_action" value="add" />

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="WPS_module_slug"><?php echo esc_html__( 'Module slug', 'plugin-wp-support-thisismyurl' ); ?></label></th>
				<td><input name="WPS_module_slug" id="WPS_module_slug" type="text" class="regular-text" required aria-required="true" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="WPS_capability_key"><?php echo esc_html__( 'Module capability', 'plugin-wp-support-thisismyurl' ); ?></label></th>
				<td><input name="WPS_capability_key" id="WPS_capability_key" type="text" class="regular-text" required aria-required="true" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="WPS_wp_capability"><?php echo esc_html__( 'WordPress capability', 'plugin-wp-support-thisismyurl' ); ?></label></th>
				<td><input name="WPS_wp_capability" id="WPS_wp_capability" type="text" class="regular-text" value="manage_options" required aria-required="true" /></td>
			</tr>
		</table>

		<?php submit_button( __( 'Add Mapping', 'plugin-wp-support-thisismyurl' ) ); ?>
	</form>

	<h2><?php echo esc_html__( 'Existing Mappings', 'plugin-wp-support-thisismyurl' ); ?></h2>
	<table class="widefat fixed striped" role="table" aria-label="<?php echo esc_attr__( 'Module capability mappings', 'plugin-wp-support-thisismyurl' ); ?>">
		<thead>
			<tr>
				<th scope="col"><?php echo esc_html__( 'Module', 'plugin-wp-support-thisismyurl' ); ?></th>
				<th scope="col"><?php echo esc_html__( 'Capability', 'plugin-wp-support-thisismyurl' ); ?></th>
				<th scope="col"><?php echo esc_html__( 'Maps to WP capability', 'plugin-wp-support-thisismyurl' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $map ) ) : ?>
				<tr>
					<td colspan="3"><?php echo esc_html__( 'No capability mappings have been registered yet.', 'plugin-wp-support-thisismyurl' ); ?></td>
				</tr>
			<?php else : ?>
				<?php foreach ( $map as $module_slug => $caps ) : ?>
					<?php foreach ( $caps as $cap_key => $wp_cap ) : ?>
						<tr>
							<td><?php echo esc_html( $module_slug ); ?></td>
							<td><?php echo esc_html( $cap_key ); ?></td>
							<td><?php echo esc_html( $wp_cap ); ?></td>
						</tr>
					<?php endforeach; ?>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
</div>


