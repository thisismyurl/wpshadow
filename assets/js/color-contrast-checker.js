(function() {
	'use strict';

	const settings = window.wpshadowColorContrast || {};
	const form = document.getElementById('wpshadow-contrast-form');
	if (!form) {
		return;
	}

	// Get site URL and host for validation
	const siteUrl = settings.defaultUrl || window.location.origin;
	const siteUrlObj = new URL(siteUrl);
	const siteHost = siteUrlObj.hostname;

	const pathInput = document.getElementById('wpshadow-contrast-path');
	const submitBtn = document.getElementById('wpshadow-contrast-submit-btn');
	const errorBox = document.getElementById('wpshadow-contrast-error');
	const resultsWrap = document.getElementById('wpshadow-contrast-results');
	const checksWrap = document.getElementById('wpshadow-contrast-checks');
	const progressWrap = document.getElementById('wpshadow-contrast-progress');
	const progressBar = document.getElementById('wpshadow-contrast-progress-bar');
	const progressText = document.getElementById('wpshadow-contrast-progress-text');
	const progressStatus = document.getElementById('wpshadow-contrast-progress-status');
	const pillPass = document.querySelector('[data-contrast-summary="pass"]');
	const pillWarn = document.querySelector('[data-contrast-summary="warn"]');
	const pillFail = document.querySelector('[data-contrast-summary="fail"]');
	const lastUrl = document.getElementById('wpshadow-contrast-last-url');

	let progressInterval = null;

	function setLoading(state) {
		if (submitBtn) {
			submitBtn.disabled = state;
			if (state) {
				const icon = submitBtn.querySelector('.dashicons');
				if (icon) {
					icon.classList.add('wps-spin');
				}
				const textNode = Array.from(submitBtn.childNodes).find(node => node.nodeType === Node.TEXT_NODE);
				if (textNode) {
					textNode.textContent = settings.i18nRunning || 'Checking...';
				}
			} else {
				const icon = submitBtn.querySelector('.dashicons');
				if (icon) {
					icon.classList.remove('wps-spin');
				}
				const textNode = Array.from(submitBtn.childNodes).find(node => node.nodeType === Node.TEXT_NODE);
				if (textNode) {
					textNode.textContent = settings.i18nRun || 'Check Contrast';
				}
			}
		}
	}

	function showProgress() {
		if (!progressWrap || !progressBar || !progressText || !progressStatus) {
			return;
		}
		
		// Reset
		progressBar.style.width = '0%';
		progressText.textContent = '0%';
		progressWrap.classList.remove('wps-none');
		
		// Simulate progress stages
		const stages = [
			{ percent: 10, text: settings.i18nStage1 || 'Fetching page content...' },
			{ percent: 30, text: settings.i18nStage2 || 'Extracting color styles...' },
			{ percent: 50, text: settings.i18nStage3 || 'Analyzing text elements...' },
			{ percent: 70, text: settings.i18nStage4 || 'Calculating contrast ratios...' },
			{ percent: 85, text: settings.i18nStage5 || 'Checking WCAG compliance...' },
			{ percent: 95, text: settings.i18nStage6 || 'Compiling results...' }
		];
		
		let currentStage = 0;
		clearInterval(progressInterval);
		progressInterval = setInterval(function() {
			if (currentStage < stages.length) {
				const stage = stages[currentStage];
				progressBar.style.width = stage.percent + '%';
				progressText.textContent = stage.percent + '%';
				progressStatus.textContent = stage.text;
				currentStage++;
			}
		}, 800);
	}

	function completeProgress() {
		clearInterval(progressInterval);
		if (progressBar && progressText && progressStatus) {
			progressBar.style.width = '100%';
			progressText.textContent = '100%';
			progressStatus.textContent = settings.i18nComplete || 'Scan complete!';
			
			setTimeout(function() {
				if (progressWrap) {
					progressWrap.classList.add('wps-none');
				}
			}, 500);
		}
	}

	function showError(message) {
		clearInterval(progressInterval);
		if (progressWrap) {
			progressWrap.classList.add('wps-none');
		}
		if (!errorBox) {
			return;
		}
		errorBox.classList.remove('wps-none');
		errorBox.textContent = message || settings.i18nError || 'Something went wrong. Please try again.';
		if (resultsWrap) {
			resultsWrap.classList.add('wps-none');
		}
	}

	function clearError() {
		if (errorBox) {
			errorBox.classList.add('wps-none');
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
		div.className = 'wpshadow-contrast-check';

		const title = document.createElement('h4');
		title.textContent = check.label || '';

		const status = document.createElement('div');
		const safeStatus = ['pass', 'warn', 'fail'].includes(check.status) ? check.status : 'warn';
		status.className = `wpshadow-contrast-status is-${safeStatus}`;
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

		resultsWrap.classList.remove('wps-none');
	}

	form.addEventListener('submit', function(e) {
		e.preventDefault();
		clearError();
		setLoading(true);
		showProgress();

		if (resultsWrap) {
			resultsWrap.classList.add('wps-none');
		}

		let path = (pathInput && pathInput.value) ? pathInput.value.trim() : '/';
		if (!path.startsWith('/')) {
			path = '/' + path;
		}

		// Reconstruct full URL
		const url = siteUrl + path;

		const formData = new FormData();
		formData.append('action', 'wpshadow_contrast_check');
		formData.append('nonce', settings.nonce || '');
		formData.append('url', url);

		fetch(settings.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: formData,
		})
			.then((response) => response.json())
			.then((data) => {
				completeProgress();
				if (!data || !data.success) {
					showError((data && data.data && data.data.message) || settings.i18nError);
					return;
				}
				setTimeout(function() {
					renderResults(data.data || {});
				}, 500);
			})
			.catch(() => {
				completeProgress();
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
