/**
 * WPShadow Phase 4 Infrastructure
 *
 * Handles advanced reporting features: export, snapshots, trends, annotations, integrations.
 *
 * @since 1.603.0200
 */

(function($) {
	'use strict';

	const WPShadowPhase4 = {
		/**
		 * Initialize
		 */
		init: function() {
			this.bindExportButtons();
			this.bindSnapshotButtons();
			this.bindComparisonButtons();
			this.bindTrendButtons();
			this.bindAnnotationButtons();
			this.bindIntegrationButtons();
			this.bindAnalyticsButtons();
			this.initAutoSnapshot();
		},

		/**
		 * Export functionality
		 */
		bindExportButtons: function() {
			$(document).on('click', '.wpshadow-export-btn', function(e) {
				e.preventDefault();
				
				const $btn = $(this);
				const reportId = $btn.data('report-id');
				const format = $btn.data('format');
				const reportData = WPShadowPhase4.getCurrentReportData(reportId);
				
				WPShadowPhase4.exportReport(reportId, format, reportData, $btn);
			});
		},

		exportReport: function(reportId, format, data, $btn) {
			const originalText = $btn.text();
			$btn.prop('disabled', true).text('Exporting...');
			
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'wpshadow_export_report',
					nonce: wpShadowPhase4.nonces.export_report,
					report_id: reportId,
					format: format,
					data: JSON.stringify(data)
				},
				success: function(response) {
					if (response.success) {
						// Download the file
						window.location.href = response.data.download_url;
						WPShadowPhase4.showNotice('success', response.data.message);
					} else {
						WPShadowPhase4.showNotice('error', response.data.message);
					}
				},
				error: function() {
					WPShadowPhase4.showNotice('error', 'Export failed. Please try again.');
				},
				complete: function() {
					$btn.prop('disabled', false).text(originalText);
				}
			});
		},

		/**
		 * Snapshot functionality
		 */
		bindSnapshotButtons: function() {
			$(document).on('click', '.wpshadow-save-snapshot-btn', function(e) {
				e.preventDefault();
				
				const $btn = $(this);
				const reportId = $btn.data('report-id');
				const reportData = WPShadowPhase4.getCurrentReportData(reportId);
				
				WPShadowPhase4.saveSnapshot(reportId, reportData, $btn);
			});
		},

		saveSnapshot: function(reportId, data, $btn) {
			const originalText = $btn ? $btn.text() : '';
			if ($btn) {
				$btn.prop('disabled', true).text('Saving...');
			}
			
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'wpshadow_save_snapshot',
					nonce: wpShadowPhase4.nonces.save_snapshot,
					report_id: reportId,
					data: JSON.stringify(data),
					metadata: JSON.stringify({
						user_id: wpShadowPhase4.userId,
						site_url: wpShadowPhase4.siteUrl
					})
				},
				success: function(response) {
					if (response.success) {
						WPShadowPhase4.showNotice('success', response.data.message);
						// Refresh snapshot list if visible
						WPShadowPhase4.refreshSnapshotList(reportId);
					} else {
						WPShadowPhase4.showNotice('error', response.data.message);
					}
				},
				error: function() {
					WPShadowPhase4.showNotice('error', 'Snapshot save failed. Please try again.');
				},
				complete: function() {
					if ($btn) {
						$btn.prop('disabled', false).text(originalText);
					}
				}
			});
		},

		initAutoSnapshot: function() {
			// Auto-save snapshot when report finishes running
			$(document).on('wpshadow:report:complete', function(e, reportId, data) {
				WPShadowPhase4.saveSnapshot(reportId, data);
			});
		},

		/**
		 * Comparison functionality
		 */
		bindComparisonButtons: function() {
			$(document).on('click', '.wpshadow-compare-btn', function(e) {
				e.preventDefault();
				
				const snapshot1 = $('#snapshot-select-1').val();
				const snapshot2 = $('#snapshot-select-2').val();
				
				if (!snapshot1 || !snapshot2) {
					WPShadowPhase4.showNotice('error', 'Please select two snapshots to compare.');
					return;
				}
				
				WPShadowPhase4.compareSnapshots(snapshot1, snapshot2);
			});
		},

		compareSnapshots: function(snapshotId1, snapshotId2) {
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'wpshadow_compare_snapshots',
					nonce: wpShadowPhase4.nonces.compare_snapshots,
					snapshot_id_1: snapshotId1,
					snapshot_id_2: snapshotId2
				},
				success: function(response) {
					if (response.success) {
						WPShadowPhase4.displayComparison(response.data.comparison);
					} else {
						WPShadowPhase4.showNotice('error', response.data.message);
					}
				},
				error: function() {
					WPShadowPhase4.showNotice('error', 'Comparison failed. Please try again.');
				}
			});
		},

		displayComparison: function(comparison) {
			const $modal = $('#wpshadow-comparison-modal');
			const $content = $modal.find('.comparison-content');
			
			let html = '<div class="comparison-results">';
			html += '<div class="comparison-summary">';
			html += '<h3>Comparison Summary</h3>';
			html += '<div class="stat-row">';
			html += '<div class="stat"><strong>Change:</strong> ' + comparison.delta + ' issues</div>';
			html += '<div class="stat"><strong>New Issues:</strong> ' + comparison.new_issues.length + '</div>';
			html += '<div class="stat"><strong>Resolved:</strong> ' + comparison.resolved_issues.length + '</div>';
			html += '</div></div>';
			
			if (comparison.new_issues.length > 0) {
				html += '<div class="new-issues"><h4>New Issues Found</h4><ul>';
				comparison.new_issues.forEach(function(issue) {
					html += '<li class="severity-' + issue.severity + '">' + issue.title + '</li>';
				});
				html += '</ul></div>';
			}
			
			if (comparison.resolved_issues.length > 0) {
				html += '<div class="resolved-issues"><h4>Resolved Issues</h4><ul>';
				comparison.resolved_issues.forEach(function(issue) {
					html += '<li>' + issue.title + '</li>';
				});
				html += '</ul></div>';
			}
			
			html += '</div>';
			
			$content.html(html);
			if (window.WPShadowModal && typeof window.WPShadowModal.openStatic === 'function') {
				window.WPShadowModal.openStatic('wpshadow-comparison-modal', { returnFocus: document.activeElement });
			} else {
				$modal.addClass('wpshadow-modal-show');
			}
		},

		/**
		 * Trend analysis
		 */
		bindTrendButtons: function() {
			$(document).on('click', '.wpshadow-trend-btn', function(e) {
				e.preventDefault();
				
				const $btn = $(this);
				const reportId = $btn.data('report-id');
				const days = $('#trend-days').val() || 30;
				
				WPShadowPhase4.getTrendData(reportId, days);
			});
		},

		getTrendData: function(reportId, days) {
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'wpshadow_get_trend_data',
					nonce: wpShadowPhase4.nonces.get_trend_data,
					report_id: reportId,
					days: days
				},
				success: function(response) {
					if (response.success) {
						WPShadowPhase4.displayTrendChart(response.data.trend_data);
					} else {
						WPShadowPhase4.showNotice('error', response.data.message);
					}
				},
				error: function() {
					WPShadowPhase4.showNotice('error', 'Failed to retrieve trend data.');
				}
			});
		},

		displayTrendChart: function(trendData) {
			// This would integrate with Chart.js or similar
			const $container = $('#wpshadow-trend-chart');
			
			if (trendData.trend === 'insufficient_data') {
				$container.html('<p class="notice">Insufficient data for trend analysis. Run reports over time to see trends.</p>');
				return;
			}
			
			let trendClass = 'trend-' + trendData.trend;
			let trendIcon = trendData.trend === 'improving' ? '↓' : (trendData.trend === 'declining' ? '↑' : '→');
			let trendText = trendData.trend.charAt(0).toUpperCase() + trendData.trend.slice(1);
			
			let html = '<div class="trend-summary ' + trendClass + '">';
			html += '<div class="trend-indicator">' + trendIcon + '</div>';
			html += '<div class="trend-status">' + trendText + '</div>';
			html += '<div class="trend-stats">';
			html += '<span>Start: ' + trendData.start_count + ' issues</span>';
			html += '<span>Current: ' + trendData.end_count + ' issues</span>';
			html += '<span>Change: ' + trendData.change_count + ' (' + trendData.percentage_change + '%)</span>';
			html += '</div></div>';
			
			$container.html(html);
		},

		/**
		 * Annotations
		 */
		bindAnnotationButtons: function() {
			$(document).on('click', '.wpshadow-add-note-btn', function(e) {
				e.preventDefault();
				
				const $btn = $(this);
				const reportId = $btn.data('report-id');
				const findingId = $btn.data('finding-id');
				
				WPShadowPhase4.showAnnotationForm(reportId, findingId);
			});
			
			$(document).on('submit', '#wpshadow-annotation-form', function(e) {
				e.preventDefault();
				
				const $form = $(this);
				const reportId = $form.find('[name="report_id"]').val();
				const findingId = $form.find('[name="finding_id"]').val();
				const text = $form.find('[name="annotation_text"]').val();
				const actionTaken = $form.find('[name="action_taken"]').val();
				
				WPShadowPhase4.addAnnotation(reportId, findingId, text, actionTaken);
			});
		},

		showAnnotationForm: function(reportId, findingId) {
			const $modal = $('#wpshadow-annotation-modal');
			$modal.find('[name="report_id"]').val(reportId);
			$modal.find('[name="finding_id"]').val(findingId);
			$modal.find('[name="annotation_text"]').val('');
			$modal.fadeIn();
		},

		addAnnotation: function(reportId, findingId, text, actionTaken) {
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'wpshadow_add_annotation',
					nonce: wpShadowPhase4.nonces.add_annotation,
					report_id: reportId,
					finding_id: findingId,
					text: text,
					action_taken: actionTaken
				},
				success: function(response) {
					if (response.success) {
						WPShadowPhase4.showNotice('success', response.data.message);
						$('#wpshadow-annotation-modal').fadeOut();
						// Refresh annotations display
						WPShadowPhase4.loadAnnotations(reportId, findingId);
					} else {
						WPShadowPhase4.showNotice('error', response.data.message);
					}
				},
				error: function() {
					WPShadowPhase4.showNotice('error', 'Failed to add annotation.');
				}
			});
		},

		/**
		 * Integrations
		 */
		bindIntegrationButtons: function() {
			$(document).on('click', '.wpshadow-send-integration-btn', function(e) {
				e.preventDefault();
				
				const $btn = $(this);
				const service = $btn.data('service');
				const reportId = $btn.data('report-id');
				const url = $btn.data('url');
				const reportData = WPShadowPhase4.getCurrentReportData(reportId);
				
				WPShadowPhase4.sendIntegration(service, reportId, url, reportData, $btn);
			});
		},

		sendIntegration: function(service, reportId, url, data, $btn) {
			const originalText = $btn.text();
			$btn.prop('disabled', true).text('Sending...');
			
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'wpshadow_send_integration',
					nonce: wpShadowPhase4.nonces.send_integration,
					service: service,
					report_id: reportId,
					url: url,
					data: JSON.stringify(data)
				},
				success: function(response) {
					if (response.success) {
						WPShadowPhase4.showNotice('success', response.data.message);
					} else {
						WPShadowPhase4.showNotice('error', response.data.message);
					}
				},
				error: function() {
					WPShadowPhase4.showNotice('error', 'Integration failed. Please try again.');
				},
				complete: function() {
					$btn.prop('disabled', false).text(originalText);
				}
			});
		},

		/**
		 * Analytics
		 */
		bindAnalyticsButtons: function() {
			$(document).on('click', '.wpshadow-calculate-analytics-btn', function(e) {
				e.preventDefault();
				
				const $btn = $(this);
				const type = $btn.data('type');
				const reportId = $btn.data('report-id');
				const reportData = WPShadowPhase4.getCurrentReportData(reportId);
				
				WPShadowPhase4.calculateAnalytics(type, reportData, $btn);
			});
		},

		calculateAnalytics: function(type, data, $btn) {
			const originalText = $btn ? $btn.text() : '';
			if ($btn) {
				$btn.prop('disabled', true).text('Calculating...');
			}
			
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'wpshadow_calculate_analytics',
					nonce: wpShadowPhase4.nonces.calculate_analytics,
					type: type,
					data: JSON.stringify(data)
				},
				success: function(response) {
					if (response.success) {
						WPShadowPhase4.displayAnalytics(type, response.data.result);
					} else {
						WPShadowPhase4.showNotice('error', response.data.message);
					}
				},
				error: function() {
					WPShadowPhase4.showNotice('error', 'Analytics calculation failed.');
				},
				complete: function() {
					if ($btn) {
						$btn.prop('disabled', false).text(originalText);
					}
				}
			});
		},

		displayAnalytics: function(type, result) {
			const $container = $('#wpshadow-analytics-display');
			let html = '';
			
			switch (type) {
				case 'roi':
					html = WPShadowPhase4.formatROI(result);
					break;
				case 'executive_summary':
					html = WPShadowPhase4.formatExecutiveSummary(result);
					break;
				case 'regression':
					html = WPShadowPhase4.formatRegressions(result);
					break;
				case 'what_if':
					html = WPShadowPhase4.formatWhatIf(result);
					break;
				case 'benchmark':
					html = WPShadowPhase4.formatBenchmark(result);
					break;
			}
			
			$container.html(html).fadeIn();
		},

		formatROI: function(roi) {
			let html = '<div class="roi-display">';
			html += '<h3>ROI Analysis</h3>';
			html += '<div class="roi-metrics">';
			html += '<div class="metric"><span>Time Saved:</span><strong>' + roi.time_saved_hours + ' hours</strong></div>';
			html += '<div class="metric"><span>Labor Cost Saved:</span><strong>$' + roi.labor_cost_saved.toLocaleString() + '</strong></div>';
			html += '<div class="metric"><span>Revenue Protected:</span><strong>$' + roi.revenue_protected.toLocaleString() + '</strong></div>';
			html += '<div class="metric total"><span>Total Value:</span><strong>$' + roi.total_value.toLocaleString() + '</strong></div>';
			html += '</div></div>';
			return html;
		},

		formatExecutiveSummary: function(summary) {
			let html = '<div class="executive-summary">';
			html += '<h3>Executive Summary</h3>';
			html += '<div class="summary-grid">';
			html += '<div class="summary-stat critical"><span>' + summary.critical_issues + '</span>Critical</div>';
			html += '<div class="summary-stat high"><span>' + summary.high_issues + '</span>High</div>';
			html += '<div class="summary-stat medium"><span>' + summary.medium_issues + '</span>Medium</div>';
			html += '<div class="summary-stat low"><span>' + summary.low_issues + '</span>Low</div>';
			html += '</div>';
			
			if (summary.priority_actions.length > 0) {
				html += '<div class="priority-actions"><h4>Priority Actions</h4><ol>';
				summary.priority_actions.forEach(function(action) {
					html += '<li class="severity-' + action.severity + '">' + action.title + '</li>';
				});
				html += '</ol></div>';
			}
			
			html += '</div>';
			return html;
		},

		formatBenchmark: function(benchmark) {
			let html = '<div class="benchmark-display">';
			html += '<h3>Industry Benchmark Comparison</h3>';
			html += '<div class="benchmark-rating rating-' + benchmark.rating + '">';
			html += '<div class="rating-badge">' + benchmark.rating.replace('_', ' ').toUpperCase() + '</div>';
			html += '<p>' + benchmark.message + '</p>';
			html += '<div class="percentile">Top ' + benchmark.percentile + '%</div>';
			html += '</div>';
			html += '<div class="benchmark-stats">';
			html += '<div class="stat"><span>Your Site:</span><strong>' + benchmark.current_count + ' issues</strong></div>';
			html += '<div class="stat"><span>Excellent:</span><strong>' + benchmark.benchmark.excellent + ' or fewer</strong></div>';
			html += '<div class="stat"><span>Good:</span><strong>' + benchmark.benchmark.good + ' or fewer</strong></div>';
			html += '<div class="stat"><span>Average:</span><strong>' + benchmark.benchmark.average + '</strong></div>';
			html += '</div></div>';
			return html;
		},

		formatWhatIf: function(whatIf) {
			let html = '<div class="what-if-display">';
			html += '<h3>What-If Scenario</h3>';
			html += '<div class="scenario-comparison">';
			html += '<div class="scenario-current"><span>Current:</span><strong>' + whatIf.current_count + ' issues</strong></div>';
			html += '<div class="scenario-arrow">→</div>';
			html += '<div class="scenario-projected"><span>After Fixes:</span><strong>' + whatIf.projected_remaining + ' issues</strong></div>';
			html += '</div>';
			html += '<div class="improvement-stat">Improvement: <strong>' + whatIf.improvement_percentage + '%</strong></div>';
			html += '<p>Applying ' + whatIf.fixes_applicable + ' fixes out of ' + whatIf.fixes_to_apply + ' selected.</p>';
			html += '</div>';
			return html;
		},

		formatRegressions: function(regressions) {
			let html = '<div class="regression-display">';
			html += '<h3>Regression Detection</h3>';
			
			if (!regressions.detected) {
				html += '<p class="no-regressions">✓ No significant regressions detected.</p>';
			} else {
				html += '<p class="regressions-found">⚠ Found ' + regressions.count + ' regression(s):</p>';
				html += '<ul class="regression-list">';
				regressions.regressions.forEach(function(reg) {
					html += '<li>';
					html += '<strong>' + reg.date + '</strong>: ';
					html += 'Issues increased from ' + reg.previous_count + ' to ' + reg.current_count;
					html += ' (+' + reg.increase + ', ' + reg.percentage + '%)';
					html += '</li>';
				});
				html += '</ul>';
			}
			
			html += '</div>';
			return html;
		},

		/**
		 * Utilities
		 */
		getCurrentReportData: function(reportId) {
			// This would extract current report data from the page
			// For now, returning a placeholder structure
			return {
				report_id: reportId,
				findings: window.wpShadowReportData || []
			};
		},

		refreshSnapshotList: function(reportId) {
			// Refresh the snapshot list UI
			$(document).trigger('wpshadow:snapshots:refresh', [reportId]);
		},

		loadAnnotations: function(reportId, findingId) {
			// Load and display annotations
			$(document).trigger('wpshadow:annotations:refresh', [reportId, findingId]);
		},

		showNotice: function(type, message) {
			const $notice = $('<div class="wpshadow-notice notice-' + type + '"></div>').text(message);
			$('.wpshadow-notices').append($notice);
			
			setTimeout(function() {
				$notice.fadeOut(function() {
					$(this).remove();
				});
			}, 5000);
		}
	};

	// Initialize when document ready
	$(document).ready(function() {
		if (typeof wpShadowPhase4 !== 'undefined') {
			WPShadowPhase4.init();
		}
	});

	// Expose globally
	window.WPShadowPhase4 = WPShadowPhase4;

})(jQuery);
