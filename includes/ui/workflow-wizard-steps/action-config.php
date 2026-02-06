<?php
/**
 * Action Configuration Step
 *
 * @package WPShadow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPShadow\Core\Form_Param_Helper;

$trigger_id   = Form_Param_Helper::get( 'trigger', 'key', '' );
$workflow_id  = Form_Param_Helper::get( 'workflow', 'key', '' );
$action_index = Form_Param_Helper::get( 'action_index', 'int', 0 );

if ( empty( $trigger_id ) ) {
	if ( ! empty( $workflow_id ) ) {
		wp_safe_redirect( admin_url( 'admin.php?page=wpshadow-automations&action=edit&workflow=' . $workflow_id . '&step=action-selection' ) );
	} else {
		wp_safe_redirect( admin_url( 'admin.php?page=wpshadow-automations&action=create' ) );
	}
	exit;
}
?>

<div class="wps-page-container">
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-automations' . ( ! empty( $workflow_id ) ? '&action=edit&workflow=' . $workflow_id : '&action=create' ) . '&step=action-selection&trigger=' . $trigger_id ) ); ?>" class="wps-btn wps-btn--ghost" style="margin-right: var(--wps-space-3);">
		<span class="dashicons dashicons-arrow-left-alt2" style="font-size: 18px;"></span>
		<?php esc_html_e( 'Back', 'wpshadow' ); ?>
	</a>
	<?php
	wpshadow_render_page_header(
		__( 'Configure Action', 'wpshadow' ),
		__( 'Set the options for how this action should run.', 'wpshadow' ),
		'dashicons-admin-tools'
	);
	?>

	<div class="wps-card" style="margin-top: var(--wps-space-6);">
		<div class="wps-card-body" id="action-config-content"></div>
	</div>
</div>

<script>
jQuery(document).ready(function($) {
	const triggerId = '<?php echo esc_js( $trigger_id ); ?>';
	const workflowId = '<?php echo esc_js( $workflow_id ); ?>';
	const actionIndex = <?php echo wp_json_encode( $action_index ); ?>;
	const baseUrl = workflowId ? '<?php echo esc_url_raw( admin_url( 'admin.php?page=wpshadow-automations&action=edit' ) ); ?>&workflow=' + workflowId : '<?php echo esc_url_raw( admin_url( 'admin.php?page=wpshadow-automations&action=create' ) ); ?>';
	
	// Load actions from sessionStorage
	if (!actions || actions.length === 0) {
		window.location.href = baseUrl + '&step=action-selection&trigger=' + triggerId;
	}
	
	if (!actions[actionIndex]) {
		window.location.href = baseUrl + '&step=action-selection&trigger=' + triggerId;
		return;
	}
	
	const currentAction = actions[actionIndex];
	
	// Update title
	$('.wps-page-title').text('Configure: ' + currentAction.label);
	
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
		const $form = $('<form id="action-config-form" class="wps-form">');
		
		fields.forEach(function(field) {
			const $fieldDiv = $('<div class="wps-form-group">');
			
			// Label
			const $label = $('<label class="wps-form-label">').attr('for', 'field_' + field.id).text(field.label);
			if (field.required) {
				$label.append('<span class="wps-text-danger">*</span>');
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
					}).val(field.default || '').addClass('wps-input');
					break;
					
				case 'number':
					$input = $('<input type="number">').attr({
						id: 'field_' + field.id,
						name: field.id,
						placeholder: field.placeholder || '',
						required: field.required || false,
						min: field.min,
						max: field.max
					}).val(field.default || '').addClass('wps-input').css('max-width', '150px');
					break;
					
				case 'textarea':
					$input = $('<textarea>').attr({
						id: 'field_' + field.id,
						name: field.id,
						placeholder: field.placeholder || '',
						required: field.required || false,
						rows: field.rows || 3
					}).val(field.default || '').addClass('wps-textarea');
					break;
					
				case 'select':
					$input = $('<select>').attr({
						id: 'field_' + field.id,
						name: field.id,
						required: field.required || false
					}).addClass('wps-select');
					
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
		const $actions = $('<div class="wps-form-actions">').css({
			'margin-top': 'var(--wps-space-6)',
			'padding-top': 'var(--wps-space-4)',
			'border-top': '1px solid var(--wps-border-color)'
		});
		const $submitBtn = $('<button type="submit" class="wps-btn wps-btn--primary">');
		
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
