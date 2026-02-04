<?php
/**
 * Wizard Step: Action Selection
 *
 * Allows user to select the action type for the automation.
 *
 * @package WPShadow
 * @subpackage Views
 * @since   1.6030.2148
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get actions from registry
$actions = \WPShadow\Workflow\Block_Registry::get_actions();

// Get selected action from session/workflow if available
$selected_action = '';
if ( ! empty( $workflow['blocks'] ) ) {
	foreach ( $workflow['blocks'] as $block ) {
		if ( $block['type'] === 'action' ) {
			$selected_action = $block['id'];
			break;
		}
	}
}
?>

<h2><?php esc_html_e( 'Select an Action', 'wpshadow' ); ?></h2>
<p><?php esc_html_e( 'Choose what you want this automation to do.', 'wpshadow' ); ?></p>

<div class="wpshadow-action-grid">
	<?php foreach ( $actions as $action_id => $action_data ) : ?>
		<label class="wpshadow-action-option <?php echo $selected_action === $action_id ? 'selected' : ''; ?>">
			<input 
				type="radio" 
				name="action_id" 
				value="<?php echo esc_attr( $action_id ); ?>"
				class="wpshadow-action-select"
				<?php checked( $selected_action, $action_id ); ?>
			>
			<span class="wpshadow-option-icon">
				<?php
				// Map action types to icons
				$icon_map = array(
					'email_action'  => 'dashicons-email-alt',
					'backup_action' => 'dashicons-database',
					'cache_action'  => 'dashicons-admin-plugins',
					'post_action'   => 'dashicons-edit',
				);
				$icon_class = $icon_map[ $action_data['type'] ] ?? 'dashicons-admin-tools';
				?>
				<span class="dashicons <?php echo esc_attr( $icon_class ); ?>"></span>
			</span>
			<span class="wpshadow-option-title"><?php echo esc_html( $action_data['label'] ); ?></span>
			<span class="wpshadow-option-description"><?php echo esc_html( $action_data['description'] ); ?></span>
		</label>
	<?php endforeach; ?>
</div>

<script>
jQuery(document).ready(function($) {
	$('.wpshadow-action-select').on('change', function() {
		$('.wpshadow-action-option').removeClass('selected');
		$(this).closest('.wpshadow-action-option').addClass('selected');
	});
});
</script>
