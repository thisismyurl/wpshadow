/**
 * Health History Charts
 *
 * Renders interactive charts for health history visualization using Chart.js.
 *
 * @package WPShadow
 * @since   1.2602.0200
 */

(function($) {
	'use strict';

	const WPShadowHealthCharts = {
		charts: {},
		currentRange: 7,

		/**
		 * Initialize charts
		 */
		init: function() {
			this.loadChartJS();
			this.bindEvents();
			this.loadData(this.currentRange);
		},

		/**
		 * Load Chart.js library from CDN
		 */
		loadChartJS: function() {
			if (typeof Chart !== 'undefined') {
				return;
			}

			const script = document.createElement('script');
			script.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js';
			script.onload = () => {
				this.loadData(this.currentRange);
			};
			document.head.appendChild(script);
		},

		/**
		 * Bind UI events
		 */
		bindEvents: function() {
			// Date range buttons
			$('.date-range-btn').on('click', (e) => {
				const range = parseInt($(e.currentTarget).data('range'));
				$('.date-range-btn').removeClass('active');
				$(e.currentTarget).addClass('active');
				this.currentRange = range;
				this.loadData(range);
			});

			// Export chart button
			$('#wpshadow-export-chart').on('click', () => {
				this.exportChart();
			});

			// Share button
			$('#wpshadow-share-chart').on('click', () => {
				this.shareChart();
			});
		},

		/**
		 * Load health history data via AJAX
		 */
		loadData: function(dateRange) {
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'wpshadow_get_health_history',
					nonce: wpShadowHealthHistory.nonce,
					date_range: dateRange
				},
				success: (response) => {
					if (response.success && response.data) {
						this.renderCharts(response.data);
					}
				},
				error: (xhr, status, error) => {
					console.error('Failed to load health history:', error);
				}
			});
		},

		/**
		 * Render all charts
		 */
		renderCharts: function(data) {
			if (typeof Chart === 'undefined') {
				setTimeout(() => this.renderCharts(data), 100);
				return;
			}

			this.renderHealthTrendChart(data.chart_data);
			this.renderCategoryChart(data.chart_data);
			this.renderIssuesChart(data.chart_data);
		},

		/**
		 * Render overall health trend line chart
		 */
		renderHealthTrendChart: function(chartData) {
			const ctx = document.getElementById('wpshadow-health-trend-chart');
			if (!ctx) return;

			// Destroy existing chart
			if (this.charts.healthTrend) {
				this.charts.healthTrend.destroy();
			}

			this.charts.healthTrend = new Chart(ctx, {
				type: 'line',
				data: {
					labels: chartData.labels,
					datasets: [{
						label: 'Overall Health',
						data: chartData.datasets.overall_health,
						borderColor: '#2271b1',
						backgroundColor: 'rgba(34, 113, 177, 0.1)',
						fill: true,
						tension: 0.4,
						pointRadius: 4,
						pointHoverRadius: 6
					}]
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					plugins: {
						legend: {
							display: true,
							position: 'top'
						},
						tooltip: {
							mode: 'index',
							intersect: false,
							callbacks: {
								label: function(context) {
									return context.dataset.label + ': ' + context.parsed.y + '%';
								}
							}
						}
					},
					scales: {
						y: {
							beginAtZero: true,
							max: 100,
							ticks: {
								callback: function(value) {
									return value + '%';
								}
							}
						}
					}
				}
			});
		},

		/**
		 * Render category health multi-line chart
		 */
		renderCategoryChart: function(chartData) {
			const ctx = document.getElementById('wpshadow-category-chart');
			if (!ctx) return;

			if (this.charts.category) {
				this.charts.category.destroy();
			}

			this.charts.category = new Chart(ctx, {
				type: 'line',
				data: {
					labels: chartData.labels,
					datasets: [
						{
							label: 'Security',
							data: chartData.datasets.security,
							borderColor: '#d63638',
							backgroundColor: 'rgba(214, 54, 56, 0.1)',
							tension: 0.4
						},
						{
							label: 'Performance',
							data: chartData.datasets.performance,
							borderColor: '#00a32a',
							backgroundColor: 'rgba(0, 163, 42, 0.1)',
							tension: 0.4
						},
						{
							label: 'Quality',
							data: chartData.datasets.quality,
							borderColor: '#f0b849',
							backgroundColor: 'rgba(240, 184, 73, 0.1)',
							tension: 0.4
						}
					]
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					plugins: {
						legend: {
							display: true,
							position: 'bottom'
						}
					},
					scales: {
						y: {
							beginAtZero: true,
							max: 100,
							ticks: {
								callback: function(value) {
									return value + '%';
								}
							}
						}
					}
				}
			});
		},

		/**
		 * Render issues by severity stacked bar chart
		 */
		renderIssuesChart: function(chartData) {
			const ctx = document.getElementById('wpshadow-issues-chart');
			if (!ctx) return;

			if (this.charts.issues) {
				this.charts.issues.destroy();
			}

			this.charts.issues = new Chart(ctx, {
				type: 'bar',
				data: {
					labels: chartData.labels,
					datasets: [
						{
							label: 'Critical',
							data: chartData.issues.critical,
							backgroundColor: '#d63638'
						},
						{
							label: 'High',
							data: chartData.issues.high,
							backgroundColor: '#f0b849'
						},
						{
							label: 'Medium',
							data: chartData.issues.medium,
							backgroundColor: '#72aee6'
						},
						{
							label: 'Low',
							data: chartData.issues.low,
							backgroundColor: '#c3c4c7'
						}
					]
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					plugins: {
						legend: {
							display: true,
							position: 'bottom'
						}
					},
					scales: {
						x: {
							stacked: true
						},
						y: {
							stacked: true,
							beginAtZero: true
						}
					}
				}
			});
		},

		/**
		 * Export chart as PNG image
		 */
		exportChart: function() {
			if (!this.charts.healthTrend) return;

			const canvas = this.charts.healthTrend.canvas;
			const url = canvas.toDataURL('image/png');
			const link = document.createElement('a');
			link.download = 'wpshadow-health-history-' + Date.now() + '.png';
			link.href = url;
			link.click();
		},

		/**
		 * Share chart on social media
		 */
		shareChart: function() {
			const summary = this.getCurrentSummary();
			const text = `My site health improved ${summary.healthChange}% and fixed ${summary.issuesFixed} issues with @WPShadow! 🚀`;
			const url = 'https://wpshadow.com';
			
			// Twitter share
			const shareUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}&url=${encodeURIComponent(url)}`;
			window.open(shareUrl, '_blank', 'width=550,height=420');
		},

		/**
		 * Get current summary from metrics cards
		 */
		getCurrentSummary: function() {
			return {
				healthChange: parseInt($('.wpshadow-metric-card').eq(2).find('.metric-value').text()) || 0,
				issuesFixed: parseInt($('.wpshadow-metric-card').eq(1).find('.metric-value').text()) || 0
			};
		}
	};

	// Initialize on document ready
	$(document).ready(function() {
		if ($('.wpshadow-health-history').length) {
			WPShadowHealthCharts.init();
		}
	});

})(jQuery);
