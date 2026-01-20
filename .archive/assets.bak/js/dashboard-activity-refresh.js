/**
 * Dashboard Activity History Auto-Refresh
 * 
 * Polls the server every 60 seconds to check for new activity history entries.
 * Updates the Activity History widget without requiring a full page reload.
 * 
 * @package WPShadow
 * @since 1.0.0
 */

(function($) {
	'use strict';

	// Configuration
	const POLL_INTERVAL = 60000; // 60 seconds (activity changes less frequently than health metrics)
	let pollTimer = null;
	let isPolling = false;

	/**
	 * Update the Activity History widget with new data.
	 * 
	 * @param {Object} data - The activity data from the AJAX response
	 */
	function updateActivityWidget(data) {
		if (!data || !data.logs) {
			console.warn('WPShadow Activity Refresh: Invalid data received');
			return;
		}

		const $timeline = $('.wpshadow-feature-log-timeline');
		if ($timeline.length === 0) {
			console.warn('WPShadow Activity Refresh: Timeline container not found');
			return;
		}

		// If no logs, show empty message
		if (data.logs.length === 0) {
			const $widgetContent = $timeline.closest('.wpshadow-widget-content');
			$widgetContent.html(
				'<p style="color: #646970; font-style: italic; margin: 0; text-align: center;">' +
				data.empty_message +
				'</p>'
			);
			return;
		}

		// Build HTML for all log entries
		let logsHtml = '';
		data.logs.forEach(function(log) {
			logsHtml += buildLogEntry(log);
		});

		// Update timeline with smooth transition
		$timeline.fadeOut(200, function() {
			$timeline.html(logsHtml);
			$timeline.fadeIn(200);
		});

		// Update "View All Activity" button visibility
		const $viewAllBtn = $('.wpshadow-widget-content .button-small');
		if (data.has_more && $viewAllBtn.length === 0) {
			// Add button if there are more logs but button doesn't exist
			const viewAllHtml = 
				'<div style="text-align: center; padding-top: 10px; border-top: 1px solid #dcdcde; margin-top: 10px;">' +
				'<a href="' + data.view_all_url + '" class="button button-small">' +
				data.view_all_label +
				'</a>' +
				'</div>';
			$timeline.after(viewAllHtml);
		} else if (!data.has_more && $viewAllBtn.length > 0) {
			// Remove button if there are no more logs
			$viewAllBtn.closest('div').remove();
		}

		console.log('WPShadow Activity Refresh: Updated with ' + data.logs.length + ' log entries');
	}

	/**
	 * Build HTML for a single log entry.
	 * 
	 * @param {Object} log - Log entry data
	 * @return {string} HTML string for the log entry
	 */
	function buildLogEntry(log) {
		let html = '<div class="wpshadow-log-entry" data-action="' + escapeHtml(log.action) + '">';
		html += '<div class="wpshadow-log-dot"></div>';
		html += '<div class="wpshadow-log-line"></div>';
		html += '<div class="wpshadow-log-content">';
		
		// Header with action and time
		html += '<div class="wpshadow-log-header">';
		html += '<span class="wpshadow-log-action">' + escapeHtml(log.action_label) + '</span>';
		html += '<span class="wpshadow-log-time" title="' + escapeHtml(log.timestamp_full) + '">';
		html += escapeHtml(log.timestamp_human);
		html += '</span>';
		html += '</div>';
		
		// Feature name with link (removed inline styles for CSS control)
		html += '<div class="wpshadow-log-feature">';
		html += '<a href="' + escapeHtml(log.feature_url) + '">';
		html += escapeHtml(log.feature_name);
		html += '</a>';
		html += '</div>';
		
		// Message if present
		if (log.message) {
			html += '<div class="wpshadow-log-message">' + escapeHtml(log.message) + '</div>';
		}
		
		// User if present
		if (log.user) {
			html += '<div class="wpshadow-log-user">by ' + escapeHtml(log.user) + '</div>';
		}
		
		html += '</div>'; // .wpshadow-log-content
		html += '</div>'; // .wpshadow-log-entry
		
		return html;
	}

	/**
	 * Escape HTML special characters.
	 * 
	 * @param {string} text - Text to escape
	 * @return {string} Escaped text
	 */
	function escapeHtml(text) {
		if (text === null || text === undefined) {
			return '';
		}
		const map = {
			'&': '&amp;',
			'<': '&lt;',
			'>': '&gt;',
			'"': '&quot;',
			"'": '&#039;'
		};
		return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
	}

	/**
	 * Poll the server for updated activity data.
	 */
	function pollActivityWidget() {
		if (isPolling) {
			console.log('WPShadow Activity Refresh: Poll already in progress, skipping...');
			return;
		}

		isPolling = true;

		$.ajax({
			url: wpshadowActivity.ajaxUrl,
			type: 'POST',
			data: {
				action: 'wpshadow_refresh_activity',
				nonce: wpshadowActivity.nonce
			},
			success: function(response) {
				if (response.success && response.data) {
					updateActivityWidget(response.data);
				} else {
					console.error('WPShadow Activity Refresh: Server returned error', response);
				}
			},
			error: function(xhr, status, error) {
				console.error('WPShadow Activity Refresh: AJAX error', status, error);
			},
			complete: function() {
				isPolling = false;
			}
		});
	}

	/**
	 * Start the polling timer.
	 */
	function startPolling() {
		// Poll immediately after first delay, then every interval
		pollTimer = setTimeout(function() {
			pollActivityWidget();
			
			// Set up recurring poll
			pollTimer = setInterval(pollActivityWidget, POLL_INTERVAL);
		}, POLL_INTERVAL);

		console.log('WPShadow Activity Refresh: Started polling (interval: ' + (POLL_INTERVAL / 1000) + 's)');
	}

	/**
	 * Stop the polling timer.
	 */
	function stopPolling() {
		if (pollTimer) {
			clearTimeout(pollTimer);
			clearInterval(pollTimer);
			pollTimer = null;
			console.log('WPShadow Activity Refresh: Stopped polling');
		}
	}

	/**
	 * Initialize the auto-refresh system.
	 */
	function init() {
		// Only initialize if we're on the dashboard tab with Activity History widget
		if ($('.wpshadow-feature-log-timeline').length === 0 && 
		    $('.wpshadow-widget-content p:contains("No activity logged yet")').length === 0) {
			console.log('WPShadow Activity Refresh: Activity History widget not found, skipping initialization');
			return;
		}

		// Verify AJAX settings are available
		if (typeof wpshadowActivity === 'undefined') {
			console.error('WPShadow Activity Refresh: wpshadowActivity object not found');
			return;
		}

		console.log('WPShadow Activity Refresh: Initialized');
		startPolling();
	}

	// Initialize on document ready
	$(document).ready(init);

	// Stop polling when page is unloaded
	$(window).on('beforeunload', stopPolling);

})(jQuery);
