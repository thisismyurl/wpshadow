/**
 * Accessibility Audit JavaScript
 *
 * Handles AJAX requests for running audits and applying fixes.
 *
 * @package wp_support_SUPPORT
 */

(function($) {
	'use strict';

	var WPSAccessibilityAudit = {
		/**
		 * Initialize the audit functionality.
		 */
		init: function() {
			this.bindEvents();
		},

		/**
		 * Bind event handlers.
		 */
		bindEvents: function() {
			$('#wps-run-audit').on('click', this.runAudit.bind(this));
			$(document).on('click', '.wps-apply-fix', this.applyFix.bind(this));
		},

		/**
		 * Run the accessibility audit.
		 */
		runAudit: function(e) {
			e.preventDefault();
			
			var url = $('#wps-audit-url').val();
			
			if (!url) {
				alert(wpsA11yAudit.strings.enterUrl || 'Please enter a URL to audit.');
				return;
			}

			// Show loading state
			$('#wps-audit-loading').show();
			$('#wps-audit-results').hide();
			$('#wps-run-audit').prop('disabled', true);

			// Make AJAX request
			$.ajax({
				url: wpsA11yAudit.ajaxUrl,
				type: 'POST',
				data: {
					action: 'wps_run_a11y_audit',
					nonce: wpsA11yAudit.nonce,
					url: url
				},
				success: this.handleAuditResponse.bind(this),
				error: this.handleAuditError.bind(this),
				complete: function() {
					$('#wps-audit-loading').hide();
					$('#wps-run-audit').prop('disabled', false);
				}
			});
		},

		/**
		 * Handle successful audit response.
		 *
		 * @param {Object} response AJAX response.
		 */
		handleAuditResponse: function(response) {
			if (response.success && response.data.issues) {
				this.displayResults(response.data.issues);
			} else {
				alert(response.data.message || 'Failed to run audit.');
			}
		},

		/**
		 * Handle audit error.
		 *
		 * @param {Object} xhr XHR object.
		 */
		handleAuditError: function(xhr) {
			alert('Error running audit. Please try again.');
			console.error('Audit error:', xhr);
		},

		/**
		 * Display audit results.
		 *
		 * @param {Array} issues Array of issues found.
		 */
		displayResults: function(issues) {
			var $resultsContent = $('#wps-audit-results-content');
			$resultsContent.empty();

			if (issues.length === 0) {
				$resultsContent.html('<p class="wps-audit-success">✓ No accessibility issues found!</p>');
			} else {
				var issuesByType = this.groupIssuesByType(issues);
				
				$.each(issuesByType, function(type, typeIssues) {
					var $section = $('<div class="wps-audit-issue-type"></div>');
					$section.append('<h4>' + this.getTypeLabel(type) + ' (' + typeIssues.length + ')</h4>');
					
					$.each(typeIssues, function(index, issue) {
						var $issue = this.renderIssue(issue);
						$section.append($issue);
					}.bind(this));
					
					$resultsContent.append($section);
				}.bind(this));
			}

			$('#wps-audit-results').show();
		},

		/**
		 * Group issues by type.
		 *
		 * @param {Array} issues Array of issues.
		 * @return {Object} Issues grouped by type.
		 */
		groupIssuesByType: function(issues) {
			var grouped = {};
			
			$.each(issues, function(index, issue) {
				if (!grouped[issue.type]) {
					grouped[issue.type] = [];
				}
				grouped[issue.type].push(issue);
			});
			
			return grouped;
		},

		/**
		 * Get human-readable label for issue type.
		 *
		 * @param {string} type Issue type.
		 * @return {string} Human-readable label.
		 */
		getTypeLabel: function(type) {
			var labels = {
				'alt_text': 'Image Alt Text',
				'aria_role': 'ARIA Roles',
				'keyboard_trap': 'Keyboard Navigation',
				'contrast': 'Color Contrast',
				'focus_order': 'Focus Indicators'
			};
			
			return labels[type] || type;
		},

		/**
		 * Render a single issue.
		 *
		 * @param {Object} issue Issue data.
		 * @return {jQuery} jQuery object for the issue element.
		 */
		renderIssue: function(issue) {
			var $issue = $('<div class="wps-audit-issue"></div>');
			$issue.addClass('severity-' + issue.severity);
			
			var $header = $('<div class="wps-audit-issue-header"></div>');
			$header.append('<span class="wps-audit-issue-title">' + this.escapeHtml(issue.message) + '</span>');
			$header.append('<span class="wps-audit-issue-severity severity-' + issue.severity + '">' + issue.severity + '</span>');
			
			$issue.append($header);
			
			if (issue.element) {
				$issue.append('<div class="wps-audit-issue-element"><code>' + this.escapeHtml(issue.element) + '</code></div>');
			}
			
			if (issue.suggestion) {
				$issue.append('<div class="wps-audit-issue-suggestion"><strong>Suggestion:</strong> ' + this.escapeHtml(issue.suggestion) + '</div>');
			}
			
			if (issue.auto_fix && issue.post_id > 0) {
				var $fixButton = $('<button type="button" class="button button-secondary wps-apply-fix" data-fix-type="' + issue.fix_action + '" data-post-id="' + issue.post_id + '">Apply Fix</button>');
				$issue.append($fixButton);
			}
			
			return $issue;
		},

		/**
		 * Apply a fix.
		 *
		 * @param {Event} e Click event.
		 */
		applyFix: function(e) {
			e.preventDefault();
			
			var $button = $(e.currentTarget);
			var fixType = $button.data('fix-type');
			var postId = $button.data('post-id');
			
			if (!confirm('Apply this fix? This will modify the content.')) {
				return;
			}
			
			$button.prop('disabled', true).text('Applying...');
			
			$.ajax({
				url: wpsA11yAudit.ajaxUrl,
				type: 'POST',
				data: {
					action: 'wps_apply_a11y_fix',
					nonce: wpsA11yAudit.nonce,
					fix_type: fixType,
					post_id: postId
				},
				success: function(response) {
					if (response.success) {
						$button.text('✓ Applied').addClass('button-primary');
						setTimeout(function() {
							$button.closest('.wps-audit-issue').fadeOut();
						}, 1000);
					} else {
						alert(response.data.message || 'Failed to apply fix.');
						$button.prop('disabled', false).text('Apply Fix');
					}
				},
				error: function() {
					alert('Error applying fix. Please try again.');
					$button.prop('disabled', false).text('Apply Fix');
				}
			});
		},

		/**
		 * Escape HTML to prevent XSS.
		 *
		 * @param {string} text Text to escape.
		 * @return {string} Escaped text.
		 */
		escapeHtml: function(text) {
			var map = {
				'&': '&amp;',
				'<': '&lt;',
				'>': '&gt;',
				'"': '&quot;',
				"'": '&#039;'
			};
			return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
		}
	};

	// Initialize on document ready
	$(document).ready(function() {
		WPSAccessibilityAudit.init();
	});

})(jQuery);
