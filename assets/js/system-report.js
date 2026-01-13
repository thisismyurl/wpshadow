/**
 * System Report Generator JavaScript.
 *
 * @package WPS_WP_SUPPORT_THISISMYURL
 */

(function($) {
	'use strict';

	let currentReport = '';
	let currentFormat = 'json';

	/**
	 * Initialize the system report functionality.
	 */
	function init() {
		// Generate report buttons
		$('#wps-generate-json').on('click', function() {
			generateReport('json');
		});

		$('#wps-generate-txt').on('click', function() {
			generateReport('txt');
		});

		$('#wps-generate-pdf').on('click', function() {
			generateReport('pdf');
		});

		// Copy report to clipboard
		$('#wps-copy-report').on('click', function() {
			copyToClipboard($('#wps-report-content').val());
		});

		// Download report
		$('#wps-download-report').on('click', function() {
			downloadReport();
		});

		// Create shareable link
		$('#wps-create-link').on('click', function() {
			createShareableLink();
		});

		// Copy shareable link
		$('#wps-copy-link').on('click', function() {
			copyToClipboard($('#wps-shareable-url').val());
		});
	}

	/**
	 * Generate a system report.
	 *
	 * @param {string} format Report format (json, txt, pdf)
	 */
	function generateReport(format) {
		const $statusDiv = $('.wps-report-status');
		const $outputDiv = $('.wps-report-output');
		const $content = $('#wps-report-content');

		// Show loading status
		$statusDiv.show();
		$statusDiv.find('.status-text').text(wpsSystemReport.strings.generating);
		$outputDiv.hide();

		// Disable buttons
		$('.wps-report-buttons button').prop('disabled', true);

		$.ajax({
			url: wpsSystemReport.ajaxUrl,
			type: 'POST',
			data: {
				action: 'wps_generate_report',
				format: format,
				nonce: wpsSystemReport.nonce
			},
			success: function(response) {
				if (response.success) {
					currentReport = response.data.report;
					currentFormat = response.data.format;

					// Display report
					$content.val(currentReport);
					$outputDiv.show();

					// Update status
					$statusDiv.find('.status-text').text(wpsSystemReport.strings.generated);
					setTimeout(function() {
						$statusDiv.hide();
					}, 2000);
				} else {
					showError(response.data.message || wpsSystemReport.strings.error);
				}
			},
			error: function(xhr, status, error) {
				showError(wpsSystemReport.strings.error + ': ' + error);
			},
			complete: function() {
				// Re-enable buttons
				$('.wps-report-buttons button').prop('disabled', false);
			}
		});
	}

	/**
	 * Create a shareable link for the report.
	 */
	function createShareableLink() {
		const password = $('#wps-link-password').val();
		const $button = $('#wps-create-link');
		const $resultDiv = $('.wps-link-result');

		// Disable button and show loading
		$button.prop('disabled', true).text(wpsSystemReport.strings.creatingLink);
		$resultDiv.hide();

		$.ajax({
			url: wpsSystemReport.ajaxUrl,
			type: 'POST',
			data: {
				action: 'wps_create_shareable_link',
				password: password,
				nonce: wpsSystemReport.nonce
			},
			success: function(response) {
				if (response.success) {
					const data = response.data;

					// Display link
					$('#wps-shareable-url').val(data.url);
					$('#wps-link-expires-at').text(data.expires_at);
					$resultDiv.show();

					// Show success message
					showSuccess(wpsSystemReport.strings.linkCreated);
				} else {
					showError(response.data.message || wpsSystemReport.strings.error);
				}
			},
			error: function(xhr, status, error) {
				showError(wpsSystemReport.strings.error + ': ' + error);
			},
			complete: function() {
				// Re-enable button
				$button.prop('disabled', false).html('<span class="dashicons dashicons-admin-links"></span> Create Shareable Link');
			}
		});
	}

	/**
	 * Copy text to clipboard.
	 *
	 * @param {string} text Text to copy
	 */
	function copyToClipboard(text) {
		const $temp = $('<textarea>');
		$('body').append($temp);
		$temp.val(text).select();

		try {
			document.execCommand('copy');
			showSuccess(wpsSystemReport.strings.copied);
		} catch (err) {
			// Fallback for modern browsers
			if (navigator.clipboard && navigator.clipboard.writeText) {
				navigator.clipboard.writeText(text).then(function() {
					showSuccess(wpsSystemReport.strings.copied);
				}).catch(function() {
					showError(wpsSystemReport.strings.copyFailed);
				});
			} else {
				showError(wpsSystemReport.strings.copyFailed);
			}
		}

		$temp.remove();
	}

	/**
	 * Download the report as a file.
	 */
	function downloadReport() {
		if (!currentReport) {
			showError('No report generated yet');
			return;
		}

		const filename = 'system-report-' + Date.now() + '.' + currentFormat;
		const blob = new Blob([currentReport], { type: 'text/plain' });
		const url = window.URL.createObjectURL(blob);
		const a = document.createElement('a');
		
		a.href = url;
		a.download = filename;
		document.body.appendChild(a);
		a.click();
		
		window.URL.revokeObjectURL(url);
		document.body.removeChild(a);
	}

	/**
	 * Show success message.
	 *
	 * @param {string} message Success message
	 */
	function showSuccess(message) {
		const $notice = $('<div class="notice notice-success is-dismissible"><p>' + message + '</p></div>');
		$('.wrap.wps-system-report-page h1').after($notice);
		
		setTimeout(function() {
			$notice.fadeOut(function() {
				$(this).remove();
			});
		}, 3000);
	}

	/**
	 * Show error message.
	 *
	 * @param {string} message Error message
	 */
	function showError(message) {
		const $notice = $('<div class="notice notice-error is-dismissible"><p>' + message + '</p></div>');
		$('.wrap.wps-system-report-page h1').after($notice);
		
		setTimeout(function() {
			$notice.fadeOut(function() {
				$(this).remove();
			});
		}, 5000);
	}

	// Initialize on document ready
	$(document).ready(init);

})(jQuery);
