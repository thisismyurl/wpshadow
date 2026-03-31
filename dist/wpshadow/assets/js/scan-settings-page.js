(function () {
	'use strict';

	const cfg     = window.wpshadowScanSettings || {};
	const ajaxurl = cfg.ajax_url || '';
	const nonce   = cfg.nonce || '';
	const strings = cfg.strings || {};

	let page          = 1;
	const perPage     = 25;
	let currentFamily = '';
	let currentSearch = '';
	let currentStatus = 'all';
	const diagnosticsFilterStorageKey = 'wpshadowScanDiagnosticsFilters';

	let tPage      = 1;
	const tPerPage = 25;
	let tSearch    = '';

	function showError(title, message) {
		if (window.WPShadowModal && typeof window.WPShadowModal.alert === 'function') {
			window.WPShadowModal.alert(
				{
					title: title,
					message: message,
					type: 'error'
				}
			);
			return;
		}

		window.alert( message );
	}

	function escapeHtml(value) {
		return String( value ).replace(
			/[&<>"']/g,
			function (char) {
				return ({
					'&': '&amp;',
					'<': '&lt;',
					'>': '&gt;',
					'"': '&quot;',
					"'": '&#39;'
				})[char];
			}
		);
	}

	function createToggleButton(isEnabled, ariaLabel) {
		const toggle     = document.createElement( 'button' );
		toggle.className = 'button';
		toggle.setAttribute( 'aria-label', ariaLabel );
		toggle.textContent = isEnabled ? (strings.disable || 'Disable') : (strings.enable || 'Enable');
		return toggle;
	}

	function renderList(items) {
		const container     = document.getElementById( 'wpshadow-diagnostics-list' );
		container.innerHTML = '';
		if ( ! items || items.length === 0) {
			container.innerHTML = '<tr><td colspan="5">' + escapeHtml( strings.no_diagnostics || 'No diagnostics found.' ) + '</td></tr>';
			return;
		}

		const frag = document.createDocumentFragment();
		items.forEach(
			function (item, index) {
				const row     = document.createElement( 'tr' );

				const numberCell = document.createElement( 'td' );
				numberCell.textContent = String( ( ( page - 1 ) * perPage ) + index + 1 );

				const diagnosticCell = document.createElement( 'td' );
				diagnosticCell.innerHTML = '<strong>' + escapeHtml( item.title || item.slug || item.class_name ) + '</strong>' +
					(item.description ? '<div class="wps-diagnostic-description">' + escapeHtml( item.description ) + '</div>' : '');

				const familyCell = document.createElement( 'td' );
				familyCell.textContent = item.family ? item.family : 'general';

				const statusCell = document.createElement( 'td' );
				statusCell.textContent = item.enabled ? 'Enabled' : 'Disabled';

				const toggle = createToggleButton( ! ! item.enabled, strings.toggle_diagnostic || 'Toggle diagnostic' );
				toggle.addEventListener(
					'click',
					function () {
						toggle.disabled = true;
						fetch(
							ajaxurl,
							{
								method: 'POST',
								headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
								body: new URLSearchParams(
									{
										action: 'wpshadow_toggle_diagnostic',
										nonce: nonce,
										class_name: item.class_name,
										enable: item.enabled ? '0' : '1'
									}
								).toString()
							}
						)
						.then(
							function (response) {
								return response.json(); }
						)
						.then(
							function (response) {
								if (response && response.success) {
									item.enabled       = ! ! response.data.enabled;
									toggle.textContent = item.enabled ? (strings.disable || 'Disable') : (strings.enable || 'Enable');
									return;
								}
								showError(
									strings.error_title || 'Error',
									(response && response.data && response.data.message) || (strings.operation_failed || 'Operation failed')
								);
							}
						)
						.catch(
							function () {
								showError( strings.network_title || 'Network Error', strings.network_error || 'Network error' );
							}
						)
						.finally(
							function () {
								toggle.disabled = false;
							}
						);
					}
				);

				const actionCell = document.createElement( 'td' );
				actionCell.appendChild( toggle );

				row.appendChild( numberCell );
				row.appendChild( diagnosticCell );
				row.appendChild( familyCell );
				row.appendChild( statusCell );
				row.appendChild( actionCell );
				frag.appendChild( row );
			}
		);
		container.appendChild( frag );
	}

	function saveDiagnosticFilters() {
		window.localStorage.setItem(
			diagnosticsFilterStorageKey,
			JSON.stringify(
				{
					search: currentSearch,
					family: currentFamily,
					status: currentStatus
				}
			)
		);
	}

	function restoreDiagnosticFilters() {
		try {
			const saved = JSON.parse( window.localStorage.getItem( diagnosticsFilterStorageKey ) || '{}' );
			currentSearch = saved.search || '';
			currentFamily = saved.family || '';
			currentStatus = saved.status || 'all';
		} catch (e) {
			currentSearch = '';
			currentFamily = '';
			currentStatus = 'all';
		}
	}

	function renderTreatments(items) {
		const container     = document.getElementById( 'wpshadow-treatments-list' );
		container.innerHTML = '';
		if ( ! items || items.length === 0) {
			container.innerHTML = '<p>' + escapeHtml( strings.no_treatments || 'No treatments found.' ) + '</p>';
			return;
		}

		const frag = document.createDocumentFragment();
		items.forEach(
			function (item) {
				const row     = document.createElement( 'div' );
				row.className = 'wpshadow-row wpshadow-scan-settings-row';

				const info     = document.createElement( 'div' );
				info.innerHTML = '<strong>' + escapeHtml( item.label || item.class_name ) + '</strong>' +
				'<div class="wps-treatment-class-name">' + escapeHtml( item.class_name ) + '</div>';

				const toggle = createToggleButton( ! ! item.enabled, strings.toggle_treatment || 'Toggle treatment' );
				toggle.addEventListener(
					'click',
					function () {
						toggle.disabled = true;
						fetch(
							ajaxurl,
							{
								method: 'POST',
								headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
								body: new URLSearchParams(
									{
										action: 'wpshadow_toggle_treatment',
										nonce: nonce,
										class_name: item.class_name,
										enable: item.enabled ? '0' : '1'
									}
								).toString()
							}
						)
						.then(
							function (response) {
								return response.json(); }
						)
						.then(
							function (response) {
								if (response && response.success) {
									item.enabled       = ! ! response.data.enabled;
									toggle.textContent = item.enabled ? (strings.disable || 'Disable') : (strings.enable || 'Enable');
									return;
								}
								showError(
									strings.error_title || 'Error',
									(response && response.data && response.data.message) || (strings.operation_failed || 'Operation failed')
								);
							}
						)
						.catch(
							function () {
								showError( strings.network_title || 'Network Error', strings.network_error || 'Network error' );
							}
						)
						.finally(
							function () {
								toggle.disabled = false;
							}
						);
					}
				);

				row.appendChild( info );
				row.appendChild( toggle );
				frag.appendChild( row );
			}
		);
		container.appendChild( frag );
	}

	function loadFamilies() {
		fetch(
			ajaxurl,
			{
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: new URLSearchParams(
					{
						action: 'wpshadow_list_diagnostics',
						nonce: nonce,
						page: '1',
						per_page: '1',
						get_families: '1'
					}
				).toString()
			}
		)
			.then(
				function (response) {
					return response.json(); }
			)
			.then(
				function (response) {
					if (response && response.success && response.data && response.data.families) {
						const select = document.getElementById( 'wpshadow-family' );
						select.innerHTML = '<option value="">All families</option>';
						response.data.families.forEach(
							function (family) {
								const opt       = document.createElement( 'option' );
								opt.value       = family;
								opt.textContent = family;
								if (currentFamily && currentFamily === family) {
									opt.selected = true;
								}
								select.appendChild( opt );
							}
						);
					}
				}
			);
	}

	function loadPage() {
		const params = new URLSearchParams(
			{
				action: 'wpshadow_list_diagnostics',
				nonce: nonce,
				page: String( page ),
				per_page: String( perPage )
			}
		);
		if (currentFamily) {
			params.append( 'family', currentFamily );
		}
		if (currentSearch) {
			params.append( 'search', currentSearch );
		}

		fetch(
			ajaxurl,
			{
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: params.toString()
			}
		)
			.then(
				function (response) {
					return response.json(); }
			)
			.then(
				function (response) {
					if (response && response.success) {
						let items = response.data.items || [];
						if (currentStatus === 'enabled') {
							items = items.filter( function (item) { return !! item.enabled; } );
						} else if (currentStatus === 'disabled') {
							items = items.filter( function (item) { return ! item.enabled; } );
						}
						renderList( items );
						document.getElementById( 'wpshadow-page' ).textContent = String( page );
					}
				}
			);
	}

	function loadTreatmentsPage() {
		const params = new URLSearchParams(
			{
				action: 'wpshadow_list_treatments',
				nonce: nonce,
				page: String( tPage ),
				per_page: String( tPerPage )
			}
		);
		if (tSearch) {
			params.append( 'search', tSearch );
		}

		fetch(
			ajaxurl,
			{
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: params.toString()
			}
		)
			.then(
				function (response) {
					return response.json(); }
			)
			.then(
				function (response) {
					if (response && response.success) {
						renderTreatments( response.data.items || [] );
						document.getElementById( 'wpshadow-t-page' ).textContent = String( tPage );
					}
				}
			);
	}

	const prevBtn      = document.getElementById( 'wpshadow-prev' );
	const nextBtn      = document.getElementById( 'wpshadow-next' );
	const familySelect = document.getElementById( 'wpshadow-family' );
	const searchEl     = document.getElementById( 'wpshadow-search' );
	const tSearchEl    = document.getElementById( 'wpshadow-t-search' );
	const statusSelect = document.getElementById( 'wpshadow-status' );
	const resetBtn     = document.getElementById( 'wpshadow-reset-filters' );
	const tPrevBtn     = document.getElementById( 'wpshadow-t-prev' );
	const tNextBtn     = document.getElementById( 'wpshadow-t-next' );

	if ( ! prevBtn || ! nextBtn || ! familySelect || ! statusSelect || ! resetBtn || ! searchEl || ! tSearchEl || ! tPrevBtn || ! tNextBtn) {
		return;
	}

	restoreDiagnosticFilters();
	searchEl.value = currentSearch;
	statusSelect.value = currentStatus;

	prevBtn.addEventListener(
		'click',
		function () {
			if (page > 1) {
				page--;
				loadPage();
			}
		}
	);
	nextBtn.addEventListener(
		'click',
		function () {
			page++;
			loadPage();
		}
	);
	familySelect.addEventListener(
		'change',
		function (e) {
			currentFamily = e.target.value || '';
			page          = 1;
			saveDiagnosticFilters();
			loadPage();
		}
	);

	statusSelect.addEventListener(
		'change',
		function (e) {
			currentStatus = e.target.value || 'all';
			page          = 1;
			saveDiagnosticFilters();
			loadPage();
		}
	);

	let searchDebounce;
	searchEl.addEventListener(
		'input',
		function () {
			clearTimeout( searchDebounce );
			searchDebounce = setTimeout(
				function () {
					currentSearch = searchEl.value || '';
					page          = 1;
					saveDiagnosticFilters();
					loadPage();
				},
				300
			);
		}
	);

		resetBtn.addEventListener(
			'click',
			function () {
				currentSearch = '';
				currentFamily = '';
				currentStatus = 'all';
				page          = 1;
				searchEl.value = '';
				familySelect.value = '';
				statusSelect.value = 'all';
				saveDiagnosticFilters();
				loadPage();
			}
		);

	let treatmentDebounce;
	tSearchEl.addEventListener(
		'input',
		function () {
			clearTimeout( treatmentDebounce );
			treatmentDebounce = setTimeout(
				function () {
					tSearch = tSearchEl.value || '';
					tPage   = 1;
					loadTreatmentsPage();
				},
				300
			);
		}
	);

	tPrevBtn.addEventListener(
		'click',
		function () {
			if (tPage > 1) {
				tPage--;
				loadTreatmentsPage();
			}
		}
	);
	tNextBtn.addEventListener(
		'click',
		function () {
			tPage++;
			loadTreatmentsPage();
		}
	);

	loadFamilies();
	loadPage();
	loadTreatmentsPage();
})();
