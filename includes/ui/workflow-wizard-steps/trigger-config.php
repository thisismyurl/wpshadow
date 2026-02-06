<?php
/**
 * Trigger Configuration Step
 *
 * @package WPShadow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPShadow\Core\Form_Param_Helper;

$trigger_id  = Form_Param_Helper::get( 'trigger', 'key', '' );
$workflow_id = Form_Param_Helper::get( 'workflow', 'key', '' );

// If no trigger specified or trigger not found, show trigger selection
if ( empty( $trigger_id ) ) {
	// Show trigger selection instead of redirecting
	include __DIR__ . '/trigger-selection.php';
	return;
}

// Get all triggers to find the selected one
$categories   = \WPShadow\Workflow\Workflow_Wizard::get_trigger_categories();
$trigger_data = null;
foreach ( $categories as $category ) {
	if ( isset( $category['triggers'][ $trigger_id ] ) ) {
		$trigger_data = $category['triggers'][ $trigger_id ];
		break;
	}
}

if ( ! $trigger_data ) {
	// Trigger not found, show trigger selection instead of redirecting
	include __DIR__ . '/trigger-selection.php';
	return;
}

$config_fields = \WPShadow\Workflow\Workflow_Wizard::get_trigger_config( $trigger_id );
$has_config    = ! empty( $config_fields );
?>

<div class="wps-page-container">
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-automations' . ( ! empty( $workflow_id ) ? '&action=edit&workflow=' . $workflow_id : '&action=create' ) . '&step=trigger' ) ); ?>" class="wps-btn wps-btn--ghost" style="margin-right: var(--wps-space-3);">
		<span class="dashicons dashicons-arrow-left-alt2" style="font-size: 18px;"></span>
		<?php esc_html_e( 'Back', 'wpshadow' ); ?>
	</a>
	<?php
	wpshadow_render_page_header(
		__( 'Configure Trigger', 'wpshadow' ),
		__( 'Set the conditions for when this workflow should run.', 'wpshadow' ),
		'dashicons-admin-generic'
	);
	?>

	<?php if ( $has_config ) : ?>
		<div class="wps-card" style="margin-top: var(--wps-space-6);">
			<div class="wps-card-body">
				<form id="trigger-config-form" class="wps-form">
					<input type="hidden" name="trigger_id" value="<?php echo esc_attr( $trigger_id ); ?>">
					<input type="hidden" name="workflow_id" value="<?php echo esc_attr( $workflow_id ); ?>">
					
					<?php foreach ( $config_fields as $field ) : ?>
						<div class="wps-form-group">
						<label for="field_<?php echo esc_attr( $field['id'] ); ?>" class="wps-form-label">
							<?php echo esc_html( $field['label'] ); ?>
							<?php if ( ! empty( $field['required'] ) ) : ?>
								<span class="wps-text-danger">*</span>
							<?php endif; ?>
						</label>

					<?php
					switch ( $field['type'] ) {
						case 'text':
							?>
							<input 
								type="text" 
								id="field_<?php echo esc_attr( $field['id'] ); ?>" 
								name="<?php echo esc_attr( $field['id'] ); ?>"
								placeholder="<?php echo esc_attr( $field['placeholder'] ?? '' ); ?>"
								value="<?php echo esc_attr( $field['default'] ?? '' ); ?>"
								<?php echo ! empty( $field['required'] ) ? 'required' : ''; ?>
								class="wps-input"
							>
							<?php
							break;

						case 'number':
							?>
								<input 
									type="number" 
									id="field_<?php echo esc_attr( $field['id'] ); ?>" 
									name="<?php echo esc_attr( $field['id'] ); ?>"
									placeholder="<?php echo esc_attr( $field['placeholder'] ?? '' ); ?>"
									value="<?php echo esc_attr( $field['default'] ?? '' ); ?>"
									<?php echo isset( $field['min'] ) ? 'min="' . esc_attr( $field['min'] ) . '"' : ''; ?>
									<?php echo isset( $field['max'] ) ? 'max="' . esc_attr( $field['max'] ) . '"' : ''; ?>
									<?php echo ! empty( $field['required'] ) ? 'required' : ''; ?>
									class="wps-input" style="max-width: 150px;"
								>
							<?php
							break;

						case 'time':
							?>
								<input 
									type="time" 
									id="field_<?php echo esc_attr( $field['id'] ); ?>" 
									name="<?php echo esc_attr( $field['id'] ); ?>"
									value="<?php echo esc_attr( $field['default'] ?? '' ); ?>"
									<?php echo ! empty( $field['required'] ) ? 'required' : ''; ?>
									class="wps-input" style="max-width: 200px;"
								>
							<?php
							break;

						case 'select':
							?>
								<select 
									id="field_<?php echo esc_attr( $field['id'] ); ?>" 
									name="<?php echo esc_attr( $field['id'] ); ?>"
									<?php echo ! empty( $field['required'] ) ? 'required' : ''; ?>
									class="wps-select"
								>
								<?php foreach ( $field['options'] as $value => $label ) : ?>
									<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $field['default'] ?? '', $value ); ?>>
										<?php echo esc_html( $label ); ?>
									</option>
								<?php endforeach; ?>
							</select>
							<?php
							break;

						case 'textarea':
							?>
								<textarea 
									id="field_<?php echo esc_attr( $field['id'] ); ?>" 
									name="<?php echo esc_attr( $field['id'] ); ?>"
									placeholder="<?php echo esc_attr( $field['placeholder'] ?? '' ); ?>"
									rows="<?php echo esc_attr( $field['rows'] ?? 3 ); ?>"
									<?php echo ! empty( $field['required'] ) ? 'required' : ''; ?>
									class="wps-textarea"
								><?php echo esc_textarea( $field['default'] ?? '' ); ?></textarea>
							<?php
							break;

						case 'checkbox_group':
							?>
							<div class="checkbox-group">
								<?php
								$default_values = $field['default'] ?? array();
								foreach ( $field['options'] as $value => $label ) :
									$checked = in_array( $value, $default_values, true );
									?>
									<label class="checkbox-label">
										<input 
											type="checkbox" 
											name="<?php echo esc_attr( $field['id'] ); ?>[]" 
											value="<?php echo esc_attr( $value ); ?>"
											<?php checked( $checked ); ?>
										>
										<span><?php echo esc_html( $label ); ?></span>
									</label>
								<?php endforeach; ?>
							</div>
							<?php
							break;
					}
					?>
						</div>
					<?php endforeach; ?>

					<div class="wps-form-actions" style="margin-top: var(--wps-space-6); padding-top: var(--wps-space-4); border-top: 1px solid var(--wps-border-color);">
						<button type="submit" class="wps-btn wps-btn--primary">
							<?php esc_html_e( 'Continue to Actions', 'wpshadow' ); ?>
							<span class="dashicons dashicons-arrow-right-alt2" style="font-size: 16px; margin-left: var(--wps-space-1);"></span>
						</button>
					</div>
				</form>
			</div>
		</div>
	<?php else : ?>
		<!-- No configuration needed - Auto-advance to action selection -->
		<div class="wps-card" style="margin-top: var(--wps-space-6); text-align: center;">
			<div class="wps-card-body" style="padding: var(--wps-space-8);">
				<p class="wps-text-base wps-text-muted">
					<?php esc_html_e( 'This trigger doesn\'t need any additional configuration. Proceeding to actions...', 'wpshadow' ); ?>
				</p>
			</div>
		</div>
		<script>
			// Auto-advance to action selection step since this trigger doesn't need configuration
			const workflowId = '<?php echo esc_js( $workflow_id ); ?>';
			const triggerId = '<?php echo esc_js( $trigger_id ); ?>';
			const baseUrl = workflowId ? '<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-automations&action=edit' ) ); ?>&workflow=' + workflowId : '<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-automations&action=create' ) ); ?>';
			window.location.href = baseUrl + '&step=action-selection&trigger=' + triggerId;
		</script>
	<?php endif; ?>
</div>

<style>
/* Checkbox Group Styling */
.checkbox-group {
	display: flex;
	flex-direction: column;
	gap: var(--wps-space-2);
}

