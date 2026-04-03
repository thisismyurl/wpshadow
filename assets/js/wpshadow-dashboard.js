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

	// Escape special HTML characters to prevent XSS when building innerHTML.
	function htmlEsc( str ) {
		return String( str )
			.replace( /&/g, '&amp;' )
			.replace( /</g, '&lt;' )
			.replace( />/g, '&gt;' )
			.replace( /"/g, '&quot;' );
	}

	// Allow only https://, http://, and /wp-admin/ URLs.
	function safeUrl( str ) {
		const s = String( str );
		if ( /^\/wp-admin\//.test( s ) || /^https?:\/\//.test( s ) ) {
			return htmlEsc( s );
		}
		return '#';
	}

	// Trim a string to at most n words, appending ' …' when truncated.
	function trimWords( str, n ) {
		const words = String( str ).trim().split( /\s+/ ).filter( Boolean );
		if ( words.length <= n ) {
			return String( str );
		}
		return words.slice( 0, n ).join( ' ' ) + ' …';
	}

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
			this.initPreselectedFamilyFilter();
			this.bindRunAllTriggers();
			this.bindAdminNoticeDismissals();
		},

		initPreselectedFamilyFilter: function() {
			const app = document.querySelector( '[data-preselected-family]' );
			const family = app ? ( app.getAttribute( 'data-preselected-family' ) || '' ) : '';

			if ( ! family ) {
				return;
			}

			const familyEl = document.getElementById( 'wps-filter-family' );
			if ( familyEl ) {
				familyEl.value = family;
				this.filterTable();
			}
		},

		bindRunAllTriggers: function() {
			document.addEventListener( 'click', ( e ) => {
				const trigger = e.target.closest( '[data-wps-run-all-trigger]' );
				if ( ! trigger ) {
					return;
				}

				e.preventDefault();
				const button = document.getElementById( 'wps-run-all' );
				if ( button ) {
					button.click();
				}
			} );
		},

		bindAdminNoticeDismissals: function() {
			document.addEventListener( 'click', ( e ) => {
				const fileWriteTrigger = e.target.closest( '.wpshadow-dismiss-file-write-notice, .wpshadow-file-write-notice .notice-dismiss' );
				if ( fileWriteTrigger ) {
					e.preventDefault();
					this.dismissAdminNotice( '.wpshadow-file-write-notice', 'wpshadow_dismiss_file_write_notice' );
					return;
				}

				const staleDiagnosticsTrigger = e.target.closest( '.wpshadow-dismiss-stale-diagnostics-notice, .wpshadow-stale-diagnostics-notice .notice-dismiss' );
				if ( staleDiagnosticsTrigger ) {
					e.preventDefault();
					this.dismissAdminNotice( '.wpshadow-stale-diagnostics-notice', 'wpshadow_dismiss_stale_diagnostics_notice' );
				}
			} );
		},

		dismissAdminNotice: function( selector, action ) {
			const notice = document.querySelector( selector );
			if ( ! notice ) {
				return;
			}

			const nonce = notice.getAttribute( 'data-nonce' ) || '';
			if ( nonce && ( window.ajaxurl || '/wp-admin/admin-ajax.php' ) ) {
				fetch( window.ajaxurl || '/wp-admin/admin-ajax.php', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded'
					},
					body: new URLSearchParams( {
						action: action,
						nonce: nonce
					} )
				} ).catch( () => {
					// Ignore dismiss errors; the notice is non-critical UI only.
				} );
			}

			notice.style.transition = 'opacity 0.3s ease';
			notice.style.opacity = '0';
			window.setTimeout( () => {
				notice.style.display = 'none';
			}, 300 );
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
		 * Diagnostic action handlers (Run)
		 */
		initDiagnosticActions: function() {
			document.addEventListener('click', (e) => {
				const runBtn = e.target.closest('[data-action="run-diagnostic"]');

				if (runBtn) {
					this.runDiagnostic(runBtn);
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
							statusEl.textContent = data.data?.message || 'Schedule saved. Refreshing…';
						}
						window.setTimeout(() => window.location.reload(), 700);
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
					this.setRunAllProgress( data.progress_percent || 0, data.current_label || '' );
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

		setRunAllProgress: function( percent, label ) {
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
				const labelStr = label && String( label ).trim() ? ' — ' + String( label ).trim() : '';
				text.textContent = safePercent + '%' + labelStr;
			}
			if ( track ) {
				track.setAttribute( 'aria-valuenow', String( safePercent ) );
			}
		},

		setPendingTestsAlertVisibility: function( visible ) {
			const alert = document.getElementById( 'wps-pending-tests-alert' );
			if ( ! alert ) {
				return;
			}

			alert.style.display = visible ? '' : 'none';
		},

		formatRunAllStatusMessage: function( data ) {
			const currentLabel = data && data.current_label ? String( data.current_label ) : '';
			const completedValue = data && data.completed_items !== undefined ? data.completed_items : data && data.completed;
			const totalValue = data && data.total_items !== undefined ? data.total_items : data && data.total;
			const completed = Number.isFinite( Number( completedValue ) ) ? Number( completedValue ) : null;
			const total = Number.isFinite( Number( totalValue ) ) ? Number( totalValue ) : null;

			let message = data && data.complete ? 'Scan complete. Refreshing…' : 'A scan is already running. Tracking progress …';
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
			const pending = Number.isFinite( Number( summary.pending ) ) ? Number( summary.pending ) : null;
			const families = summary.families && typeof summary.families === 'object' ? summary.families : null;

			const scoreEl = document.getElementById( 'wps-dashboard-score-value' );
			const passedEl = document.getElementById( 'wps-dashboard-passed-value' );
			const failedEl = document.getElementById( 'wps-dashboard-failed-value' );

			if ( scoreEl && null !== score ) {
				scoreEl.textContent = score + '%';
				if ( score >= 80 ) {
					scoreEl.style.color = 'var(--wps-status-pass)';
				} else if ( score >= 60 ) {
					scoreEl.style.color = 'var(--wps-amber-500)';
				} else {
					scoreEl.style.color = 'var(--wps-status-fail)';
				}
			}

			if ( passedEl && null !== passed ) {
				passedEl.textContent = String( passed );
			}

			if ( failedEl && null !== failed ) {
				failedEl.textContent = String( failed );
				failedEl.style.color = failed > 0 ? 'var(--wps-status-fail)' : 'var(--wps-gray-400)';
			}

			const attentionTitle = document.getElementById( 'wps-dashboard-attention-title' );
			if ( attentionTitle && null !== failed ) {
				attentionTitle.textContent = failed === 1 ? '⚠️ 1 Issue Needs Attention' : '⚠️ ' + failed + ' Issues Need Attention';
			}

			const attentionCard = document.getElementById( 'wps-dashboard-attention-card' );
			if ( attentionCard && null !== failed && null !== pending ) {
				attentionCard.style.display = failed > 0 ? '' : 'none';
			}

			// Rebuild the attention items table.
			const attentionItems = Array.isArray( summary.attention_items ) ? summary.attention_items : null;
			const extraIssues    = Number.isFinite( Number( summary.extra_issues ) ) ? Number( summary.extra_issues ) : 0;
			const guardianUrl    = ( attentionCard && attentionCard.dataset.guardianUrl ) ? safeUrl( attentionCard.dataset.guardianUrl ) : safeUrl( '' );
			const tbody          = document.getElementById( 'wps-dashboard-attention-tbody' );

			if ( tbody && attentionItems !== null ) {
				let html = '';
				attentionItems.forEach( function( item ) {
					const name   = item.name || '';
					const reason = item.failure_reason || '';
					const url    = safeUrl( item.detail_url || '#' );
					const isCore = !! item.is_core;
					const family = item.family || '';

					html += '<tr style="border-top: 1px solid var(--wps-gray-100);">';
					html += '<td style="padding: var(--wps-space-4) var(--wps-space-3) var(--wps-space-4) 0; width: 1.25rem; vertical-align: top;"><span style="color: var(--wps-status-fail);">✕</span></td>';
					html += '<td style="padding: var(--wps-space-4) var(--wps-space-4) var(--wps-space-4) 0;">';
					html += '<div style="display: flex; align-items: center; gap: var(--wps-space-3); flex-wrap: wrap;">';
					html += '<span style="font-weight: var(--wps-font-weight-medium); color: var(--wps-gray-900);">' + htmlEsc( name ) + '</span>';
					if ( isCore ) {
						html += '<span class="wps-badge wps-badge--core">Core</span>';
					}
					if ( family ) {
						const familyLabel = family.replace( /-/g, ' ' ).replace( /\b\w/g, function( c ) { return c.toUpperCase(); } );
						html += '<span class="wps-badge" style="background: var(--wps-gray-100); color: var(--wps-gray-600);">' + htmlEsc( familyLabel ) + '</span>';
					}
					html += '</div>';
					if ( reason ) {
						html += '<p style="margin: var(--wps-space-1) 0 0; font-size: var(--wps-text-sm); color: var(--wps-gray-600);">' + htmlEsc( trimWords( reason, 15 ) ) + '</p>';
					}
					html += '</td>';
					html += '<td style="padding: var(--wps-space-4) 0; white-space: nowrap; text-align: right; vertical-align: top;"><a href="' + url + '" class="wps-button wps-button--secondary" style="padding: var(--wps-space-2) var(--wps-space-4); font-size: var(--wps-text-sm);">Details →</a></td>';
					html += '</tr>';
				} );

				if ( extraIssues > 0 ) {
					html += '<tr style="border-top: 1px solid var(--wps-gray-100);"><td colspan="3" style="padding: var(--wps-space-4) 0; text-align: center; font-size: var(--wps-text-sm); color: var(--wps-gray-600);">+' + extraIssues + ' more issue' + ( extraIssues !== 1 ? 's' : '' ) + ' — <a href="' + guardianUrl + '">View all in Guardian</a></td></tr>';
				}

				tbody.innerHTML = html;
			}

			if ( ! families ) {
				return;
			}

			Object.keys( families ).forEach( ( familySlug ) => {
				const familySummary = families[ familySlug ];
				if ( ! familySummary || typeof familySummary !== 'object' ) {
					return;
				}

				const familyScore = Number.isFinite( Number( familySummary.score ) ) ? Number( familySummary.score ) : 100;
				const familyFailed = Number.isFinite( Number( familySummary.failed ) ) ? Number( familySummary.failed ) : 0;
				const familyPassed = Number.isFinite( Number( familySummary.passed ) ) ? Number( familySummary.passed ) : 0;
				const familyActive = Number.isFinite( Number( familySummary.active ) ) ? Number( familySummary.active ) : 0;

				const cardEl = document.querySelector( '[data-family-card="' + familySlug + '"]' );
				const scoreValueEl = document.querySelector( '[data-family-score="' + familySlug + '"]' );
				const failedValueEl = document.querySelector( '[data-family-failed="' + familySlug + '"]' );
				const passedValueEl = document.querySelector( '[data-family-passed="' + familySlug + '"]' );

				if ( scoreValueEl ) {
					scoreValueEl.textContent = familyScore + '%';
					if ( familyFailed > 0 ) {
						scoreValueEl.style.color = familyScore < 60 ? 'var(--wps-status-fail)' : 'var(--wps-amber-500)';
					} else {
						scoreValueEl.style.color = 'var(--wps-status-pass)';
					}
				}

				if ( failedValueEl ) {
					failedValueEl.textContent = familyFailed === 1 ? '1 issue' : familyFailed + ' issues';
				}

				if ( passedValueEl ) {
					passedValueEl.textContent = familyPassed + ' / ' + familyActive + ' passed';
				}

				if ( cardEl ) {
					cardEl.style.opacity = familyActive > 0 ? '1' : '0.72';
				}
			} );
		},

		stopRunAllPolling: function() {
			if ( this.runAllPollTimer ) {
				clearInterval( this.runAllPollTimer );
				this.runAllPollTimer = null;
			}
			if ( this.runAllResumeTimer ) {
				clearTimeout( this.runAllResumeTimer );
				this.runAllResumeTimer = null;
			}
		},

		scheduleRunAllResume: function( button, statusEl, nonce, delay ) {
			if ( this.runAllResumeTimer ) {
				clearTimeout( this.runAllResumeTimer );
			}

			this.runAllResumeTimer = setTimeout( () => {
				this.runAllResumeTimer = null;
				this.requestRunAllBatch( button, statusEl, nonce );
			}, delay || 600 );
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
							this.setPendingTestsAlertVisibility( true );
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
							this.setPendingTestsAlertVisibility( false );
							this.setRunAllProgress( data.progress_percent || 0, data.current_label || '' );
							if ( statusEl ) {
								statusEl.style.display = '';
								statusEl.textContent = this.formatRunAllStatusMessage( data );
							}
							if ( data.resume_available && ! this.runAllRequestInFlight && ! this.runAllResumeTimer ) {
								this.scheduleRunAllResume( button, statusEl, nonce, 400 );
							}
							return;
						}

						this.setRunAllProgress( 100 );
						this.setPendingTestsAlertVisibility( true );
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
						this.setPendingTestsAlertVisibility( true );
						if ( statusEl ) {
							statusEl.style.display = '';
							statusEl.textContent = 'Error: ' + ( err && err.message ? err.message : 'Could not track scan status.' );
						}
					} );
			};

			tick();
			this.runAllPollTimer = setInterval( tick, 3000 );
		},

		requestRunAllBatch: function( button, statusEl, nonce ) {
			if ( this.runAllRequestInFlight ) {
				return;
			}

			this.runAllRequestInFlight = true;

			fetch( window.ajaxurl || '/wp-admin/admin-ajax.php', {
				method  : 'POST',
				headers : { 'Content-Type': 'application/x-www-form-urlencoded' },
				body    : new URLSearchParams( { action: 'wpshadow_deep_scan', nonce: nonce, mode: 'now' } )
			} )
				.then( ( r ) => this.parseAjaxPayload( r ) )
				.then( ( payload ) => {
					const data = payload && payload.data ? payload.data : null;

					if ( payload && payload.success && data ) {
						if ( data.dashboard_summary ) {
							this.updateDashboardScoreCards( data.dashboard_summary );
						}

						if ( Number.isFinite( Number( data.progress_percent ) ) ) {
						this.setRunAllProgress( Number( data.progress_percent ), data.current_label || '' );
					}
						if ( statusEl ) {
							statusEl.style.display = '';
							statusEl.textContent = data.message || this.formatRunAllStatusMessage( data );
						}

						this.setPendingTestsAlertVisibility( ! data.continue && ! data.complete );

						if ( data.complete ) {
							this.stopRunAllPolling();
							this.setRunAllProgress( 100 );
							setTimeout( () => window.location.reload(), 800 );
							return;
						}

						if ( data.continue ) {
							this.scheduleRunAllResume( button, statusEl, nonce, 250 );
							return;
						}

						button.disabled = false;
						return;
					}

					if ( data && data.locked ) {
						this.setPendingTestsAlertVisibility( false );
						if ( statusEl ) {
							statusEl.style.display = '';
							statusEl.textContent = this.formatRunAllStatusMessage( data );
						}
						this.scheduleRunAllResume( button, statusEl, nonce, 1200 );
						return;
					}

					throw new Error( ( data && data.message ) || 'Scan failed' );
				} )
				.catch( ( err ) => {
					if ( this.shouldFallbackToPolling( err ) ) {
						if ( statusEl ) {
							statusEl.style.display = '';
							statusEl.textContent = 'Scan response was delayed. Tracking status and resuming…';
						}
						this.startRunAllStatusPolling( button, statusEl, nonce );
						this.scheduleRunAllResume( button, statusEl, nonce, 2000 );
						return;
					}

					this.stopRunAllPolling();
					button.disabled = false;
					this.setPendingTestsAlertVisibility( true );
					if ( statusEl ) {
						statusEl.style.display = '';
						statusEl.textContent = 'Error: ' + ( err && err.message ? err.message : 'Request failed. Please try again.' );
					}
				} )
				.finally( () => {
					this.runAllRequestInFlight = false;
				} );
		},

		/**
		 * Fire batched wpshadow_deep_scan requests until the queue is exhausted.
		 */
		runAllDiagnostics: function( button ) {
			const nonce    = button.getAttribute( 'data-nonce' );
			const statusEl = document.getElementById( 'wps-run-all-status' );
			button.disabled = true;
			if ( statusEl ) {
				statusEl.style.display  = '';
				statusEl.textContent    = 'Running all diagnostics…';
			}
			this.setPendingTestsAlertVisibility( false );
			this.setRunAllProgress( 3 );
			this.startRunAllStatusPolling( button, statusEl, nonce );
			this.requestRunAllBatch( button, statusEl, nonce );
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
