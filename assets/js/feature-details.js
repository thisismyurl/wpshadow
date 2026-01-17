/**
 * Feature Details Page JavaScript
 *
 * Handles feature toggle auto-save functionality on the features page.
 * Listens for checkbox changes and sends AJAX requests to save state.
 *
 * @package WPShadow\CoreSupport
 */
(function($) {
	'use strict';

	$(document).ready(function() {
		initializeFeatureToggles();
		initializeSubFeatureToggles();
		initializeParentChildToggles();
	});

	/**
	 * Initialize feature toggle handlers.
	 */
	function initializeFeatureToggles() {
		var toggles = $('.wps-feature-toggle-input');

		toggles.on('change', function() {
			var $toggle = $(this);
			var featureId = extractFeatureId($toggle);
			var featureName = extractFeatureName($toggle);
			var isEnabled = $toggle.is(':checked');

			saveFeatureState(featureId, featureName, isEnabled);
		});
	}

	/**
	 * Initialize sub-feature toggle handlers.
	 */
	function initializeSubFeatureToggles() {
		var subToggles = $('.wpshadow-sub-feature-toggle');

		subToggles.on('change', function() {
			var $toggle = $(this);
			var featureId = $toggle.data('feature-id');
			var settingKey = $toggle.data('setting-key');
			var isEnabled = $toggle.is(':checked');
			
			// Extract setting name from the label
			var $row = $toggle.closest('tr');
			var settingName = $row.find('strong').text().trim();

			saveSubFeatureState(featureId, settingKey, settingName, isEnabled);
		});
	}

	/**
	 * Initialize parent-child feature toggle handlers for hierarchical features.
	 * When a parent feature is toggled off, all child features are automatically disabled.
	 */
	function initializeParentChildToggles() {
		// Handle parent feature toggles
		$(document).on('change', '.wpshadow-parent-toggle', function() {
			var $parentToggle = $(this);
			var parentId = $parentToggle.data('parent-id');
			var isParentEnabled = $parentToggle.is(':checked');

			// Find all child rows for this parent
			var $childRows = $('.wpshadow-child-of-' + parentId);
			var $childToggles = $childRows.find('.wpshadow-child-toggle');

			if (!isParentEnabled) {
				// If parent is disabled, disable all children
				$childToggles.each(function() {
					var $childToggle = $(this);
					if ($childToggle.is(':checked')) {
						$childToggle.prop('checked', false).trigger('change');
					}
					$childToggle.prop('disabled', true).css('opacity', '0.5');
				});

				// Visual feedback: slightly fade child rows
				$childRows.css('opacity', '0.6');
			} else {
				// If parent is enabled, re-enable child toggles (they can be individually controlled)
				$childToggles.each(function() {
					$(this).prop('disabled', false).css('opacity', '1');
				});

				// Visual feedback: restore child rows to full opacity
				$childRows.css('opacity', '1');
			}
		});

		// Disable children if parent is unchecked on page load
		$('.wpshadow-parent-toggle').each(function() {
			var $parentToggle = $(this);
			var parentId = $parentToggle.data('parent-id');
			var isParentEnabled = $parentToggle.is(':checked');

			if (!isParentEnabled) {
				var $childRows = $('.wpshadow-child-of-' + parentId);
				var $childToggles = $childRows.find('.wpshadow-child-toggle');

				$childToggles.each(function() {
					$(this).prop('disabled', true).css('opacity', '0.5');
				});

				$childRows.css('opacity', '0.6');
			}
		});
	}

	/**
	 * Extract feature ID from toggle element's name attribute.
	 *
	 * @param {jQuery} $toggle The toggle element
	 * @returns {string} Feature ID or 'unknown'
	 */
	function extractFeatureId($toggle) {
		var name = $toggle.attr('name');
		if (!name) {
			return 'unknown';
		}

		// Extract from name like "features[feature-id]"
		var match = name.match(/\[([^\]]+)\]/);
		return match ? match[1] : 'unknown';
	}

	/**
	 * Extract feature name from the row.
	 *
	 * @param {jQuery} $toggle The toggle element
	 * @returns {string} Feature name or 'Feature'
	 */
	function extractFeatureName($toggle) {
		// Find the closest row, then find the feature name link or strong text
		var $row = $toggle.closest('tr');
		if ($row.length) {
			// Try to find the link first (features with settings pages)
			var $link = $row.find('strong a');
			if ($link.length) {
				return $link.text().trim();
			}
			// Otherwise find the strong text (features without settings)
			var $strong = $row.find('strong').first();
			if ($strong.length) {
				return $strong.text().trim();
			}
		}
		return 'Feature';
	}

	/**
	 * Save feature state via AJAX request.
	 *
	 * @param {string} featureId The feature ID to save
	 * @param {string} featureName The feature name to display
	 * @param {boolean} isEnabled Whether the feature is enabled
	 */
	function saveFeatureState(featureId, featureName, isEnabled) {
		// Validate localized data availability
		if (typeof wpshadowFeatureDetails === 'undefined' || !wpshadowFeatureDetails.nonce) {
			console.error('[WPShadow] Feature toggle nonce not available');
			return;
		}

		var data = {
			action: 'wpshadow_toggle_feature',
			nonce: wpshadowFeatureDetails.nonce,
			feature_id: featureId,
			enabled: isEnabled ? 'true' : 'false'
		};

		$.ajax({
			type: 'POST',
			url: wpshadowFeatureDetails.ajaxurl,
			data: data,
			success: function(response) {
				if (response.success) {
					var message = featureName + ' ' + (isEnabled ? 'enabled.' : 'disabled.');
					var type = isEnabled ? 'success' : 'disabled';
					showToast(message, type, featureId);
					
					// Toggle settings section disabled state (on feature details page)
					var $settingsSection = $('.feature-sub-features-section');
					if ($settingsSection.length) {
						if (isEnabled) {
							$settingsSection.removeClass('feature-settings-disabled');
							$settingsSection.find('.wpshadow-sub-feature-toggle').prop('disabled', false);
						} else {
							$settingsSection.addClass('feature-settings-disabled');
							$settingsSection.find('.wpshadow-sub-feature-toggle').prop('disabled', true);
						}
					}
					
					// Toggle settings button on features list page
					var $row = $('.wps-feature-toggle-input[name="features[' + featureId + ']"]').closest('tr');
					if ($row.length) {
						var $settingsBtn = $row.find('.wps-feature-settings-btn');
						var $healthBadge = $row.find('.wps-health-score-badge');
						
						if (isEnabled) {
							// Enable settings button
							if ($settingsBtn.length) {
								$settingsBtn.removeAttr('disabled').css({'pointer-events': '', 'opacity': ''});
							}
							// Show health badge with animation
							if ($healthBadge.length) {
								$healthBadge.css({
									'display': 'inline-flex',
									'animation': 'fadeIn 0.3s ease'
								});
							}
						} else {
							// Disable settings button
							if ($settingsBtn.length) {
								$settingsBtn.attr('disabled', 'disabled').css({'pointer-events': 'none', 'opacity': '0.5'});
							}
							// Hide health badge
							if ($healthBadge.length) {
								$healthBadge.css('display', 'none');
							}
						}
					}
				}
			},
			error: function(xhr, status, error) {
				console.error('[WPShadow] Error saving feature state:', error);
				showToast('Error saving ' + featureName + '.', 'error', featureId);
			}
		});
	}

	/**
	 * Save sub-feature setting state via AJAX request.
	 *
	 * @param {string} featureId The parent feature ID
	 * @param {string} settingKey The setting key
	 * @param {string} settingName The setting name to display
	 * @param {boolean} isEnabled Whether the setting is enabled
	 */
	function saveSubFeatureState(featureId, settingKey, settingName, isEnabled) {
		// Validate localized data availability
		if (typeof wpshadowFeatureDetails === 'undefined' || !wpshadowFeatureDetails.nonce) {
			console.error('[WPShadow] Feature details nonce not available');
			return;
		}

		var data = {
			action: 'wpshadow_toggle_feature_setting',
			nonce: wpshadowFeatureDetails.nonce,
			feature_id: featureId,
			setting_key: settingKey,
			enabled: isEnabled ? 'true' : 'false'
		};

		$.ajax({
			type: 'POST',
			url: wpshadowFeatureDetails.ajaxurl,
			data: data,
			success: function(response) {
				if (response.success) {
					var message = settingName + ' ' + (isEnabled ? 'enabled.' : 'disabled.');
					var type = isEnabled ? 'success' : 'disabled';
					showToast(message, type, featureId);
				}
			},
			error: function(xhr, status, error) {
				console.error('[WPShadow] Error saving sub-feature setting:', error);
				showToast('Error saving ' + settingName + '.', 'error', featureId);
			}
		});
	}

	/**
	 * Display a modern toast notification.
	 *
	 * @param {string} message The message to display
	 * @param {string} type The toast type: 'success', 'disabled', or 'error'
	 * @param {string} featureId Optional feature ID for context
	 */
	function showToast(message, type, featureId) {
		type = type || 'success';

		// Create toast container if it doesn't exist
		var container = $('#wpshadow-toast-container');
		if (!container.length) {
			container = $('<div id="wpshadow-toast-container"></div>').appendTo('body');
		}

		// Create toast element with appropriate icon
		var icon = type === 'success' ? '✓' : (type === 'disabled' ? '○' : '✕');
		var toast = $(
			'<div class="wpshadow-toast wpshadow-toast-' + type + '" role="alert">' +
				'<span class="wpshadow-toast-icon">' + icon + '</span>' +
				'<span class="wpshadow-toast-message">' + escapeHtml(message) + '</span>' +
			'</div>'
		);

		// Add to container
		container.append(toast);

		// Trigger animation
		setTimeout(function() {
			toast.addClass('wpshadow-toast-show');
		}, 10);

		// Auto-dismiss after 4 seconds
		setTimeout(function() {
			toast.removeClass('wpshadow-toast-show');
			setTimeout(function() {
				toast.remove();
			}, 300);
		}, 4000);
	}

	/**
	 * Escape HTML to prevent XSS.
	 *
	 * @param {string} text The text to escape
	 * @returns {string} Escaped text
	 */
	function escapeHtml(text) {
		var div = document.createElement('div');
		div.textContent = text;
		return div.innerHTML;
	}

})(jQuery);