.checkbox-label {
	display: flex;
	align-items: center;
	gap: var(--wps-space-2);
	cursor: pointer;
	font-size: var(--wps-text-sm);
}

.checkbox-label input[type="checkbox"] {
	margin: 0;
}

/* Page Header Flex Layout */
.wps-page-header {
	display: flex;
	align-items: flex-start;
	gap: var(--wps-space-3);
}
</style>

<script>
jQuery(document).ready(function($) {
	$('#trigger-config-form').on('submit', function(e) {
		e.preventDefault();
		
		// Serialize form data
		const formData = {};
		$(this).serializeArray().forEach(function(field) {
			if (field.name.endsWith('[]')) {
				const key = field.name.replace('[]', '');
				if (!formData[key]) {
					formData[key] = [];
				}
				formData[key].push(field.value);
			} else {
				formData[field.name] = field.value;
			}
		});
		
		// Store in sessionStorage
		sessionStorage.setItem('workflow_trigger_config', JSON.stringify(formData));
		
		// Navigate to action selection - preserve workflow ID if editing
		const triggerId = formData.trigger_id;
		const workflowId = formData.workflow_id;
		const baseUrl = workflowId ? '<?php echo esc_url_raw( admin_url( 'admin.php?page=wpshadow-automations&action=edit' ) ); ?>&workflow=' + workflowId : '<?php echo esc_url_raw( admin_url( 'admin.php?page=wpshadow-automations&action=create' ) ); ?>';
		window.location.href = baseUrl + '&step=action-selection&trigger=' + triggerId;
	});
});
</script>