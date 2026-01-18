/**
 * Dark Mode JavaScript
 *
 * Handles dark mode toggle, auto-detection, and preference storage.
 *
 * @package WPShadow\CoreSupport
 */
(function($) {
	'use strict';

	var currentMode = 'light';
	var userPreference = 'auto';

	$(document).ready(function() {
		initDarkMode();
		attachEventHandlers();
	});

	/**
	 * Initialize dark mode based on preferences.
	 */
	function initDarkMode() {
		if (typeof wpshadowDarkMode === 'undefined') {
			return;
		}

		userPreference = wpshadowDarkMode.userPreference || 'auto';
		currentMode = wpshadowDarkMode.currentMode || 'light';

		// Apply initial mode
		applyDarkMode(currentMode);
	}

	/**
	 * Attach event handlers.
	 */
	function attachEventHandlers() {
		// Admin bar toggle
		$('#wp-admin-bar-wpshadow-dark-mode-toggle a').on('click', function(e) {
			e.preventDefault();
			toggleDarkMode();
		});

		// Dashboard widget toggle (if exists)
		$(document).on('click', '.wpshadow-dark-mode-toggle-btn', function(e) {
			e.preventDefault();
			cycleModes();
		});

		// Listen for system preference changes
		if (window.matchMedia) {
			window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
				if (userPreference === 'auto') {
					applyDarkMode(e.matches ? 'dark' : 'light');
				}
			});
		}
	}

	/**
	 * Toggle between light and dark mode.
	 */
	function toggleDarkMode() {
		var newMode = currentMode === 'dark' ? 'light' : 'dark';
		savePreference(newMode);
	}

	/**
	 * Cycle through auto -> light -> dark -> auto.
	 */
	function cycleModes() {
		var modes = ['auto', 'light', 'dark'];
		var currentIndex = modes.indexOf(userPreference);
		var nextIndex = (currentIndex + 1) % modes.length;
		var newMode = modes[nextIndex];
		
		savePreference(newMode);
	}

	/**
	 * Apply dark mode class to body.
	 *
	 * @param {string} mode 'light' or 'dark'
	 */
	function applyDarkMode(mode) {
		currentMode = mode;

		if (mode === 'dark') {
			$('body').addClass('wpshadow-dark-mode').removeClass('wpshadow-light-mode');
		} else {
			$('body').addClass('wpshadow-light-mode').removeClass('wpshadow-dark-mode');
		}

		// Update admin bar icon
		updateAdminBarIcon(mode);
	}

	/**
	 * Update admin bar icon.
	 *
	 * @param {string} mode Current mode
	 */
	function updateAdminBarIcon(mode) {
		var $adminBarLink = $('#wp-admin-bar-wpshadow-dark-mode-toggle a');
		if ($adminBarLink.length) {
			var icon = mode === 'dark' ? '🌙' : '☀️';
			var text = icon + ' Dark Mode';
			$adminBarLink.text(text);
		}
	}

	/**
	 * Save user preference via AJAX.
	 *
	 * @param {string} mode 'auto', 'light', or 'dark'
	 */
	function savePreference(mode) {
		if (typeof wpshadowDarkMode === 'undefined') {
			return;
		}

		userPreference = mode;

		// Determine actual mode to apply
		var modeToApply = mode;
		if (mode === 'auto') {
			// Check system preference
			if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
				modeToApply = 'dark';
			} else if (wpshadowDarkMode.wpColorScheme) {
				// Check WordPress color scheme
				var darkSchemes = ['midnight', 'ectoplasm', 'coffee'];
				modeToApply = darkSchemes.indexOf(wpshadowDarkMode.wpColorScheme) !== -1 ? 'dark' : 'light';
			} else {
				modeToApply = 'light';
			}
		}

		applyDarkMode(modeToApply);

		$.ajax({
			type: 'POST',
			url: wpshadowDarkMode.ajaxurl,
			data: {
				action: 'wpshadow_set_dark_mode',
				nonce: wpshadowDarkMode.nonce,
				mode: mode
			},
			success: function(response) {
				if (response.success) {
					// Update button text if exists
					updateModeButton(mode);
				}
			}
		});
	}

	/**
	 * Update mode button text.
	 *
	 * @param {string} mode Current preference mode
	 */
	function updateModeButton(mode) {
		var $button = $('.wpshadow-dark-mode-toggle-btn');
		if ($button.length) {
			var labels = {
				'auto': 'Auto',
				'light': 'Light',
				'dark': 'Dark'
			};
			$button.text('Mode: ' + (labels[mode] || 'Auto'));
		}
	}

})(jQuery);
