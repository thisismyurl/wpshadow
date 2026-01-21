jQuery(document).ready(function($) {
	/**
	 * Handle Suggested Workflow Creation (Phase B #570-571)
	 * Philosophy: Helpful Neighbor (#1) - One-click workflow creation
	 */
	$('.create-suggested-workflow').on('click', function(e) {
		e.preventDefault();
		const $button = $(this);
		const title = $button.data('title');
		const trigger = $button.data('trigger');
		const actions = $button.data('actions');

		if ($button.prop('disabled')) {
			return;
		}

		// Disable button and show loading
		$button.prop('disabled', true).html('<span class="dashicons dashicons-update dashicons-spin"></span> Creating...');

		$.post(ajaxurl, {
			action: 'wpshadow_create_suggested_workflow',
			nonce: wpshadowWorkflow.nonce,
			title: title,
			trigger: trigger,
			actions: actions
		}, function(response) {
			if (response.success) {
				// Show success message
				const $notice = $('<div class="notice notice-success is-dismissible"><p>' + response.data.message + '</p></div>');
				$('.wrap').prepend($notice);
				
				// Redirect to edit page after 1 second
				setTimeout(function() {
					window.location.href = response.data.redirect;
				}, 1000);
			} else {
				$button.prop('disabled', false).html('<span class="dashicons dashicons-plus-alt2"></span> Create This Workflow');
				const $notice = $('<div class="notice notice-error is-dismissible"><p>' + (response.data.message || 'Error creating workflow') + '</p></div>');
				$('.wrap').prepend($notice);
			}
		}).fail(function() {
			$button.prop('disabled', false).html('<span class="dashicons dashicons-plus-alt2"></span> Create This Workflow');
			const $notice = $('<div class="notice notice-error is-dismissible"><p>Network error. Please try again.</p></div>');
			$('.wrap').prepend($notice);
		});
	});
});
