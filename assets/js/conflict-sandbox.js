/**
 * Conflict Sandbox Admin Interface
 *
 * Handles AJAX interactions for the Conflict Sandbox feature.
 *
 * @package WPS\CoreSupport
 */

(function($) {
	'use strict';

	$(document).ready(function() {
		const sandbox = {
			/**
			 * Initialize sandbox controls.
			 */
			init: function() {
				this.bindEvents();
			},

			/**
			 * Bind event handlers.
			 */
			bindEvents: function() {
				// Enter sandbox mode.
				$('#wps-enter-sandbox').on('click', this.enterSandbox.bind(this));

				// Exit sandbox mode.
				$('#wps-exit-sandbox').on('click', this.exitSandbox.bind(this));

				// Toggle plugin.
				$('.wps-toggle-plugin').on('click', this.togglePlugin.bind(this));

				// Set theme.
				$('.wps-set-theme').on('click', this.setTheme.bind(this));
			},

			/**
			 * Enter sandbox mode.
			 */
			enterSandbox: function(e) {
				e.preventDefault();

				const $button = $(e.currentTarget);
				const originalText = $button.text();

				$button.prop('disabled', true).text(wpsSandbox.strings.entering);

				$.ajax({
					url: wpsSandbox.ajaxUrl,
					type: 'POST',
					data: {
						action: 'wpshadow_sandbox_enter',
						nonce: wpsSandbox.nonce
					},
					success: function() {
						// Reload page to show sandbox controls.
						window.location.reload();
					},
					error: function() {
						alert(wpsSandbox.strings.error);
						$button.prop('disabled', false).text(originalText);
					}
				});
			},

			/**
			 * Exit sandbox mode.
			 */
			exitSandbox: function(e) {
				e.preventDefault();

				if (!confirm(wpsSandbox.strings.confirmExit)) {
					return;
				}

				const $button = $(e.currentTarget);
				const originalText = $button.text();

				$button.prop('disabled', true).text(wpsSandbox.strings.exiting);

				$.ajax({
					url: wpsSandbox.ajaxUrl,
					type: 'POST',
					data: {
						action: 'wpshadow_sandbox_exit',
						nonce: wpsSandbox.nonce
					},
					success: function() {
						// Reload page to hide sandbox controls.
						window.location.reload();
					},
					error: function() {
						alert(wpsSandbox.strings.error);
						$button.prop('disabled', false).text(originalText);
					}
				});
			},

			/**
			 * Toggle plugin state in sandbox.
			 */
			togglePlugin: function(e) {
				e.preventDefault();

				const $button = $(e.currentTarget);
				const plugin = $button.data('plugin');
				const action = $button.data('action');
				const originalText = $button.text();

				$button.prop('disabled', true).text(wpsSandbox.strings.toggling);

				$.ajax({
					url: wpsSandbox.ajaxUrl,
					type: 'POST',
					data: {
						action: 'wpshadow_sandbox_toggle_plugin',
						nonce: wpsSandbox.nonce,
						plugin: plugin,
						action_type: action
					},
					success: function() {
						// Reload page to reflect changes.
						window.location.reload();
					},
					error: function(xhr) {
						const message = xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message
							? xhr.responseJSON.data.message
							: wpsSandbox.strings.error;
						alert(message);
						$button.prop('disabled', false).text(originalText);
					}
				});
			},

			/**
			 * Set theme in sandbox.
			 */
			setTheme: function(e) {
				e.preventDefault();

				const $button = $(e.currentTarget);
				const theme = $button.data('theme');
				const originalText = $button.text();

				$button.prop('disabled', true).text(wpsSandbox.strings.settingTheme);

				$.ajax({
					url: wpsSandbox.ajaxUrl,
					type: 'POST',
					data: {
						action: 'wpshadow_sandbox_set_theme',
						nonce: wpsSandbox.nonce,
						theme: theme
					},
					success: function() {
						// Reload page to apply theme change.
						window.location.reload();
					},
					error: function(xhr) {
						const message = xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message
							? xhr.responseJSON.data.message
							: wpsSandbox.strings.error;
						alert(message);
						$button.prop('disabled', false).text(originalText);
					}
				});
			}
		};

		// Initialize.
		sandbox.init();
	});
})(jQuery);
