(function() {
	'use strict';

	const settings = window.wpshadowSiteDNA || {};
	const form = document.getElementById('wpshadow-dna-form');
	if (!form) {
		return;
	}

	const submitBtn = document.getElementById('wpshadow-dna-submit-btn');
	const errorBox = document.getElementById('wpshadow-dna-error');
	const resultsWrap = document.getElementById('wpshadow-dna-results');
	const progressWrap = document.getElementById('wpshadow-dna-progress');
	const progressBar = document.getElementById('wpshadow-dna-progress-bar');
	const progressText = document.getElementById('wpshadow-dna-progress-text');
	const progressStatus = document.getElementById('wpshadow-dna-progress-status');

	let progressInterval = null;

	function setLoading(state) {
		if (submitBtn) {
			submitBtn.disabled = state;
			if (state) {
				const icon = submitBtn.querySelector('.dashicons');
				if (icon) {
					icon.classList.add('wps-spin');
				}
			} else {
				const icon = submitBtn.querySelector('.dashicons');
				if (icon) {
					icon.classList.remove('wps-spin');
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
		
		const stages = [
			{ percent: 5, text: settings.i18nStage1 || 'Initializing diagnostic scan...' },
			{ percent: 15, text: settings.i18nStage2 || 'Analyzing security posture...' },
			{ percent: 30, text: settings.i18nStage3 || 'Evaluating performance metrics...' },
			{ percent: 45, text: settings.i18nStage4 || 'Checking accessibility compliance...' },
			{ percent: 60, text: settings.i18nStage5 || 'Assessing code quality...' },
			{ percent: 75, text: settings.i18nStage6 || 'Measuring UX excellence...' },
			{ percent: 90, text: settings.i18nStage7 || 'Computing DNA signature...' },
			{ percent: 95, text: settings.i18nStage8 || 'Generating visualizations...' }
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
		}, 1500);
	}

	function completeProgress() {
		clearInterval(progressInterval);
		if (progressBar && progressText && progressStatus) {
			progressBar.style.width = '100%';
			progressText.textContent = '100%';
			progressStatus.textContent = settings.i18nComplete || 'Analysis complete!';
			
			setTimeout(function() {
				if (progressWrap) {
					progressWrap.classList.add('wps-none');
				}
			}, 1000);
		}
	}

	function showError(message) {
		if (errorBox) {
			errorBox.textContent = message;
			errorBox.classList.remove('wps-none');
			setTimeout(function() {
				errorBox.classList.add('wps-none');
			}, 8000);
		}
	}

	function renderDNAVisualization(data) {
		const canvas = document.getElementById('wpshadow-dna-canvas');
		if (!canvas) return;

		const ctx = canvas.getContext('2d');
		const centerX = canvas.width / 2;
		const centerY = canvas.height / 2;

		// Clear canvas
		ctx.clearRect(0, 0, canvas.width, canvas.height);

		// Draw DNA helix (simplified visualization)
		const helixPoints = 50;
		const amplitude = 80;
		const wavelength = canvas.width / 3;

		ctx.lineWidth = 3;
		
		// Draw first strand
		ctx.beginPath();
		ctx.strokeStyle = '#0073aa';
		for (let i = 0; i < helixPoints; i++) {
			const x = (i / helixPoints) * canvas.width;
			const y = centerY + amplitude * Math.sin((x / wavelength) * Math.PI * 2);
			if (i === 0) {
				ctx.moveTo(x, y);
			} else {
				ctx.lineTo(x, y);
			}
		}
		ctx.stroke();

		// Draw second strand
		ctx.beginPath();
		ctx.strokeStyle = '#00a32a';
		for (let i = 0; i < helixPoints; i++) {
			const x = (i / helixPoints) * canvas.width;
			const y = centerY - amplitude * Math.sin((x / wavelength) * Math.PI * 2);
			if (i === 0) {
				ctx.moveTo(x, y);
			} else {
				ctx.lineTo(x, y);
			}
		}
		ctx.stroke();

		// Draw connecting bars with color coding
		ctx.lineWidth = 2;
		for (let i = 0; i < helixPoints; i += 3) {
			const x = (i / helixPoints) * canvas.width;
			const y1 = centerY + amplitude * Math.sin((x / wavelength) * Math.PI * 2);
			const y2 = centerY - amplitude * Math.sin((x / wavelength) * Math.PI * 2);
			
			// Color based on health (simplified)
			const healthScore = data.overall_score || 0;
			if (healthScore >= 80) {
				ctx.strokeStyle = '#00a32a';
			} else if (healthScore >= 60) {
				ctx.strokeStyle = '#d98300';
			} else {
				ctx.strokeStyle = '#dc3232';
			}
			
			ctx.beginPath();
			ctx.moveTo(x, y1);
			ctx.lineTo(x, y2);
			ctx.stroke();
		}
	}

	function renderResults(data) {
		if (!resultsWrap) return;

		// Show overall score
		const scoreDisplay = document.getElementById('wpshadow-dna-overall-score');
		if (scoreDisplay) {
			const score = Math.round(data.overall_score || 0);
			const grade = score >= 90 ? 'A+' : score >= 80 ? 'A' : score >= 70 ? 'B' : score >= 60 ? 'C' : 'D';
			scoreDisplay.innerHTML = `
				<div class="wpshadow-dna-overall-score">${score}</div>
				<div style="font-size: 24px; color: #50575e; margin-top: 10px;">Overall Health Grade: ${grade}</div>
				<div style="font-size: 14px; color: #7e8993; margin-top: 5px;">${data.diagnostics_checked || 0} diagnostic checks completed</div>
			`;
		}

		// Render DNA visualization
		renderDNAVisualization(data);

		// Render category scores
		const categoriesContainer = document.getElementById('wpshadow-dna-categories');
		if (categoriesContainer && data.categories) {
			categoriesContainer.innerHTML = '';
			Object.keys(data.categories).forEach(function(categoryKey) {
				const category = data.categories[categoryKey];
				const score = Math.round(category.score || 0);
				const colorClass = score >= 80 ? 'good' : score >= 60 ? 'warning' : 'critical';
				
				const categoryEl = document.createElement('div');
				categoryEl.className = 'wpshadow-dna-category';
				categoryEl.innerHTML = `
					<div class="wpshadow-dna-category-score" style="color: ${score >= 80 ? '#00a32a' : score >= 60 ? '#d98300' : '#dc3232'}">${score}</div>
					<div class="wpshadow-dna-category-label">${category.label || categoryKey}</div>
					<div style="font-size: 12px; color: #7e8993; margin-top: 5px;">${category.checks_run || 0} checks</div>
				`;
				categoriesContainer.appendChild(categoryEl);
			});
		}

		// Render insights
		const insightsContainer = document.getElementById('wpshadow-dna-insights');
		if (insightsContainer && data.insights) {
			insightsContainer.innerHTML = '';
			data.insights.forEach(function(insight) {
				const insightEl = document.createElement('div');
				insightEl.className = 'wps-info-box wps-info-box-' + (insight.type || 'info') + ' wps-mb-3';
				insightEl.innerHTML = `
					<strong>${insight.title || ''}</strong>
					<p class="wps-mb-0">${insight.message || ''}</p>
				`;
				insightsContainer.appendChild(insightEl);
			});
		}

		resultsWrap.classList.remove('wps-none');
	}

	form.addEventListener('submit', function(e) {
		e.preventDefault();
		
		if (submitBtn.disabled) {
			return;
		}

		const formData = new FormData(form);
		formData.append('action', 'wpshadow_generate_dna');
		formData.append('nonce', settings.nonce || '');

		setLoading(true);
		showProgress();
		
		if (resultsWrap) {
			resultsWrap.classList.add('wps-none');
		}

		fetch(settings.ajaxUrl, {
			method: 'POST',
			body: formData,
			credentials: 'same-origin'
		})
		.then(function(response) {
			return response.json();
		})
		.then(function(response) {
			completeProgress();
			setLoading(false);

			if (response.success && response.data) {
				renderResults(response.data);
			} else {
				showError(response.data?.message || settings.i18nError || 'Failed to generate DNA report. Please try again.');
			}
		})
		.catch(function(error) {
			completeProgress();
			setLoading(false);
			showError(settings.i18nError || 'An error occurred. Please try again.');
			console.error('DNA Report Error:', error);
		});
	});

	// Export PDF handler
	const exportPdfBtn = document.getElementById('wpshadow-dna-export-pdf');
	if (exportPdfBtn) {
		exportPdfBtn.addEventListener('click', function() {
			window.WPShadowModal.alert({
				title: 'Coming Soon',
				message: 'PDF export feature coming soon!',
				type: 'info'
			});
		});
	}

	// Share badge handler
	const shareBadgeBtn = document.getElementById('wpshadow-dna-share-badge');
	if (shareBadgeBtn) {
		shareBadgeBtn.addEventListener('click', function() {
			window.WPShadowModal.alert({
				title: 'Coming Soon',
				message: 'Shareable badge feature coming soon!',
				type: 'info'
			});
		});
	}

	// Historical comparison handler
	const compareHistoricalBtn = document.getElementById('wpshadow-dna-compare-historical');
	if (compareHistoricalBtn) {
		compareHistoricalBtn.addEventListener('click', function() {
			window.WPShadowModal.alert({
				title: 'Coming Soon',
				message: 'Historical comparison feature coming soon!',
				type: 'info'
			});
		});
	}
})();
