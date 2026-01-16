/**
 * Feature Details Page JavaScript
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.76000
 */

(function($) {
	'use strict';

	const FeatureDetails = {
		init: function() {
			this.bindEvents();
		},

		bindEvents: function() {
			// Main feature toggle
			$(document).on('change', '.wpshadow-feature-toggle', this.handleFeatureToggle);
			
			// Sub-feature/setting toggles
			$(document).on('change', '.wpshadow-sub-feature-toggle', this.handleSettingToggle);
			
			// Refresh log button
			$(document).on('click', '.wpshadow-refresh-log', this.handleRefreshLog);
		},

		handleFeatureToggle: function(e) {
			const $toggle = $(this);
			const $label = $toggle.closest('.wpshadow-toggle-switch').find('.wpshadow-toggle-label');
			const featureId = $toggle.data('feature-id');
			const enabled = $toggle.is(':checked');
			
			// Disable toggle during request
			$toggle.prop('disabled', true);
			$label.text(wpshadowFeatureDetails.strings.enabling);

			$.ajax({
				url: wpshadowFeatureDetails.ajaxurl,
				type: 'POST',
				data: {
					action: 'wpshadow_toggle_feature',
					nonce: wpshadowFeatureDetails.nonce,
					feature_id: featureId,
					enabled: enabled ? 'true' : 'false'
				},
				success: function(response) {
					if (response.success) {
						$label.text(enabled ? wpshadowFeatureDetails.strings.enabled : wpshadowFeatureDetails.strings.disabled);
						
						// Enable/disable sub-feature toggles
						$('.wpshadow-sub-feature-toggle').prop('disabled', !enabled);
						
						FeatureDetails.showMessage('success', response.data.message);
						
						// Refresh log after a short delay
						setTimeout(function() {
							FeatureDetails.refreshLog(featureId);
						}, 500);
					} else {
						// Revert toggle on error
						$toggle.prop('checked', !enabled);
						$label.text(!enabled ? wpshadowFeatureDetails.strings.enabled : wpshadowFeatureDetails.strings.disabled);
						FeatureDetails.showMessage('error', response.data.message || wpshadowFeatureDetails.strings.error);
					}
				},
				error: function(xhr) {
					// Revert toggle on error
					$toggle.prop('checked', !enabled);
					$label.text(!enabled ? wpshadowFeatureDetails.strings.enabled : wpshadowFeatureDetails.strings.disabled);
					FeatureDetails.showMessage('error', wpshadowFeatureDetails.strings.error + xhr.statusText);
				},
				complete: function() {
					$toggle.prop('disabled', false);
				}
			});
		},

		handleSettingToggle: function(e) {
			const $toggle = $(this);
			const $label = $toggle.closest('.wpshadow-toggle-switch').find('.wpshadow-toggle-label');
			const featureId = $toggle.data('feature-id');
			const settingKey = $toggle.data('setting-key');
			const enabled = $toggle.is(':checked');
			
			// Disable toggle during request
			$toggle.prop('disabled', true);

			$.ajax({
				url: wpshadowFeatureDetails.ajaxurl,
				type: 'POST',
				data: {
					action: 'wpshadow_toggle_feature_setting',
					nonce: wpshadowFeatureDetails.nonce,
					feature_id: featureId,
					setting_key: settingKey,
					enabled: enabled ? 'true' : 'false'
				},
				success: function(response) {
					if (response.success) {
						FeatureDetails.showMessage('success', response.data.message);
						
						// Refresh log after a short delay
						setTimeout(function() {
							FeatureDetails.refreshLog(featureId);
						}, 500);
					} else {
						// Revert toggle on error
						$toggle.prop('checked', !enabled);
						FeatureDetails.showMessage('error', response.data.message || wpshadowFeatureDetails.strings.error);
					}
				},
				error: function(xhr) {
					// Revert toggle on error
					$toggle.prop('checked', !enabled);
					FeatureDetails.showMessage('error', wpshadowFeatureDetails.strings.error + xhr.statusText);
				},
				complete: function() {
					$toggle.prop('disabled', false);
				}
			});
		},

		handleRefreshLog: function(e) {
			e.preventDefault();
			const $button = $(this);
			const featureId = $button.data('feature-id');
			
			FeatureDetails.refreshLog(featureId, $button);
		},

		refreshLog: function(featureId, $button) {
			const $logContainer = $('.feature-activity-log[data-feature-id="' + featureId + '"]');
			
			if ($button) {
				$button.prop('disabled', true);
				$button.find('.dashicons').addClass('dashicons-update-spin');
			}

			$.ajax({
				url: wpshadowFeatureDetails.ajaxurl,
				type: 'POST',
				data: {
					action: 'wpshadow_get_feature_log',
					nonce: wpshadowFeatureDetails.nonce,
					feature_id: featureId
				},
				success: function(response) {
					if (response.success && response.data.log) {
						FeatureDetails.updateLogDisplay($logContainer, response.data.log);
					}
				},
				error: function(xhr) {
					console.error('Failed to refresh log:', xhr);
				},
				complete: function() {
					if ($button) {
						$button.prop('disabled', false);
						$button.find('.dashicons').removeClass('dashicons-update-spin');
					}
				}
			});
		},

		updateLogDisplay: function($container, log) {
			if (!log || log.length === 0) {
				$container.html('<p class="no-activity">No activity recorded yet.</p>');
				return;
			}

			let html = '<table class="widefat striped activity-log-table">';
			html += '<thead><tr>';
			html += '<th>Time</th>';
			html += '<th>Action</th>';
			html += '<th>Details</th>';
			html += '</tr></thead><tbody>';

			// Reverse to show newest first
			log.reverse().forEach(function(entry) {
				const date = new Date(entry.timestamp * 1000);
				const timeStr = date.toISOString().slice(0, 19).replace('T', ' ');
				const level = entry.level || 'info';

				html += '<tr>';
				html += '<td class="log-time">' + FeatureDetails.escapeHtml(timeStr) + '</td>';
				html += '<td class="log-action"><span class="log-level log-level-' + level + '">' + 
						FeatureDetails.escapeHtml(entry.action) + '</span></td>';
				html += '<td class="log-details">' + FeatureDetails.escapeHtml(entry.message) + '</td>';
				html += '</tr>';
			});

			html += '</tbody></table>';
			$container.html(html);
		},

		showMessage: function(type, message) {
			const $container = $('.wpshadow-feature-details');
			const $existing = $container.find('.wpshadow-message');
			
			// Remove existing messages
			$existing.fadeOut(function() {
				$(this).remove();
			});

			// Add new message
			const $message = $('<div class="wpshadow-message ' + type + '">' + 
							 FeatureDetails.escapeHtml(message) + '</div>');
			$container.prepend($message);

			// Auto-hide after 5 seconds
			setTimeout(function() {
				$message.fadeOut(function() {
					$(this).remove();
				});
			}, 5000);
		},

		escapeHtml: function(text) {
			const map = {
				'&': '&amp;',
				'<': '&lt;',
				'>': '&gt;',
				'"': '&quot;',
				"'": '&#039;'
			};
			return text.replace(/[&<>"']/g, function(m) { return map[m]; });
		}
	};

	// Initialize when document is ready
	$(document).ready(function() {
		FeatureDetails.init();
	});

})(jQuery);
