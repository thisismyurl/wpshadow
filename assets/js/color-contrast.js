(function() {
	'use strict';

	const data = window.wpshadowContrast || {};
	const form = document.getElementById('wpshadow-contrast-form');
	if (!form) {
		return;
	}

	const textInput = form.querySelector('[name="text_color"]');
	const bgInput = form.querySelector('[name="background_color"]');
	const textSize = form.querySelector('[name="text_size"]');
	const errorNotice = document.getElementById('wpshadow-contrast-error');
	const results = document.getElementById('wpshadow-contrast-results');
	const ratioEl = document.getElementById('wpshadow-contrast-ratio');
	const preview = document.getElementById('wpshadow-contrast-preview');
	const previewText = document.getElementById('wpshadow-contrast-preview-text');
	const badges = document.querySelectorAll('[data-contrast-badge]');
	const sampleButtons = document.querySelectorAll('[data-text-color][data-bg-color]');
	const themeScanBtn = document.getElementById('wpshadow-contrast-theme-scan');
	const themeList = document.getElementById('wpshadow-contrast-theme-list');
	const themeSection = document.getElementById('wpshadow-contrast-theme-section');
	const themeBgLabel = document.getElementById('wpshadow-contrast-theme-bg');
	const themeContextList = document.getElementById('wpshadow-contrast-theme-contexts');

	function showError(message) {
		if (!errorNotice) {
			return;
		}
		errorNotice.style.display = 'block';
		errorNotice.textContent = message || data.i18nInvalid || 'Please enter valid hex colors.';
	}

	function hideError() {
		if (errorNotice) {
			errorNotice.style.display = 'none';
		}
	}

	function hexToRgb(hex) {
		const normalized = hex.trim().replace('#', '');
		if (normalized.length !== 6) {
			return null;
		}
		const intVal = parseInt(normalized, 16);
		if (Number.isNaN(intVal)) {
			return null;
		}
		return {
			r: (intVal >> 16) & 255,
			g: (intVal >> 8) & 255,
			b: intVal & 255,
		};
	}

	function relativeLuminance(rgb) {
		const transform = (channel) => {
			const v = channel / 255;
			return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
		};
		return 0.2126 * transform(rgb.r) + 0.7152 * transform(rgb.g) + 0.0722 * transform(rgb.b);
	}

	function contrastRatio(fg, bg) {
		const l1 = relativeLuminance(fg);
		const l2 = relativeLuminance(bg);
		const lighter = Math.max(l1, l2);
		const darker = Math.min(l1, l2);
		return (lighter + 0.05) / (darker + 0.05);
	}

	function updateBadges(ratio, isLarge) {
		const thresholds = {
			aaNormal: 4.5,
			aaLarge: 3,
			aaaNormal: 7,
			aaaLarge: 4.5,
		};

		badges.forEach((badge) => {
			const key = badge.getAttribute('data-contrast-badge');
			let pass = false;

			switch (key) {
				case 'aa':
					pass = ratio >= (isLarge ? thresholds.aaLarge : thresholds.aaNormal);
					break;
				case 'aaa':
					pass = ratio >= (isLarge ? thresholds.aaaLarge : thresholds.aaaNormal);
					break;
				default:
					pass = false;
			}

			badge.classList.toggle('is-pass', pass);
			badge.classList.toggle('is-fail', !pass);
			badge.textContent = pass ? (data.i18nPass || 'Pass') : (data.i18nFail || 'Fail');
		});
	}

	function updatePreview(textColor, bgColor, ratio) {
		if (preview) {
			preview.style.color = textColor;
			preview.style.backgroundColor = bgColor;
		}
		if (previewText) {
			const ratioLabel = data.i18nRatioLabel || 'Contrast ratio';
			previewText.textContent = `${ratioLabel}: ${ratio}`;
		}
	}

	function handleSubmit(event) {
		event.preventDefault();
		hideError();

		const textColor = textInput.value;
		const bgColor = bgInput.value;
		const textRgb = hexToRgb(textColor);
		const bgRgb = hexToRgb(bgColor);

		if (!textRgb || !bgRgb) {
			showError();
			return;
		}

		const ratio = contrastRatio(textRgb, bgRgb);
		const rounded = ratio.toFixed(2);

		if (ratioEl) {
			ratioEl.textContent = rounded;
		}

		updatePreview(textColor, bgColor, rounded);
		updateBadges(ratio, textSize && textSize.value === 'large');

		if (results) {
			results.classList.remove('is-hidden');
		}
	}

	form.addEventListener('submit', handleSubmit);

	sampleButtons.forEach((btn) => {
		btn.addEventListener('click', function() {
			textInput.value = this.dataset.textColor;
			bgInput.value = this.dataset.bgColor;
			form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
		});
	});

	function renderThemeItem(item) {
		const li = document.createElement('li');
		li.className = 'wpshadow-theme-color';

		const swatch = document.createElement('span');
		swatch.className = 'wpshadow-theme-swatch';
		swatch.style.backgroundColor = item.color;

		const label = document.createElement('div');
		label.className = 'wpshadow-theme-label';
		label.textContent = item.name || item.slug || item.color;

		const ratio = document.createElement('div');
		ratio.className = 'wpshadow-theme-ratio';
		ratio.textContent = `${item.ratio || '--'}:1`;

		const status = document.createElement('span');
		const safeStatus = ['pass', 'warn', 'fail'].includes(item.status) ? item.status : 'warn';
		status.className = `wpshadow-theme-status is-${safeStatus}`;
		status.textContent = safeStatus;

		li.appendChild(swatch);
		li.appendChild(label);
		li.appendChild(ratio);
		li.appendChild(status);
		return li;
	}

	function renderContextItem(item) {
		const li = document.createElement('li');
		li.className = 'wpshadow-theme-context';

		const title = document.createElement('div');
		title.className = 'wpshadow-theme-label';
		title.textContent = item.label || '';

		const detail = document.createElement('div');
		detail.className = 'wpshadow-theme-ratio';
		detail.textContent = `${item.ratio || '--'}:1 (${item.fg || ''} on ${item.bg || ''})`;

		const status = document.createElement('span');
		const safeStatus = ['pass', 'warn', 'fail'].includes(item.status) ? item.status : 'warn';
		status.className = `wpshadow-theme-status is-${safeStatus}`;
		status.textContent = safeStatus;

		li.appendChild(title);
		li.appendChild(detail);
		li.appendChild(status);
		return li;
	}

	function renderThemeReport(payload) {
		if (!themeSection || !themeList) {
			return;
		}

		themeList.innerHTML = '';
		(payload.colors || []).forEach((item) => {
			themeList.appendChild(renderThemeItem(item));
		});

		if (themeBgLabel && payload.background) {
			themeBgLabel.textContent = `${data.i18nThemeBg || 'Background'}: ${payload.background}`;
		}

		if (themeContextList) {
			themeContextList.innerHTML = '';
			(payload.contexts || []).forEach((ctx) => {
				themeContextList.appendChild(renderContextItem(ctx));
			});
		}

		themeSection.classList.remove('is-hidden');
	}

	function scanTheme() {
		if (!data.ajaxUrl || !data.themeNonce || !themeScanBtn) {
			return;
		}

		themeScanBtn.disabled = true;
		const originalLabel = themeScanBtn.textContent;
		themeScanBtn.textContent = data.i18nThemeScan || 'Scan Active Theme';

		const body = new FormData();
		body.append('action', 'wpshadow_theme_contrast');
		body.append('nonce', data.themeNonce);

		fetch(data.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body,
		})
			.then((res) => res.json())
			.then((json) => {
				if (!json || !json.success) {
					alert((json && json.data && json.data.message) || data.i18nThemeError || 'Theme scan failed.');
					return;
				}
				renderThemeReport(json.data || {});
			})
			.catch(() => {
				alert(data.i18nThemeError || 'Theme scan failed.');
			})
			.finally(() => {
				themeScanBtn.disabled = false;
				themeScanBtn.textContent = originalLabel;
			});
	}

	if (themeScanBtn) {
		themeScanBtn.addEventListener('click', function(e) {
			e.preventDefault();
			scanTheme();
		});
	}
})();
