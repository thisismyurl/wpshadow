/**
 * WPS Guided Tasks Scripts
 *
 * @package WPSHADOW_WP_SUPPORT
 * @since 1.2601.1111
 */

(function($) {
	'use strict';

	const GuidedTasks = {
		init() {
			$('.wps-start-workflow').on('click', this.startWorkflow.bind(this));
			$('.wps-complete-step').on('click', this.completeStep.bind(this));
			$('.wps-undo-step').on('click', this.undoStep.bind(this));
			$('.wps-cancel-walkthrough').on('click', this.cancelWalkthrough.bind(this));
			$('.wps-finish-walkthrough').on('click', this.finishWalkthrough.bind(this));
		},

		startWorkflow(e) {
			const $button = $(e.currentTarget);
			const workflow = $button.data('workflow');

			$.ajax({
				url: wpsGuidedTasks.ajax_url,
				method: 'POST',
				data: {
					action: 'wpshadow_start_walkthrough',
					nonce: wpsGuidedTasks.nonce,
					workflow: workflow
				},
				beforeSend() {
					$button.prop('disabled', true).text('Starting...');
				},
				success(response) {
					if (response.success) {
						location.reload();
					} else {
						alert('Failed to start walkthrough: ' + response.data.message);
					}
				},
				error() {
					alert('Request failed. Please try again.');
				},
				complete() {
					$button.prop('disabled', false).text('Start Guided Task');
				}
			});
		},

		completeStep(e) {
			const $button = $(e.currentTarget);

			$.ajax({
				url: wpsGuidedTasks.ajax_url,
				method: 'POST',
				data: {
					action: 'wpshadow_complete_step',
					nonce: wpsGuidedTasks.nonce,
					step_data: {}
				},
				beforeSend() {
					$button.prop('disabled', true).text('Processing...');
				},
				success(response) {
					if (response.success) {
						if (response.data.complete) {
							// Show completion message
							$('.wps-current-step').html(
								'<div class="notice notice-success"><p>Task completed successfully!</p></div>' +
								'<button type="button" class="button button-primary wps-finish-walkthrough">Done</button>'
							);
							$('.wps-finish-walkthrough').on('click', this.finishWalkthrough.bind(this));
						} else {
							location.reload();
						}
					} else {
						alert('Failed to complete step: ' + response.data.message);
					}
				},
				error() {
					alert('Request failed. Please try again.');
				},
				complete() {
					$button.prop('disabled', false).text('Continue →');
				}
			});
		},

		undoStep(e) {
			const $button = $(e.currentTarget);

			if (!confirm('Go back to the previous step?')) {
				return;
			}

			$.ajax({
				url: wpsGuidedTasks.ajax_url,
				method: 'POST',
				data: {
					action: 'wpshadow_undo_step',
					nonce: wpsGuidedTasks.nonce
				},
				beforeSend() {
					$button.prop('disabled', true).text('Undoing...');
				},
				success(response) {
					if (response.success) {
						location.reload();
					} else {
						alert('Failed to undo step: ' + response.data.message);
					}
				},
				error() {
					alert('Request failed. Please try again.');
				},
				complete() {
					$button.prop('disabled', false).text('← Previous Step');
				}
			});
		},

		cancelWalkthrough(e) {
			if (!confirm('Cancel this guided task? Your progress will be lost.')) {
				return;
			}

			// Clear progress by finishing
			this.finishWalkthrough(e);
		},

		finishWalkthrough(e) {
			// Simply reload to clear active walkthrough
			location.reload();
		}
	};

	$(document).ready(() => GuidedTasks.init());

})(jQuery);
