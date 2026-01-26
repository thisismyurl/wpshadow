(function() {
	'use strict';

	const settings = window.wpshadowMobileCheck || {};
	const form = document.getElementById('wpshadow-mobile-form');
	if (!form) {
		return;
	}

	// Get site URL and host for validation
	const siteUrl = settings.defaultUrl || window.location.origin;
	const siteUrlObj = new URL(siteUrl);
	const siteHost = siteUrlObj.hostname;

	const pathInput = document.getElementById('wpshadow-mobile-path');
	const submitBtn = form.querySelector('button[type="submit"]');
	const errorBox = document.getElementById('wpshadow-mobile-error');
	const resultsWrap = document.getElementById('wpshadow-mobile-results');
	const checksWrap = document.getElementById('wpshadow-mobile-checks');
	const pillPass = document.querySelector('[data-mobile-summary="pass"]');
	const pillWarn = document.querySelector('[data-mobile-summary="warn"]');
	const pillFail = document.querySelector('[data-mobile-summary="fail"]');
	const lastUrl = document.getElementById('wpshadow-mobile-last-url');

	function setLoading(state) {
		if (submitBtn) {
			submitBtn.disabled = state;
			submitBtn.textContent = state ? (settings.i18nRunning || 'Checking...') : (settings.i18nRun || 'Run check');
		}
	}

	function showError(message) {
		if (!errorBox) {
			return;
		}
		errorBox.style.display = 'block';
		errorBox.textContent = message || settings.i18nError || 'Something went wrong. Please try again.';
		if (resultsWrap) {
			resultsWrap.classList.add('is-hidden');
		}
	}

	function clearError() {
		if (errorBox) {
			errorBox.style.display = 'none';
			errorBox.textContent = '';
		}
	}

	// Auto-clean URLs pasted into path field
	if (pathInput) {
		pathInput.addEventListener('blur', function() {
			let value = this.value.trim();

			// If it looks like a full URL, extract the path
			if (value.match(/^https?:\/\//i)) {
				try {
					const urlObj = new URL(value);

					// Validate same-site
					if (urlObj.hostname !== siteHost) {
						this.value = '/';
						const message = 'You can only test your own site. Please enter a path from your domain.';
						if (window.WPShadowDesign && typeof window.WPShadowDesign.alert === 'function') {
							window.WPShadowDesign.alert('Invalid URL', message, 'warning');
						} else {
							alert(message);
						}
						return;
					}

					// Extract path + query
					const path = urlObj.pathname + urlObj.search;
					this.value = path || '/';
				} catch (e) {
					this.value = '/';
					const message = 'Invalid URL format. Please enter a valid path or URL.';
					if (window.WPShadowDesign && typeof window.WPShadowDesign.alert === 'function') {
						window.WPShadowDesign.alert('Invalid URL', message, 'warning');
					} else {
						alert(message);
					}
				}
			} else if (!value.startsWith('/')) {
				// Ensure path starts with /
				this.value = '/' + value;
			}
		});
	}

	function renderCheck(check) {
		const div = document.createElement('div');
		div.className = 'wpshadow-mobile-check';

		const title = document.createElement('h4');
		title.textContent = check.label || '';

		const status = document.createElement('div');
		const safeStatus = ['pass', 'warn', 'fail'].includes(check.status) ? check.status : 'warn';
		status.className = `wpshadow-mobile-status is-${safeStatus}`;
		status.textContent = safeStatus;

		const desc = document.createElement('p');
		desc.textContent = check.details || '';

		div.appendChild(title);
		div.appendChild(status);
		div.appendChild(desc);
		return div;
	}

	function updateSummary(summary) {
		if (pillPass) {
			pillPass.querySelector('strong').textContent = summary.pass ?? 0;
		}
		if (pillWarn) {
			pillWarn.querySelector('strong').textContent = summary.warn ?? 0;
		}
		if (pillFail) {
			pillFail.querySelector('strong').textContent = summary.fail ?? 0;
		}
	}

	function renderResults(payload) {
		if (!resultsWrap || !checksWrap) {
			return;
		}

		checksWrap.innerHTML = '';
		(payload.checks || []).forEach((check) => {
			checksWrap.appendChild(renderCheck(check));
		});

		updateSummary(payload.summary || {});

		if (lastUrl && payload.url) {
			lastUrl.textContent = payload.url;
		}

		resultsWrap.classList.remove('is-hidden');
	}

	form.addEventListener('submit', function(e) {
		e.preventDefault();
		clearError();
		setLoading(true);

		let path = (pathInput && pathInput.value) ? pathInput.value.trim() : '/';
		if (!path.startsWith('/')) {
			path = '/' + path;
		}

		// Reconstruct full URL
		const url = siteUrl + path;

		const formData = new FormData();
		formData.append('action', 'wpshadow_mobile_check');
		formData.append('nonce', settings.nonce || '');
		formData.append('url', url);

		fetch(settings.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: formData,
		})
			.then((response) => response.json())
			.then((data) => {
				if (!data || !data.success) {
					showError((data && data.data && data.data.message) || settings.i18nError);
					return;
				}
				renderResults(data.data || {});
			})
			.catch(() => {
				showError(settings.i18nError);
			})
			.finally(() => {
				setLoading(false);
			});
	});

	// Pre-fill path from settings or site origin.
	if (pathInput && !pathInput.value) {
		pathInput.value = '/';
	}
})();
