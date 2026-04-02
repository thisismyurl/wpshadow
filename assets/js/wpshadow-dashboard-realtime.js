/**
 * WPShadow Dashboard: Real-Time Updates and Full-Screen Mode
 *
 * Features:
 * - Real-time gauge and findings updates during scans
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
			treatmentPromptCooldownMs: 30000
		},

		postScanTreatmentsInFlight: false,
		postScanTreatmentModalMounted: false,
		lastPostScanPromptAt: 0,
		diagnosticRelevanceModalMounted: false,
		diagnosticRelevanceCheckInFlight: false,

		/**
		 * Initialize real-time dashboard updates
		 */
		init: function () {
			this.bindEvents();
			this.checkFullscreenMode();
			this.initAutoRefresh();
			// Evaluate live dashboard state immediately on page load.
			this.updateDashboardData();
			this.maybeSuggestDiagnosticCleanup();
		},

		/**
		 * Get the dashboard nonce used by dashboard AJAX actions.
		 */
		getDashboardNonce: function () {
			return (typeof window.wpshadowDashboardData !== 'undefined' && window.wpshadowDashboardData.dashboard_nonce)
				? window.wpshadowDashboardData.dashboard_nonce
				: ((typeof window.wpshadow !== 'undefined' && window.wpshadow.dashboard_nonce)
					? window.wpshadow.dashboard_nonce
					: '');
		},

		/**
		 * Bind event listeners
		 */
		bindEvents: function () {
			const self = this;

			// Listen for scan start events (Deep Scan)
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
					self.maybeRunPostScanTreatments( 'scan_complete' );
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

			// Heartbeat-driven background diagnostics.
			let heartbeatBatchRunning = false;

			$( document ).on(
				'heartbeat-tick',
				function (event, heartbeatData) {
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

									if ( parseInt( result.executed || 0, 10 ) > 0 ) {
										self.maybeRunPostScanTreatments( 'heartbeat' );
									}

									// Pull fresh data so gauges reflect newly recorded pass/fail results.
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
			const dashboardNonce = this.getDashboardNonce();

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
							WPShadowDashboard.updateStatusText( response.data );
						}
					},
					error: function (xhr, status, error) {
						// Ignore transient refresh failures.
					}
				}
			);
		},

		/**
		 * Suggest disabling diagnostics that are likely irrelevant for this site.
		 */
		maybeSuggestDiagnosticCleanup: function () {
			if ( this.diagnosticRelevanceCheckInFlight ) {
				return;
			}

			if ( window.location.search.indexOf( 'page=wpshadow' ) < 0 ) {
				return;
			}

			const dashboardNonce = this.getDashboardNonce();
			if ( ! dashboardNonce ) {
				return;
			}

			this.diagnosticRelevanceCheckInFlight = true;

			$.ajax(
				{
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'wpshadow_get_diagnostic_applicability',
						nonce: dashboardNonce
					},
					success: function (response) {
						WPShadowDashboard.diagnosticRelevanceCheckInFlight = false;

						if ( ! response || ! response.success || ! response.data ) {
							return;
						}

						const groups = Array.isArray( response.data.groups ) ? response.data.groups : [];
						if ( 0 === groups.length ) {
							return;
						}

						const hash = String( response.data.hash || '' );
						const seenKey = hash ? 'wpshadow_diag_reco_seen_' + hash : '';
						if ( seenKey && window.localStorage && window.localStorage.getItem( seenKey ) ) {
							return;
						}

						WPShadowDashboard.showDiagnosticRelevanceModal( groups, seenKey );
					},
					error: function () {
						WPShadowDashboard.diagnosticRelevanceCheckInFlight = false;
					}
				}
			);
		},

		/**
		 * Reset one-time prompt state and open recommendations modal on demand.
		 */
		requestDiagnosticRelevanceReview: function () {
			const dashboardNonce = this.getDashboardNonce();
			const i18n = (window.wpshadowDashboardData && window.wpshadowDashboardData.diagnostic_relevance) ? window.wpshadowDashboardData.diagnostic_relevance : {};

			if ( ! dashboardNonce ) {
				return;
			}

			$.ajax(
				{
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'wpshadow_reset_diagnostic_relevance_prompt',
						nonce: dashboardNonce
					},
					success: function (response) {
						if ( ! response || ! response.success || ! response.data ) {
							$( '#wpshadow-dashboard-status' ).text( i18n.error_message || 'We could not update those diagnostics right now. Please try again.' );
							return;
						}

						const groups = Array.isArray( response.data.groups ) ? response.data.groups : [];
						if ( 0 === groups.length ) {
							$( '#wpshadow-dashboard-status' ).text( i18n.no_groups_message || 'No recommended groups were found for this site right now.' );
							return;
						}

						const hash = String( response.data.hash || '' );
						const seenKey = hash ? 'wpshadow_diag_reco_seen_' + hash : '';
						WPShadowDashboard.showDiagnosticRelevanceModal( groups, seenKey );
					},
					error: function () {
						$( '#wpshadow-dashboard-status' ).text( i18n.error_message || 'We could not update those diagnostics right now. Please try again.' );
					}
				}
			);
		},

		/**
		 * Render and show the relevance recommendations modal.
		 */
		showDiagnosticRelevanceModal: function (groups, seenKey) {
			if ( ! this.diagnosticRelevanceModalMounted ) {
				this.mountDiagnosticRelevanceModal();
			}

			const i18n = (window.wpshadowDashboardData && window.wpshadowDashboardData.diagnostic_relevance) ? window.wpshadowDashboardData.diagnostic_relevance : {};
			const $modal = $( '#wpshadow-diagnostic-relevance-modal' );
			const $list = $( '#wpshadow-diagnostic-relevance-list' );
			$list.empty();

			$( '#wpshadow-diagnostic-relevance-title' ).text( i18n.title || 'Recommended Diagnostic Cleanup' );
			$( '#wpshadow-diagnostic-relevance-subtitle' ).text( i18n.subtitle || 'We found checks that may not apply to this website. You can turn them off to keep scans focused and faster.' );
			$( '#wpshadow-diagnostic-relevance-heading' ).text( i18n.list_heading || 'Suggested diagnostic groups to manage' );

			$.each(
				groups,
				function (index, item) {
					const groupId = item && item.id ? String( item.id ) : '';
					if ( ! groupId ) {
						return;
					}

					const title = item && item.label ? String( item.label ) : groupId;
					const reason = item && item.reason ? String( item.reason ) : '';
					const count = parseInt( item && item.count ? item.count : 0, 10 );
					const enabledCount = parseInt( item && item.enabled_count ? item.enabled_count : 0, 10 );
					const disabledCount = parseInt( item && item.disabled_count ? item.disabled_count : 0, 10 );
					const classNames = Array.isArray( item && item.class_names ? item.class_names : [] ) ? item.class_names : [];

					const $row = $( '<label style="display:block;padding:10px 12px;border:1px solid #dbe3ec;border-radius:8px;margin-bottom:8px;background:#fff;cursor:pointer;"></label>' );
					const $check = $( '<input type="checkbox" class="wpshadow-diag-reco-check" checked="checked" style="margin-right:8px;" />' );
					$check.attr( 'data-group-id', groupId );
					$check.attr( 'data-class-names', classNames.join( '||' ) );
					const $title = $( '<strong style="color:#0f172a;"></strong>' ).text( title );
					const countText = count > 0 ? ' (' + count + ')' : '';
					const $count = $( '<span style="font-size:12px;color:#334155;margin-left:4px;"></span>' ).text( countText );
					const statusText = ( i18n.group_status || 'On: %1$d, Off: %2$d' )
						.replace( '%1$d', String( enabledCount ) )
						.replace( '%2$d', String( disabledCount ) );
					const $reason = $( '<div style="font-size:12px;color:#475569;margin-top:5px;padding-left:24px;"></div>' ).text( reason );
					const $status = $( '<div style="font-size:12px;color:#0f172a;margin-top:4px;padding-left:24px;font-weight:600;"></div>' ).text( statusText );
					$row.append( $check ).append( $title ).append( $reason );
					$row.append( $status );
					$title.append( $count );
					$list.append( $row );
				}
			);

			$( '#wpshadow-diagnostic-relevance-disable' ).text( i18n.disable_button || 'Turn Off Selected' ).off( 'click' ).on(
				'click',
				function (e) {
					e.preventDefault();

					const selected = [];
					const selectedGroups = [];
					$list.find( '.wpshadow-diag-reco-check:checked' ).each(
						function () {
							const groupId = String( $( this ).attr( 'data-group-id' ) || '' );
							const classNames = String( $( this ).attr( 'data-class-names' ) || '' )
								.split( '||' )
								.filter( function (name) {
									return name && name.length > 0;
								} );

							if (groupId) {
								selectedGroups.push( groupId );
							}

							$.each(
								classNames,
								function (idx, className) {
									selected.push( String( className ) );
								}
							);
						}
					);

					$modal.hide();

					if ( selected.length < 1 ) {
						if ( seenKey && window.localStorage ) {
							window.localStorage.setItem( seenKey, '1' );
						}
						return;
					}

					WPShadowDashboard.applyDiagnosticRecommendations( selected, selectedGroups, 'disable', seenKey );
				}
			);

			$( '#wpshadow-diagnostic-relevance-enable' ).text( i18n.enable_button || 'Turn On Selected' ).off( 'click' ).on(
				'click',
				function (e) {
					e.preventDefault();

					const selected = [];
					const selectedGroups = [];
					$list.find( '.wpshadow-diag-reco-check:checked' ).each(
						function () {
							const groupId = String( $( this ).attr( 'data-group-id' ) || '' );
							const classNames = String( $( this ).attr( 'data-class-names' ) || '' )
								.split( '||' )
								.filter( function (name) {
									return name && name.length > 0;
								} );

							if (groupId) {
								selectedGroups.push( groupId );
							}

							$.each(
								classNames,
								function (idx, className) {
									selected.push( String( className ) );
								}
							);
						}
					);

					$modal.hide();

					if ( selected.length < 1 ) {
						if ( seenKey && window.localStorage ) {
							window.localStorage.setItem( seenKey, '1' );
						}
						return;
					}

					WPShadowDashboard.applyDiagnosticRecommendations( selected, selectedGroups, 'enable', seenKey );
				}
			);

			$( '#wpshadow-diagnostic-relevance-cancel' ).text( i18n.cancel_button || 'Keep As Is' ).off( 'click' ).on(
				'click',
				function (e) {
					e.preventDefault();
					$modal.hide();
					if ( seenKey && window.localStorage ) {
						window.localStorage.setItem( seenKey, '1' );
					}
				}
			);

			$modal.show();
		},

		/**
		 * Persist selected recommendation disables.
		 */
		applyDiagnosticRecommendations: function (classNames, groupIds, applyMode, seenKey) {
			const dashboardNonce = this.getDashboardNonce();
			const i18n = (window.wpshadowDashboardData && window.wpshadowDashboardData.diagnostic_relevance) ? window.wpshadowDashboardData.diagnostic_relevance : {};

			$.ajax(
				{
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'wpshadow_apply_diagnostic_recommendations',
						nonce: dashboardNonce,
						class_names: classNames,
						group_ids: groupIds,
						apply_mode: applyMode
					},
					success: function (response) {
						if ( response && response.success ) {
							if ( seenKey && window.localStorage ) {
								window.localStorage.setItem( seenKey, '1' );
							}
							if ( 'enable' === applyMode ) {
								$( '#wpshadow-dashboard-status' ).text( i18n.turned_on_message || 'Selected diagnostics were turned on.' );
							} else {
								$( '#wpshadow-dashboard-status' ).text( i18n.turned_off_message || 'Selected diagnostics were turned off.' );
							}

							WPShadowDashboard.updateDashboardData();
							window.setTimeout(
								function () {
									window.location.reload();
								},
								300
							);
							return;
						}

						$( '#wpshadow-dashboard-status' ).text( i18n.error_message || 'We could not update those diagnostics right now. Please try again.' );
					},
					error: function () {
						$( '#wpshadow-dashboard-status' ).text( i18n.error_message || 'We could not update those diagnostics right now. Please try again.' );
					}
				}
			);
		},

		/**
		 * Mount diagnostic relevance modal markup.
		 */
		mountDiagnosticRelevanceModal: function () {
			const html = [
				'<div id="wpshadow-diagnostic-relevance-modal" style="display:none;position:fixed;inset:0;z-index:999998;background:rgba(15,23,42,.55);padding:24px;overflow:auto;">',
				'<div role="dialog" aria-modal="true" aria-labelledby="wpshadow-diagnostic-relevance-title" style="max-width:780px;margin:20px auto;background:#f8fafc;border-radius:10px;border:1px solid #cbd5e1;box-shadow:0 16px 40px rgba(2,6,23,.25);">',
				'<div style="padding:16px 18px;border-bottom:1px solid #e2e8f0;">',
				'<h2 id="wpshadow-diagnostic-relevance-title" style="margin:0;font-size:18px;line-height:1.3;color:#0f172a;"></h2>',
				'<p id="wpshadow-diagnostic-relevance-subtitle" style="margin:8px 0 0;font-size:13px;color:#334155;"></p>',
				'</div>',
				'<div style="padding:16px 18px;max-height:420px;overflow:auto;">',
				'<h3 id="wpshadow-diagnostic-relevance-heading" style="margin:0 0 10px;font-size:14px;color:#1e293b;"></h3>',
				'<div id="wpshadow-diagnostic-relevance-list"></div>',
				'</div>',
				'<div style="padding:14px 18px;border-top:1px solid #e2e8f0;display:flex;gap:8px;justify-content:flex-end;background:#fff;">',
				'<button id="wpshadow-diagnostic-relevance-cancel" class="button button-secondary"></button>',
				'<button id="wpshadow-diagnostic-relevance-enable" class="button"></button>',
				'<button id="wpshadow-diagnostic-relevance-disable" class="button button-primary"></button>',
				'</div>',
				'</div>',
				'</div>'
			].join( '' );

			$( 'body' ).append( html );
			this.diagnosticRelevanceModalMounted = true;
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
		 * Fetch available post-scan treatments and run auto-apply flow.
		 */
		maybeRunPostScanTreatments: function () {
			const now = Date.now();

			if ( this.postScanTreatmentsInFlight ) {
				return;
			}

			if ( now - this.lastPostScanPromptAt < this.config.treatmentPromptCooldownMs ) {
				return;
			}

			this.postScanTreatmentsInFlight = true;

			this.postScanTreatmentsRequest(
				'fetch',
				{},
				function (fetchData) {
					const safeTreatments = Array.isArray( fetchData && fetchData.safe ) ? fetchData.safe : [];
					const moderateTreatments = Array.isArray( fetchData && fetchData.moderate ) ? fetchData.moderate : [];
					const highTreatments = Array.isArray( fetchData && fetchData.high ) ? fetchData.high : [];
					const alwaysApproved = Array.isArray( fetchData && fetchData.always_approved ) ? fetchData.always_approved : [];

					if ( 0 === safeTreatments.length && 0 === moderateTreatments.length && 0 === highTreatments.length ) {
						WPShadowDashboard.postScanTreatmentsInFlight = false;
						WPShadowDashboard.lastPostScanPromptAt = now;
						return;
					}

					WPShadowDashboard.postScanTreatmentsRequest(
						'apply_safe',
						{},
						function () {
							const riskyCandidates = moderateTreatments.concat( highTreatments );
							const riskyTreatments = riskyCandidates;

							if ( riskyTreatments.length > 0 ) {
								WPShadowDashboard.showRiskyTreatmentsModal(
									riskyTreatments,
									alwaysApproved,
									function () {
										WPShadowDashboard.updateDashboardData();
										WPShadowDashboard.postScanTreatmentsInFlight = false;
										WPShadowDashboard.lastPostScanPromptAt = Date.now();
									}
								);
								return;
							}

							WPShadowDashboard.updateDashboardData();
							WPShadowDashboard.postScanTreatmentsInFlight = false;
							WPShadowDashboard.lastPostScanPromptAt = Date.now();
						},
						function () {
							WPShadowDashboard.postScanTreatmentsInFlight = false;
						}
					);
				},
				function () {
					WPShadowDashboard.postScanTreatmentsInFlight = false;
				}
			);
		},

		/**
		 * Execute post-scan treatment AJAX requests.
		 */
		postScanTreatmentsRequest: function (mode, extraData, onSuccess, onError) {
			const dashboardNonce =
				(typeof window.wpshadowDashboardData !== 'undefined' && window.wpshadowDashboardData.dashboard_nonce)
					? window.wpshadowDashboardData.dashboard_nonce
					: ((typeof window.wpshadow !== 'undefined' && window.wpshadow.dashboard_nonce)
						? window.wpshadow.dashboard_nonce
						: '');

			if ( ! dashboardNonce ) {
				onError();
				return;
			}

			const data = $.extend(
				{
					action: 'wpshadow_post_scan_treatments',
					nonce: dashboardNonce,
					mode: mode
				},
				extraData || {}
			);

			$.ajax(
				{
					url: ajaxurl,
					type: 'POST',
					data: data,
					success: function (response) {
						let payload = response;
						if ( typeof payload === 'string' ) {
							try {
								payload = JSON.parse( payload );
							} catch (e) {
								onError();
								return;
							}
						}

						if ( payload && payload.success && payload.data ) {
							onSuccess( payload.data );
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
		 * Build and display approval modal for moderate/high-risk treatments.
		 */
		showRiskyTreatmentsModal: function (riskyTreatments, alwaysApproved, onComplete) {
			if ( ! this.postScanTreatmentModalMounted ) {
				this.mountRiskyTreatmentsModal();
			}

			const $modal = $( '#wpshadow-post-scan-treatments-modal' );
			const $list = $( '#wpshadow-post-scan-treatments-list' );
			$list.empty();
			const alwaysApprovedMap = {};
			$.each(
				Array.isArray( alwaysApproved ) ? alwaysApproved : [],
				function (index, findingId) {
					alwaysApprovedMap[ String( findingId ) ] = true;
				}
			);

			$.each(
				riskyTreatments,
				function (index, treatment) {
					const findingId = treatment && treatment.finding_id ? String( treatment.finding_id ) : '';
					if ( ! findingId ) {
						return;
					}

					const title = treatment.title ? String( treatment.title ) : findingId;
					const riskLevel = treatment.risk_level ? String( treatment.risk_level ) : 'moderate';
					const description = treatment.description ? String( treatment.description ) : '';

					const $row = $( '<div class="wpshadow-post-scan-treatment-row" style="padding:12px;border:1px solid #e2e8f0;border-radius:8px;margin-bottom:8px;background:#fff;"></div>' );
					const $name = $( '<div style="font-weight:600;color:#0f172a;margin-bottom:4px;"></div>' ).text( title );
					const $meta = $( '<div style="font-size:12px;color:#475569;margin-bottom:6px;"></div>' ).text( 'Risk level: ' + riskLevel );
					const $desc = $( '<div style="font-size:12px;color:#334155;margin-bottom:8px;"></div>' ).text( description );
					const $applyWrap = $( '<label style="display:block;font-size:12px;color:#1f2937;margin-bottom:6px;"></label>' );
					const $apply = $( '<input type="checkbox" class="wpshadow-post-scan-apply" />' );
					$apply.attr( 'data-finding-id', findingId );
					$apply.attr( 'data-title', title );
					$apply.attr( 'data-risk-level', riskLevel );

					const $alwaysWrap = $( '<label style="display:block;font-size:12px;color:#475569;padding-left:18px;"></label>' );
					const $always = $( '<input type="checkbox" class="wpshadow-post-scan-always" disabled="disabled" />' );
					$always.attr( 'data-finding-id', findingId );

					if ( alwaysApprovedMap[ findingId ] ) {
						$apply.prop( 'checked', true );
						$always.prop( 'checked', true ).prop( 'disabled', false );
					}

					$applyWrap.append( $apply ).append( document.createTextNode( ' Apply this fix now' ) );
					$alwaysWrap.append( $always ).append( document.createTextNode( ' Always apply this fix after future scans' ) );

					$row.append( $name ).append( $meta );
					if ( description ) {
						$row.append( $desc );
					}
					$row.append( $applyWrap ).append( $alwaysWrap );
					$list.append( $row );
				}
			);

			$list.off( 'change', '.wpshadow-post-scan-apply' ).on(
				'change',
				'.wpshadow-post-scan-apply',
				function () {
					const findingId = String( $( this ).attr( 'data-finding-id' ) || '' );
					const $always = $list.find( '.wpshadow-post-scan-always[data-finding-id="' + findingId + '"]' );
					if ( $( this ).is( ':checked' ) ) {
						$always.prop( 'disabled', false );
					} else {
						$always.prop( 'checked', false ).prop( 'disabled', true );
					}
				}
			);

			$( '#wpshadow-post-scan-apply-selected' ).off( 'click' ).on(
				'click',
				function (e) {
					e.preventDefault();
					const selections = [];

					$list.find( '.wpshadow-post-scan-apply:checked' ).each(
						function () {
							const findingId = String( $( this ).attr( 'data-finding-id' ) || '' );
							if ( ! findingId ) {
								return;
							}

							const alwaysApply = $list.find( '.wpshadow-post-scan-always[data-finding-id="' + findingId + '"]' ).is( ':checked' );
							selections.push(
								{
									finding_id: findingId,
									always_apply: alwaysApply
								}
							);
						}
					);

					$modal.hide();

					if ( 0 === selections.length ) {
						onComplete();
						return;
					}

					WPShadowDashboard.applySelectedRiskyTreatments( selections, onComplete );
				}
			);

			$( '#wpshadow-post-scan-skip' ).off( 'click' ).on(
				'click',
				function (e) {
					e.preventDefault();
					$modal.hide();
					onComplete();
				}
			);

			$modal.show();
		},

		/**
		 * Apply selected risky treatments sequentially.
		 */
		applySelectedRiskyTreatments: function (selections, onComplete) {
			const results = {
				success: 0,
				failed: 0
			};

			const runNext = function (index) {
				if ( index >= selections.length ) {
					if ( results.failed > 0 ) {
						$( '#wpshadow-dashboard-status' ).html( 'Applied ' + results.success + ' selected fixes. ' + results.failed + ' failed.' );
					} else {
						$( '#wpshadow-dashboard-status' ).html( 'Applied ' + results.success + ' selected fixes.' );
					}

					onComplete();
					return;
				}

				WPShadowDashboard.postScanTreatmentsRequest(
					'apply_one',
					{
						finding_id: selections[ index ].finding_id,
						always_apply: selections[ index ].always_apply ? '1' : '0'
					},
					function () {
						results.success += 1;
						runNext( index + 1 );
					},
					function () {
						results.failed += 1;
						runNext( index + 1 );
					}
				);
			};

			runNext( 0 );
		},

		/**
		 * Mount post-scan treatment approval modal markup.
		 */
		mountRiskyTreatmentsModal: function () {
			const html = [
				'<div id="wpshadow-post-scan-treatments-modal" style="display:none;position:fixed;inset:0;z-index:999999;background:rgba(15,23,42,.55);padding:24px;overflow:auto;">',
				'<div role="dialog" aria-modal="true" aria-labelledby="wpshadow-post-scan-title" style="max-width:760px;margin:20px auto;background:#f8fafc;border-radius:10px;border:1px solid #cbd5e1;box-shadow:0 16px 40px rgba(2,6,23,.25);">',
				'<div style="padding:16px 18px;border-bottom:1px solid #e2e8f0;">',
				'<h2 id="wpshadow-post-scan-title" style="margin:0;font-size:18px;line-height:1.3;color:#0f172a;">Review Recommended Fixes</h2>',
				'<p style="margin:8px 0 0;font-size:13px;color:#334155;">We already applied low-risk fixes. Choose which higher-impact fixes to apply now.</p>',
				'</div>',
				'<div style="padding:16px 18px;max-height:420px;overflow:auto;">',
				'<div id="wpshadow-post-scan-treatments-list"></div>',
				'</div>',
				'<div style="padding:14px 18px;border-top:1px solid #e2e8f0;display:flex;gap:8px;justify-content:flex-end;background:#fff;">',
				'<button id="wpshadow-post-scan-skip" class="button button-secondary">Skip for Now</button>',
				'<button id="wpshadow-post-scan-apply-selected" class="button button-primary">Apply Selected</button>',
				'</div>',
				'</div>',
				'</div>'
			].join( '' );

			$( 'body' ).append( html );
			this.postScanTreatmentModalMounted = true;
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
			// Dispatch scan start event when first scan begins.
			$( document ).on(
				'click',
				'#wpshadow-start-first-scan',
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
