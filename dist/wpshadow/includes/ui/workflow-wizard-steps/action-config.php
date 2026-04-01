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
$action_id    = Form_Param_Helper::get( 'action_id', 'key', '' );

if ( empty( $trigger_id ) ) {
	if ( ! empty( $workflow_id ) ) {
		wp_safe_redirect( admin_url( 'admin.php?page=wpshadow-automations&action=edit&workflow=' . $workflow_id . '&step=action-selection' ) );
	} else {
		wp_safe_redirect( admin_url( 'admin.php?page=wpshadow-automations&action=create' ) );
	}
	exit;
}

$back_url = admin_url( 'admin.php?page=wpshadow-automations' . ( ! empty( $workflow_id ) ? '&action=edit&workflow=' . $workflow_id : '&action=create' ) . '&step=action-selection&trigger=' . $trigger_id );
?>

<div class="wps-page-container">
	<?php wpshadow_render_wizard_back_button( $back_url ); ?>
	<?php
	wpshadow_render_page_header(
		__( 'Configure Action', 'wpshadow' ),
		__( 'Set the options for how this action should run.', 'wpshadow' ),
		'dashicons-admin-tools'
	);
	?>

	<div class="wps-card wps-wizard-step-card">
		<div class="wps-card-body" id="action-config-content"></div>
	</div>
</div>

