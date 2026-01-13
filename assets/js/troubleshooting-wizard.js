/**
 * Troubleshooting Wizard JavaScript
 *
 * @package WPS_WP_SUPPORT
 * @since 1.2601.73002
 */

(function($) {
	'use strict';

	const WPSTroubleshootWizard = {
		/**
		 * Initialize the wizard.
		 */
		init: function() {
			this.bindEvents();
			this.initializePage();
		},

		/**
		 * Bind event handlers.
		 */
		bindEvents: function() {
			// Issue selection
			$(document).on('click', '.wps-select-issue', this.handleIssueSelection.bind(this));

			// Start diagnosis automatically
			$(document).on('click', '.wps-view-fixes', this.handleViewFixes.bind(this));

			// Apply fix
			$(document).on('click', '.wps-apply-fix', this.handleApplyFix.bind(this));

			// Need support
			$(document).on('click', '.wps-need-support', this.handleNeedSupport.bind(this));

			// Download report
			$(document).on('click', '.wps-download-report', this.handleDownloadReport.bind(this));

			// Restart wizard
			$(document).on('click', '.wps-restart-wizard', this.handleRestartWizard.bind(this));
		},

		/**
		 * Initialize page-specific functionality.
		 */
		initializePage: function() {
			// If we're on the diagnosis step, start analysis automatically
			if ($('.wps-step-diagnosis').length > 0 && $('.wps-diagnosis-loading').is(':visible')) {
				this.runDiagnosis();
			}
		},

		/**
		 * Handle issue selection.
		 */
		handleIssueSelection: function(e) {
			e.preventDefault();
			const $card = $(e.currentTarget).closest('.wps-issue-card');
			const issue = $card.data('issue');

			// Highlight selected card
			$('.wps-issue-card').removeClass('selected');
			$card.addClass('selected');

			// Start troubleshooting session
			this.startTroubleshooting(issue);
		},

		/**
		 * Start troubleshooting session.
		 */
		startTroubleshooting: function(issue) {
			$.ajax({
				url: wpsWizard.ajaxurl,
				type: 'POST',
				data: {
					action: 'wps_start_troubleshooting',
					nonce: wpsWizard.nonce,
					issue: issue
				},
				success: function(response) {
					if (response.success && response.data.redirect) {
						window.location.href = response.data.redirect;
					}
				},
				error: function() {
					alert('Failed to start troubleshooting session. Please try again.');
				}
			});
		},

		/**
		 * Run diagnosis for the current issue.
		 */
		runDiagnosis: function() {
			$.ajax({
				url: wpsWizard.ajaxurl,
				type: 'POST',
				data: {
					action: 'wps_analyze_issue',
					nonce: wpsWizard.nonce
				},
				success: function(response) {
					if (response.success) {
						this.displayDiagnosisResults(response.data);
					} else {
						this.displayDiagnosisError(response.data.message);
					}
				}.bind(this),
				error: function() {
					this.displayDiagnosisError('Analysis failed. Please try again.');
				}.bind(this)
			});
		},

		/**
		 * Display diagnosis results.
		 */
		displayDiagnosisResults: function(data) {
			const $resultsContainer = $('.wps-diagnosis-results');
			const $loadingContainer = $('.wps-diagnosis-loading');
			
			// Build results HTML
			let html = '<h3>Diagnosis Complete</h3>';

			if (data.findings && data.findings.length > 0) {
				html += '<div class="wps-findings-list">';
				data.findings.forEach(function(finding) {
					const severityClass = finding.severity === 'critical' ? 'critical' : 
										  finding.severity === 'warning' ? 'warning' : 'info';
					const icon = finding.severity === 'critical' ? 'warning' : 
								 finding.severity === 'warning' ? 'warning' : 'info';

					html += `<div class="finding-item ${severityClass}">
						<span class="dashicons dashicons-${icon}"></span>
						<strong>${this.escapeHtml(finding.message)}</strong>`;

					if (finding.details) {
						html += `<p style="margin-top: 8px; color: #666;">${this.escapeHtml(finding.details)}</p>`;
					}

					html += '</div>';
				}.bind(this));
				html += '</div>';
			} else {
				html += '<p>No specific issues found. Your site appears to be functioning normally.</p>';
			}

			// Show results and hide loading
			$resultsContainer.html(html).show();
			$loadingContainer.hide();
			$('.wps-view-fixes').show();
		},

		/**
		 * Display diagnosis error.
		 */
		displayDiagnosisError: function(message) {
			const $resultsContainer = $('.wps-diagnosis-results');
			const $loadingContainer = $('.wps-diagnosis-loading');

			$resultsContainer.html('<p class="error-message">' + this.escapeHtml(message) + '</p>').show();
			$loadingContainer.hide();
		},

		/**
		 * Handle view fixes button.
		 */
		handleViewFixes: function(e) {
			e.preventDefault();
			// In a real implementation, this would transition to fixes step
			// For now, we'll reload the page with a step parameter
			const url = new URL(window.location.href);
			url.searchParams.set('step', 'fixes');
			window.location.href = url.toString();
		},

		/**
		 * Handle apply fix button.
		 */
		handleApplyFix: function(e) {
			e.preventDefault();
			const $button = $(e.currentTarget);
			const fix = $button.data('fix');

			$button.prop('disabled', true).text('Applying...');

			$.ajax({
				url: wpsWizard.ajaxurl,
				type: 'POST',
				data: {
					action: 'wps_apply_fix',
					nonce: wpsWizard.nonce,
					fix: fix
				},
				success: function(response) {
					if (response.success) {
						this.displayFixResult(response.data, $button);
					} else {
						alert(response.data.message || 'Fix failed. Please try again.');
						$button.prop('disabled', false).text('Apply Fix');
					}
				}.bind(this),
				error: function() {
					alert('Failed to apply fix. Please try again.');
					$button.prop('disabled', false).text('Apply Fix');
				}
			});
		},

		/**
		 * Display fix result.
		 */
		displayFixResult: function(data, $button) {
			const $card = $button.closest('.wps-fix-card');

			if (data.code) {
				// Show code to add
				const codeHtml = '<div class="wps-fix-code"><pre>' + this.escapeHtml(data.code) + '</pre></div>';
				$card.append(codeHtml);
			}

			$button.prop('disabled', true).text('Applied').addClass('button-success');

			if (data.message) {
				$card.find('p').text(data.message);
			}
		},

		/**
		 * Handle need support button.
		 */
		handleNeedSupport: function(e) {
			e.preventDefault();
			// Transition to support step
			const url = new URL(window.location.href);
			url.searchParams.set('step', 'support');
			window.location.href = url.toString();
		},

		/**
		 * Handle download report button.
		 */
		handleDownloadReport: function(e) {
			e.preventDefault();
			const $button = $(e.currentTarget);

			$button.prop('disabled', true);

			$.ajax({
				url: wpsWizard.ajaxurl,
				type: 'POST',
				data: {
					action: 'wps_generate_support_report',
					nonce: wpsWizard.nonce
				},
				success: function(response) {
					if (response.success && response.data.report) {
						this.downloadReport(response.data.report, response.data.filename);
					} else {
						alert('Failed to generate report. Please try again.');
					}
					$button.prop('disabled', false);
				}.bind(this),
				error: function() {
					alert('Failed to generate report. Please try again.');
					$button.prop('disabled', false);
				}
			});
		},

		/**
		 * Download report as text file.
		 */
		downloadReport: function(content, filename) {
			const blob = new Blob([content], { type: 'text/plain' });
			const url = window.URL.createObjectURL(blob);
			const a = document.createElement('a');
			a.href = url;
			a.download = filename || 'wps-troubleshooting-report.txt';
			document.body.appendChild(a);
			a.click();
			window.URL.revokeObjectURL(url);
			document.body.removeChild(a);
		},

		/**
		 * Handle restart wizard button.
		 */
		handleRestartWizard: function(e) {
			e.preventDefault();
			// Clear session and redirect to start
			const url = window.location.href.split('?')[0];
			window.location.href = url;
		},

		/**
		 * Escape HTML to prevent XSS.
		 */
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
		WPSTroubleshootWizard.init();
	});

})(jQuery);
