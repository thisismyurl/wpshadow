/**
 * Exit Followups Admin Interface
 *
 * Handles the admin interface for viewing and managing exit interview followups.
 *
 * @since 1.6030.2148
 */

(function($) {
	'use strict';

	const ExitFollowups = {
		/**
		 * Initialize
		 */
		init: function() {
			this.bindEvents();
			this.loadFollowups('');
		},

		/**
		 * Bind event handlers
		 */
		bindEvents: function() {
			$('#followup-status-filter').on('change', function() {
				ExitFollowups.loadFollowups($(this).val());
			});

			$('#refresh-followups').on('click', function() {
				ExitFollowups.loadFollowups($('#followup-status-filter').val());
			});

			$(document).on('click', '.wpshadow-mark-sent', function() {
				ExitFollowups.updateFollowupStatus($(this).data('followup-id'), 'sent');
			});

			$(document).on('click', '.wpshadow-mark-completed', function() {
				ExitFollowups.updateFollowupStatus($(this).data('followup-id'), 'completed');
			});

			$(document).on('click', '.wpshadow-view-details', function() {
				ExitFollowups.viewFollowupDetails($(this).data('followup-id'));
			});
		},

		/**
		 * Load followups via AJAX
		 *
		 * @param {string} status Status filter
		 */
		loadFollowups: function(status) {
			const $container = $('#wpshadow-followups-container');
			$container.html('<p>' + wpShadowExitFollowups.strings.loading + '</p>');

			$.ajax({
				url: wpShadowExitFollowups.ajaxUrl,
				type: 'POST',
				data: {
					action: 'wpshadow_get_exit_followups',
					nonce: wpShadowExitFollowups.nonce,
					status: status,
					limit: 100
				},
				success: function(response) {
					if (response.success && response.data.followups) {
						ExitFollowups.renderFollowupsTable(response.data.followups);
					} else {
						$container.html('<p class="wpshadow-error">' + wpShadowExitFollowups.strings.error + '</p>');
					}
				},
				error: function() {
					$container.html('<p class="wpshadow-error">' + wpShadowExitFollowups.strings.error + '</p>');
				}
			});
		},

		/**
		 * Render followups table
		 *
		 * @param {Array} followups Array of followup objects
		 */
		renderFollowupsTable: function(followups) {
			const $container = $('#wpshadow-followups-container');

			if (followups.length === 0) {
				$container.html('<p>' + wpShadowExitFollowups.strings.noFollowups + '</p>');
				return;
			}

			let html = '<table class="wp-list-table widefat fixed striped wpshadow-followups-table">';
			html += '<thead><tr>';
			html += '<th>Type</th>';
			html += '<th>Contact Email</th>';
			html += '<th>Exit Reason</th>';
			html += '<th>Scheduled Date</th>';
			html += '<th>Status</th>';
			html += '<th>Actions</th>';
			html += '</tr></thead><tbody>';

			followups.forEach(function(followup) {
				html += '<tr data-followup-id="' + followup.id + '">';
				html += '<td>' + ExitFollowups.getFollowupTypeLabel(followup.followup_type) + '</td>';
				html += '<td>' + followup.contact_email + '</td>';
				html += '<td>' + (followup.exit_reason || 'Not specified') + '</td>';
				html += '<td>' + ExitFollowups.formatDate(followup.scheduled_date) + '</td>';
				html += '<td><span class="wpshadow-status-badge wpshadow-status-' + followup.status + '">' + followup.status + '</span></td>';
				html += '<td>';

				if (followup.status === 'pending') {
					html += '<button type="button" class="button button-small wpshadow-mark-sent" data-followup-id="' + followup.id + '">Mark as Sent</button> ';
				}

				if (followup.status === 'sent') {
					html += '<button type="button" class="button button-small wpshadow-mark-completed" data-followup-id="' + followup.id + '">Mark as Completed</button> ';
				}

				html += '<button type="button" class="button button-small wpshadow-view-details" data-followup-id="' + followup.id + '">View Details</button>';
				html += '</td>';
				html += '</tr>';
			});

			html += '</tbody></table>';
			$container.html(html);
		},

		/**
		 * Update followup status
		 *
		 * @param {number} followupId Followup ID
		 * @param {string} status New status
		 */
		updateFollowupStatus: function(followupId, status) {
			$.ajax({
				url: wpShadowExitFollowups.ajaxUrl,
				type: 'POST',
				data: {
					action: 'wpshadow_update_exit_followup',
					nonce: wpShadowExitFollowups.updateNonce,
					followup_id: followupId,
					status: status
				},
				success: function(response) {
					if (response.success) {
						// Reload followups
						ExitFollowups.loadFollowups($('#followup-status-filter').val());
						// Show success message
						ExitFollowups.showNotice(wpShadowExitFollowups.strings.updateSuccess, 'success');
					} else {
						ExitFollowups.showNotice(wpShadowExitFollowups.strings.updateError, 'error');
					}
				},
				error: function() {
					ExitFollowups.showNotice(wpShadowExitFollowups.strings.updateError, 'error');
				}
			});
		},

		/**
		 * View followup details
		 *
		 * @param {number} followupId Followup ID
		 */
		viewFollowupDetails: function(followupId) {
			// TODO: Implement modal/details view in future iteration
			// For now, this is a placeholder for future functionality
			// Will display survey questions, responses, and detailed history
		},

		/**
		 * Get human-readable followup type label
		 *
		 * @param {string} type Followup type
		 * @return {string} Label
		 */
		getFollowupTypeLabel: function(type) {
			const labels = {
				'immediate': 'Immediate (3 days)',
				'short_term': 'Short-term (14 days)',
				'long_term': 'Long-term (30 days)'
			};
			return labels[type] || type;
		},

		/**
		 * Format date
		 *
		 * @param {string} dateString Date string
		 * @return {string} Formatted date
		 */
		formatDate: function(dateString) {
			const date = new Date(dateString);
			return date.toLocaleString();
		},

		/**
		 * Show admin notice
		 *
		 * @param {string} message Notice message
		 * @param {string} type Notice type (success, error, warning, info)
		 */
		showNotice: function(message, type) {
			const $notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
			$('.wpshadow-exit-followups-page h1').after($notice);

			// Auto-dismiss after 3 seconds
			setTimeout(function() {
				$notice.fadeOut(function() {
					$(this).remove();
				});
			}, 3000);
		}
	};

	// Initialize on document ready
	$(document).ready(function() {
		if ($('.wpshadow-exit-followups-page').length) {
			ExitFollowups.init();
		}
	});

})(jQuery);