<script>
jQuery(document).ready(function($) {
	const triggerId = '<?php echo esc_js( $trigger_id ); ?>';
	const workflowId = '<?php echo esc_js( $workflow_id ); ?>';
	let actionIndex = <?php echo wp_json_encode( $action_index ); ?>;
	const baseUrl = workflowId ? '<?php echo esc_url_raw( admin_url( 'admin.php?page=wpshadow-automations&action=edit' ) ); ?>&workflow=' + workflowId : '<?php echo esc_url_raw( admin_url( 'admin.php?page=wpshadow-automations&action=create' ) ); ?>';
	const actionIdParam = <?php echo wp_json_encode( $action_id ); ?>;
	const selectedActions = JSON.parse(window.sessionStorage ? window.sessionStorage.getItem('workflow_actions') : '[]') || [];
	let actions = selectedActions;

	if ((!actions || actions.length === 0) && actionIdParam) {
		actions = [{
			id: actionIdParam,
			label: actionIdParam,
			config: {}
		}];
		actionIndex = 0;
	}

	if (!actions || actions.length === 0) {
		window.location.href = baseUrl + '&step=action-selection&trigger=' + triggerId;
		return;
	}

	if (!actions[actionIndex]) {
		actionIndex = 0;
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
			renderNoConfig('<?php echo esc_js( __( 'This action does not need additional configuration.', 'wpshadow' ) ); ?>');
			return;
		}

		const fields = response.data.fields;

		if (fields.length === 0) {
			renderNoConfig('<?php echo esc_js( __( 'This action does not need additional configuration.', 'wpshadow' ) ); ?>');
			return;
		}

		// Build form
		const $form = $('<form id="action-config-form" class="wps-form">');
		const fieldLookup = {};
		fields.forEach(function(field) {
			if (field && field.id) {
				fieldLookup[field.id] = field;
			}
		});

		fields.forEach(function(field) {
			const $fieldDiv = $('<div class="wps-form-group">');
			if (field.type !== 'checkbox' && field.type !== 'checkbox_group' && field.type !== 'hidden') {
				const $label = $('<label class="wps-form-label">').attr('for', 'field_' + field.id).text(field.label);
				if (field.required) {
					$label.append('<span class="wps-text-danger">*</span>');
				}
				$fieldDiv.append($label);
			}

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

				case 'hidden':
					$input = $('<input type="hidden">').attr({
						name: field.id
					}).val(field.default || '');
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

				case 'checkbox':
					{
						const isChecked = field.default === true || field.default === '1' || field.default === 1;
						const $wrapper = $('<label class="wps-checkbox-wrapper">');
						$input = $('<input type="checkbox">').attr({
							id: 'field_' + field.id,
							name: field.id,
							value: '1'
						}).addClass('wps-checkbox');
						if (isChecked) {
							$input.prop('checked', true);
						}
						$wrapper.append($input, $('<span class="wps-checkbox-label">').text(field.label));
						$fieldDiv.append($wrapper);
						$input = null;
					}
					break;

				case 'checkbox_group':
					{
						const defaults = Array.isArray(field.default) ? field.default.map(String) : [];
						const $fieldset = $('<fieldset class="wps-checkbox-group">');
						$fieldset.append($('<legend>').text(field.label));
						Object.entries(field.options || {}).forEach(([value, label]) => {
							const $wrapper = $('<label class="wps-checkbox-wrapper">');
							const $checkbox = $('<input type="checkbox">').attr({
								name: field.id + '[]',
								value: value
							}).addClass('wps-checkbox');
							if (defaults.includes(String(value))) {
								$checkbox.prop('checked', true);
							}
							$wrapper.append($checkbox, $('<span class="wps-checkbox-label">').text(label));
							$fieldset.append($wrapper);
						});
						$fieldDiv.append($fieldset);
						$input = null;
					}
					break;

				case 'treatment_search':
					$input = $('<input type="text">').attr({
						id: 'field_' + field.id,
						placeholder: field.placeholder || '',
						autocomplete: 'off'
					}).addClass('wps-input');

					const $hidden = $('<input type="hidden">').attr({
						name: field.id,
						required: field.required || false
					});
					const $results = $('<div class="wps-layout-stack wps-layout-stack-sm">').css({
						'margin-top': 'var(--wps-space-2)'
					});

					let searchTimer = null;
					$input.on('input', function() {
						const query = $(this).val().trim();
						clearTimeout(searchTimer);
						if (query.length < 2) {
							$results.empty();
							return;
						}
						searchTimer = setTimeout(function() {
							$.post(ajaxurl, {
								action: field.ajax_action,
								nonce: field.nonce,
								search: query
							}, function(searchResponse) {
								$results.empty();
								if (!searchResponse.success || !searchResponse.data.items || searchResponse.data.items.length === 0) {
									$results.append('<div class="wps-text-sm wps-text-muted"><?php echo esc_js( __( 'No treatments found.', 'wpshadow' ) ); ?></div>');
									return;
								}
								searchResponse.data.items.forEach(function(item) {
									const $button = $('<button type="button" class="wps-btn wps-btn--secondary wps-btn--sm">').text(item.label);
									$button.on('click', function() {
										$hidden.val(item.class_name);
										$input.val(item.label);
										$results.empty();
									});
									$results.append($button);
								});
							});
						}, 300);
					});

					$fieldDiv.append($input, $hidden, $results);
					$input = null;
					break;
			}

			if ($input) {
				$fieldDiv.append($input);
			}

			if (field.note) {
				$fieldDiv.append($('<p class="wps-help-text">').text(field.note));
			}
			$form.append($fieldDiv);
		});

		// Submit button
		const $actions = $('<div class="wps-form-actions wps-wizard-form-actions">');
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
			fields.forEach(function(field) {
				if (!field || !field.id) {
					return;
				}
				if (field.type === 'checkbox_group') {
					formData[field.id] = [];
					return;
				}
				if (field.type === 'checkbox') {
					formData[field.id] = '0';
					return;
				}
				formData[field.id] = field.default || '';
			});

			$(this).serializeArray().forEach(function(field) {
				const name = field.name.replace(/\[\]$/, '');
				if (Object.prototype.hasOwnProperty.call(formData, name) && Array.isArray(formData[name])) {
					formData[name].push(field.value);
					return;
				}
				formData[name] = field.value;
			});

			actions[actionIndex].config = formData;
			sessionStorage.setItem('workflow_actions', JSON.stringify(actions));

			// Move to next step
			moveToNextStep();
		});
	});

	function renderNoConfig(message) {
		const $container = $('#action-config-content');
		$container.empty();

		const $notice = $('<div class="wps-alert wps-alert--info">').text(message);
		const $actions = $('<div class="wps-form-actions wps-wizard-form-actions">');
		const $submitBtn = $('<button type="button" class="wps-btn wps-btn--primary">');

		if (actionIndex < actions.length - 1) {
			$submitBtn.html('<?php echo esc_js( __( 'Next Action', 'wpshadow' ) ); ?> <span class="dashicons dashicons-arrow-right-alt2"></span>');
		} else {
			$submitBtn.html('<?php echo esc_js( __( 'Continue to Review', 'wpshadow' ) ); ?> <span class="dashicons dashicons-arrow-right-alt2"></span>');
		}

		$submitBtn.on('click', function() {
			moveToNextStep();
		});

		$actions.append($submitBtn);
		$container.append($notice, $actions);
	}

	function moveToNextStep() {
		if (actionIndex < actions.length - 1) {
			// Next action config - preserve workflow ID if editing
			window.location.href = baseUrl + '&step=action-config&trigger=' + triggerId + '&action_index=' + (actionIndex + 1) + '&action_id=' + encodeURIComponent(actions[actionIndex + 1].id);
		} else {
			// Review - preserve workflow ID if editing
			window.location.href = baseUrl + '&step=review&trigger=' + triggerId;
		}
	}
});
</script>
