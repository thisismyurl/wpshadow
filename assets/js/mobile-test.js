/**
 * Mobile-Friendliness Test JavaScript
 *
 * @package WPShadow
 * @since 1.2601.75001
 */

(function($) {
	'use strict';

	var MobileTest = {
		init: function() {
			this.bindEvents();
		},

		bindEvents: function() {
			$('#wpshadow-run-mobile-test').on('click', this.runTest.bind(this));
		},

		runTest: function(e) {
			e.preventDefault();

			var $button = $(e.currentTarget);
			var $results = $('#wpshadow-test-results');
			var $loading = $('#wpshadow-test-loading');
			var $report = $('#wpshadow-test-report');
			var url = $('#wpshadow-test-url').val() || wpshadowMobileTest.strings.homeUrl || '';

			// Disable button and show loading state
			$button.prop('disabled', true).addClass('disabled');
			$results.show();
			$loading.show();
			$report.hide();

			// Run the test via AJAX
			$.ajax({
				url: wpshadowMobileTest.ajaxUrl,
				type: 'POST',
				data: {
					action: 'wpshadow_run_mobile_test',
					nonce: wpshadowMobileTest.nonce,
					url: url
				},
				success: function(response) {
					if (response.success) {
						MobileTest.displayResults(response.data);
					} else {
						MobileTest.displayError(response.data.message || wpshadowMobileTest.strings.error);
					}
				},
				error: function() {
					MobileTest.displayError(wpshadowMobileTest.strings.error);
				},
				complete: function() {
					$button.prop('disabled', false).removeClass('disabled');
					$loading.hide();
				}
			});
		},

		displayResults: function(data) {
			var $report = $('#wpshadow-test-report');
			var score = parseInt(data.score) || 0;

			// Update score
			this.updateScore(score);

			// Update status text
			var statusText = '';
			var statusClass = '';
			if (score >= 80) {
				statusText = 'Good - Your site is mobile-friendly';
				statusClass = 'good';
			} else if (score >= 50) {
				statusText = 'Needs Improvement';
				statusClass = 'warning';
			} else {
				statusText = 'Poor - Significant mobile issues found';
				statusClass = 'critical';
			}
			$('#score-status').text(statusText).attr('class', 'score-status ' + statusClass);

			// Display issues
			this.displayList('issues', data.issues || []);
			this.displayList('warnings', data.warnings || []);
			this.displayList('passes', data.passes || []);
			this.displayList('recommendations', data.recommendations || []);

			// Show report
			$report.show();
		},

		updateScore: function(score) {
			var circumference = 2 * Math.PI * 45; // radius = 45
			var offset = circumference - (score / 100) * circumference;
			
			$('#score-value').text(score);
			$('#score-fill').css('stroke-dashoffset', offset);

			// Update color based on score
			var color = '#46b450'; // green
			if (score < 80) {
				color = '#f0b849'; // yellow
			}
			if (score < 50) {
				color = '#dc3232'; // red
			}
			$('#score-fill').css('stroke', color);
		},

		displayList: function(type, items) {
			var $section = $('#test-' + type);
			var $list = $section.find('.' + type + '-list');

			$list.empty();

			if (items.length > 0) {
				items.forEach(function(item) {
					$list.append($('<li>').text(item));
				});
				$section.show();
			} else {
				$section.hide();
			}
		},

		displayError: function(message) {
			var $report = $('#wpshadow-test-report');
			$report.html(
				'<div class="notice notice-error"><p>' + 
				$('<div>').text(message).html() + 
				'</p></div>'
			).show();
		}
	};

	$(document).ready(function() {
		MobileTest.init();
	});

})(jQuery);
