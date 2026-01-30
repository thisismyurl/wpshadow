(function() {
	'use strict';

	const settings = window.wpshadowVisualComparisons || {};
	const form = document.getElementById('wpshadow-visual-capture-form');
	if (!form) {
		return;
	}

	const urlInput = document.getElementById('wpshadow-visual-url');
	const labelInput = document.getElementById('wpshadow-visual-label');
	const captureBtn = document.getElementById('wpshadow-visual-capture-btn');
	const errorBox = document.getElementById('wpshadow-visual-error');
	const progressWrap = document.getElementById('wpshadow-visual-progress');
	const progressBar = document.getElementById('wpshadow-visual-progress-bar');
	const progressText = document.getElementById('wpshadow-visual-progress-text');
	const progressStatus = document.getElementById('wpshadow-visual-progress-status');
	const previewWrap = document.getElementById('wpshadow-visual-preview');
	const previewContent = document.getElementById('wpshadow-visual-preview-content');

	let progressInterval = null;

	function setLoading(state) {
		if (captureBtn) {
			captureBtn.disabled = state;
			if (state) {
				const icon = captureBtn.querySelector('.dashicons');
				if (icon) {
					icon.classList.add('wps-spin');
				}
				const textNode = Array.from(captureBtn.childNodes).find(node => node.nodeType === Node.TEXT_NODE);
				if (textNode) {
					textNode.textContent = settings.i18nCapturing || 'Capturing...';
				}
			} else {
				const icon = captureBtn.querySelector('.dashicons');
				if (icon) {
					icon.classList.remove('wps-spin');
				}
				const textNode = Array.from(captureBtn.childNodes).find(node => node.nodeType === Node.TEXT_NODE);
				if (textNode) {
					textNode.textContent = 'Capture Screenshot';
				}
			}
		}
	}

	function showProgress() {
		if (!progressWrap || !progressBar || !progressText || !progressStatus) {
			return;
		}
		
		progressBar.style.width = '0%';
		progressText.textContent = '0%';
		progressWrap.classList.remove('wps-none');
		
		// Simulate progress
		const stages = [
			{ percent: 20, text: settings.i18nCapturing || 'Loading page...' },
			{ percent: 50, text: 'Rendering viewport...' },
			{ percent: 80, text: 'Capturing screenshot...' },
			{ percent: 95, text: 'Saving image...' }
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
		}, 500);
	}

	function completeProgress() {
		clearInterval(progressInterval);
		if (progressBar && progressText && progressStatus) {
			progressBar.style.width = '100%';
			progressText.textContent = '100%';
			progressStatus.textContent = settings.i18nSuccess || 'Screenshot captured!';
			
			setTimeout(function() {
				if (progressWrap) {
					progressWrap.classList.add('wps-none');
				}
			}, 1000);
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
		if (previewWrap) {
			previewWrap.classList.add('wps-none');
		}
	}

	function clearError() {
		if (errorBox) {
			errorBox.classList.add('wps-none');
			errorBox.textContent = '';
		}
	}

	function showPreview(data) {
		if (!previewWrap || !previewContent) {
			return;
		}

		let html = '<div style="border: 1px solid #dcdcde; padding: 15px; background: #fff;">';
		
		if (data.screenshot_url) {
			html += '<img src="' + data.screenshot_url + '" alt="Screenshot" style="max-width: 100%; height: auto; display: block; margin-bottom: 15px;" />';
		}

		html += '<div style="display: flex; gap: 15px; flex-wrap: wrap;">';
		html += '<div><strong>URL:</strong> <a href="' + data.page_url + '" target="_blank">' + data.page_url + '</a></div>';
		
		if (data.label) {
			html += '<div><strong>Label:</strong> ' + data.label + '</div>';
		}
		
		if (data.timestamp) {
			html += '<div><strong>Captured:</strong> ' + data.timestamp + '</div>';
		}
		
		html += '</div></div>';

		previewContent.innerHTML = html;
		previewWrap.classList.remove('wps-none');
	}

	// Auto-clean URLs pasted into URL field
	if (urlInput) {
		urlInput.addEventListener('blur', function() {
			let value = this.value.trim();

			// If it looks like a full URL, extract the path
			if (value.match(/^https?:\/\//i)) {
				try {
					const urlObj = new URL(value);
					const siteUrlObj = new URL(settings.defaultUrl);

					// Validate same-site
					if (urlObj.hostname !== siteUrlObj.hostname) {
						this.value = '/';
						alert('You can only capture screenshots of your own site.');
						return;
					}

					// Extract path + query
					const path = urlObj.pathname + urlObj.search;
					this.value = path || '/';
				} catch (e) {
					this.value = '/';
					alert('Invalid URL format. Please enter a valid path or URL.');
				}
			} else if (!value.startsWith('/')) {
				// Ensure path starts with /
				this.value = '/' + value;
			}
		});
	}

	form.addEventListener('submit', function(e) {
		e.preventDefault();
		clearError();
		setLoading(true);
		showProgress();

		if (previewWrap) {
			previewWrap.classList.add('wps-none');
		}

		let path = (urlInput && urlInput.value) ? urlInput.value.trim() : '/';
		if (!path.startsWith('/')) {
			path = '/' + path;
		}

		// Reconstruct full URL
		const url = settings.defaultUrl.replace(/\/$/, '') + path;
		const label = labelInput ? labelInput.value.trim() : '';

		const formData = new FormData();
		formData.append('action', 'wpshadow_capture_screenshot');
		formData.append('nonce', settings.nonce || '');
		formData.append('url', url);
		if (label) {
			formData.append('label', label);
		}

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
					showPreview(data.data || {});
					
					// Clear label input for next capture
					if (labelInput) {
						labelInput.value = '';
					}
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
})();
