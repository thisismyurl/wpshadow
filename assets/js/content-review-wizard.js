/**
 * WPShadow Content Review Wizard
 *
 * Manages the modal wizard for reviewing content before publishing.
 * Handles wizard steps, diagnostics display, AI suggestions, and user preferences.
 *
 * @since 1.6034.0000
 */

(function ($) {
	'use strict';

	/**
	 * Content Review Wizard Class
	 */
	const ContentReviewWizard = function () {
		this.currentStep = 0;
		this.postId = 0;
		this.wizardData = null;
		this.currentFamily = null;
		this.init();
	};

	/**
	 * Initialize wizard
	 */
	ContentReviewWizard.prototype.init = function () {
		const self = this;

		// Review button click handler
		$(document).on('click', '.wpshadow-review-button', function () {
			self.postId = $(this).data('post-id');
			self.openWizard();
		});

		// Modal close handler
		$(document).on('click', '.wpshadow-review-modal-close, .wpshadow-review-modal-overlay', function () {
			self.closeWizard();
		});

		// Wizard navigation
		$(document).on('click', '.wpshadow-wizard-next', function () {
			self.nextStep();
		});

		$(document).on('click', '.wpshadow-wizard-prev', function () {
			self.prevStep();
		});

		// Hide tip button
		$(document).on('click', '.wpshadow-hide-tip-btn', function (e) {
			e.preventDefault();
			const tipId = $(this).data('tip-id');
			self.hideTip(tipId);
		});

		// Skip diagnostic button
		$(document).on('click', '.wpshadow-skip-diagnostic-btn', function (e) {
			e.preventDefault();
			const diagnosticSlug = $(this).data('diagnostic-slug');
			self.skipDiagnostic(diagnosticSlug);
		});

		// AI improvement button
		$(document).on('click', '.wpshadow-ai-improve-btn', function (e) {
			e.preventDefault();
			const aspect = $(this).data('aspect');
			self.requestAIImprovement(aspect);
		});

		// Generate report button
		$(document).on('click', '.wpshadow-generate-report-btn', function (e) {
			e.preventDefault();
			self.generateReport();
		});
	};

	/**
	 * Open the review wizard modal
	 */
	ContentReviewWizard.prototype.openWizard = function () {
		const self = this;

		// Show loading state
		this.showLoading('Loading content review...');

		// Fetch wizard data
		wp.util.sendJsonRequest({
			url: wpShadowReview.ajax_url,
			method: 'POST',
			data: {
				action: 'wpshadow_content_review_get_data',
				post_id: this.postId,
				nonce: wpShadowReview.nonce,
			},
		}).done(function (response) {
			if (response.success && response.data) {
				self.wizardData = response.data;
				self.currentStep = 0;
				self.renderWizardModal();
				self.hideLoading();
			}
		}).fail(function (response) {
			self.showError(response.data.message || 'Failed to load content review');
			self.hideLoading();
		});
	};

	/**
	 * Close the wizard modal
	 */
	ContentReviewWizard.prototype.closeWizard = function () {
		$('.wpshadow-review-modal').remove();
		$('.wpshadow-review-modal-overlay').remove();
	};

	/**
	 * Render the wizard modal
	 */
	ContentReviewWizard.prototype.renderWizardModal = function () {
		const html = this.buildWizardHTML();
		$('body').append(html);
		this.showStep(0);
	};

	/**
	 * Build wizard HTML
	 */
	ContentReviewWizard.prototype.buildWizardHTML = function () {
		const families = Object.keys(this.wizardData.diagnostics);
		const totalSteps = families.length + 1; // +1 for summary

		let html = '<div class="wpshadow-review-modal-overlay"></div>';
		html += '<div class="wpshadow-review-modal">';
		html += '  <div class="wpshadow-modal-header">';
		html += '    <h2>' + this.escapeHtml('Content Review: ' + this.wizardData.post.title) + '</h2>';
		html += '    <button class="wpshadow-review-modal-close" aria-label="Close">×</button>';
		html += '  </div>';
		html += '  <div class="wpshadow-modal-content">';

		// Introduction step
		html += this.buildIntroStep();

		// Family diagnostic steps
		families.forEach((family, index) => {
			html += this.buildFamilyStep(family, index);
		});

		// Summary step
		html += this.buildSummaryStep(families.length);

		html += '  </div>';
		html += '  <div class="wpshadow-modal-footer">';
		html += '    <button class="button wpshadow-wizard-prev" style="display: none;">← Previous</button>';
		html += '    <div class="wpshadow-step-indicator"></div>';
		html += '    <button class="button button-primary wpshadow-wizard-next">Next →</button>';
		html += '  </div>';
		html += '</div>';

		return html;
	};

	/**
	 * Build introduction step
	 */
	ContentReviewWizard.prototype.buildIntroStep = function () {
		const diagnosticCount = Object.keys(this.wizardData.diagnostics).reduce((sum, key) => {
			return sum + this.wizardData.diagnostics[key].length;
		}, 0);

		let html = '<div class="wpshadow-wizard-step" data-step="0">';
		html += '  <div class="wpshadow-step-intro">';
		html += '    <h3>Welcome to Content Review</h3>';
		html += '    <p>Let\'s review your content before publishing to ensure it\'s the best it can be.</p>';
		html += '    <div class="wpshadow-review-stats">';
		html += '      <div class="stat"><span class="number">' + Object.keys(this.wizardData.diagnostics).length + '</span><span class="label">Check Categories</span></div>';
		html += '      <div class="stat"><span class="number">' + diagnosticCount + '</span><span class="label">Checks to Review</span></div>';
		html += '    </div>';
		html += '    <p class="wpshadow-review-info">';
		html += '      <strong>What we\'ll check:</strong> SEO, accessibility, readability, content quality, and code standards.';
		html += '    </p>';

		if (this.wizardData.cloud_status.is_registered) {
			html += '    <p class="wpshadow-cloud-available">';
			html += '      💡 <strong>Cloud AI Available:</strong> Get AI suggestions for improving your content at each step.';
			html += '    </p>';
		}

		html += '  </div>';
		html += '</div>';

		return html;
	};

	/**
	 * Build family diagnostic step
	 */
	ContentReviewWizard.prototype.buildFamilyStep = function (family, index) {
		const diagnostics = this.wizardData.diagnostics[family] || [];
		const familyTitle = this.getFamilyTitle(family);

		let html = '<div class="wpshadow-wizard-step" data-step="' + (index + 1) + '" data-family="' + family + '">';
		html += '  <div class="wpshadow-step-content">';
		html += '    <h3>' + this.escapeHtml(familyTitle) + '</h3>';

		if (diagnostics.length === 0) {
			html += '    <div class="wpshadow-no-issues"><p>✓ No issues found in this category</p></div>';
		} else {
			html += '    <div class="wpshadow-diagnostics-list">';

			diagnostics.forEach((diagnostic) => {
				const issue = diagnostic.finding;
				const severity = diagnostic.severity;

				html += '      <div class="wpshadow-diagnostic-item wpshadow-severity-' + severity + '">';
				html += '        <div class="wpshadow-diagnostic-header">';
				html += '          <h4><span class="severity-badge">' + severity.toUpperCase() + '</span> ' + this.escapeHtml(issue.title || diagnostic.slug) + '</h4>';
				html += '        </div>';
				html += '        <div class="wpshadow-diagnostic-body">';
				html += '          <p>' + this.escapeHtml(issue.description || '') + '</p>';

				// Show KB article link if available
				if (this.wizardData.kb_articles[diagnostic.slug]) {
					const kb = this.wizardData.kb_articles[diagnostic.slug][0];
					html += '          <p class="wpshadow-kb-link">';
					html += '            <a href="' + kb.url + '" target="_blank" rel="noopener">Learn more →</a>';
					html += '          </p>';
				}

				// Show AI improvement suggestion if available
				if (this.wizardData.cloud_status.is_registered) {
					html += '          <p class="wpshadow-ai-section">';
					html += '            <button class="button button-secondary wpshadow-ai-improve-btn" data-aspect="' + family + '">';
					html += '              ✨ Get AI Suggestion';
					html += '            </button>';
					html += '          </p>';
				}

				// Hide/Skip options
				html += '          <p class="wpshadow-diagnostic-actions">';
				html += '            <a href="#" class="wpshadow-hide-tip-btn" data-tip-id="' + diagnostic.slug + '">Hide this tip</a>';
				html += '            <a href="#" class="wpshadow-skip-diagnostic-btn" data-diagnostic-slug="' + diagnostic.slug + '">Skip in future</a>';
				html += '          </p>';

				html += '        </div>';
				html += '      </div>';
			});

			html += '    </div>';
		}

		html += '  </div>';
		html += '</div>';

		return html;
	};

	/**
	 * Build summary step
	 */
	ContentReviewWizard.prototype.buildSummaryStep = function (stepIndex) {
		const totalIssues = Object.keys(this.wizardData.diagnostics).reduce((sum, key) => {
			return sum + this.wizardData.diagnostics[key].length;
		}, 0);

		let html = '<div class="wpshadow-wizard-step" data-step="' + (stepIndex + 1) + '">';
		html += '  <div class="wpshadow-step-content">';
		html += '    <h3>Review Summary</h3>';

		if (totalIssues === 0) {
			html += '    <div class="wpshadow-all-clear">';
			html += '      <p><strong style="font-size: 24px;">✓</strong></p>';
			html += '      <p><strong>Your content is looking great!</strong></p>';
			html += '      <p>No issues found across all categories. You\'re ready to publish.</p>';
			html += '    </div>';
		} else {
			html += '    <p>We found <strong>' + totalIssues + ' issue' + (totalIssues !== 1 ? 's' : '') + '</strong> to review:</p>';
			html += '    <div class="wpshadow-summary-stats">';

			Object.keys(this.wizardData.diagnostics).forEach((family) => {
				const count = this.wizardData.diagnostics[family].length;
				if (count > 0) {
					html += '      <div class="stat">';
					html += '        <span class="family-name">' + this.getFamilyTitle(family) + '</span>';
					html += '        <span class="issue-count">' + count + '</span>';
					html += '      </div>';
				}
			});

			html += '    </div>';
		}

		html += '    <div class="wpshadow-summary-actions">';
		html += '      <button class="button button-primary wpshadow-generate-report-btn">📊 Generate Full Report</button>';
		html += '    </div>';

		html += '  </div>';
		html += '</div>';

		return html;
	};

	/**
	 * Show a specific step
	 */
	ContentReviewWizard.prototype.showStep = function (stepIndex) {
		const self = this;
		const totalSteps = $('.wpshadow-wizard-step').length;

		// Hide all steps
		$('.wpshadow-wizard-step').hide();

		// Show current step
		$('[data-step="' + stepIndex + '"]').show();

		// Update step indicator
		this.updateStepIndicator(stepIndex, totalSteps);

		// Update navigation buttons
		const prevBtn = $('.wpshadow-wizard-prev');
		const nextBtn = $('.wpshadow-wizard-next');

		if (stepIndex === 0) {
			prevBtn.hide();
			nextBtn.text('Get Started →');
		} else if (stepIndex === totalSteps - 1) {
			prevBtn.show();
			nextBtn.text('Finish');
		} else {
			prevBtn.show();
			nextBtn.text('Next →');
		}
	};

	/**
	 * Move to next step
	 */
	ContentReviewWizard.prototype.nextStep = function () {
		const totalSteps = $('.wpshadow-wizard-step').length;
		if (this.currentStep < totalSteps - 1) {
			this.currentStep++;
			this.showStep(this.currentStep);
		} else {
			this.closeWizard();
		}
	};

	/**
	 * Move to previous step
	 */
	ContentReviewWizard.prototype.prevStep = function () {
		if (this.currentStep > 0) {
			this.currentStep--;
			this.showStep(this.currentStep);
		}
	};

	/**
	 * Update step indicator
	 */
	ContentReviewWizard.prototype.updateStepIndicator = function (current, total) {
		const indicator = $('.wpshadow-step-indicator');
		indicator.text((current + 1) + ' of ' + total);
	};

	/**
	 * Hide a tip
	 */
	ContentReviewWizard.prototype.hideTip = function (tipId) {
		const self = this;

		wp.util.sendJsonRequest({
			url: wpShadowReview.ajax_url,
			method: 'POST',
			data: {
				action: 'wpshadow_content_review_hide_tip',
				tip_id: tipId,
				nonce: wpShadowReview.nonce,
			},
		}).done(function (response) {
			if (response.success) {
				$('[data-tip-id="' + tipId + '"]').closest('.wpshadow-diagnostic-item').fadeOut();
			}
		});
	};

	/**
	 * Skip a diagnostic
	 */
	ContentReviewWizard.prototype.skipDiagnostic = function (diagnosticSlug) {
		const self = this;

		wp.util.sendJsonRequest({
			url: wpShadowReview.ajax_url,
			method: 'POST',
			data: {
				action: 'wpshadow_content_review_skip_diagnostic',
				diagnostic_slug: diagnosticSlug,
				nonce: wpShadowReview.nonce,
			},
		}).done(function (response) {
			if (response.success) {
				$('[data-diagnostic-slug="' + diagnosticSlug + '"]').closest('.wpshadow-diagnostic-item').fadeOut();
			}
		});
	};

	/**
	 * Request AI improvement
	 */
	ContentReviewWizard.prototype.requestAIImprovement = function (aspect) {
		const self = this;
		const btn = $('[data-aspect="' + aspect + '"]');

		btn.prop('disabled', true).text('✨ Getting suggestion...');

		wp.util.sendJsonRequest({
			url: wpShadowReview.ajax_url,
			method: 'POST',
			data: {
				action: 'wpshadow_content_review_ai_improvement',
				post_id: this.postId,
				aspect: aspect,
				nonce: wpShadowReview.nonce,
			},
		}).done(function (response) {
			if (response.success && response.data.improvements) {
				self.showAIImprovementModal(response.data.improvements, aspect);
				btn.prop('disabled', false).text('✨ Get AI Suggestion');
			}
		}).fail(function () {
			btn.prop('disabled', false).text('✨ Get AI Suggestion');
		});
	};

	/**
	 * Show AI improvement modal
	 */
	ContentReviewWizard.prototype.showAIImprovementModal = function (improvements, aspect) {
		let html = '<div class="wpshadow-ai-modal-overlay"></div>';
		html += '<div class="wpshadow-ai-modal">';
		html += '  <div class="wpshadow-modal-header">';
		html += '    <h3>AI Suggestions for ' + this.getFamilyTitle(aspect) + '</h3>';
		html += '    <button class="wpshadow-ai-modal-close" aria-label="Close">×</button>';
		html += '  </div>';
		html += '  <div class="wpshadow-modal-content">';

		if (Array.isArray(improvements)) {
			improvements.forEach((improvement, index) => {
				html += '    <div class="wpshadow-ai-suggestion">';
				html += '      <h4>' + this.escapeHtml(improvement.title || 'Suggestion ' + (index + 1)) + '</h4>';
				html += '      <p>' + this.escapeHtml(improvement.description || '') + '</p>';
				if (improvement.example) {
					html += '      <blockquote>' + this.escapeHtml(improvement.example) + '</blockquote>';
				}
				html += '    </div>';
			});
		}

		html += '  </div>';
		html += '  <div class="wpshadow-modal-footer">';
		html += '    <button class="button button-primary wpshadow-ai-modal-close">Got it</button>';
		html += '  </div>';
		html += '</div>';

		$('body').append(html);

		$('.wpshadow-ai-modal-close').on('click', function () {
			$('.wpshadow-ai-modal-overlay').remove();
			$('.wpshadow-ai-modal').remove();
		});

		$('.wpshadow-ai-modal-overlay').on('click', function () {
			$('.wpshadow-ai-modal-overlay').remove();
			$('.wpshadow-ai-modal').remove();
		});
	};

	/**
	 * Generate full report
	 */
	ContentReviewWizard.prototype.generateReport = function () {
		const self = this;

		self.showLoading('Generating report...');

		wp.util.sendJsonRequest({
			url: wpShadowReview.ajax_url,
			method: 'POST',
			data: {
				action: 'wpshadow_content_review_generate_report',
				post_id: this.postId,
				nonce: wpShadowReview.nonce,
			},
		}).done(function (response) {
			self.hideLoading();
			if (response.success && response.data.report) {
				self.showReport(response.data.report);
			}
		}).fail(function () {
			self.hideLoading();
			self.showError('Failed to generate report');
		});
	};

	/**
	 * Show full report
	 */
	ContentReviewWizard.prototype.showReport = function (report) {
		// This could be expanded to show detailed PDF or page report
		// For now, log to console and show message
		console.log('Content Report:', report);

		// Could open report page or trigger download
		alert('Report generated successfully! Check the WPShadow Reports menu for details.');
	};

	/**
	 * Show loading indicator
	 */
	ContentReviewWizard.prototype.showLoading = function (message) {
		message = message || 'Loading...';
		const html = '<div class="wpshadow-loading-overlay"><div class="wpshadow-loading-spinner"><p>' + message + '</p></div></div>';
		$('body').append(html);
	};

	/**
	 * Hide loading indicator
	 */
	ContentReviewWizard.prototype.hideLoading = function () {
		$('.wpshadow-loading-overlay').remove();
	};

	/**
	 * Show error message
	 */
	ContentReviewWizard.prototype.showError = function (message) {
		alert('Error: ' + message);
	};

	/**
	 * Get family title
	 */
	ContentReviewWizard.prototype.getFamilyTitle = function (family) {
		const titles = {
			'content': 'Content Quality',
			'seo': 'SEO & Search',
			'accessibility': 'Accessibility',
			'readability': 'Readability',
			'code-quality': 'Code Quality',
		};
		return titles[family] || this.capitalizeFirst(family);
	};

	/**
	 * Capitalize first letter
	 */
	ContentReviewWizard.prototype.capitalizeFirst = function (str) {
		return str.charAt(0).toUpperCase() + str.slice(1).replace(/-/g, ' ');
	};

	/**
	 * Escape HTML
	 */
	ContentReviewWizard.prototype.escapeHtml = function (text) {
		const div = document.createElement('div');
		div.textContent = text;
		return div.innerHTML;
	};

	// Initialize on document ready
	$(document).ready(function () {
		window.wpShadowContentReviewWizard = new ContentReviewWizard();
	});

})(jQuery);
