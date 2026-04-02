/**
 * WPShadow Dark Mode JavaScript
 * Handles dark mode application and switching
 */

jQuery( document ).ready(
	function ($) {
		// Get the dark mode preference from localized variable
		var darkModePreference = wpshadowDarkMode.preference || 'auto';

		/**
		 * Determine if dark mode should be active
		 */
		function shouldUseDarkMode(preference) {
			if (preference === 'dark') {
				return true;
			} else if (preference === 'light') {
				return false;
			} else if (preference === 'auto') {
				// Check system preference
				if (window.matchMedia && window.matchMedia( '(prefers-color-scheme: dark)' ).matches) {
					return true;
				}
				return false;
			}
			return false;
		}

		/**
		 * Apply or remove dark mode class
		 */
		function applyDarkMode(isDark) {
			if (isDark) {
				$( 'body' ).addClass( 'wpshadow-dark-mode' );
			} else {
				$( 'body' ).removeClass( 'wpshadow-dark-mode' );
			}
		}

		/**
		 * Initialize dark mode on page load
		 */
		function initializeDarkMode() {
			var isDark = shouldUseDarkMode( darkModePreference );
			applyDarkMode( isDark );
			updatePreviewMode( isDark );
		}

		/**
		 * Update preview element styling
		 */
		function updatePreviewMode(isDark) {
			var $preview = $( '#dark-mode-preview' );
			if ($preview.length) {
				if (isDark) {
					$preview.css(
						{
							'background-color': '#1e1e1e',
							'color': '#e0e0e0',
							'border-color': '#444'
						}
					);
				} else {
					$preview.css(
						{
							'background-color': '#fff',
							'color': '#333',
							'border-color': '#ddd'
						}
					);
				}
			}
		}

		/**
		 * Listen for radio button changes
		 */
		$( 'input[name="dark_mode_pref"]' ).on(
			'change',
			function () {
				var newPref = $( this ).val();
				var isDark  = shouldUseDarkMode( newPref );
				applyDarkMode( isDark );
				updatePreviewMode( isDark );
				darkModePreference = newPref;
			}
		);

		/**
		 * Listen for system theme changes (when in auto mode)
		 */
		if (window.matchMedia) {
			var darkModeQuery = window.matchMedia( '(prefers-color-scheme: dark)' );

			// Modern approach using addEventListener
			if (darkModeQuery.addEventListener) {
				darkModeQuery.addEventListener(
					'change',
					function (e) {
						if (darkModePreference === 'auto') {
							applyDarkMode( e.matches );
							updatePreviewMode( e.matches );
						}
					}
				);
			}
			// Fallback for older browsers using addListener
			else if (darkModeQuery.addListener) {
				darkModeQuery.addListener(
					function (e) {
						if (darkModePreference === 'auto') {
							applyDarkMode( e.matches );
							updatePreviewMode( e.matches );
						}
					}
				);
			}
		}

		// Initialize on page load
		initializeDarkMode();

		/**
		 * Reapply dark mode when new content is dynamically added
		 */
		$( document ).on(
			'contentLoaded',
			function () {
				initializeDarkMode();
			}
		);

		/**
		 * Listen for AJAX responses and reapply dark mode
		 */
		$( document ).ajaxComplete(
			function () {
				var isDark = shouldUseDarkMode( darkModePreference );
				applyDarkMode( isDark );
			}
		);
	}
);
