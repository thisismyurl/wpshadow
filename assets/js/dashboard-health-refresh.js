/**
 * WPShadow Dashboard Health Widget Auto-Refresh
 * 
 * Polls the health widget data every 30 seconds and updates the display.
 */
(function($) {
	'use strict';

	// Only run on dashboard tab
	if (!$('body').hasClass('wpshadow_tab_dashboard')) {
		return;
	}

	var healthWidget = $('#wpshadow_dashboard_health');
	
	if (healthWidget.length === 0) {
		return;
	}

	// Poll interval in milliseconds (30 seconds)
	var pollInterval = 30000;
	var pollTimer = null;

	/**
	 * Update the health widget with new data
	 */
	function updateHealthWidget(data) {
		// Update circular progress
		var progressCircle = healthWidget.find('circle[stroke-dasharray]');
		if (progressCircle.length) {
			progressCircle.attr('stroke-dashoffset', data.offset);
			progressCircle.attr('stroke', data.status_color);
		}

		// Update score number
		var scoreText = healthWidget.find('.wpshadow-health-score-text');
		if (scoreText.length) {
			scoreText.text(data.score);
		}

		// Update status label and color
		var statusLabel = healthWidget.find('.wpshadow-health-status-label');
		if (statusLabel.length) {
			statusLabel.text(data.status_label);
			statusLabel.css('color', data.status_color);
		}

		// Update memory usage
		var memoryUsage = healthWidget.find('[data-metric="memory-usage"]');
		if (memoryUsage.length) {
			memoryUsage.text(data.metrics.memory_usage + ' / ' + data.metrics.memory_limit);
		}
		var memoryBar = healthWidget.find('[data-metric="memory-bar"]');
		if (memoryBar.length) {
			memoryBar.css('width', data.metrics.memory_percent + '%');
			memoryBar.css('background', getMetricColor(data.metrics.memory_percent));
		}
		var memoryIndicator = healthWidget.find('[data-indicator="memory"]');
		if (memoryIndicator.length) {
			memoryIndicator.html(data.indicators.memory.icon);
			memoryIndicator.attr('title', data.indicators.memory.tooltip);
		}

		// Update disk space
		var diskUsage = healthWidget.find('[data-metric="disk-usage"]');
		if (diskUsage.length) {
			diskUsage.text(data.metrics.disk_used + ' / ' + data.metrics.disk_total);
		}
		if (data.metrics.disk_percent > 0) {
			var diskBar = healthWidget.find('[data-metric="disk-bar"]');
			if (diskBar.length) {
				diskBar.css('width', data.metrics.disk_percent + '%');
				diskBar.css('background', getMetricColor(data.metrics.disk_percent));
			}
		}
		var diskIndicator = healthWidget.find('[data-indicator="disk"]');
		if (diskIndicator.length) {
			diskIndicator.html(data.indicators.disk.icon);
			diskIndicator.attr('title', data.indicators.disk.tooltip);
		}

		// Update PHP version
		var phpVersion = healthWidget.find('[data-metric="php-version"]');
		if (phpVersion.length) {
			phpVersion.text(data.metrics.php_version);
		}
		var phpIndicator = healthWidget.find('[data-indicator="php-version"]');
		if (phpIndicator.length) {
			phpIndicator.html(data.indicators.php_version.icon);
			phpIndicator.attr('title', data.indicators.php_version.tooltip);
		}

		// Update WP version
		var wpVersion = healthWidget.find('[data-metric="wp-version"]');
		if (wpVersion.length) {
			wpVersion.text(data.metrics.wp_version);
		}
		var wpIndicator = healthWidget.find('[data-indicator="wp-version"]');
		if (wpIndicator.length) {
			wpIndicator.html(data.indicators.wp_version.icon);
			wpIndicator.attr('title', data.indicators.wp_version.tooltip);
		}

		// Update max upload
		var maxUpload = healthWidget.find('[data-metric="max-upload"]');
		if (maxUpload.length) {
			maxUpload.text(data.metrics.max_upload);
		}
		var uploadIndicator = healthWidget.find('[data-indicator="max-upload"]');
		if (uploadIndicator.length) {
			uploadIndicator.html(data.indicators.max_upload.icon);
			uploadIndicator.attr('title', data.indicators.max_upload.tooltip);
		}

		// Update max execution time
		var maxExecution = healthWidget.find('[data-metric="max-execution"]');
		if (maxExecution.length) {
			maxExecution.text(data.metrics.max_execution_time + 's');
		}
		var executionIndicator = healthWidget.find('[data-indicator="max-execution"]');
		if (executionIndicator.length) {
			executionIndicator.html(data.indicators.max_execution.icon);
			executionIndicator.attr('title', data.indicators.max_execution.tooltip);
		}

		// Update database size
		var dbSize = healthWidget.find('[data-metric="db-size"]');
		if (dbSize.length) {
			dbSize.text(data.metrics.db_size);
		}
		var dbIndicator = healthWidget.find('[data-indicator="db-size"]');
		if (dbIndicator.length) {
			dbIndicator.html(data.indicators.db_size.icon);
			dbIndicator.attr('title', data.indicators.db_size.tooltip);
		}

		// Update active plugins
		var activePlugins = healthWidget.find('[data-metric="active-plugins"]');
		if (activePlugins.length) {
			activePlugins.text(data.metrics.active_plugins);
		}
		var pluginsIndicator = healthWidget.find('[data-indicator="plugins"]');
		if (pluginsIndicator.length) {
			pluginsIndicator.html(data.indicators.plugins.icon);
			pluginsIndicator.attr('title', data.indicators.plugins.tooltip);
		}

		// Update active theme
		var activeTheme = healthWidget.find('[data-metric="active-theme"]');
		if (activeTheme.length) {
			activeTheme.text(data.metrics.active_theme);
		}
		var themeIndicator = healthWidget.find('[data-indicator="theme"]');
		if (themeIndicator.length) {
			themeIndicator.html(data.indicators.theme.icon);
			themeIndicator.attr('title', data.indicators.theme.tooltip);
		}
	}

	/**
	 * Get metric color based on percentage
	 */
	function getMetricColor(percent) {
		if (percent >= 90) {
			return '#d63638'; // Red
		} else if (percent >= 75) {
			return '#f0b849'; // Yellow
		} else if (percent >= 50) {
			return '#2271b1'; // Blue
		} else {
			return '#00a32a'; // Green
		}
	}

	/**
	 * Poll the health widget data
	 */
	function pollHealthWidget() {
		$.ajax({
			url: wpshadowHealth.ajaxUrl,
			type: 'POST',
			data: {
				action: 'wpshadow_refresh_health_widget',
				nonce: wpshadowHealth.nonce
			},
			success: function(response) {
				if (response.success && response.data) {
					updateHealthWidget(response.data);
				}
			},
			error: function() {
				// Silently fail - don't interrupt user experience
				console.log('WPShadow: Health widget refresh failed');
			}
		});
	}

	/**
	 * Start polling
	 */
	function startPolling() {
		// Initial poll after 30 seconds (let the page load first)
		pollTimer = setTimeout(function() {
			pollHealthWidget();
			// Set up recurring polls
			pollTimer = setInterval(pollHealthWidget, pollInterval);
		}, pollInterval);
	}

	/**
	 * Stop polling (cleanup)
	 */
	function stopPolling() {
		if (pollTimer) {
			clearTimeout(pollTimer);
			clearInterval(pollTimer);
			pollTimer = null;
		}
	}

	// Start polling when document is ready
	$(document).ready(function() {
		startPolling();
	});

	// Clean up on page unload
	$(window).on('beforeunload', function() {
		stopPolling();
	});

})(jQuery);
