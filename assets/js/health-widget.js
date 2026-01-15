/**
 * WP Support Health Widget JavaScript
 *
 * Handles live updates and interactions for the health score widget.
 *
 * @package WPS\CoreSupport
 * @since 1.2601.75000
 */

(function($) {
	'use strict';

	/**
	 * Health Widget Controller
	 */
	const WPSHealthWidget = {

		/**
		 * Initialize the widget.
		 */
		init: function() {
			this.bindEvents();
			this.startAutoRefresh();
		},

		/**
		 * Bind event handlers.
		 */
		bindEvents: function() {
			// Refresh button (if added later)
			$(document).on('click', '.wps-health-refresh', this.refreshScores.bind(this));
		},

		/**
		 * Start auto-refresh timer.
		 */
		startAutoRefresh: function() {
			// Refresh scores every 5 minutes
			setInterval(this.refreshScores.bind(this), 300000);
		},

		/**
		 * Refresh health scores via AJAX.
		 */
		refreshScores: function() {
			const widget = $('.wps-health-widget');
			
			if (widget.length === 0) {
				return;
			}

			// Add loading state
			widget.addClass('wps-health-loading');

			$.ajax({
				url: wpsHealth.ajaxUrl,
				type: 'POST',
				data: {
					action: 'wps_get_health_score',
					nonce: wpsHealth.nonce
				},
				success: function(response) {
					if (response.success && response.data) {
						WPSHealthWidget.updateScores(response.data);
					}
				},
				error: function() {
					console.error('Failed to refresh health scores');
				},
				complete: function() {
					widget.removeClass('wps-health-loading');
				}
			});
		},

		/**
		 * Update displayed scores.
		 *
		 * @param {Object} data - Score data from server.
		 */
		updateScores: function(data) {
			// Update overall score
			$('.wps-health-overall .wps-health-score').text(data.overall);
			this.updateStatusClass('.wps-health-overall .wps-health-circle', data.overall);

			// Update security bar
			const securityBar = $('.wps-health-breakdown .wps-health-item:eq(0) .wps-health-bar');
			securityBar.css('width', data.security + '%');
			this.updateStatusClass(securityBar, data.security);
			$('.wps-health-breakdown .wps-health-item:eq(0) .wps-health-value').text(data.security + '/100');

			// Update performance bar
			const performanceBar = $('.wps-health-breakdown .wps-health-item:eq(1) .wps-health-bar');
			performanceBar.css('width', data.performance + '%');
			this.updateStatusClass(performanceBar, data.performance);
			$('.wps-health-breakdown .wps-health-item:eq(1) .wps-health-value').text(data.performance + '/100');

			// Animate changes
			this.animateScoreChange();
		},

		/**
		 * Update status class based on score.
		 *
		 * @param {string|jQuery} element - Element selector or jQuery object.
		 * @param {number} score - Score value.
		 */
		updateStatusClass: function(element, score) {
			const $el = $(element);
			$el.removeClass('wps-health-good wps-health-warning wps-health-critical');

			if (score >= 80) {
				$el.addClass('wps-health-good');
			} else if (score >= 60) {
				$el.addClass('wps-health-warning');
			} else {
				$el.addClass('wps-health-critical');
			}
		},

		/**
		 * Animate score changes.
		 */
		animateScoreChange: function() {
			$('.wps-health-circle').addClass('wps-health-pulse');
			setTimeout(function() {
				$('.wps-health-circle').removeClass('wps-health-pulse');
			}, 600);
		}
	};

	// Initialize on document ready
	$(document).ready(function() {
		WPSHealthWidget.init();
	});

	// Expose to global scope for external access
	window.WPSHealthWidget = WPSHealthWidget;

})(jQuery);
