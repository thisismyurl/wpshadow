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
			fetch( window.ajaxurl || '/wp-admin/admin-ajax.php', {
				method  : 'POST',
				headers : { 'Content-Type': 'application/x-www-form-urlencoded' },
				body    : new URLSearchParams( { action: 'wpshadow_deep_scan', nonce: nonce } )
			} )
			.then( r => r.json() )
			.then( data => {
				if ( data.success ) {
					if ( statusEl ) statusEl.textContent = 'Scan complete. Refreshing…';
					setTimeout( () => window.location.reload(), 1000 );
				} else {
					if ( statusEl ) statusEl.textContent = 'Error: ' + ( ( data.data && data.data.message ) || 'Scan failed' );
					button.disabled = false;
				}
			} )
			.catch( () => {
				if ( statusEl ) statusEl.textContent = 'Network error. Please try again.';
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
