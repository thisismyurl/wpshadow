/**
 * WPShadow Dashboard Engine
 * 
 * Minimal, focused JavaScript for admin dashboard
 * Philosophy: Vanilla JS, REST API driven, accessibility-first
 * 
 * @package WPShadow
 */

(function() {
	'use strict';

	const WPShadowUI = {
		/**
		 * Initialize all dashboard features
		 */
		init: function() {
			this.initSearchFilter();
			this.initFilterButtons();
			this.initDiagnosticActions();
			this.initDetailPageActions();
			this.initRunAll();
		},

		initSearchFilter: function() {
			const searchInput = document.getElementById('wps-search-diagnostics');
			if (!searchInput) return;

			let debounceTimer;
			searchInput.addEventListener('input', (e) => {
				clearTimeout(debounceTimer);
				debounceTimer = setTimeout(() => {
					this.filterTable();
				}, 200);
			});
		},

		/**
		 * Filter button handlers (Status, Family, Confidence, Core)
		 */
		initFilterButtons: function() {
			const filters = [
				'wps-filter-result',
				'wps-filter-family',
				'wps-filter-confidence',
				'wps-filter-core'
			];

			filters.forEach(filterId => {
				const el = document.getElementById(filterId);
				if (el) {
					el.addEventListener('change', () => this.filterTable());
				}
			});

			const clearBtn = document.getElementById('wps-filter-clear');
			if (clearBtn) {
				clearBtn.addEventListener('click', () => this.clearFilters());
			}
		},

		/**
		 * Apply all active filters
		 */
		filterTable: function() {
			const searchText = (document.getElementById('wps-search-diagnostics')?.value || '').toLowerCase();
			const resultFilter = document.getElementById('wps-filter-result')?.value || 'all';
			const familyFilter = document.getElementById('wps-filter-family')?.value || 'all';
			const confidenceFilter = document.getElementById('wps-filter-confidence')?.value || 'all';
			const coreOnlyChecked = document.getElementById('wps-filter-core')?.checked || false;

			const rows = document.querySelectorAll('[data-diagnostic-row]');
			let visibleCount = 0;

			rows.forEach((row) => {
				const name = (row.getAttribute('data-name') || '').toLowerCase();
				const result = row.getAttribute('data-result') || 'unknown';
				const family = row.getAttribute('data-family') || '';
				const confidence = row.getAttribute('data-confidence') || 'standard';
				const isCore = row.getAttribute('data-core') === 'yes';

				const matchesSearch = !searchText || name.includes(searchText);
				const matchesResult = resultFilter === 'all' || result === resultFilter;
				const matchesFamily = familyFilter === 'all' || family === familyFilter;
				const matchesConfidence = confidenceFilter === 'all' || confidence === confidenceFilter;
				const matchesCore = !coreOnlyChecked || isCore;

				const isVisible = matchesSearch && matchesResult && matchesFamily && matchesConfidence && matchesCore;

				row.style.display = isVisible ? '' : 'none';
				if (isVisible) visibleCount++;

				// Update row index
				if (isVisible) {
					const indexCell = row.querySelector('[data-index-number]');
					if (indexCell) {
						indexCell.textContent = visibleCount;
					}
				}
			});

			// Show "no results" message if needed
			const noResults = document.getElementById('wps-no-results');
			if (noResults) {
				noResults.style.display = visibleCount === 0 ? '' : 'none';
			}
		},

		/**
		 * Clear all filters
		 */
		clearFilters: function() {
			document.getElementById('wps-search-diagnostics').value = '';
			document.getElementById('wps-filter-result').value = 'all';
			document.getElementById('wps-filter-family').value = 'all';
			document.getElementById('wps-filter-confidence').value = 'all';
			document.getElementById('wps-filter-core').checked = false;
			this.filterTable();
		},

		/**
		 * Diagnostic action handlers (Run, Enable/Disable)
		 */
		initDiagnosticActions: function() {
			document.addEventListener('click', (e) => {
				const runBtn = e.target.closest('[data-action="run-diagnostic"]');
				const toggleBtn = e.target.closest('[data-action="toggle-diagnostic"]');

				if (runBtn) {
					this.runDiagnostic(runBtn);
				}
				if (toggleBtn) {
					this.toggleDiagnostic(toggleBtn);
				}
			});
		},

		/**
		 * Run a single diagnostic via AJAX
		 */
		runDiagnostic: function(button) {
			const className = button.getAttribute('data-class-name');
			const nonce = button.getAttribute('data-nonce');

			if (!className || !nonce) return;

			button.disabled = true;
			const statusEl = button.closest('[data-status-for]')?.querySelector('[data-status-message]');

			fetch(window.ajaxurl || '/wp-admin/admin-ajax.php', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: new URLSearchParams({
					action: 'wpshadow_run_single_diagnostic',
					nonce: nonce,
					class_name: className
				})
			})
				.then(res => res.json())
				.then(data => {
					if (data.success) {
						if (statusEl) {
							statusEl.textContent = 'Diagnostic completed. Refreshing...';
						}
						setTimeout(() => window.location.reload(), 1000);
					} else {
						if (statusEl) {
							statusEl.textContent = 'Error: ' + (data.data?.message || 'Failed');
						}
					}
				})
				.catch(err => {
					if (statusEl) {
						statusEl.textContent = 'Error: Network request failed';
					}
				})
				.finally(() => {
					button.disabled = false;
				});
		},

		/**
		 * Toggle diagnostic enabled state
		 */
		toggleDiagnostic: function(button) {
			const className = button.getAttribute('data-class-name');
			const nonce = button.getAttribute('data-nonce');
			const currentState = button.getAttribute('data-enabled') === '1';

			if (!className || !nonce) return;

			button.disabled = true;
			const statusEl = button.closest('[data-status-for]')?.querySelector('[data-status-message]');

			fetch(window.ajaxurl || '/wp-admin/admin-ajax.php', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: new URLSearchParams({
					action: 'wpshadow_toggle_diagnostic',
					nonce: nonce,
					class_name: className,
					enable: currentState ? 0 : 1
				})
			})
				.then(res => res.json())
				.then(data => {
					if (data.success) {
						button.setAttribute('data-enabled', currentState ? '0' : '1');
						button.textContent = currentState ? 'Enable' : 'Disable';
						if (statusEl) {
							statusEl.textContent = currentState ? 'Diagnostic disabled.' : 'Diagnostic enabled.';
						}
					} else {
						if (statusEl) {
							statusEl.textContent = 'Error: ' + (data.data?.message || 'Failed');
						}
					}
				})
				.catch(err => {
					if (statusEl) {
						statusEl.textContent = 'Error: Network request failed';
					}
				})
				.finally(() => {
					button.disabled = false;
				});
		},

		/**
		 * Detail page: Save frequency, run diagnostic, toggle
		 */
		initDetailPageActions: function() {
			const saveFreqBtn = document.getElementById('wps-save-frequency');
			if (saveFreqBtn) {
				saveFreqBtn.addEventListener('click', () => this.saveFrequency());
			}
		},

		/**
		 * Save diagnostic frequency preference
		 */
		saveFrequency: function() {
			const select = document.getElementById('wps-frequency-select');
			const className = select?.getAttribute('data-class-name');
			const nonce = select?.getAttribute('data-nonce');
			const frequency = select?.value;

			if (!className || !nonce || !frequency) return;

			const button = document.getElementById('wps-save-frequency');
			button.disabled = true;

			fetch(window.ajaxurl || '/wp-admin/admin-ajax.php', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: new URLSearchParams({
					action: 'wpshadow_save_diagnostic_frequency',
					nonce: nonce,
					class_name: className,
					frequency: frequency
				})
			})
				.then(res => res.json())
				.then(data => {
					const statusEl = document.getElementById('wps-frequency-status');
					if (data.success) {
						if (statusEl) {
							statusEl.textContent = 'Frequency saved.';
						}
					} else {
						if (statusEl) {
							statusEl.textContent = 'Error: ' + (data.data?.message || 'Failed');
						}
					}
				})
				.catch(err => {
					const statusEl = document.getElementById('wps-frequency-status');
					if (statusEl) {
						statusEl.textContent = 'Error: Network request failed';
					}
				})
				.finally(() => {
					button.disabled = false;
				});
		},

		/**
		 * Bind the Run All Tests button on the Dashboard page.
		 */
		initRunAll: function() {
			const btn = document.getElementById( 'wps-run-all' );
			if ( ! btn ) return;
			btn.addEventListener( 'click', () => this.runAllDiagnostics( btn ) );

			const nonce = btn.getAttribute( 'data-nonce' );
			if ( nonce ) {
				this.fetchRunAllStatus( nonce )
					.then( ( data ) => {
						if ( ! data || ! data.running ) {
							return;
						}

						const statusEl = document.getElementById( 'wps-run-all-status' );
						if ( statusEl ) {
							statusEl.style.display = '';
							statusEl.textContent = this.formatRunAllStatusMessage( data );
						}

						btn.disabled = true;
						this.setRunAllProgress( data.progress_percent || 0 );
						this.startRunAllStatusPolling( btn, statusEl, nonce );
					} )
					.catch( () => {
						// Silent on initial status check to avoid noisy load-time errors.
					} );
			}
		},

		ensureRunAllProgressBar: function() {
			let wrap = document.getElementById( 'wps-run-all-progress-wrap' );
			if ( wrap ) {
				return wrap;
			}

			const statusEl = document.getElementById( 'wps-run-all-status' );
			if ( ! statusEl || ! statusEl.parentNode ) {
				return null;
			}

			wrap = document.createElement( 'div' );
			wrap.id = 'wps-run-all-progress-wrap';
			wrap.style.display = 'none';
			wrap.style.marginBottom = 'var(--wps-space-8)';

			const track = document.createElement( 'div' );
			track.style.position = 'relative';
			track.style.width = '100%';
			track.style.height = '8px';
			track.style.borderRadius = '999px';
			track.style.background = 'var(--wps-gray-200)';
			track.setAttribute( 'role', 'progressbar' );
			track.setAttribute( 'aria-valuemin', '0' );
			track.setAttribute( 'aria-valuemax', '100' );
			track.setAttribute( 'aria-valuenow', '0' );

			const bar = document.createElement( 'div' );
			bar.id = 'wps-run-all-progress-bar';
			bar.style.width = '0%';
			bar.style.height = '100%';
			bar.style.borderRadius = '999px';
			bar.style.background = 'var(--wps-accent, #0b57d0)';
			bar.style.transition = 'width 0.3s ease';

			const text = document.createElement( 'div' );
			text.id = 'wps-run-all-progress-text';
			text.textContent = '0%';
			text.style.marginTop = '6px';
			text.style.fontSize = 'var(--wps-text-xs)';
			text.style.color = 'var(--wps-gray-600)';

			track.appendChild( bar );
			wrap.appendChild( track );
			wrap.appendChild( text );
			statusEl.parentNode.insertBefore( wrap, statusEl.nextSibling );

			return wrap;
		},

		setRunAllProgress: function( percent ) {
			const safePercent = Math.max( 0, Math.min( 100, parseInt( percent, 10 ) || 0 ) );
			const wrap = this.ensureRunAllProgressBar();
			if ( ! wrap ) {
				return;
			}

			const bar = document.getElementById( 'wps-run-all-progress-bar' );
			const text = document.getElementById( 'wps-run-all-progress-text' );
			const track = wrap.firstChild;

			wrap.style.display = '';
			if ( bar ) {
				bar.style.width = safePercent + '%';
			}
			if ( text ) {
				text.textContent = safePercent + '%';
			}
			if ( track ) {
				track.setAttribute( 'aria-valuenow', String( safePercent ) );
			}
		},

		formatRunAllStatusMessage: function( data ) {
			const currentLabel = data && data.current_label ? String( data.current_label ) : '';
			const completed = data && Number.isFinite( Number( data.completed_items ) ) ? Number( data.completed_items ) : null;
			const total = data && Number.isFinite( Number( data.total_items ) ) ? Number( data.total_items ) : null;

			let message = 'A scan is already running. Tracking progress…';
			if ( currentLabel ) {
				message += ' Checking: ' + currentLabel + '.';
			}
			if ( null !== completed && null !== total && total > 0 ) {
				message += ' (' + completed + '/' + total + ')';
			}

			return message;
		},

		updateDashboardScoreCards: function( summary ) {
			if ( ! summary || typeof summary !== 'object' ) {
				return;
			}

			const score = Number.isFinite( Number( summary.score ) ) ? Number( summary.score ) : null;
			const passed = Number.isFinite( Number( summary.passed ) ) ? Number( summary.passed ) : null;
			const failed = Number.isFinite( Number( summary.failed ) ) ? Number( summary.failed ) : null;

			const statValues = document.querySelectorAll( '.wps-grid.wps-grid--3col .wps-stat .wps-stat-value' );
			if ( statValues.length >= 3 ) {
				if ( null !== score ) {
					const scoreEl = statValues[0];
					scoreEl.textContent = score + '%';
					if ( score >= 80 ) {
						scoreEl.style.color = 'var(--wps-status-pass)';
					} else if ( score >= 60 ) {
						scoreEl.style.color = 'var(--wps-amber-500)';
					} else {
						scoreEl.style.color = 'var(--wps-status-fail)';
					}
				}

				if ( null !== passed ) {
					statValues[1].textContent = String( passed );
				}

				if ( null !== failed ) {
					const failedEl = statValues[2];
					failedEl.textContent = String( failed );
					failedEl.style.color = failed > 0 ? 'var(--wps-status-fail)' : 'var(--wps-gray-400)';
				}
			}

			const pageContainer = document.querySelector( '.wpshadow-dashboard.wps-page-container' );
			if ( ! pageContainer || null === failed ) {
				return;
			}

			const attentionTitle = pageContainer.querySelector( '.wps-card[style*="border-left: 4px solid var(--wps-status-fail)"] .wps-card-title' );
			if ( attentionTitle ) {
				attentionTitle.textContent = failed === 1 ? '⚠️ 1 Issue Needs Attention' : '⚠️ ' + failed + ' Issues Need Attention';
			}
		},

		stopRunAllPolling: function() {
			if ( this.runAllPollTimer ) {
				clearInterval( this.runAllPollTimer );
				this.runAllPollTimer = null;
			}
		},

		parseAjaxPayload: async function( response ) {
			const raw = await response.text();
			const trimmed = typeof raw === 'string' ? raw.trim() : '';

			if ( trimmed === '-1' ) {
				throw new Error( 'Security token check failed. Please refresh and try again.' );
			}
			if ( trimmed === '' || trimmed === '0' ) {
				throw new Error( 'Scan request returned an empty response.' );
			}

			try {
				return JSON.parse( trimmed );
			} catch ( e ) {
				throw new Error( 'Unexpected server response: ' + trimmed.slice( 0, 180 ) );
			}
		},

		shouldFallbackToPolling: function( err ) {
			const message = err && err.message ? String( err.message ) : '';
			return message.includes( 'empty response' ) || message.includes( 'Unexpected server response' );
		},

		fetchRunAllStatus: function( nonce ) {
			return fetch( window.ajaxurl || '/wp-admin/admin-ajax.php', {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: new URLSearchParams( { action: 'wpshadow_deep_scan_status', nonce: nonce } )
			} )
			.then( ( r ) => this.parseAjaxPayload( r ) )
			.then( ( payload ) => {
				if ( payload && payload.success && payload.data ) {
					return payload.data;
				}
				throw new Error( ( payload && payload.data && payload.data.message ) || 'Failed to fetch scan status.' );
			} );
		},

		startRunAllStatusPolling: function( button, statusEl, nonce ) {
			this.stopRunAllPolling();

			const tick = () => {
				this.fetchRunAllStatus( nonce )
					.then( ( data ) => {
						if ( data && data.stalled ) {
							this.stopRunAllPolling();
							button.disabled = false;
							if ( statusEl ) {
								statusEl.style.display = '';
								statusEl.textContent = 'Error: ' + ( data.stalled_message || 'Scan startup stalled.' );
							}
							return;
						}

						if ( data.dashboard_summary ) {
							this.updateDashboardScoreCards( data.dashboard_summary );
						}

						if ( data.running ) {
							button.disabled = true;
							this.setRunAllProgress( data.progress_percent || 0 );
							if ( statusEl ) {
								statusEl.style.display = '';
								statusEl.textContent = this.formatRunAllStatusMessage( data );
							}
							return;
						}

						this.setRunAllProgress( 100 );
						if ( statusEl ) {
							statusEl.style.display = '';
							statusEl.textContent = 'Scan complete. Refreshing…';
						}
						this.stopRunAllPolling();
						setTimeout( () => window.location.reload(), 800 );
					} )
					.catch( ( err ) => {
						if ( this.shouldFallbackToPolling( err ) ) {
							if ( statusEl ) {
								statusEl.style.display = '';
								statusEl.textContent = 'Scan is running. Waiting for status updates…';
							}
							return;
						}

						this.stopRunAllPolling();
						button.disabled = false;
						if ( statusEl ) {
							statusEl.style.display = '';
							statusEl.textContent = 'Error: ' + ( err && err.message ? err.message : 'Could not track scan status.' );
						}
					} );
			};

			tick();
			this.runAllPollTimer = setInterval( tick, 3000 );
		},

		/**
		 * Fire wpshadow_deep_scan via AJAX then reload on success.
		 */
		runAllDiagnostics: function( button ) {
			const nonce    = button.getAttribute( 'data-nonce' );
			const statusEl = document.getElementById( 'wps-run-all-status' );
			button.disabled = true;
			if ( statusEl ) {
				statusEl.style.display  = '';
				statusEl.textContent    = 'Running all diagnostics…';
			}
			this.setRunAllProgress( 3 );
			fetch( window.ajaxurl || '/wp-admin/admin-ajax.php', {
				method  : 'POST',
				headers : { 'Content-Type': 'application/x-www-form-urlencoded' },
				body    : new URLSearchParams( { action: 'wpshadow_deep_scan', nonce: nonce, mode: 'now' } )
			} )
			.then( ( r ) => this.parseAjaxPayload( r ) )
			.then( data => {
				if ( data.success ) {
					this.setRunAllProgress( 100 );
					if ( statusEl ) statusEl.textContent = 'Scan complete. Refreshing…';
					setTimeout( () => window.location.reload(), 1000 );
				} else if ( data.data && data.data.locked ) {
					if ( statusEl ) {
						statusEl.style.display = '';
						statusEl.textContent = this.formatRunAllStatusMessage( data.data );
					}
					this.startRunAllStatusPolling( button, statusEl, nonce );
				} else {
					if ( statusEl ) statusEl.textContent = 'Error: ' + ( ( data.data && data.data.message ) || 'Scan failed' );
					button.disabled = false;
				}
			} )
			.catch( ( err ) => {
				if ( this.shouldFallbackToPolling( err ) ) {
					if ( statusEl ) {
						statusEl.style.display = '';
						statusEl.textContent = 'Scan start response was delayed. Tracking status…';
					}
					this.startRunAllStatusPolling( button, statusEl, nonce );
					return;
				}

				if ( statusEl ) statusEl.textContent = 'Error: ' + ( err && err.message ? err.message : 'Request failed. Please try again.' );
				button.disabled = false;
			} );
		}
	};

	// Initialize when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', () => WPShadowUI.init());
	} else {
		WPShadowUI.init();
	}

	// Export for global access if needed
	window.WPShadowUI = WPShadowUI;
})();
