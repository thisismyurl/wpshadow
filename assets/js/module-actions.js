/**
 * Module actions JavaScript handler.
 *
 * Handles AJAX calls for install, update, activate, and deactivate actions.
 * @since 1.2601.73000
 */

(function () {
	'use strict';

	// Ensure we have the required globals.
	if (typeof wpsModuleActions === 'undefined') {
		return;
	}

	const { ajaxurl, nonce } = wpsModuleActions;
	const actionButtons = {
		install: '.wps-btn-install-activate',
		update: '.wps-btn-update',
		activate: '.wps-activate',
		deactivate: '.wps-deactivate-network',
	};

	/**
	 * Show progress UI for action.
	 *
	 * @param {HTMLElement} button Button element.
	 * @param {string} message Status message.
	 * @param {number} percent Progress percent (0-100, optional).
	 */
	function showProgress(button, message, percent = null) {
		const originalText = button.textContent;
		button.disabled = true;
		button.classList.add('wps-loading');

		// Show progress with percentage if provided.
		if (percent !== null && !isNaN(percent)) {
			button.textContent = `${message} (${Math.round(percent)}%)`;
		} else {
			button.textContent = message;
		}

		return {
			update: (newMessage, newPercent = null) => {
				if (newPercent !== null && !isNaN(newPercent)) {
					button.textContent = `${newMessage} (${Math.round(newPercent)}%)`;
				} else {
					button.textContent = newMessage;
				}
			},
			restore: () => {
				button.disabled = false;
				button.classList.remove('wps-loading');
				button.textContent = originalText;
			},
		};
	}

	/**
	 * Show error notice.
	 *
	 * @param {string} message Error message.
	 */
	function showError(message) {
		const notice = document.createElement('div');
		notice.className = 'notice notice-error is-dismissible';
		notice.innerHTML = `
			<p>${escapeHtml(message)}</p>
			<button type="button" class="notice-dismiss">
				<span class="screen-reader-text">Dismiss this notice.</span>
			</button>
		`;

		const container = document.querySelector('.wrap') || document.body;
		container.insertBefore(notice, container.firstChild);

		// Auto-dismiss after 5 seconds.
		setTimeout(() => {
			notice.style.opacity = '0';
			setTimeout(() => notice.remove(), 300);
		}, 5000);
	}

	/**
	 * Show success notice.
	 *
	 * @param {string} message Success message.
	 */
	function showSuccess(message) {
		const notice = document.createElement('div');
		notice.className = 'notice notice-success is-dismissible';
		notice.innerHTML = `
			<p>${escapeHtml(message)}</p>
			<button type="button" class="notice-dismiss">
				<span class="screen-reader-text">Dismiss this notice.</span>
			</button>
		`;

		const container = document.querySelector('.wrap') || document.body;
		container.insertBefore(notice, container.firstChild);

		// Auto-dismiss after 3 seconds.
		setTimeout(() => {
			notice.style.opacity = '0';
			setTimeout(() => notice.remove(), 300);
		}, 3000);
	}

	/**
	 * Escape HTML special characters.
	 *
	 * @param {string} text Text to escape.
	 * @returns {string} Escaped text.
	 */
	function escapeHtml(text) {
		const map = {
			'&': '&amp;',
			'<': '&lt;',
			'>': '&gt;',
			'"': '&quot;',
			"'": '&#039;',
		};
		return text.replace(/[&<>"']/g, (m) => map[m]);
	}

	/**
	 * Update row status after action.
	 *
	 * @param {string} slug Module slug.
	 * @param {Object} status Module status data.
	 */
	function updateRowStatus(slug, status) {
		const row = document.querySelector(`[data-group="${slug}"]`);
		if (!row) return;

		// Update status class.
		row.classList.remove('wps-module-available', 'wps-module-enabled', 'wps-module-disabled');

		if (status.installed) {
			row.classList.add(status.enabled ? 'wps-module-enabled' : 'wps-module-disabled');
			row.dataset.status = status.update_available ? 'update' : 'installed';
		} else {
			row.classList.add('wps-module-available');
			row.dataset.status = 'available';
		}
	}

	function toggleSubmenu(slug, enabled) {
		const hrefContains = `page=wp-support&module=${slug.replace('-support-thisismyurl','')}`;
		const menus = document.querySelectorAll(`#adminmenu a[href*="${hrefContains}"]`);
		menus.forEach((link) => {
			const li = link.closest('li');
			if (!li) return;
			li.style.display = enabled ? '' : 'none';
		});
	}

	/**
	 * Poll download progress for a session.
	 *
	 * @param {string} sessionId Session ID.
	 * @param {Object} progress Progress UI object.
	 * @returns {Promise} Promise that resolves when polling completes.
	 */
	async function pollDownloadProgress(sessionId, progress) {
		const maxAttempts = 60; // Poll for up to 60 seconds.
		let attempts = 0;

		return new Promise((resolve, reject) => {
			const interval = setInterval(async () => {
				attempts++;

				try {
					const response = await fetch(ajaxurl, {
						method: 'POST',
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded',
						},
						body: new URLSearchParams({
							action: 'wps_module_download_progress',
							nonce: nonce,
							session_id: sessionId,
						}),
					});

					const data = await response.json();

					if (data.success && data.data) {
						const { percent, status, message } = data.data;

						// Update UI with progress.
						progress.update(message || 'Downloading...', percent);

						// Stop polling if complete or failed.
						if (status === 'complete' || status === 'failed') {
							clearInterval(interval);
							resolve({ status, message });
						}
					}

					// Stop after max attempts.
					if (attempts >= maxAttempts) {
						clearInterval(interval);
						resolve({ status: 'timeout', message: 'Progress polling timed out' });
					}
				} catch (error) {
					// Ignore polling errors, continue.
					console.warn('Progress polling error:', error);
				}
			}, 1000); // Poll every second.
		});
	}

	/**
	 * Handle install and activate action.
	 *
	 * @param {Event} e Click event.
	 */
	async function handleInstall(e) {
		const button = e.target.closest(actionButtons.install);
		if (!button) return;
		e.preventDefault();

		const slug = button.dataset.slug;
		if (!slug) return;

		const progress = showProgress(button, 'Starting installation...', 0);

		try {
			const response = await fetch(ajaxurl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: new URLSearchParams({
					action: 'wps_module_install',
					nonce: nonce,
					slug: slug,
				}),
			});

			const data = await response.json();

			if (data.success) {
				showSuccess(data.data.message);
				updateRowStatus(slug, data.data.status);
				// Reload page after a short delay for visual confirmation.
				setTimeout(() => location.reload(), 1500);
			} else {
				showError(data.data.message || 'Installation failed.');
				progress.restore();
			}
		} catch (error) {
			showError('Network error: ' + error.message);
			progress.restore();
		}
	}

	/**
	 * Handle update action.
	 *
	 * @param {Event} e Click event.
	 */
	async function handleUpdate(e) {
		const button = e.target.closest(actionButtons.update);
		if (!button) return;
		e.preventDefault();

		const slug = button.dataset.slug;
		if (!slug) return;

		const progress = showProgress(button, 'Starting update...', 0);

		try {
			const response = await fetch(ajaxurl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: new URLSearchParams({
					action: 'wps_module_update',
					nonce: nonce,
					slug: slug,
				}),
			});

			const data = await response.json();

			if (data.success) {
				showSuccess(data.data.message);
				updateRowStatus(slug, data.data.status);
				// Reload page after a short delay for visual confirmation.
				setTimeout(() => location.reload(), 1500);
			} else {
				showError(data.data.message || 'Update failed.');
				progress.restore();
			}
		} catch (error) {
			showError('Network error: ' + error.message);
			progress.restore();
		}
	}

	/**
	 * Handle activate action.
	 *
	 * @param {Event} e Click event.
	 */
	async function handleActivate(e) {
		const button = e.target.closest(actionButtons.activate);
		if (!button) return;
		e.preventDefault();

		const slug = button.dataset.slug;
		if (!slug) return;

		const progress = showProgress(button, 'Activating...');

		try {
			const response = await fetch(ajaxurl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: new URLSearchParams({
					action: 'wps_module_toggle',
					nonce: nonce,
					slug: slug,
					enabled: 1,
				}),
			});

			const data = await response.json();

			if (data.success) {
				showSuccess(data.data.message || 'Module enabled.');
				updateRowStatus(slug, data.data.status || {});
				toggleSubmenu(slug, true);
					// Avoid reload to prevent flicker; submenu is toggled client-side.
			} else {
				showError(data.data.message || 'Activation failed.');
				progress.restore();
			}
		} catch (error) {
			showError('Network error: ' + error.message);
			progress.restore();
		}
	}

	/**
	 * Handle deactivate action (network scope).
	 *
	 * @param {Event} e Click event.
	 */
	async function handleDeactivate(e) {
		const button = e.target.closest(actionButtons.deactivate);
		if (!button) return;
		e.preventDefault();

		const slug = button.dataset.slug;
		if (!slug) return;

		// Confirm deactivation.
		if (!confirm('Deactivate this module network-wide?')) {
			return;
		}

		const progress = showProgress(button, 'Deactivating...');

		try {
			const response = await fetch(ajaxurl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: new URLSearchParams({
					action: 'wps_module_toggle',
					nonce: nonce,
					slug: slug,
					enabled: 0,
				}),
			});

			const data = await response.json();

			if (data.success) {
				showSuccess(data.data.message || 'Module disabled.');
				updateRowStatus(slug, data.data.status || {});
				toggleSubmenu(slug, false);
					// Avoid reload to prevent flicker; submenu is toggled client-side.
			} else {
				showError(data.data.message || 'Deactivation failed.');
				progress.restore();
			}
		} catch (error) {
			showError('Network error: ' + error.message);
			progress.restore();
		}
	}

	/**
	 * Initialize event listeners.
	 */
	function init() {
		document.addEventListener('click', handleInstall);
		document.addEventListener('click', handleUpdate);
		document.addEventListener('click', handleActivate);
		document.addEventListener('click', handleDeactivate);
	}

	// Initialize when DOM is ready.
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();

