/**
 * WPShadow Dashboard: Real-Time Updates and Full-Screen Mode
 * 
 * Features:
 * - Real-time gauge and kanban updates during scans
 * - Full-screen dashboard mode for office screens/screensaver
 * - Auto-refresh mechanism with configurable interval
 * - Live progress feedback
 */

(function($) {
	'use strict';

	const WPShadowDashboard = {
		
		// Configuration
		config: {
			updateInterval: 5000, // Update dashboard every 5 seconds during scan
			fullscreenRefreshInterval: 30000, // Refresh every 30 seconds in fullscreen
			enableFullscreen: true,
			enableAutoRefresh: true
		},

		/**
		 * Initialize real-time dashboard updates
		 */
		init: function() {
			this.bindEvents();
			this.checkFullscreenMode();
			this.initAutoRefresh();
		},

		/**
		 * Bind event listeners
		 */
		bindEvents: function() {
			const self = this;

			// Listen for scan start events (Quick Scan, Deep Scan)
			$(document).on('wpshadow:scan:start', function() {
				self.startRealtimeUpdates();
			});

			// Listen for scan complete events
			$(document).on('wpshadow:scan:complete', function() {
				self.stopRealtimeUpdates();
				self.refreshDashboard(); // One final refresh
			});

			// Fullscreen toggle button
			$(document).on('click', '#wpshadow-fullscreen-toggle', function(e) {
				e.preventDefault();
			self.toggleFullscreen();
		});
		
			// Auto-refresh toggle
			$(document).on('change', '#wpshadow-autorefresh-toggle', function() {
				const enabled = $(this).prop('checked');
				self.config.enableAutoRefresh = enabled;
				localStorage.setItem('wpshadow_autorefresh', enabled ? '1' : '0');
				
				if (enabled) {
					self.initAutoRefresh();
					$(this).closest('label').html('Auto-refresh: <span style="color: #2e7d32;">ON</span>');
				} else {
					self.stopAutoRefresh();
					$(this).closest('label').html('Auto-refresh: <span style="color: #999;">OFF</span>');
				}
			});

			// Refresh interval selector
			$(document).on('change', '#wpshadow-refresh-interval', function() {
				const interval = parseInt($(this).val());
				if (interval > 0) {
					self.config.fullscreenRefreshInterval = interval;
					localStorage.setItem('wpshadow_refresh_interval', interval);
					self.stopAutoRefresh();
					self.initAutoRefresh();
				}
			});
		},

		/**
		 * Start real-time updates during scan
		 */
		startRealtimeUpdates: function() {
			const self = this;
			
			// Stop existing interval if any
			if (this.realtimeInterval) {
				clearInterval(this.realtimeInterval);
			}

			// Update dashboard data every 500ms during scan for smooth progress
			this.realtimeInterval = setInterval(function() {
				self.updateDashboardData();
			}, 500);

			// Mark dashboard as updating
			$('#wpshadow-dashboard-wrapper').addClass('wpshadow-updating');
			$('#wpshadow-dashboard-status').html('🔄 Scan in progress...').show();
		},

		/**
		 * Stop real-time updates
		 */
		stopRealtimeUpdates: function() {
			if (this.realtimeInterval) {
				clearInterval(this.realtimeInterval);
				this.realtimeInterval = null;
			}

			$('#wpshadow-dashboard-wrapper').removeClass('wpshadow-updating');
		},

		/**
		 * Fetch and update dashboard data via AJAX
		 */
		updateDashboardData: function() {
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'wpshadow_get_dashboard_data',
					nonce: typeof wpshadow !== 'undefined' ? wpshadow.dashboard_nonce : ''
				},
				success: function(response) {
					if (response.success && response.data) {
						WPShadowDashboard.updateGauges(response.data);
						WPShadowDashboard.updateKanban(response.data);
						WPShadowDashboard.updateStatusText(response.data);
					}
				},
				error: function(xhr, status, error) {
					console.log('Dashboard update failed (this is normal if page is reloading)');
				}
			});
		},

		/**
		 * Update all gauge displays with fresh data
		 */
		updateGauges: function(data) {
			if (!data.gauges) return;

			// Update overall health gauge
			if (data.overall_health !== undefined) {
				const percent = data.overall_health;
				$('#wpshadow-overall-gauge circle:eq(2)').css({
					'stroke-dasharray': Math.round((percent / 100) * 534) + ' 534'
				});
				$('#wpshadow-overall-gauge text:eq(0)').text(percent + '%');
			}

			// Update category gauges
			$.each(data.gauges, function(category, gaugeData) {
				const $gauge = $(`[data-gauge-category="${category}"]`);
				if ($gauge.length) {
					const circleIndex = 2; // Third circle element
					const dasharray = Math.round((gaugeData.percent / 100) * 251) + ' 251';
					$gauge.find('circle').eq(circleIndex).css('stroke-dasharray', dasharray);
					$gauge.find('text').text(Math.round(gaugeData.percent) + '%');
					
					// Update finding count
					$gauge.closest('a').find('.wpshadow-finding-count').text(
						gaugeData.findings_count + ' issue' + (gaugeData.findings_count !== 1 ? 's' : '')
					);
				}
			});

			// Update findings count badge
			if (data.total_findings !== undefined) {
				$('.wpshadow-findings-badge').text(data.total_findings).toggleClass('wpshadow-has-findings', data.total_findings > 0);
			}
		},

		/**
		 * Update kanban board with fresh findings
		 */
		updateKanban: function(data) {
			if (!data.findings) return;

			// Rebuild kanban columns based on finding status
			const kanbanData = {
				detected: [],
				ignored: [],
				user_to_fix: [],
				fix_now: [],
				workflows: [],
				fixed: []
			};

			$.each(data.findings, function(i, finding) {
				const status = finding.status || 'detected';
				if (kanbanData[status] !== undefined) {
					kanbanData[status].push(finding);
				} else {
					kanbanData.detected.push(finding);
				}
			});

			// Update kanban column counts
			$.each(kanbanData, function(status, findings) {
				const $column = $(`[data-kanban-status="${status}"]`);
				if ($column.length) {
					$column.find('.wpshadow-column-count').text('(' + findings.length + ')');
				}
			});
		},

		/**
		 * Update status text with current findings summary
		 */
		updateStatusText: function(data) {
			if (data.total_findings === undefined) return;

			let statusText = '';
			if (data.critical_count > 0) {
				statusText = '⚠️ ' + data.critical_count + ' critical issue' + (data.critical_count !== 1 ? 's' : '');
			} else if (data.total_findings > 0) {
				statusText = '📋 ' + data.total_findings + ' issue' + (data.total_findings !== 1 ? 's' : '') + ' found';
			} else {
				statusText = '✅ All checks passed!';
			}

			$('#wpshadow-dashboard-status').html(statusText);
		},

		/**
		 * Full refresh of dashboard data
		 */
		refreshDashboard: function() {
			location.reload();
		},

		/**
		 * Initialize auto-refresh for fullscreen mode
		 */
		initAutoRefresh: function() {
			if (!this.config.enableAutoRefresh) return;

			const self = this;
			
			// Check if we're in fullscreen mode
			const isFullscreen = document.fullscreenElement || 
				document.webkitFullscreenElement || 
				document.mozFullScreenElement;

			if (!isFullscreen) return;

			// Auto-refresh interval for fullscreen
			if (this.autoRefreshInterval) {
				clearInterval(this.autoRefreshInterval);
			}

			this.autoRefreshInterval = setInterval(function() {
				self.updateDashboardData();
			}, this.config.fullscreenRefreshInterval);
		},

		/**
		 * Stop auto-refresh
		 */
		stopAutoRefresh: function() {
			if (this.autoRefreshInterval) {
				clearInterval(this.autoRefreshInterval);
				this.autoRefreshInterval = null;
			}
		},

		/**
	 * Toggle fullscreen mode
	 */
	toggleFullscreen: function() {
		const elem = document.getElementById('wpshadow-dashboard-wrapper');
		
		if (!elem) {
			return;
		}

		const isFullscreen = document.fullscreenElement || 
			document.webkitFullscreenElement || 
			document.mozFullScreenElement;

		if (!isFullscreen) {
			this.enterFullscreen(elem);
		} else {
			this.exitFullscreen();
		}
	},

	/**
	 * Enter fullscreen mode
	 */
	enterFullscreen: function(elem) {
		const self = this;

		// Request fullscreen
		const requestFullscreen = elem.requestFullscreen || 
			elem.webkitRequestFullscreen || 
			elem.mozRequestFullScreen;

		if (requestFullscreen) {
			requestFullscreen.call(elem).then(function() {
				// Hide WordPress admin chrome
				$('html').addClass('wpshadow-fullscreen-mode');
				$('#wpadminbar').hide();
				$('#wpshadow-dashboard-wrapper').css({
					'width': '100vw',
					'height': '100vh',
					'margin': '0',
					'padding': '20px',
					'box-sizing': 'border-box',
					'overflow': 'auto'
				});

				// Optimize for fullscreen display
				$('#wpshadow-dashboard-wrapper').addClass('wpshadow-fullscreen-optimized');

				// Enable auto-refresh in fullscreen
				self.initAutoRefresh();

				// Show exit instructions
				self.showFullscreenInstructions();

				// Update button text
				$('#wpshadow-fullscreen-toggle').html('Exit Full Screen (ESC)');

			}).catch(function(err) {
				if (window.WPShadowDesign && typeof window.WPShadowDesign.alert === 'function') {
					window.WPShadowDesign.alert('Fullscreen unavailable', 'Unable to enter fullscreen mode. Error: ' + err.message, 'error');
				} else {
					alert('Unable to enter fullscreen mode. Error: ' + err.message);
				}
			});
		} else {
			if (window.WPShadowDesign && typeof window.WPShadowDesign.alert === 'function') {
				window.WPShadowDesign.alert('Fullscreen unavailable', 'Fullscreen mode is not supported in your browser.', 'warning');
			} else {
				alert('Fullscreen mode is not supported in your browser.');
			}
		}
		
		// Listen for ESC key to exit
		$(document).on('keydown.wpshadow-fullscreen', function(e) {
			if (e.key === 'Escape') {
				self.exitFullscreen();
			}
		});
	},



		/**
		 * Exit fullscreen mode
		 */
		exitFullscreen: function() {
			const exitFullscreen = document.exitFullscreen || 
				document.webkitExitFullscreen || 
				document.mozCancelFullScreen;

			if (exitFullscreen) {
				exitFullscreen.call(document);
			}

			// Restore WordPress admin chrome
			$('html').removeClass('wpshadow-fullscreen-mode');
			$('#wpadminbar').show();
		$('#wpshadow-dashboard-wrapper').css({
			'width': '',
			'height': '',
				'overflow': ''
			});

			// Hide fullscreen instructions
			$('#wpshadow-fullscreen-instructions').remove();

			// Disable fullscreen auto-refresh
			this.stopAutoRefresh();

			// Update button text
			$('#wpshadow-fullscreen-toggle').html('Full Screen');

			// Unbind ESC handler
			$(document).off('keydown.wpshadow-fullscreen');
		},

		/**
		 * Show fullscreen mode instructions
		 */
		showFullscreenInstructions: function() {
			const instructions = $(`
				<div id="wpshadow-fullscreen-instructions" style="
					position: fixed;
					bottom: 20px;
					right: 20px;
					background: rgba(0,0,0,0.9);
					color: #fff;
					padding: 15px 20px;
					border-radius: 8px;
					font-size: 14px;
					z-index: 999999;
					max-width: 300px;
					line-height: 1.6;
				">
					<strong>Full Screen Mode</strong><br/>
					Press <strong>ESC</strong> to exit<br/>
					<span style="color: #aaa; font-size: 12px;">Dashboard updates every 30 seconds</span>
				</div>
			`);

			$('body').append(instructions);

			// Auto-hide after 5 seconds
			setTimeout(function() {
				instructions.fadeOut(500, function() { $(this).remove(); });
			}, 5000);
		},

		/**
		 * Check if we should enter fullscreen mode on page load
		 */
		checkFullscreenMode: function() {
			// Check for fullscreen parameter in URL or localStorage
			const urlParams = new URLSearchParams(window.location.search);
			const forceFullscreen = urlParams.get('wpshadow_fullscreen') === '1' || 
				localStorage.getItem('wpshadow_fullscreen_mode') === '1';

			if (forceFullscreen && this.config.enableFullscreen) {
				setTimeout(function() {
					WPShadowDashboard.toggleFullscreen();
				}, 500);
			}
		}
	};

	// Initialize on document ready
	$(document).ready(function() {
		// Dispatch scan start event when Quick Scan begins
		const originalFirstScanClick = $('#wpshadow-start-first-scan').off('click').on('click');
		$(document).on('wpshadow:quickscan:started', function() {
			$(document).trigger('wpshadow:scan:start');
		});

		// Initialize dashboard
		WPShadowDashboard.init();

		// Expose globally for other scripts
		window.WPShadowDashboard = WPShadowDashboard;
	});

})(jQuery);
