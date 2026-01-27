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

<div class="wizard-step trigger-config">
	<div class="step-header">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-workflows' . ( ! empty( $workflow_id ) ? '&action=edit&workflow=' . $workflow_id : '&action=create' ) . '&step=trigger' ) ); ?>" class="back-button">
			<span class="dashicons dashicons-arrow-left-alt2"></span>
			<?php esc_html_e( 'Back', 'wpshadow' ); ?>
		</a>
		<h2><?php echo esc_html( $trigger_data['label'] ); ?></h2>
	</div>

	<p class="description">
		<?php echo esc_html( $trigger_data['description'] ); ?>
	</p>

	<?php if ( $has_config ) : ?>
		<form id="trigger-config-form" class="config-form">
			<input type="hidden" name="trigger_id" value="<?php echo esc_attr( $trigger_id ); ?>">
			<input type="hidden" name="workflow_id" value="<?php echo esc_attr( $workflow_id ); ?>">
			
			<?php foreach ( $config_fields as $field ) : ?>
				<div class="form-field">
					<label for="field_<?php echo esc_attr( $field['id'] ); ?>">
						<?php echo esc_html( $field['label'] ); ?>
						<?php if ( ! empty( $field['required'] ) ) : ?>
							<span class="required">*</span>
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
								class="small-text"
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
							>
							<?php
							break;

						case 'select':
							?>
							<select 
								id="field_<?php echo esc_attr( $field['id'] ); ?>" 
								name="<?php echo esc_attr( $field['id'] ); ?>"
								<?php echo ! empty( $field['required'] ) ? 'required' : ''; ?>
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
								class="large-text"
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

			<div class="form-actions">
				<button type="submit" class="wps-btn wps-btn--primary">
					<?php esc_html_e( 'Continue to Actions', 'wpshadow' ); ?>
					<span class="dashicons dashicons-arrow-right-alt2"></span>
				</button>
			</div>
		</form>
	<?php else : ?>
		<!-- No configuration needed - Auto-advance to action selection -->
		<script>
			// Auto-advance to action selection step since this trigger doesn't need configuration
			const workflowId = '<?php echo esc_js( $workflow_id ); ?>';
			const triggerId = '<?php echo esc_js( $trigger_id ); ?>';
			const baseUrl = workflowId ? '<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-workflows&action=edit' ) ); ?>&workflow=' + workflowId : '<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-workflows&action=create' ) ); ?>';
			window.location.href = baseUrl + '&step=action&trigger=' + triggerId;
		</script>
		<p><?php esc_html_e( 'This trigger doesn\'t need any additional configuration. Proceeding to actions...', 'wpshadow' ); ?></p>
	<?php endif; ?>
</div>

<style>
.step-header {
	display: flex;
	align-items: center;
	gap: 15px;
	margin-bottom: 20px;
}

.back-button {
	display: inline-flex;
	align-items: center;
	gap: 5px;
	text-decoration: none;
	color: #2271b1;
	font-weight: 500;
}

.back-button:hover {
	color: #135e96;
}

.back-button .dashicons {
	width: 20px;
	height: 20px;
	font-size: 20px;
}

.step-header h2 {
	margin: 0;
	font-size: 24px;
}

.config-form {
	max-width: 600px;
	margin-top: 30px;
}

.form-field {
	margin-bottom: 25px;
}

.form-field label {
	display: block;
	font-weight: 600;
	margin-bottom: 8px;
	font-size: 14px;
}

.required {
	color: #d63638;
}

.checkbox-group {
	display: flex;
	flex-direction: column;
	gap: 10px;
}

.checkbox-label {
	display: flex;
	align-items: center;
	gap: 8px;
	cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
	margin: 0;
}

.form-actions {
	margin-top: 30px;
	padding-top: 20px;
	border-top: 1px solid #ddd;
}

.button-large {
	display: inline-flex;
	align-items: center;
	gap: 8px;
	padding: 8px 24px;
	height: auto;
	font-size: 14px;
}

.button-large .dashicons {
	width: 18px;
	height: 18px;
	font-size: 18px;
}

.no-config-message {
	text-align: center;
	padding: 60px 20px;
	background: #f9f9f9;
	border-radius: 8px;
	margin-top: 30px;
}

.no-config-message p {
	font-size: 16px;
	margin-bottom: 20px;
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
		const baseUrl = workflowId ? '<?php echo esc_url_raw( admin_url( 'admin.php?page=wpshadow-workflows&action=edit' ) ); ?>&workflow=' + workflowId : '<?php echo esc_url_raw( admin_url( 'admin.php?page=wpshadow-workflows&action=create' ) ); ?>';
		window.location.href = baseUrl + '&step=action&trigger=' + triggerId;
	});
});
</script>
