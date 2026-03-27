/**
 * Workflow Wizard Steps Scripts
 *
 * Extracted from includes/views/workflow-wizard-steps/*.php
 *
 * @package WPShadow
 */

jQuery(function ($) {
	'use strict';

	// Initialize trigger and workflow IDs from PHP data
	const triggerId = $('#workflow-review').data('trigger-id') || '';
	const workflowId = $('#workflow-review').data('workflow-id') || '';
	const baseUrl = workflowId
		? window.wpshadowData.adminUrl + 'admin.php?page=wpshadow-automations&action=edit&workflow=' + workflowId
		: window.wpshadowData.adminUrl + 'admin.php?page=wpshadow-automations&action=create';

	// Load workflow data from sessionStorage
	const triggerConfig = JSON.parse(sessionStorage.getItem('workflow_trigger_config') || '{}');
	const actions = JSON.parse(sessionStorage.getItem('workflow_actions') || '[]');

	if (actions.length === 0 && $('#workflow-review').length) {
		window.location.href = baseUrl + '&step=action&trigger=' + triggerId;
		return;
	}

	// Build summary
	buildSummary();

	/**
	 * Build and render the workflow summary
	 */
	function buildSummary() {
		const $summary = $('#workflow-summary');
		if (!$summary.length) {
			return;
		}

		// Trigger section
		const $triggerSection = $('<div class="summary-section">');
		$triggerSection.append('<h3>' + (typeof wpshadowI18n !== 'undefined' ? wpshadowI18n.trigger : 'Trigger') + '</h3>');

		const $triggerContent = $('<div class="summary-content">');
		$triggerContent.append('<div class="summary-icon"><span class="dashicons dashicons-clock"></span></div>');

		const $triggerText = $('<div class="summary-text">');
		$triggerText.append('<div class="summary-title">' + getTriggerLabel(triggerId) + '</div>');

		if (Object.keys(triggerConfig).length > 0) {
			const $configDiv = $('<div class="summary-config">');
			Object.entries(triggerConfig).forEach(([key, value]) => {
				if (key !== 'trigger_id') {
					$configDiv.append('<div><strong>' + key + ':</strong> ' + value + '</div>');
				}
			});
			$triggerText.append($configDiv);
		}

		$triggerContent.append($triggerText);
		$triggerSection.append($triggerContent);
		$summary.append($triggerSection);

		// Actions section
		const $actionsSection = $('<div class="summary-section">');
		$actionsSection.append('<h3>' + (typeof wpshadowI18n !== 'undefined' ? wpshadowI18n.actions : 'Actions') + '</h3>');

		const $actionList = $('<div class="action-list">');

		actions.forEach(function (action, index) {
			const $actionItem = $('<div class="action-item">');
			$actionItem.append('<div class="action-number">' + (index + 1) + '</div>');

			const $actionText = $('<div class="summary-text">');
			$actionText.append('<div class="summary-title">' + action.label + '</div>');

			if (action.config && Object.keys(action.config).length > 0) {
				const $configDiv = $('<div class="summary-config">');
				Object.entries(action.config).forEach(([key, value]) => {
					$configDiv.append('<div><strong>' + key + ':</strong> ' + value + '</div>');
				});
				$actionText.append($configDiv);
			}

			$actionItem.append($actionText);
			$actionList.append($actionItem);
		});

		$actionsSection.append($actionList);
		$summary.append($actionsSection);
	}

	/**
	 * Get human-readable trigger label
	 *
	 * @param {string} id Trigger ID
	 * @returns {string} Human-readable label
	 */
	function getTriggerLabel(id) {
		const labels = {
			time_daily: typeof wpshadowI18n !== 'undefined' ? wpshadowI18n.everyDay : 'Every Day',
			time_weekly: typeof wpshadowI18n !== 'undefined' ? wpshadowI18n.everyWeek : 'Every Week',
			time_hourly: typeof wpshadowI18n !== 'undefined' ? wpshadowI18n.everyHour : 'Every Hour',
			page_load_all: typeof wpshadowI18n !== 'undefined' ? wpshadowI18n.everyPageLoad : 'Every Page Load',
			page_load_frontend: typeof wpshadowI18n !== 'undefined' ? wpshadowI18n.frontendPageLoad : 'Frontend Page Load',
			page_load_admin: typeof wpshadowI18n !== 'undefined' ? wpshadowI18n.adminPageLoad : 'Admin Page Load',
			user_login: typeof wpshadowI18n !== 'undefined' ? wpshadowI18n.userLogin : 'User Login',
			plugin_activated: typeof wpshadowI18n !== 'undefined' ? wpshadowI18n.pluginActivated : 'Plugin Activated',
		};

		return labels[id] || id;
	}

	// Handle form submission
	$('#save-workflow-form').on('submit', function (e) {
		e.preventDefault();

		const workflowName = $('#workflow_name').val().trim();

		// Build workflow data
		const workflowData = {
			name: workflowName,
			trigger: {
				type: triggerId,
				config: triggerConfig,
			},
			actions: actions,
		};

		// Save via AJAX
		$.post(
			ajaxurl,
			{
				action: 'wpshadow_save_workflow',
				nonce: typeof wpshadowWorkflow !== 'undefined' ? wpshadowWorkflow.nonce : '',
				workflow: JSON.stringify(workflowData),
			},
			function (response) {
				const $result = $('#save-result');
				if (response.success) {
					// Clear sessionStorage
					sessionStorage.removeItem('workflow_trigger_id');
					sessionStorage.removeItem('workflow_trigger_config');
					sessionStorage.removeItem('workflow_actions');

					// Show success message
					$result
						.removeClass('error')
						.addClass('success')
						.text(
							typeof wpshadowI18n !== 'undefined' ? wpshadowI18n.workflowSavedSuccess : 'Workflow saved successfully!'
						);

					// Redirect after 1 second - go back to list
					setTimeout(function () {
						window.location.href = window.wpshadowData.adminUrl + 'admin.php?page=wpshadow-automations';
					}, 1000);
				} else {
					$result
						.removeClass('success')
						.addClass('error')
						.text(response.data.message || (typeof wpshadowI18n !== 'undefined' ? wpshadowI18n.errorSavingWorkflow : 'Error saving workflow'));
				}
			}
		);
	});
});
