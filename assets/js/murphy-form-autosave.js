/**
 * Murphy-Safe Form Auto-Save
 *
 * Automatically saves form data to localStorage every 5 seconds.
 * Prompts user to restore unsaved changes on page load.
 * Implements Murphy's Law: "Users will close browsers at the worst possible moment."
 *
 * Philosophy Alignment:
 * - ⚙️ Murphy's Law: Assume browser will crash mid-form
 * - #8 Inspire Confidence: Users trust their work is protected
 * - #1 Helpful Neighbor: Graceful recovery from browser issues
 *
 * @package    WPShadow
 * @subpackage Assets
 * @since      1.6035.1515
 */

(function() {
	'use strict';

	const AUTOSAVE_KEY_PREFIX = 'wpshadow_form_autosave_';
	const AUTOSAVE_INTERVAL = 5000; // 5 seconds
	const MAX_AUTOSAVE_AGE = 24 * 60 * 60 * 1000; // 24 hours

	/**
	 * Get unique form identifier
	 *
	 * @param {HTMLFormElement} form Form element.
	 * @return {string} Unique identifier.
	 */
	function getFormId(form) {
		// Use form ID if available.
		if (form.id) {
			return form.id;
		}

		// Otherwise use form action or data-autosave-id attribute.
		if (form.dataset.autosaveId) {
			return form.dataset.autosaveId;
		}

		// Fall back to form action URL.
		const action = form.getAttribute('action') || window.location.pathname;
		return btoa(action).substring(0, 20);
	}

	/**
	 * Get autosave storage key for form
	 *
	 * @param {HTMLFormElement} form Form element.
	 * @return {string} Storage key.
	 */
	function getStorageKey(form) {
		return AUTOSAVE_KEY_PREFIX + getFormId(form);
	}

	/**
	 * Extract form data
	 *
	 * @param {HTMLFormElement} form Form element.
	 * @return {Object} Form data.
	 */
	function extractFormData(form) {
		const data = {
			timestamp: new Date().toISOString(),
			fields: {}
		};

		// Get all form fields.
		const fields = form.querySelectorAll('input, textarea, select');

		fields.forEach(field => {
			// Skip fields without name.
			if (!field.name) {
				return;
			}

			// Skip password fields (security).
			if (field.type === 'password') {
				return;
			}

			// Skip nonce fields (they expire).
			if (field.name.includes('nonce') || field.name.includes('_wpnonce')) {
				return;
			}

			// Handle checkboxes and radios.
			if (field.type === 'checkbox' || field.type === 'radio') {
				if (field.checked) {
					data.fields[field.name] = field.value;
				}
			}
			// Handle multi-select.
			else if (field.tagName === 'SELECT' && field.multiple) {
				const selected = Array.from(field.selectedOptions).map(opt => opt.value);
				data.fields[field.name] = selected;
			}
			// Handle regular fields.
			else {
				data.fields[field.name] = field.value;
			}
		});

		return data;
	}

	/**
	 * Save form data to localStorage
	 *
	 * @param {HTMLFormElement} form Form element.
	 * @return {boolean} Success.
	 */
	function saveFormData(form) {
		try {
			const data = extractFormData(form);
			const key = getStorageKey(form);

			// Check if form has changed data.
			if (Object.keys(data.fields).length === 0) {
				return false;
			}

			localStorage.setItem(key, JSON.stringify(data));

			// Show save indicator if element exists.
			const indicator = form.querySelector('.wpshadow-autosave-indicator');
			if (indicator) {
				indicator.textContent = 'Draft saved at ' + new Date().toLocaleTimeString();
				indicator.style.opacity = '1';

				setTimeout(() => {
					indicator.style.opacity = '0';
				}, 2000);
			}

			return true;
		} catch (e) {
			// localStorage might be full or disabled.
			console.warn('WPShadow: Failed to auto-save form data:', e);
			return false;
		}
	}

	/**
	 * Restore form data from localStorage
	 *
	 * @param {HTMLFormElement} form Form element.
	 * @param {Object} data Saved data.
	 */
	function restoreFormData(form, data) {
		Object.keys(data.fields).forEach(name => {
			const value = data.fields[name];
			const fields = form.querySelectorAll(`[name="${CSS.escape(name)}"]`);

			fields.forEach(field => {
				// Handle checkboxes and radios.
				if (field.type === 'checkbox' || field.type === 'radio') {
					if (field.value === value) {
						field.checked = true;
					}
				}
				// Handle multi-select.
				else if (field.tagName === 'SELECT' && field.multiple) {
					if (Array.isArray(value)) {
						Array.from(field.options).forEach(opt => {
							opt.selected = value.includes(opt.value);
						});
					}
				}
				// Handle regular fields.
				else {
					field.value = value;

					// Trigger change event for JavaScript that listens to changes.
					field.dispatchEvent(new Event('change', { bubbles: true }));
				}
			});
		});
	}

	/**
	 * Check for saved data and offer restore
	 *
	 * @param {HTMLFormElement} form Form element.
	 */
	function checkForAutosave(form) {
		try {
			const key = getStorageKey(form);
			const saved = localStorage.getItem(key);

			if (!saved) {
				return;
			}

			const data = JSON.parse(saved);

			// Check if autosave is too old.
			const savedTime = new Date(data.timestamp).getTime();
			const now = Date.now();

			if (now - savedTime > MAX_AUTOSAVE_AGE) {
				// Expired, delete it.
				localStorage.removeItem(key);
				return;
			}

			// Show restore prompt.
			const timestamp = new Date(data.timestamp).toLocaleString();
			const message = `Restore unsaved changes from ${timestamp}?`;

			if (confirm(message)) {
				restoreFormData(form, data);

				// Show notification.
				showNotification(form, 'Unsaved changes restored.', 'success');
			} else {
				// User declined, delete the autosave.
				localStorage.removeItem(key);
			}
		} catch (e) {
			console.warn('WPShadow: Failed to restore form data:', e);
		}
	}

	/**
	 * Clear autosave data
	 *
	 * @param {HTMLFormElement} form Form element.
	 */
	function clearAutosave(form) {
		try {
			const key = getStorageKey(form);
			localStorage.removeItem(key);
		} catch (e) {
			console.warn('WPShadow: Failed to clear autosave:', e);
		}
	}

	/**
	 * Show notification message
	 *
	 * @param {HTMLFormElement} form Form element.
	 * @param {string} message Message text.
	 * @param {string} type Type (success|error|info).
	 */
	function showNotification(form, message, type = 'info') {
		// Look for existing notification area.
		let notice = form.querySelector('.wpshadow-autosave-notice');

		if (!notice) {
			notice = document.createElement('div');
			notice.className = 'wpshadow-autosave-notice notice notice-' + type;
			notice.setAttribute('role', 'status');
			notice.setAttribute('aria-live', 'polite');
			form.insertBefore(notice, form.firstChild);
		}

		notice.textContent = message;
		notice.style.display = 'block';

		setTimeout(() => {
			notice.style.display = 'none';
		}, 5000);
	}

	/**
	 * Initialize autosave for a form
	 *
	 * @param {HTMLFormElement} form Form element.
	 */
	function initFormAutosave(form) {
		// Skip if already initialized.
		if (form.dataset.autosaveInit) {
			return;
		}

		form.dataset.autosaveInit = 'true';

		// Check for existing autosave on load.
		checkForAutosave(form);

		// Set up periodic auto-save.
		const intervalId = setInterval(() => {
			saveFormData(form);
		}, AUTOSAVE_INTERVAL);

		// Clear on successful submit.
		form.addEventListener('submit', function(e) {
			// Only clear if form validates.
			if (form.checkValidity()) {
				clearAutosave(form);
				clearInterval(intervalId);
			}
		});

		// Clear if user navigates away after successful save.
		window.addEventListener('beforeunload', function(e) {
			// Save one last time before leaving.
			saveFormData(form);
		});

		// Add autosave indicator if not exists.
		if (!form.querySelector('.wpshadow-autosave-indicator')) {
			const indicator = document.createElement('div');
			indicator.className = 'wpshadow-autosave-indicator';
			indicator.style.cssText = 'opacity: 0; transition: opacity 0.3s; font-size: 12px; color: #666; margin-top: 5px;';
			indicator.setAttribute('aria-live', 'polite');
			form.appendChild(indicator);
		}

		console.log('WPShadow: Auto-save initialized for form:', getFormId(form));
	}

	/**
	 * Initialize all forms on page
	 */
	function initAllForms() {
		// Find all forms with data-autosave attribute.
		const forms = document.querySelectorAll('form[data-autosave="true"]');

		forms.forEach(form => {
			initFormAutosave(form);
		});

		// Also watch for dynamically added forms.
		const observer = new MutationObserver(mutations => {
			mutations.forEach(mutation => {
				mutation.addedNodes.forEach(node => {
					if (node.nodeType === Node.ELEMENT_NODE) {
						if (node.matches && node.matches('form[data-autosave="true"]')) {
							initFormAutosave(node);
						}

						// Check children too.
						const childForms = node.querySelectorAll && node.querySelectorAll('form[data-autosave="true"]');
						if (childForms) {
							childForms.forEach(form => initFormAutosave(form));
						}
					}
				});
			});
		});

		observer.observe(document.body, {
			childList: true,
			subtree: true
		});
	}

	// Initialize when DOM is ready.
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initAllForms);
	} else {
		initAllForms();
	}

	// Expose public API.
	window.wpShadowFormAutosave = {
		init: initFormAutosave,
		save: saveFormData,
		clear: clearAutosave
	};
})();
