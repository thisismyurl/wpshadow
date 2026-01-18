<?php
/**
 * Capabilities management view.
 *
 * @package WPShadow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPShadow\WPSHADOW_Capabilities;

$map = WPSHADOW_Capabilities::get_map();
?>
<div class="wrap">
	<h1><?php echo esc_html__( 'Module Capabilities', 'wpshadow' ); ?></h1>
	<p><?php echo esc_html__( 'Map module capabilities to WordPress capabilities. This controls who can access module features.', 'wpshadow' ); ?></p>

	<?php settings_errors( 'wpshadow_capabilities' ); ?>

	<form method="post" action="">
		<?php wp_nonce_field( 'wpshadow_capabilities', 'wpshadow_capabilities_nonce' ); ?>
		<input type="hidden" name="wpshadow_capability_action" value="add" />

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="wpshadow_module_slug"><?php echo esc_html__( 'Module slug', 'wpshadow' ); ?></label></th>
				<td><input name="wpshadow_module_slug" id="wpshadow_module_slug" type="text" class="regular-text" required aria-required="true" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="wpshadow_capability_key"><?php echo esc_html__( 'Module capability', 'wpshadow' ); ?></label></th>
				<td><input name="wpshadow_capability_key" id="wpshadow_capability_key" type="text" class="regular-text" required aria-required="true" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="wpshadow_wp_capability"><?php echo esc_html__( 'WordPress capability', 'wpshadow' ); ?></label></th>
				<td><input name="wpshadow_wp_capability" id="wpshadow_wp_capability" type="text" class="regular-text" value="manage_options" required aria-required="true" /></td>
			</tr>
		</table>

		<?php submit_button( __( 'Add Mapping', 'wpshadow' ) ); ?>
	</form>

	<h2><?php echo esc_html__( 'Existing Mappings', 'wpshadow' ); ?></h2>
	<table class="widefat fixed striped" role="table" aria-label="<?php echo esc_attr__( 'Module capability mappings', 'wpshadow' ); ?>">
		<thead>
			<tr>
				<th scope="col"><?php echo esc_html__( 'Module', 'wpshadow' ); ?></th>
				<th scope="col"><?php echo esc_html__( 'Capability', 'wpshadow' ); ?></th>
				<th scope="col"><?php echo esc_html__( 'Maps to WP capability', 'wpshadow' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $map ) ) : ?>
				<tr>
					<td colspan="3"><?php echo esc_html__( 'No capability mappings have been registered yet.', 'wpshadow' ); ?></td>
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


