<?php
/**
 * Action Configuration Step
 *
 * @package WPShadow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$trigger_id = isset( $_GET['trigger'] ) ? sanitize_key( $_GET['trigger'] ) : '';
$workflow_id = isset( $_GET['workflow'] ) ? sanitize_key( $_GET['workflow'] ) : '';
$action_index = isset( $_GET['action_index'] ) ? absint( $_GET['action_index'] ) : 0;

if ( empty( $trigger_id ) ) {
	if ( ! empty( $workflow_id ) ) {
		wp_safe_redirect( admin_url( 'admin.php?page=wpshadow-workflows&action=edit&workflow=' . $workflow_id . '&step=action' ) );
	} else {
		wp_safe_redirect( admin_url( 'admin.php?page=wpshadow-workflows&action=create' ) );
	}
	exit;
}
?>

<div class="wizard-step action-config">
	<div class="step-header">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-workflows' . ( ! empty( $workflow_id ) ? '&action=edit&workflow=' . $workflow_id : '&action=create' ) . '&step=action&trigger=' . $trigger_id ) ); ?>" class="back-button">
			<span class="dashicons dashicons-arrow-left-alt2"></span>
			<?php esc_html_e( 'Back', 'wpshadow' ); ?>
		</a>
		<h2 id="action-title"><?php esc_html_e( 'Configure Action', 'wpshadow' ); ?></h2>
	</div>

	<div id="action-config-content"></div>
</div>

<script>
jQuery(document).ready(function($) {
	const triggerId = '<?php echo esc_js( $trigger_id ); ?>';
	const workflowId = '<?php echo esc_js( $workflow_id ); ?>';
	const actionIndex = <?php echo $action_index; ?>;
	const baseUrl = workflowId ? '<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-workflows&action=edit' ) ); ?>&workflow=' + workflowId : '<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-workflows&action=create' ) ); ?>';
	
	// Load actions from sessionStorage
	if (!actions || actions.length === 0) {
		window.location.href = baseUrl + '&step=action&trigger=' + triggerId;
	}
	
	if (!actions[actionIndex]) {
		window.location.href = baseUrl + '&step=action&trigger=' + triggerId;
		return;
	}
	
	const currentAction = actions[actionIndex];
	
	// Update title
	$('#action-title').text('Configure: ' + currentAction.label);
	
	// Get action config fields via AJAX
	$.post(ajaxurl, {
		action: 'wpshadow_get_action_config',
		nonce: '<?php echo wp_create_nonce( 'wpshadow_workflow' ); ?>',
		action_id: currentAction.id
	}, function(response) {
		if (!response.success) {
			// No config needed, move to next action or review
				moveToNextStep(); // Call to move to the next step
			return;
		}
		
		const fields = response.data.fields;
		
		if (fields.length === 0) {
			// No config needed
				moveToNextStep(); // Call to move to the next step
			return;
		}
		
		// Build form
		const $form = $('<form id="action-config-form" class="config-form">');
		
		fields.forEach(function(field) {
			const $fieldDiv = $('<div class="form-field">');
			
			// Label
			const $label = $('<label>').attr('for', 'field_' + field.id).text(field.label);
			if (field.required) {
				$label.append('<span class="required">*</span>');
			}
			$fieldDiv.append($label);
			
			// Input
			let $input;
			switch (field.type) {
				case 'text':
					$input = $('<input type="text">').attr({
						id: 'field_' + field.id,
						name: field.id,
						placeholder: field.placeholder || '',
						required: field.required || false
					}).val(field.default || '').addClass('regular-text');
					break;
					
				case 'number':
					$input = $('<input type="number">').attr({
						id: 'field_' + field.id,
						name: field.id,
						placeholder: field.placeholder || '',
						required: field.required || false,
						min: field.min,
						max: field.max
					}).val(field.default || '').addClass('small-text');
					break;
					
				case 'textarea':
					$input = $('<textarea>').attr({
						id: 'field_' + field.id,
						name: field.id,
						placeholder: field.placeholder || '',
						required: field.required || false,
						rows: field.rows || 3
					}).val(field.default || '').addClass('large-text');
					break;
					
				case 'select':
					$input = $('<select>').attr({
						id: 'field_' + field.id,
						name: field.id,
						required: field.required || false
					});
					
					Object.entries(field.options).forEach(([value, label]) => {
						$input.append(
							$('<option>').val(value).text(label).prop('selected', value === field.default)
						);
					});
					break;
			}
			
			$fieldDiv.append($input);
			$form.append($fieldDiv);
		});
		
		// Submit button
		const $actions = $('<div class="form-actions">');
		const $submitBtn = $('<button type="submit" class="button button-primary button-large">');
		
		if (actionIndex < actions.length - 1) {
			$submitBtn.html('<?php esc_html_e( 'Next Action', 'wpshadow' ); ?> <span class="dashicons dashicons-arrow-right-alt2"></span>');
		} else {
			$submitBtn.html('<?php esc_html_e( 'Continue to Review', 'wpshadow' ); ?> <span class="dashicons dashicons-arrow-right-alt2"></span>');
		}
		
		$actions.append($submitBtn);
		$form.append($actions);
		
		$('#action-config-content').append($form);
		
		// Handle form submission
		$form.on('submit', function(e) {
			e.preventDefault();
			
			// Save config
			const formData = {};
			$(this).serializeArray().forEach(function(field) {
				formData[field.name] = field.value;
			});
			
			actions[actionIndex].config = formData;
			sessionStorage.setItem('workflow_actions', JSON.stringify(actions));
			
			// Move to next step
			moveToNextStep();
		});
	});

	function moveToNextStep() {
		if (actionIndex < actions.length - 1) {
			// Next action config - preserve workflow ID if editing
			window.location.href = baseUrl + '&step=action-config&trigger=' + triggerId + '&action_index=' + (actionIndex + 1);
		} else {
			// Review - preserve workflow ID if editing
			window.location.href = baseUrl + '&step=review&trigger=' + triggerId;
		}
	}
});
</script>
