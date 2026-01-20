/**
 * Workflow List - JavaScript
 *
 * Handles workflow list interactions (enable/disable, run, delete)
 */

jQuery(document).ready(function($) {
	
	// Toggle workflow enabled/disabled
	$('.workflow-enable-toggle').on('change', function() {
		const $toggle = $(this);
		const $card = $toggle.closest('.workflow-card');
		const workflowId = $card.data('workflow-id');
		const enabled = $toggle.is(':checked');
		
		$.post(ajaxurl, {
			action: 'wpshadow_toggle_workflow',
			nonce: wpshadowWorkflow.nonce,
			workflow_id: workflowId,
			enabled: enabled
		}, function(response) {
			if (response.success) {
				$card.toggleClass('enabled', enabled);
				$card.toggleClass('disabled', !enabled);
				
				showNotice('Workflow ' + (enabled ? 'enabled' : 'disabled'), 'success');
			} else {
				// Revert toggle on error
				$toggle.prop('checked', !enabled);
				showNotice(response.data.message || 'Error toggling workflow', 'error');
			}
		});
	});
	
	// Run workflow manually
	$('.workflow-run-btn').on('click', function() {
		const $btn = $(this);
		const workflowId = $btn.data('workflow-id');
		const $card = $btn.closest('.workflow-card');
		const workflowName = $card.find('.workflow-name').text();
		
		if (!confirm('Run workflow "' + workflowName + '" now?')) {
			return;
		}
		
		$btn.prop('disabled', true).text('Running...');
		
		$.post(ajaxurl, {
			action: 'wpshadow_run_workflow',
			nonce: wpshadowWorkflow.nonce,
			workflow_id: workflowId
		}, function(response) {
			$btn.prop('disabled', false).text('Run Now');
			
			if (response.success) {
				showNotice('Workflow executed successfully!', 'success');
			} else {
				showNotice(response.data.message || 'Error running workflow', 'error');
			}
		}).fail(function() {
			$btn.prop('disabled', false).text('Run Now');
			showNotice('Error running workflow', 'error');
		});
	});
	
	// Delete workflow
	$('.workflow-delete-btn').on('click', function() {
		const $btn = $(this);
		const workflowId = $btn.data('workflow-id');
		const $card = $btn.closest('.workflow-card');
		const workflowName = $card.find('.workflow-name').text();
		
		if (!confirm('Delete workflow "' + workflowName + '"? This cannot be undone.')) {
			return;
		}
		
		$btn.prop('disabled', true);
		
		$.post(ajaxurl, {
			action: 'wpshadow_delete_workflow',
			nonce: wpshadowWorkflow.nonce,
			workflow_id: workflowId
		}, function(response) {
			if (response.success) {
				$card.fadeOut(function() {
					$(this).remove();
					
					// Check if all workflows deleted
					if ($('.workflow-card').length === 0) {
						location.reload();
					}
				});
				
				showNotice('Workflow deleted successfully', 'success');
			} else {
				$btn.prop('disabled', false);
				showNotice(response.data.message || 'Error deleting workflow', 'error');
			}
		});
	});
	
	// Show admin notice
	function showNotice(message, type) {
		const $notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
		$('.wrap').prepend($notice);
		
		setTimeout(function() {
			$notice.fadeOut(function() {
				$(this).remove();
			});
		}, 3000);
	}
});
