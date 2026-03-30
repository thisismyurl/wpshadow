/**
 * WPShadow Dashboard: Real-Time Updates and Full-Screen Mode
 *
 * Features:
 * - Real-time gauge and kanban updates during scans
 * - Full-screen dashboard mode for office screens/screensaver
 * - Auto-refresh mechanism with configurable interval
 * - Live progress feedback
 */

(function ($) {
	'use strict';

	const WPShadowDashboard = {

		// Configuration
		config: {
			updateInterval: 5000, // Update dashboard every 5 seconds during scan
			fullscreenRefreshInterval: 30000, // Refresh every 30 seconds in fullscreen
			enableFullscreen: true,
			enableAutoRefresh: true,
			bulkRunOfferThreshold: 100,
			bulkRunBatchSize: 15,
			bulkRunBatchBudgetMs: 2000
		},

		bulkRunOfferDismissed: false,
		bulkRunActive: false,
		bulkRunPromptMounted: false,
		bulkRunCountCheckInFlight: false,
		bulkRunPendingCount: null,

		/**
		 * Initialize real-time dashboard updates
		 */
		init: function () {
			this.bindEvents();
			this.checkFullscreenMode();
			this.initAutoRefresh();
			// Evaluate live dashboard state immediately on page load.
			this.updateDashboardData();
		},

		/**
		 * Bind event listeners
		 */
		bindEvents: function () {
			const self = this;

			// Listen for scan start events (Quick Scan, Deep Scan)
			$( document ).on(
				'wpshadow:scan:start',
				function () {
					self.startRealtimeUpdates();
				}
			);

			// Listen for scan complete events
			$( document ).on(
				'wpshadow:scan:complete',
				function () {
					self.stopRealtimeUpdates();
					self.refreshDashboard(); // One final refresh
				}
			);

			// Fullscreen toggle button
			$( document ).on(
				'click',
				'#wpshadow-fullscreen-toggle',
				function (e) {
					e.preventDefault();
					self.toggleFullscreen();
				}
			);

			// Auto-refresh toggle
			$( document ).on(
				'change',
				'#wpshadow-auto-refresh-toggle',
				function () {
					const enabled                 = $( this ).prop( 'checked' );
					self.config.enableAutoRefresh = enabled;
					localStorage.setItem( 'wpshadow_autorefresh', enabled ? '1' : '0' );

					if (enabled) {
						self.initAutoRefresh();
						$( this ).closest( 'label' ).html( 'Auto-refresh: <span style="color: #2e7d32;">ON</span>' );
					} else {
						self.stopAutoRefresh();
						$( this ).closest( 'label' ).html( 'Auto-refresh: <span style="color: #999;">OFF</span>' );
					}
				}
			);

			// Refresh interval selector
			$( document ).on(
				'change',
				'#wpshadow-refresh-interval',
				function () {
					const interval = parseInt( $( this ).val() );
					if (interval > 0) {
						self.config.fullscreenRefreshInterval = interval;
						localStorage.setItem( 'wpshadow_refresh_interval', interval );
						self.stopAutoRefresh();
						self.initAutoRefresh();
					}
				}
			);

			$( document ).on(
				'click',
				'#wpshadow-bulk-run-start',
				function (e) {
					e.preventDefault();
					self.startBulkRunPendingDiagnostics();
				}
			);

			$( document ).on(
				'click',
				'#wpshadow-bulk-run-dismiss',
				function (e) {
					e.preventDefault();
					self.bulkRunOfferDismissed = true;
					$( '#wpshadow-bulk-run-prompt' ).remove();
				}
			);

			// Heartbeat-driven background diagnostics.
			let heartbeatBatchRunning = false;

			$( document ).on(
				'heartbeat-tick',
				function (event, heartbeatData) {
					if ( heartbeatData && heartbeatData.wpshadow_guardian ) {
						const result = heartbeatData.wpshadow_guardian;
						// Use the same snapshot endpoint to repaint gauges/text from persisted states.
						self.updateDashboardData();
						return;
					}

					if ( heartbeatBatchRunning ) {
						return;
					}

					heartbeatBatchRunning = true;

					const dashboardNonce =
						(typeof window.wpshadowDashboardData !== 'undefined' && window.wpshadowDashboardData.dashboard_nonce)
							? window.wpshadowDashboardData.dashboard_nonce
							: ((typeof window.wpshadow !== 'undefined' && window.wpshadow.dashboard_nonce)
								? window.wpshadow.dashboard_nonce
								: '');

					$.ajax(
						{
							url: ajaxurl,
							type: 'POST',
							data: {
								action: 'wpshadow_heartbeat_diagnostics',
								nonce: dashboardNonce
							},
							success: function (response) {
								let payload = response;
								if ( typeof payload === 'string' ) {
									try {
										payload = JSON.parse( payload );
									} catch (e) {
										// Keep original payload for fallback handling below.
									}
								}

								if (payload && payload.success && payload.data) {
									const result = payload.data;

									if ( result.test_counts ) {
										self.updateGauges( { test_counts: result.test_counts } );
									}

									// Pull fresh snapshot so gauges reflect newly recorded pass/fail results.
									self.updateDashboardData();
								} else {
									// Ignore heartbeat batch failures silently; UI keeps working via periodic refresh.
								}
							},
							error: function () {
								// Ignore heartbeat request errors silently.
							},
							complete: function () {
								heartbeatBatchRunning = false;
							}
						}
					);
				}
			);

		},

		/**
		 * Start real-time updates during scan
		 */
		startRealtimeUpdates: function () {
			const self = this;

			// Stop existing interval if any
			if (this.realtimeInterval) {
				clearInterval( this.realtimeInterval );
			}

			// Update dashboard data every 500ms during scan for smooth progress
			this.realtimeInterval = setInterval(
				function () {
					self.updateDashboardData();
				},
				500
			);

			// Mark dashboard as updating
			$( '.wpshadow-dashboard' ).addClass( 'wpshadow-updating' );
			$( '#wpshadow-dashboard-status' ).html( '🔄 Scan in progress...' ).show();
		},

		/**
		 * Stop real-time updates
		 */
		stopRealtimeUpdates: function () {
			if (this.realtimeInterval) {
				clearInterval( this.realtimeInterval );
				this.realtimeInterval = null;
			}

			$( '.wpshadow-dashboard' ).removeClass( 'wpshadow-updating' );
		},

		/**
		 * Fetch and update dashboard data via AJAX
		 */
		updateDashboardData: function () {
			const dashboardNonce =
				(typeof window.wpshadowDashboardData !== 'undefined' && window.wpshadowDashboardData.dashboard_nonce)
					? window.wpshadowDashboardData.dashboard_nonce
					: ((typeof window.wpshadow !== 'undefined' && window.wpshadow.dashboard_nonce)
						? window.wpshadow.dashboard_nonce
						: '');

			$.ajax(
				{
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'wpshadow_get_dashboard_data',
						nonce: dashboardNonce
					},
					success: function (response) {
						if (response.success && response.data) {
							WPShadowDashboard.updateGauges( response.data );
							WPShadowDashboard.updateKanban( response.data );
							WPShadowDashboard.updateStatusText( response.data );
							WPShadowDashboard.maybeOfferBulkRun( response.data );
						}
					},
					error: function (xhr, status, error) {
						// Ignore transient refresh failures.
					}
				}
			);
		},

		/**
		 * Update all gauge displays with fresh data
		 */
		updateGauges: function (data) {
			if ( ! data) {
				return;
			}

			// Update test count displays with pass rates
			if (data.test_counts) {
				$.each(
					data.test_counts,
					function (category, countData) {
						const total = parseInt( countData.total || 0, 10 );
						const passed = parseInt( (countData.passed !== undefined ? countData.passed : countData.run) || 0, 10 );
						const unknown = parseInt( countData.unknown || 0, 10 );
						const passedLabel = unknown > 0
							? 'Passed: ' + passed + '/' + total + ' (Unknown: ' + unknown + ')'
							: 'Passed: ' + passed + '/' + total;
						$( `[data-test-category="${category}"]` ).text( passedLabel );
					}
				);
			}

			// Update overall gauge with pass rate percentage
			if (data.test_counts && data.test_counts.overall) {
				const overallTotal = parseInt( data.test_counts.overall.total || 0, 10 );
				if ( data.test_counts.overall.passed === undefined ) {
					return;
				}
				const overallPassed = parseInt( data.test_counts.overall.passed || 0, 10 );
				const overallPassRate = overallTotal > 0 ? Math.round( (overallPassed / overallTotal) * 100 ) : 0;
				const strokeDasharray = Math.round( (overallPassRate / 100) * 534 );
				
				$( '#wpshadow-overall-gauge circle:eq(2)' ).css(
					{
						'stroke-dasharray': strokeDasharray + ' 534'
					}
				);
				$( '#wpshadow-overall-gauge text:eq(0)' ).text( overallPassed );
				// Update percentage text (third text element is percentage/status)
				$( '#wpshadow-overall-gauge text:eq(2)' ).text( overallPassRate + '% Pass' );
			}

			// Update category gauges with pass rate percentages
			if (data.test_counts) {
				$.each(
					data.test_counts,
					function (category, countData) {
						if ( 'overall' === category ) {
							return; // Skip overall (handled above)
						}
						const $gauge = $( `[data-category="${category}"]` );
						if ($gauge.length) {
							const total = parseInt( countData.total || 0, 10 );
							if ( countData.passed === undefined ) {
								return;
							}
							const passed = parseInt( countData.passed || 0, 10 );
							const passRate = total > 0 ? Math.round( (passed / total) * 100 ) : 0;
							const strokeDasharray = Math.round( (passRate / 100) * 251 ) + ' 251';
							
							const circleIndex = 2; // Third circle element
							$gauge.find( 'circle' ).eq( circleIndex ).css( 'stroke-dasharray', strokeDasharray );
							$gauge.find( 'text' ).eq( 0 ).text( passRate + '%' );
						}
					}
				);
			}

			// Update findings count badge
			if (data.total_findings !== undefined) {
				$( '.wpshadow-findings-badge' ).text( data.total_findings ).toggleClass( 'wpshadow-has-findings', data.total_findings > 0 );
			}
		},

		/**
		 * Update kanban board with fresh findings
		 */
		updateKanban: function (data) {
			if ( ! data.findings) {
				return;
			}

			// Rebuild kanban columns based on finding status
			const kanbanData = {
				detected: [],
				ignored: [],
				user_to_fix: [],
				fix_now: [],
				workflows: [],
				fixed: []
			};

			$.each(
				data.findings,
				function (i, finding) {
					const status = finding.status || 'detected';
					if (kanbanData[status] !== undefined) {
						kanbanData[status].push( finding );
					} else {
						kanbanData.detected.push( finding );
					}
				}
			);

			// Update kanban column counts
			$.each(
				kanbanData,
				function (status, findings) {
					const $column = $( `[data-kanban-status="${status}"]` );
					if ($column.length) {
						$column.find( '.wpshadow-column-count' ).text( '(' + findings.length + ')' );
					}
				}
			);
		},

		/**
		 * Update status text with current findings summary
		 */
		updateStatusText: function (data) {
			if (data.total_findings === undefined) {
				return;
			}

			let statusText = '';
			if (data.critical_count > 0) {
				statusText = '⚠️ ' + data.critical_count + ' critical issue' + (data.critical_count !== 1 ? 's' : '');
			} else if (data.total_findings > 0) {
				statusText = '📋 ' + data.total_findings + ' issue' + (data.total_findings !== 1 ? 's' : '') + ' found';
			} else {
				statusText = '✅ All checks passed!';
			}

			$( '#wpshadow-dashboard-status' ).html( statusText );
		},

		/**
		 * Offer a one-click batched run when many diagnostics are unknown.
		 */
		maybeOfferBulkRun: function (data) {
			if ( this.bulkRunOfferDismissed || this.bulkRunActive ) {
				return;
			}

			const counts = data && data.test_counts ? data.test_counts : null;
			let unknown = 0;

			if ( counts && counts.overall && counts.overall.unknown !== undefined ) {
				unknown = parseInt( counts.overall.unknown || 0, 10 );
			} else if ( counts ) {
				$.each(
					counts,
					function (category, countData) {
						if ( 'overall' === category ) {
							return;
						}

						unknown += parseInt( countData && countData.unknown ? countData.unknown : 0, 10 );
					}
				);
			}

			if ( isNaN( unknown ) ) {
				unknown = 0;
			}

			if ( unknown <= this.config.bulkRunOfferThreshold ) {
				return;
			}

			if ( this.bulkRunPromptMounted && $( '#wpshadow-bulk-run-controls' ).is( ':hidden' ) ) {
				return;
			}

			if ( this.bulkRunPendingCount !== null ) {
				if ( this.bulkRunPendingCount <= this.config.bulkRunOfferThreshold ) {
					return;
				}

				if ( ! this.bulkRunPromptMounted ) {
					this.mountBulkRunPrompt();
				}

				$( '#wpshadow-bulk-run-summary' ).text(
					this.bulkRunPendingCount + ' pending diagnostics can be run now in safe batches. Run them now?'
				);
				$( '#wpshadow-bulk-run-prompt' ).show();
				return;
			}

			if ( this.bulkRunCountCheckInFlight ) {
				return;
			}

			this.bulkRunCountCheckInFlight = true;
			this.fetchPendingBulkRunCount(
				function (pendingCount) {
					const normalized = parseInt( pendingCount || 0, 10 );
					WPShadowDashboard.bulkRunPendingCount = isNaN( normalized ) ? 0 : normalized;
					WPShadowDashboard.bulkRunCountCheckInFlight = false;
					if ( WPShadowDashboard.bulkRunOfferDismissed || WPShadowDashboard.bulkRunActive ) {
						return;
					}

					if ( WPShadowDashboard.bulkRunPendingCount <= WPShadowDashboard.config.bulkRunOfferThreshold ) {
						return;
					}

					if ( ! WPShadowDashboard.bulkRunPromptMounted ) {
						WPShadowDashboard.mountBulkRunPrompt();
					}

					$( '#wpshadow-bulk-run-summary' ).text(
						WPShadowDashboard.bulkRunPendingCount + ' pending diagnostics can be run now in safe batches. Run them now?'
					);
					$( '#wpshadow-bulk-run-prompt' ).show();
				},
				function () {
					WPShadowDashboard.bulkRunCountCheckInFlight = false;
				}
			);
		},

		/**
		 * Fetch number of pending diagnostics that are actually executable.
		 */
		fetchPendingBulkRunCount: function (onSuccess, onError) {
			const dashboardNonce =
				(typeof window.wpshadowDashboardData !== 'undefined' && window.wpshadowDashboardData.dashboard_nonce)
					? window.wpshadowDashboardData.dashboard_nonce
					: ((typeof window.wpshadow !== 'undefined' && window.wpshadow.dashboard_nonce)
						? window.wpshadow.dashboard_nonce
						: '');

			$.ajax(
				{
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'wpshadow_run_pending_diagnostics',
						nonce: dashboardNonce,
						mode: 'count'
					},
					success: function (response) {
						if ( response && response.success && response.data ) {
							onSuccess( response.data.pending_total || 0 );
							return;
						}

						onError();
					},
					error: function () {
						onError();
					}
				}
			);

		},

		/**
		 * Render the bulk-run prompt and progress bar container.
		 */
		mountBulkRunPrompt: function () {
			const html = [
				'<div id="wpshadow-bulk-run-prompt" style="display:none;margin:12px 0;padding:12px 14px;border:1px solid #d0d9e2;background:#f7fbff;border-radius:8px;">',
				'<div id="wpshadow-bulk-run-summary" style="margin-bottom:10px;color:#1f2937;"></div>',
				'<div id="wpshadow-bulk-run-controls" style="display:flex;gap:8px;align-items:center;">',
				'<button id="wpshadow-bulk-run-start" class="button button-primary">Run All Pending Diagnostics</button>',
				'<button id="wpshadow-bulk-run-dismiss" class="button button-secondary">Not Now</button>',
				'</div>',
				'<div id="wpshadow-bulk-run-progress-wrap" style="display:none;margin-top:10px;">',
				'<div style="width:100%;height:12px;background:#e5edf5;border-radius:999px;overflow:hidden;">',
				'<div id="wpshadow-bulk-run-progress" style="height:12px;width:0%;background:#2271b1;transition:width .2s ease;"></div>',
				'</div>',
				'<div id="wpshadow-bulk-run-progress-label" style="margin-top:8px;color:#334155;font-size:12px;"></div>',
				'</div>',
				'</div>'
			].join( '' );

			const $status = $( '#wpshadow-dashboard-status' );
			if ( $status.length ) {
				$status.before( html );
			} else {
				$( '.wpshadow-dashboard' ).first().prepend( html );
			}

			this.bulkRunPromptMounted = true;
		},

		/**
		 * Start batched execution for pending diagnostics.
		 */
		startBulkRunPendingDiagnostics: function () {
			if ( this.bulkRunActive ) {
				return;
			}

			this.bulkRunPendingCount = null;

			this.bulkRunActive = true;
			$( '#wpshadow-bulk-run-controls button' ).prop( 'disabled', true );
			$( '#wpshadow-bulk-run-progress-wrap' ).show();
			$( '#wpshadow-bulk-run-progress' ).css( 'width', '0%' );
			$( '#wpshadow-bulk-run-progress-label' ).text( 'Starting batched run...' );

			this.runPendingDiagnosticsBatch( 'start' );
		},

		/**
		 * Execute one pending-diagnostics batch and continue until complete.
		 */
		runPendingDiagnosticsBatch: function (mode) {
			const self = this;
			const dashboardNonce =
				(typeof window.wpshadowDashboardData !== 'undefined' && window.wpshadowDashboardData.dashboard_nonce)
					? window.wpshadowDashboardData.dashboard_nonce
					: ((typeof window.wpshadow !== 'undefined' && window.wpshadow.dashboard_nonce)
						? window.wpshadow.dashboard_nonce
						: '');

			if ( ! dashboardNonce ) {
				self.bulkRunActive = false;
				$( '#wpshadow-bulk-run-progress-label' ).text( 'Security token missing. Please reload this page and try again.' );
				$( '#wpshadow-bulk-run-controls button' ).prop( 'disabled', false );
				return;
			}

			$.ajax(
				{
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'wpshadow_run_pending_diagnostics',
						nonce: dashboardNonce,
						_ajax_nonce: dashboardNonce,
						_wpnonce: dashboardNonce,
						mode: mode,
						batch_size: self.config.bulkRunBatchSize,
						budget_ms: self.config.bulkRunBatchBudgetMs
					},
					success: function (response) {
						let payload = response;
						if ( payload === '-1' ) {
							self.bulkRunActive = false;
							$( '#wpshadow-bulk-run-progress-label' ).text( 'Security check failed. Please reload the page and try again.' );
							$( '#wpshadow-bulk-run-controls button' ).prop( 'disabled', false );
							return;
						}

						if ( payload === '0' ) {
							self.bulkRunActive = false;
							$( '#wpshadow-bulk-run-progress-label' ).text( 'Bulk runner action is unavailable right now. Please reload and try again.' );
							$( '#wpshadow-bulk-run-controls button' ).prop( 'disabled', false );
							return;
						}

						if ( typeof payload === 'string' ) {
							try {
								payload = JSON.parse( payload );
							} catch (e) {
								const jsonStart = payload.lastIndexOf( '{"success"' );
								if ( jsonStart >= 0 ) {
									const candidate = payload.slice( jsonStart );
									try {
										payload = JSON.parse( candidate );
									} catch (ignored) {
										// Keep raw payload for error details below.
									}
								}
							}
						}

						if ( ! payload || ! payload.success || ! payload.data ) {
							let errorMessage = payload && payload.data && payload.data.message
								? payload.data.message
								: 'Bulk run failed. Please try again.';
							const details = payload && payload.data && payload.data.details
								? String( payload.data.details )
								: '';
							if ( details ) {
								errorMessage += ' (' + details + ')';
							}
							self.bulkRunActive = false;
							$( '#wpshadow-bulk-run-progress-label' ).text( errorMessage );
							$( '#wpshadow-bulk-run-controls button' ).prop( 'disabled', false );
							return;
						}

						const result = payload.data;
						const percent = parseInt( result.percent || 0, 10 );
						const processed = parseInt( result.processed || 0, 10 );
						const total = parseInt( result.total || 0, 10 );
						const remaining = parseInt( result.remaining || 0, 10 );

						$( '#wpshadow-bulk-run-progress' ).css( 'width', percent + '%' );
						$( '#wpshadow-bulk-run-progress-label' ).text(
							'Processed ' + processed + ' / ' + total + ' diagnostics. Remaining: ' + remaining + '.'
						);

						self.updateDashboardData();

						if ( result.complete ) {
							self.bulkRunActive = false;
							self.bulkRunOfferDismissed = true;
							self.bulkRunPendingCount = 0;
							$( '#wpshadow-bulk-run-progress' ).css( 'width', '100%' );
							$( '#wpshadow-bulk-run-summary' ).text( 'Bulk run completed for the current pending diagnostics queue.' );
							$( '#wpshadow-bulk-run-progress-label' ).text( 'Completed. All pending diagnostics in this run have been processed.' );
							$( '#wpshadow-bulk-run-controls' ).hide();
							return;
						}

						setTimeout(
							function () {
								self.runPendingDiagnosticsBatch( 'batch' );
							},
							250
						);
					},
					error: function (xhr) {
						let serverMessage = 'Bulk run request failed. Please try again.';
						const body = xhr && xhr.responseText ? xhr.responseText : '';
						if ( body ) {
							try {
								const parsed = JSON.parse( body );
								if ( parsed && parsed.data && parsed.data.message ) {
									serverMessage = parsed.data.message;
								}
							} catch (e) {
								// Leave fallback message; body may be HTML/PHP error text.
							}
						}

						self.bulkRunActive = false;
						$( '#wpshadow-bulk-run-progress-label' ).text( serverMessage );
						$( '#wpshadow-bulk-run-controls button' ).prop( 'disabled', false );
					}
				}
			);
		},

		/**
		 * Full refresh of dashboard data
		 */
		refreshDashboard: function () {
			this.updateDashboardData();
			$( document ).trigger( 'wpshadow_dashboard_refresh' );
		},

		/**
		 * Initialize auto-refresh for fullscreen mode
		 */
		initAutoRefresh: function () {
			if ( ! this.config.enableAutoRefresh) {
				return;
			}

			const self = this;

			// Check if we're in fullscreen mode
			const isFullscreen = document.fullscreenElement ||
				document.webkitFullscreenElement ||
				document.mozFullScreenElement;

			if ( ! isFullscreen) {
				return;
			}

			// Auto-refresh interval for fullscreen
			if (this.autoRefreshInterval) {
				clearInterval( this.autoRefreshInterval );
			}

			this.autoRefreshInterval = setInterval(
				function () {
					self.updateDashboardData();
				},
				this.config.fullscreenRefreshInterval
			);
		},

		/**
		 * Stop auto-refresh
		 */
		stopAutoRefresh: function () {
			if (this.autoRefreshInterval) {
				clearInterval( this.autoRefreshInterval );
				this.autoRefreshInterval = null;
			}
		},

		/**
		 * Toggle fullscreen mode
		 */
		toggleFullscreen: function () {
			const elem = document.querySelector( '.wpshadow-dashboard' ) ||
			document.getElementById( 'wpshadow-dashboard-wrapper' );

			if ( ! elem) {
				return;
			}

			const isFullscreen = document.fullscreenElement ||
			document.webkitFullscreenElement ||
			document.mozFullScreenElement;

			if ( ! isFullscreen) {
				this.enterFullscreen( elem );
			} else {
				this.exitFullscreen();
			}
		},

		/**
		 * Enter fullscreen mode
		 */
		enterFullscreen: function (elem) {
			const self = this;

			// Request fullscreen
			const requestFullscreen = elem.requestFullscreen ||
			elem.webkitRequestFullscreen ||
			elem.mozRequestFullScreen;

			if (requestFullscreen) {
				requestFullscreen.call( elem ).then(
					function () {
						// Hide WordPress admin chrome
						$( 'html' ).addClass( 'wpshadow-fullscreen-mode' );
						$( '#wpadminbar' ).hide();
						$( '.wpshadow-dashboard' ).css(
							{
								'width': '100vw',
								'height': '100vh',
								'margin': '0',
								'padding': '20px',
								'box-sizing': 'border-box',
								'overflow': 'auto'
							}
						);

						// Optimize for fullscreen display
						$( '.wpshadow-dashboard' ).addClass( 'wpshadow-fullscreen-optimized' );

						// Enable auto-refresh in fullscreen
						self.initAutoRefresh();

						// Show exit instructions
						self.showFullscreenInstructions();

						// Update button text
						$( '#wpshadow-fullscreen-toggle' ).html( 'Exit Full Screen (ESC)' );

					}
				).catch(
					function (err) {
						if (window.WPShadowDesign && typeof window.WPShadowDesign.alert === 'function') {
							window.WPShadowDesign.alert( 'Fullscreen unavailable', 'Unable to enter fullscreen mode. Error: ' + err.message, 'error' );
						} else {
							window.WPShadowModal.alert(
								{
									title: 'Fullscreen Unavailable',
									message: 'Unable to enter fullscreen mode. Error: ' + err.message,
									type: 'danger'
								}
							);
						}
					}
				);
			} else {
				if (window.WPShadowDesign && typeof window.WPShadowDesign.alert === 'function') {
					window.WPShadowDesign.alert( 'Fullscreen unavailable', 'Fullscreen mode is not supported in your browser.', 'warning' );
				} else {
					window.WPShadowModal.alert(
						{
							title: 'Fullscreen Unavailable',
							message: 'Fullscreen mode is not supported in your browser.',
							type: 'warning'
						}
					);
				}
			}

			// Listen for ESC key to exit
			$( document ).on(
				'keydown.wpshadow-fullscreen',
				function (e) {
					if (e.key === 'Escape') {
						self.exitFullscreen();
					}
				}
			);
		},

		/**
		 * Exit fullscreen mode
		 */
		exitFullscreen: function () {
			const exitFullscreen = document.exitFullscreen ||
				document.webkitExitFullscreen ||
				document.mozCancelFullScreen;

			if (exitFullscreen) {
				exitFullscreen.call( document );
			}

			// Restore WordPress admin chrome
			$( 'html' ).removeClass( 'wpshadow-fullscreen-mode' );
			$( '#wpadminbar' ).show();
			$( '.wpshadow-dashboard' ).css(
				{
					'width': 'auto',
					'height': 'auto',
					'margin': 'auto',
					'padding': 'auto',
					'box-sizing': 'border-box',
					'overflow': 'visible'
				}
			);

			// Remove fullscreen optimizations
			$( '.wpshadow-dashboard' ).removeClass( 'wpshadow-fullscreen-optimized' );

			// Update button text
			$( '#wpshadow-fullscreen-toggle' ).html( 'Full Screen' );

			// Clean up event listener
			$( document ).off( 'keydown.wpshadow-fullscreen' );
		},

		/**
		 * Show fullscreen instructions
		 */
		showFullscreenInstructions: function () {
			const instructions = $(
				'<div class="wpshadow-fullscreen-instructions" style="position: fixed; top: 80px; right: 20px; z-index: 999999; background: rgba(0, 0, 0, 0.85); color: #fff; padding: 12px 16px; border-radius: 8px; max-width: 300px; line-height: 1.6;">' +
				'<strong>Full Screen Mode</strong><br/>' +
				'Press <strong>ESC</strong> to exit<br/>' +
				'<span style="color: #aaa; font-size: 12px;">Dashboard updates every 30 seconds</span>' +
				'</div>'
			);

			$( 'body' ).append( instructions );

			// Auto-hide after 5 seconds
			setTimeout(
				function () {
					instructions.fadeOut(
						500,
						function () {
							$( this ).remove(); }
					);
				},
				5000
			);
		},

		/**
		 * Check if we should enter fullscreen mode on page load
		 */
		checkFullscreenMode: function () {
			// Check for fullscreen parameter in URL or localStorage
			const urlParams       = new URLSearchParams( window.location.search );
			const forceFullscreen = urlParams.get( 'wpshadow_fullscreen' ) === '1' ||
				localStorage.getItem( 'wpshadow_fullscreen_mode' ) === '1';

			if (forceFullscreen && this.config.enableFullscreen) {
				setTimeout(
					function () {
						WPShadowDashboard.toggleFullscreen();
					},
					500
				);
			}
		}
	};

	// Initialize on document ready
	$( document ).ready(
		function () {
			// Dispatch scan start event when Quick Scan begins.
			$( document ).on(
				'click',
				'#wpshadow-start-first-scan',
				function () {
					$( document ).trigger( 'wpshadow:scan:start' );
				}
			);
			$( document ).on(
				'wpshadow:quickscan:started',
				function () {
					$( document ).trigger( 'wpshadow:scan:start' );
				}
			);

			// Initialize dashboard
			WPShadowDashboard.init();

			// Expose globally for other scripts
			window.WPShadowDashboard = WPShadowDashboard;
		}
	);

})( jQuery );
