<?php
/**
 * Wizard Step: Trigger Selection
 *
 * Allows user to select the trigger type for the automation.
 *
 * @package WPShadow
 * @subpackage Views
 * @since 1.6093.1200
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get triggers from registry
$triggers = \WPShadow\Workflow\Block_Registry::get_triggers();

// Get selected trigger from session/workflow if available
$selected_trigger = '';
if ( ! empty( $workflow['blocks'] ) ) {
	foreach ( $workflow['blocks'] as $block ) {
		if ( $block['type'] === 'trigger' ) {
			$selected_trigger = $block['id'];
			break;
		}
	}
}
?>

<h2><?php esc_html_e( 'Select a Trigger', 'wpshadow' ); ?></h2>
<p><?php esc_html_e( 'Choose when you want this automation to run.', 'wpshadow' ); ?></p>

<div class="wpshadow-trigger-grid">
	<?php foreach ( $triggers as $trigger_id => $trigger_data ) : ?>
		<label class="wpshadow-trigger-option <?php echo $selected_trigger === $trigger_id ? 'selected' : ''; ?>">
			<input 
				type="radio" 
				name="trigger_id" 
				value="<?php echo esc_attr( $trigger_id ); ?>"
				class="wpshadow-trigger-select"
				<?php checked( $selected_trigger, $trigger_id ); ?>
			>
			<span class="wpshadow-option-icon">
				<?php
				// Map trigger types to icons
				$icon_map = array(
					'time_trigger'      => 'dashicons-clock',
					'page_load_trigger' => 'dashicons-admin-generic',
					'event_trigger'     => 'dashicons-flag',
					'condition_trigger' => 'dashicons-yes',
				);
				$icon_class = $icon_map[ $trigger_data['type'] ] ?? 'dashicons-update';
				?>
				<span class="dashicons <?php echo esc_attr( $icon_class ); ?>"></span>
			</span>
			<span class="wpshadow-option-title"><?php echo esc_html( $trigger_data['label'] ); ?></span>
			<span class="wpshadow-option-description"><?php echo esc_html( $trigger_data['description'] ); ?></span>
		</label>
	<?php endforeach; ?>
</div>

<script>
jQuery(document).ready(function($) {
	$('.wpshadow-trigger-select').on('change', function() {
		$('.wpshadow-trigger-option').removeClass('selected');
		$(this).closest('.wpshadow-trigger-option').addClass('selected');
	});
});
</script>
