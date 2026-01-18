/**
 * WPShadow Publishing Assistant - Classic Editor Integration
 *
 * Provides publishing assistant integration for WordPress classic editor.
 *
 * @package WPSHADOW
 */

(function($) {
	'use strict';

	const PublishingAssistantClassic = {
		isRunning: false,
		reviews: {},

		init: function() {
			if (!wpsPublishingAssistantClassic || !wpsPublishingAssistantClassic.reviewers) {
				return;
			}

			this.bindPublishButton();
			this.addReviewPanel();
		},

		bindPublishButton: function() {
			const self = this;

			// Hook into publish button
			$('#publish, #save-post').on('click', function(e) {
				// Only intercept if this is actually a publish action (not auto-save)
				if ('publish' !== $('#original_post_status').val() && '#publish' === this.id) {
					e.preventDefault();

					// Run reviews
					self.runReviews(() => {
						// If any errors, show the panel
						if (self.hasErrors()) {
							self.showReviewPanel();
						} else {
							// Otherwise proceed with publish
							$('#publish').off('click').trigger('click');
						}
					});

					return false;
				}
			});
		},

		addReviewPanel: function() {
			// Add review panel after the publish box
			const panel = $(`
				<div id="wps-publishing-assistant-panel" style="display: none; margin-top: 20px; padding: 15px; background: #fff; border: 1px solid #ccc; border-radius: 4px;">
					<h3 style="margin-top: 0;">${wp.i18n.__('Content Review', 'wpshadow')}</h3>
					<p style="font-size: 13px; color: #666;">${wp.i18n.__('Review your content before publishing', 'wpshadow')}</p>
					<div id="wps-review-results" style="margin-top: 15px;"></div>
					<button type="button" id="wps-run-review-btn" class="button button-primary" style="margin-top: 15px;">
						${wp.i18n.__('Run Review', 'wpshadow')}
					</button>
					<button type="button" id="wps-proceed-publish-btn" class="button" style="margin-left: 10px; display: none;">
						${wp.i18n.__('Proceed with Publish', 'wpshadow')}
					</button>
				</div>
			`);

			$('#postdivrich, #post-status-info').after(panel);

			const self = this;
			$('#wps-run-review-btn').on('click', function() {
				self.runReviews();
			});

			$('#wps-proceed-publish-btn').on('click', function() {
				$('#publish').off('click').trigger('click');
			});
		},

		showReviewPanel: function() {
			$('#wps-publishing-assistant-panel').slideDown();
		},

		hideReviewPanel: function() {
			$('#wps-publishing-assistant-panel').slideUp();
		},

		runReviews: function(callback) {
			if (this.isRunning) {
				return;
			}

			this.isRunning = true;
			this.reviews = {};

			const $resultsContainer = $('#wps-review-results');
			$resultsContainer.html(`<div style="text-align: center; padding: 20px;"><span class="spinner" style="float: none; margin: 0 10px;"></span> ${wp.i18n.__('Running content review...', 'wpshadow')}</div>`);
			this.showReviewPanel();

			$.ajax({
				url: wpsPublishingAssistantClassic.ajaxUrl,
				type: 'POST',
				data: {
					action: 'wpshadow_run_content_review',
					nonce: wpsPublishingAssistantClassic.nonce,
					post_id: $('#post_ID').val(),
				},
				success: (response) => {
					if (response.success && response.data.reviews) {
						this.reviews = response.data.reviews;
						this.renderReviews();
						this.showReviewPanel();

						if (callback) {
							callback();
						}
					}
				},
				error: () => {
					$resultsContainer.html(`
						<div class="notice notice-error" style="margin: 0;">
							<p>${wp.i18n.__('Error running content review.', 'wpshadow')}</p>
						</div>
					`);
				},
				complete: () => {
					this.isRunning = false;
				},
			});
		},

		renderReviews: function() {
			const $resultsContainer = $('#wps-review-results');
			$resultsContainer.empty();

			if (Object.keys(this.reviews).length === 0) {
				$resultsContainer.html(`<p style="color: #666;">${wp.i18n.__('No reviews to display.', 'wpshadow')}</p>`);
				return;
			}

			const reviewsHtml = Object.entries(this.reviews).map(([reviewerId, review]) => {
				const statusColor = this.getStatusColor(review.status);
				const statusIcon = this.getStatusIcon(review.status);

				let itemsHtml = '';
				if (review.items && review.items.length > 0) {
					itemsHtml = `
						<div style="margin-left: 32px; margin-bottom: 8px; font-size: 12px;">
							<details>
								<summary style="cursor: pointer; margin-bottom: 4px;">
									${wp.i18n.sprintf(
										wp.i18n._n(
											'%d issue',
											'%d issues',
											review.items.length,
											'wpshadow'
										),
										review.items.length
									)}
								</summary>
								<ul style="margin: 4px 0 0 16px; padding-left: 0;">
									${review.items.slice(0, 5)
										.map((item) => {
											if (item.url) {
												return `<li style="list-style: disc;"><a href="${item.url}" target="_blank">${item.url}</a> (${item.code})</li>`;
											}
											return `<li style="list-style: disc;">${item}</li>`;
										})
										.join('')}
									${review.items.length > 5 ? `<li style="list-style: disc; margin-top: 4px;">... and ${review.items.length - 5} more</li>` : ''}
								</ul>
							</details>
						</div>
					`;
				}

				return `
					<div style="border: 1px solid ${statusColor}; border-radius: 4px; padding: 12px; margin-bottom: 12px; background-color: ${statusColor}15;">
						<div style="display: flex; align-items: center; margin-bottom: 8px;">
							<span style="display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; border-radius: 50%; background-color: ${statusColor}; color: white; margin-right: 8px; font-size: 14px; font-weight: bold;">
								${statusIcon}
							</span>
							<strong style="color: ${statusColor};">${review.name}</strong>
						</div>
						<p style="margin: 0 0 8px 32px; font-size: 13px; color: #333;">
							${review.message}
						</p>
						${itemsHtml}
					</div>
				`;
			}).join('');

			$resultsContainer.html(reviewsHtml);

			// Show proceed button if there are warnings/errors
			if (this.hasErrors()) {
				$('#wps-proceed-publish-btn').show();
			} else {
				$('#wps-proceed-publish-btn').hide();
			}
		},

		hasErrors: function() {
			return Object.values(this.reviews).some(
				(review) => review.status === 'warning' || review.status === 'error'
			);
		},

		getStatusColor: function(status) {
			switch (status) {
				case 'success':
					return '#28a745';
				case 'warning':
					return '#ffc107';
				case 'error':
					return '#dc3545';
				default:
					return '#17a2b8';
			}
		},

		getStatusIcon: function(status) {
			switch (status) {
				case 'success':
					return '✓';
				case 'warning':
					return '⚠';
				case 'error':
					return '✕';
				default:
					return 'ℹ';
			}
		},
	};

	$(document).ready(function() {
		PublishingAssistantClassic.init();
	});
})(jQuery);
