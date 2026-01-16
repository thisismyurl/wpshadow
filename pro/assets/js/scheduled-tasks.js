/**
 * Scheduled Tasks Management
 *
 * Handles AJAX interactions for pausing, resuming, and removing scheduled cron tasks.
 *
 * @package    WP_Support
 * @subpackage Assets
 * @since      1.2601.76000
 */

(function($) {
	'use strict';

	/**
	 * Initialize scheduled tasks management
	 */
	function initScheduledTasks() {
		// Pause task button
		$(document).on('click', '.wps-pause-task', function(e) {
			e.preventDefault();
			
			const $button = $(this);
			const hook = $button.data('hook');
			const timestamp = $button.data('timestamp');
			
			if (!confirm(wpshadowScheduledTasks.strings.confirmPause)) {
				return;
			}
			
			pauseTask(hook, timestamp, $button);
		});

		// Resume task button
		$(document).on('click', '.wps-resume-task', function(e) {
			e.preventDefault();
			
			const $button = $(this);
			const hook = $button.data('hook');
			
			resumeTask(hook, $button);
		});

		// Remove active task button
		$(document).on('click', '.wps-remove-task', function(e) {
			e.preventDefault();
			
			const $button = $(this);
			const hook = $button.data('hook');
			const timestamp = $button.data('timestamp');
			
			if (!confirm(wpshadowScheduledTasks.strings.confirmRemove)) {
				return;
			}
			
			removeTask(hook, timestamp, $button);
		});

		// Remove paused task button
		$(document).on('click', '.wps-remove-paused-task', function(e) {
			e.preventDefault();
			
			const $button = $(this);
			const hook = $button.data('hook');
			
			if (!confirm(wpshadowScheduledTasks.strings.confirmDelete)) {
				return;
			}
			
			removePausedTask(hook, $button);
		});
	}

	/**
	 * Pause a scheduled task
	 */
	function pauseTask(hook, timestamp, $button) {
		$button.prop('disabled', true).text('Pausing...');
		
		$.ajax({
			url: wpshadowScheduledTasks.ajaxUrl,
			type: 'POST',
			data: {
				action: 'wpshadow_pause_task',
				nonce: wpshadowScheduledTasks.nonce,
				hook: hook,
				timestamp: timestamp
			},
			success: function(response) {
				if (response.success) {
					showNotice(wpshadowScheduledTasks.strings.success, 'success');
					if (response.data.reload) {
						setTimeout(function() {
							location.reload();
						}, 1000);
					}
				} else {
					showNotice(response.data.message || wpshadowScheduledTasks.strings.error, 'error');
					$button.prop('disabled', false).text('Pause');
				}
			},
			error: function() {
				showNotice(wpshadowScheduledTasks.strings.error, 'error');
				$button.prop('disabled', false).text('Pause');
			}
		});
	}

	/**
	 * Resume a paused task
	 */
	function resumeTask(hook, $button) {
		$button.prop('disabled', true).text('Resuming...');
		
		$.ajax({
			url: wpshadowScheduledTasks.ajaxUrl,
			type: 'POST',
			data: {
				action: 'wpshadow_resume_task',
				nonce: wpshadowScheduledTasks.nonce,
				hook: hook
			},
			success: function(response) {
				if (response.success) {
					showNotice(wpshadowScheduledTasks.strings.success, 'success');
					if (response.data.reload) {
						setTimeout(function() {
							location.reload();
						}, 1000);
					}
				} else {
					showNotice(response.data.message || wpshadowScheduledTasks.strings.error, 'error');
					$button.prop('disabled', false).text('Resume');
				}
			},
			error: function() {
				showNotice(wpshadowScheduledTasks.strings.error, 'error');
				$button.prop('disabled', false).text('Resume');
			}
		});
	}

	/**
	 * Remove an active scheduled task
	 */
	function removeTask(hook, timestamp, $button) {
		$button.prop('disabled', true).text('Removing...');
		
		$.ajax({
			url: wpshadowScheduledTasks.ajaxUrl,
			type: 'POST',
			data: {
				action: 'wpshadow_remove_task',
				nonce: wpshadowScheduledTasks.nonce,
				hook: hook,
				timestamp: timestamp
			},
			success: function(response) {
				if (response.success) {
					showNotice(wpshadowScheduledTasks.strings.success, 'success');
					if (response.data.reload) {
						setTimeout(function() {
							location.reload();
						}, 1000);
					}
				} else {
					showNotice(response.data.message || wpshadowScheduledTasks.strings.error, 'error');
					$button.prop('disabled', false).text('Remove');
				}
			},
			error: function() {
				showNotice(wpshadowScheduledTasks.strings.error, 'error');
				$button.prop('disabled', false).text('Remove');
			}
		});
	}

	/**
	 * Remove a paused task
	 */
	function removePausedTask(hook, $button) {
		$button.prop('disabled', true).text('Deleting...');
		
		$.ajax({
			url: wpshadowScheduledTasks.ajaxUrl,
			type: 'POST',
			data: {
				action: 'wpshadow_remove_paused_task',
				nonce: wpshadowScheduledTasks.nonce,
				hook: hook
			},
			success: function(response) {
				if (response.success) {
					showNotice(wpshadowScheduledTasks.strings.success, 'success');
					if (response.data.reload) {
						setTimeout(function() {
							location.reload();
						}, 1000);
					}
				} else {
					showNotice(response.data.message || wpshadowScheduledTasks.strings.error, 'error');
					$button.prop('disabled', false).text('Delete');
				}
			},
			error: function() {
				showNotice(wpshadowScheduledTasks.strings.error, 'error');
				$button.prop('disabled', false).text('Delete');
			}
		});
	}

	/**
	 * Show admin notice
	 */
	function showNotice(message, type) {
		type = type || 'info';
		
		const $notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
		
		// Find the best place to insert the notice
		const $target = $('.wps-dashboard-header, .wrap > h1').first();
		
		if ($target.length) {
			$target.after($notice);
		} else {
			$('.wrap').prepend($notice);
		}
		
		// Auto-dismiss after 5 seconds
		setTimeout(function() {
			$notice.fadeOut(function() {
				$(this).remove();
			});
		}, 5000);
		
		// Make dismissible button work
		$notice.on('click', '.notice-dismiss', function() {
			$notice.remove();
		});
	}

	// Initialize on document ready
	$(document).ready(function() {
		initScheduledTasks();
	});

})(jQuery);
