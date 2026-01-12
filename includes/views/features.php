<?php
/**
 * Features management view with widget grouping.
 *
 * @package wp_support_SUPPORT
 */

use WPS\CoreSupport\WPS_Tab_Navigation;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$level    = $level ?? 'core';
$hub_id   = $hub_id ?? '';
$spoke_id = $spoke_id ?? '';
$network  = $network_scope ?? ( is_multisite() && is_network_admin() );

// Group features by widget group
$grouped_features = array();
foreach ( $features as $feature ) {
	$group = $feature['widget_group'] ?? 'general';
	if ( ! isset( $grouped_features[ $group ] ) ) {
		$grouped_features[ $group ] = array(
			'label'       => $feature['widget_label'] ?? 'General',
			'description' => $feature['widget_description'] ?? 'Features',
			'features'    => array(),
		);
	}
	$grouped_features[ $group ]['features'][] = $feature;
}

?>
<div class="wrap">
	<h1><?php echo esc_html__( 'Features', 'plugin-wp-support-thisismyurl' ); ?></h1>
	<?php settings_errors( 'WPS_features' ); ?>

	<div class="wps-features-container">
		<form method="post" id="wps-features-form">
			<?php wp_nonce_field( 'WPS_save_features', 'wps_features_nonce' ); ?>
			<input type="hidden" name="wps_features_context[level]" value="<?php echo esc_attr( $level ); ?>" />
			<input type="hidden" name="wps_features_context[hub]" value="<?php echo esc_attr( $hub_id ); ?>" />
			<input type="hidden" name="wps_features_context[spoke]" value="<?php echo esc_attr( $spoke_id ); ?>" />

			<?php if ( empty( $features ) ) : ?>
				<div class="notice notice-info">
					<p><?php esc_html_e( 'No features registered for this context yet.', 'plugin-wp-support-thisismyurl' ); ?></p>
				</div>
			<?php else : ?>
				<div class="meta-box-sortables">
				<?php foreach ( $grouped_features as $group_id => $group_data ) : ?>
					<div class="wps-feature-widget postbox" id="wps-widget-<?php echo esc_attr( $group_id ); ?>">
						<div class="wps-widget-header" style="border-bottom: 1px solid #ddd; padding: 12px; background: #f5f5f5; border-radius: 3px 3px 0 0;">
							<h3 style="margin: 0 0 4px 0; font-size: 13px; font-weight: 600; color: #23282d;">
								<button type="button" class="handlediv button-link" aria-expanded="true">
									<span class="screen-reader-text"><?php echo esc_html__( 'Toggle feature group', 'plugin-wp-support-thisismyurl' ); ?></span>
								</button>
								<?php echo esc_html( $group_data['label'] ); ?>
							</h3>
							<p style="margin: 0; font-size: 12px; color: #666;">
								<?php echo esc_html( $group_data['description'] ); ?>
							</p>
						</div>

						<div class="wps-widget-content" style="padding: 12px; border: 1px solid #ddd; border-top: none; border-radius: 0 0 3px 3px; margin-bottom: 20px;">
							<table class="wp-list-table widefat fixed striped" style="margin: 0;">
								<thead>
									<tr>
										<th scope="col" class="manage-column column-cb check-column" style="width: 40px;">
											<span class="screen-reader-text"><?php esc_html_e( 'Toggle feature', 'plugin-wp-support-thisismyurl' ); ?></span>
										</th>
										<th scope="col" style="width: 25%;"><?php esc_html_e( 'Feature', 'plugin-wp-support-thisismyurl' ); ?></th>
										<th scope="col" style="width: 55%;"><?php esc_html_e( 'Description', 'plugin-wp-support-thisismyurl' ); ?></th>
										<th scope="col" style="width: 10%;"><?php esc_html_e( 'Version', 'plugin-wp-support-thisismyurl' ); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ( $group_data['features'] as $feature ) :
										$feature_id      = esc_attr( $feature['id'] ?? '' );
										$feature_name    = esc_html( $feature['name'] ?? $feature_id );
										$feature_desc    = esc_html( $feature['description'] ?? '' );
										$feature_version = esc_html( $feature['version'] ?? '1.0.0' );
										$is_enabled      = ! empty( $feature['enabled'] );
										?>
										<tr>
											<th scope="row" class="check-column">
												<input type="checkbox" name="features[<?php echo $feature_id; ?>]" value="1" <?php checked( $is_enabled ); ?> />
											</th>
											<td>
												<strong><?php echo $feature_name; ?></strong>
												<?php if ( ! empty( $feature['hub'] ) || ! empty( $feature['spoke'] ) ) : ?>
													<div style="color:#555;font-size:11px;margin-top:4px;">
														<?php
														if ( ! empty( $feature['hub'] ) ) {
															echo esc_html( sprintf( /* translators: %s hub identifier */ __( 'Hub: %s', 'plugin-wp-support-thisismyurl' ), $feature['hub'] ) );
														}
														if ( ! empty( $feature['spoke'] ) ) {
															echo ' · ' . esc_html( sprintf( /* translators: %s spoke identifier */ __( 'Spoke: %s', 'plugin-wp-support-thisismyurl' ), $feature['spoke'] ) );
														}
														?>
													</div>
												<?php endif; ?>
											</td>
											<td><?php echo $feature_desc; ?></td>
											<td><?php echo $feature_version; ?></td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					</div>
				<?php endforeach; ?>
				<?php endforeach; ?>
			</div>

			<p class="submit">
				<button type="submit" class="button button-primary">
					<?php echo esc_html__( 'Save features', 'plugin-wp-support-thisismyurl' ); ?>
				</button>
				<?php if ( $network ) : ?>
					<span style="margin-left:8px; color:#666; font-size:12px;">
						<?php echo esc_html__( 'Network scope', 'plugin-wp-support-thisismyurl' ); ?>
					</span>
				<?php endif; ?>
			</p>
		</form>
	</div>
</div>

<style>
	.meta-box-sortables {
		display: flex;
		flex-direction: column;
		gap: 0;
	}

	.wps-feature-widget {
		background: #fff;
		border: 1px solid #ddd;
		border-radius: 3px;
		margin-bottom: 20px;
		cursor: move;
	}

	.wps-feature-widget.closed .wps-widget-content {
		display: none;
	}

	.wps-feature-widget .wp-list-table {
		border-collapse: collapse;
	}

	.wps-feature-widget tbody tr {
		border-bottom: 1px solid #ddd;
	}

	.wps-feature-widget tbody tr:last-child {
		border-bottom: none;
	}

	.wps-feature-widget td,
	.wps-feature-widget th {
		padding: 10px 8px;
		vertical-align: middle;
	}

	.wps-feature-widget .check-column {
		text-align: center;
	}
</style>

<script>
(function() {
	// Enqueue postbox script if not already loaded
	if ( typeof postboxes !== 'undefined' ) {
		// Initialize sortable postboxes for features tab
		postboxes.add_postbox_toggles('wps_features_page');
		
		// Save widget state on toggle
		jQuery(document).on('postbox-toggled', function(e, postbox) {
			var closed = postbox.hasClass('closed') ? 1 : 0;
			jQuery.post(
				ajaxurl,
				{
					action: 'wps_save_widget_state',
					postbox_id: postbox.attr('id'),
					closed: closed,
					nonce: jQuery('input[name="wps_features_nonce"]').val()
				}
			);
		});
	}
})();
</script>
