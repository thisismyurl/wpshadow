/**
 * WPS Site Documentation Manager Scripts
 *
 * JavaScript for Site Blueprint, Protected Plugins, and Export Documentation pages.
 *
 * @package WPS_WP_SUPPORT
 * @since 1.2601.1111
 */

(function($) {
	'use strict';

	/**
	 * Site Blueprint functionality.
	 */
	const Blueprint = {
		init() {
			$('#wps-export-blueprint').on('click', this.exportBlueprint.bind(this));
			$('#wps-refresh-blueprint').on('click', this.refreshBlueprint.bind(this));
		},

		exportBlueprint() {
			const format = prompt('Export format (html/markdown/text):', 'html');
			if (!format) return;

			this.performExport(format, 'blueprint');
		},

		refreshBlueprint() {
			window.location.reload();
		},

		performExport(format, type) {
			$.ajax({
			url: wpsDocumentation.ajax_url,
			method: 'POST',
			data: {
				action: 'wps_export_documentation',
				nonce: wpsDocumentation.nonce,
					format: format,
					type: type || 'full'
				},
				beforeSend() {
					$('.button').prop('disabled', true);
				},
				success(response) {
					if (response.success) {
						const blob = new Blob([response.data.content], { 
							type: format === 'html' ? 'text/html' : 'text/plain' 
						});
						const url = window.URL.createObjectURL(blob);
						const link = document.createElement('a');
						link.href = url;
						link.download = response.data.filename;
						document.body.appendChild(link);
						link.click();
						document.body.removeChild(link);
						window.URL.revokeObjectURL(url);
						alert('Export successful!');
					} else {
						alert('Export failed: ' + response.data.message);
					}
				},
				error() {
					alert('Export request failed. Please try again.');
				},
				complete() {
					$('.button').prop('disabled', false);
				}
			});
		}
	};

	/**
	 * Protected Plugins functionality.
	 */
	const ProtectedPlugins = {
		init() {
			$(document).on('click', '.wps-toggle-protection', this.toggleProtection.bind(this));
			$(document).on('click', '.wps-protect-plugin, .wps-unprotect-plugin', this.toggleFromPluginsList.bind(this));
		},

		toggleProtection(e) {
			e.preventDefault();
			const $button = $(e.currentTarget);
			const plugin = $button.data('plugin');

			this.sendToggleRequest(plugin, $button);
		},

		toggleFromPluginsList(e) {
			e.preventDefault();
			const $link = $(e.currentTarget);
			const plugin = $link.data('plugin');

			this.sendToggleRequest(plugin, $link);
		},

		sendToggleRequest(plugin, $element) {
			$.ajax({
				url: wpsDocumentation.ajax_url,
				method: 'POST',
				data: {
					action: 'wps_toggle_plugin_protection',
					nonce: wpsDocumentation.nonce,
					plugin: plugin
				},
				beforeSend() {
					$element.prop('disabled', true).addClass('updating-message');
				},
				success(response) {
					if (response.success) {
						// Update UI based on new protection status.
						if (response.data.protected) {
							$element.text('Unprotect').removeClass('wps-protect-plugin').addClass('wps-unprotect-plugin');
							$element.closest('tr').addClass('wps-plugin-protected');
						} else {
							$element.text('Protect').removeClass('wps-unprotect-plugin').addClass('wps-protect-plugin');
							$element.closest('tr').removeClass('wps-plugin-protected');
						}
						
						// Show success message.
						const $notice = $('<div class="notice notice-success is-dismissible"><p>' + response.data.message + '</p></div>');
						$('.wrap > h1').after($notice);
						
						setTimeout(() => {
							$notice.fadeOut(() => $notice.remove());
						}, 3000);
					} else {
						alert('Failed to update protection: ' + response.data.message);
					}
				},
				error() {
					alert('Request failed. Please try again.');
				},
				complete() {
					$element.prop('disabled', false).removeClass('updating-message');
				}
			});
		}
	};

	/**
	 * Export Documentation functionality.
	 */
	const ExportDocumentation = {
		init() {
			$('#wps-export-now').on('click', this.startExport.bind(this));
			$('#wps-download-export').on('click', this.downloadExport.bind(this));
		},

		startExport() {
			const format = $('input[name="export_format"]:checked').val() || 'html';
			const $preview = $('.wps-export-preview');
			const $content = $('.wps-export-content');

			$.ajax({
				url: wpsDocumentation.ajax_url,
				method: 'POST',
				data: {
					action: 'wps_export_documentation',
					nonce: wpsDocumentation.nonce,
					format: format
				},
				beforeSend() {
					$('#wps-export-now').prop('disabled', true).text('Generating...');
				},
				success(response) {
					if (response.success) {
						$content.text(response.data.content);
						$content.data('filename', response.data.filename);
						$preview.slideDown();
						$('html, body').animate({
							scrollTop: $preview.offset().top - 50
						}, 500);
					} else {
						alert('Export failed: ' + response.data.message);
					}
				},
				error() {
					alert('Export request failed. Please try again.');
				},
				complete() {
					$('#wps-export-now').prop('disabled', false).text('Export Documentation');
				}
			});
		},

		downloadExport() {
			const content = $('.wps-export-content').text();
			const filename = $('.wps-export-content').data('filename');
			const format = $('input[name="export_format"]:checked').val() || 'html';

			const blob = new Blob([content], { 
				type: format === 'html' ? 'text/html' : 'text/plain' 
			});
			const url = window.URL.createObjectURL(blob);
			const link = document.createElement('a');
			link.href = url;
			link.download = filename;
			document.body.appendChild(link);
			link.click();
			document.body.removeChild(link);
			window.URL.revokeObjectURL(url);
		}
	};

	/**
	 * Initialize all modules on document ready.
	 */
	$(document).ready(function() {
		Blueprint.init();
		ProtectedPlugins.init();
		ExportDocumentation.init();
	});

})(jQuery);

