/**
 * WPShadow Academy UI - JavaScript
 *
 * Handles client-side interactions for Academy.
 *
 * @since 0.6030.1925
 */

(function ($) {
	'use strict';

	const AcademyUI = {
		/**
		 * Initialize
		 */
		init() {
			this.bindEvents();
			this.initializeTracking();
		},

		/**
		 * Bind event handlers
		 */
		bindEvents() {
			// Dismiss learning suggestion
			$(document).on('click', '.dismiss-suggestion', this.dismissSuggestion.bind(this));

			// Category filter
			$(document).on('click', '.filter-category', this.filterArticles.bind(this));

			// Track article views
			$(document).on('click', 'a[href*="wpshadow.com/kb/"]', this.trackArticleView.bind(this));

			// Track video completions (when video ends)
			$(document).on('click', 'a[href*="wpshadow.com/academy/videos/"]', this.trackVideoStart.bind(this));
		},

		/**
		 * Initialize tracking
		 */
		initializeTracking() {
			// Listen for YouTube video completions (if embedded)
			if (window.YT && window.YT.Player) {
				this.initYouTubeTracking();
			}
		},

		/**
		 * Dismiss learning suggestion
		 *
		 * @param {Event} e Click event
		 */
		dismissSuggestion(e) {
			e.preventDefault();

			const button = $(e.currentTarget);
			const suggestionId = button.data('suggestion-id');

			$.ajax({
				url: wpShadowAcademy.ajaxUrl,
				type: 'POST',
				data: {
					action: 'wpshadow_dismiss_learning_suggestion',
					nonce: wpShadowAcademy.nonce,
					suggestion_id: suggestionId
				},
				beforeSend: () => {
					button.prop('disabled', true).text('Dismissing...');
				},
				success: (response) => {
					if (response.success) {
						button.closest('.learning-suggestion').fadeOut(300, function () {
							$(this).remove();
						});
					} else {
						window.WPShadowModal.alert({
							title: 'Error',
							message: response.data.message || 'Failed to dismiss suggestion',
							type: 'danger'
						});
						button.prop('disabled', false).text('Dismiss');
					}
				},
				error: () => {
					window.WPShadowModal.alert({
						title: 'Network Error',
						message: 'Network error. Please try again.',
						type: 'danger'
					});
					button.prop('disabled', false).text('Dismiss');
				}
			});
		},

		/**
		 * Filter articles by category
		 *
		 * @param {Event} e Click event
		 */
		filterArticles(e) {
			e.preventDefault();

			const button = $(e.currentTarget);
			const category = button.data('category');

			// Update active state
			$('.filter-category').removeClass('active');
			button.addClass('active');

			// Filter articles (client-side)
			if (category === 'all') {
				$('.article-card').show();
			} else {
				$('.article-card').hide();
				$(`.article-card[data-category="${category}"]`).show();
			}
		},

		/**
		 * Track article view
		 *
		 * @param {Event} e Click event
		 */
		trackArticleView(e) {
			const link = $(e.currentTarget);
			const articleId = this.extractArticleId(link.attr('href'));

			if (articleId) {
				$.post(wpShadowAcademy.ajaxUrl, {
					action: 'wpshadow_track_article_view',
					nonce: wpShadowAcademy.nonce,
					article_id: articleId
				});
			}
		},

		/**
		 * Track video start
		 *
		 * @param {Event} e Click event
		 */
		trackVideoStart(e) {
			const link = $(e.currentTarget);
			const videoId = this.extractVideoId(link.attr('href'));

			if (videoId) {
				// Store in session for completion tracking
				sessionStorage.setItem('wpshadow_current_video', videoId);
			}
		},

		/**
		 * Track video completion
		 *
		 * @param {string} videoId Video ID
		 */
		trackVideoCompletion(videoId) {
			$.post(wpShadowAcademy.ajaxUrl, {
				action: 'wpshadow_track_video_completion',
				nonce: wpShadowAcademy.nonce,
				video_id: videoId
			}, (response) => {
				if (response.success) {
					console.log('Video completion tracked:', videoId);

					// Show achievement notification if earned
					if (response.data.achievement) {
						this.showAchievementNotification(response.data.achievement);
					}
				}
			});
		},

		/**
		 * Initialize YouTube tracking
		 */
		initYouTubeTracking() {
			// Find all YouTube embeds
			$('iframe[src*="youtube.com"]').each((index, iframe) => {
				const player = new window.YT.Player(iframe, {
					events: {
						'onStateChange': (event) => {
							// Video ended
							if (event.data === window.YT.PlayerState.ENDED) {
								const videoId = sessionStorage.getItem('wpshadow_current_video');
								if (videoId) {
									this.trackVideoCompletion(videoId);
									sessionStorage.removeItem('wpshadow_current_video');
								}
							}
						}
					}
				});
			});
		},

		/**
		 * Extract article ID from URL
		 *
		 * @param {string} url Article URL
		 * @return {string|null} Article ID
		 */
		extractArticleId(url) {
			const match = url.match(/wpshadow\.com\/kb\/([^\/]+)/);
			return match ? match[1] : null;
		},

		/**
		 * Extract video ID from URL
		 *
		 * @param {string} url Video URL
		 * @return {string|null} Video ID
		 */
		extractVideoId(url) {
			const match = url.match(/wpshadow\.com\/academy\/videos\/([^\/]+)/);
			return match ? match[1] : null;
		},

		/**
		 * Show achievement notification
		 *
		 * @param {Object} achievement Achievement data
		 */
		showAchievementNotification(achievement) {
			const notification = $('<div class="wpshadow-achievement-notification">')
				.html(`
					<div class="achievement-content">
						<span class="achievement-icon">${this.escapeHtml(achievement.icon)}</span>
						<div class="achievement-text">
							<strong>${this.escapeHtml(achievement.title)}</strong>
							<p>${this.escapeHtml(achievement.description)}</p>
						</div>
					</div>
				`)
				.appendTo('body')
				.fadeIn(300);

			setTimeout(() => {
				notification.fadeOut(300, function () {
					$(this).remove();
				});
			}, 5000);
		},

		/**
		 * Escape HTML to prevent XSS
		 *
		 * @param {string} text Raw text
		 * @return {string} Escaped text
		 */
		escapeHtml(text) {
			const div = document.createElement('div');
			div.textContent = text;
			return div.innerHTML;
		}
	};

	// Initialize on DOM ready
	$(document).ready(() => {
		AcademyUI.init();
	});

})(jQuery);
