/**
 * WPShadow Accessibility Helper
 * Version: 1.0 (2026 Design Update - Phase 5)
 *
 * Runtime accessibility validation and enhancement utility.
 * Provides ARIA attribute validation, keyboard navigation support,
 * focus management, and screen reader announcements.
 *
 * Philosophy Alignment:
 * - CANON Pillar #1: Accessibility First
 * - WCAG AA compliance enforced
 * - Screen reader and keyboard accessible
 *
 * @package WPShadow
 * @since   1.6030.2148
 */

(function() {
	'use strict';

	/**
	 * WPShadow Accessibility Helper
	 */
	window.WPShadowA11y = {

		/**
		 * Screen reader announcement region
		 */
		announcementRegion: null,

		/**
		 * Initialize accessibility helpers
		 */
		init: function() {
			this.createAnnouncementRegion();
			this.validateAriaAttributes();
			this.enhanceKeyboardNavigation();
			this.monitorFocusIndicators();
			this.detectReducedMotion();
		},

		/**
		 * Create ARIA live region for screen reader announcements
		 */
		createAnnouncementRegion: function() {
			if (this.announcementRegion) {
				return;
			}

			this.announcementRegion = document.createElement('div');
			this.announcementRegion.setAttribute('role', 'status');
			this.announcementRegion.setAttribute('aria-live', 'polite');
			this.announcementRegion.setAttribute('aria-atomic', 'true');
			this.announcementRegion.className = 'screen-reader-text';
			this.announcementRegion.id = 'wpshadow-a11y-announcements';
			
			document.body.appendChild(this.announcementRegion);
		},

		/**
		 * Announce message to screen readers
		 * 
		 * @param {string} message - Message to announce
		 * @param {string} priority - 'polite' or 'assertive'
		 */
		announce: function(message, priority = 'polite') {
			if (!this.announcementRegion) {
				this.createAnnouncementRegion();
			}

			// Update priority if needed
			if (priority === 'assertive') {
				this.announcementRegion.setAttribute('aria-live', 'assertive');
			} else {
				this.announcementRegion.setAttribute('aria-live', 'polite');
			}

			// Clear and set message
			this.announcementRegion.textContent = '';
			
			// Use setTimeout to ensure screen readers detect the change
			setTimeout(() => {
				this.announcementRegion.textContent = message;
			}, 100);

			// Clear after 3 seconds to prepare for next announcement
			setTimeout(() => {
				this.announcementRegion.textContent = '';
			}, 3000);
		},

		/**
		 * Validate ARIA attributes on interactive elements
		 */
		validateAriaAttributes: function() {
			const issues = [];

			// Check buttons without labels
			const buttons = document.querySelectorAll('button:not([aria-label]):not([aria-labelledby])');
			buttons.forEach(function(button) {
				if (!button.textContent.trim()) {
					issues.push({
						element: button,
						issue: 'Button without accessible name',
						fix: 'Add aria-label or text content'
					});
					console.warn('[WPShadow A11y] Button without accessible name:', button);
				}
			});

			// Check inputs without labels
			const inputs = document.querySelectorAll('input:not([type="hidden"]):not([aria-label]):not([aria-labelledby])');
			inputs.forEach(function(input) {
				const id = input.getAttribute('id');
				if (id) {
					const label = document.querySelector('label[for="' + id + '"]');
					if (!label) {
						issues.push({
							element: input,
							issue: 'Input without associated label',
							fix: 'Add <label for="' + id + '"> or aria-label'
						});
						console.warn('[WPShadow A11y] Input without label:', input);
					}
				}
			});

			// Check images without alt text
			const images = document.querySelectorAll('img:not([alt])');
			images.forEach(function(img) {
				issues.push({
					element: img,
					issue: 'Image without alt text',
					fix: 'Add alt attribute'
				});
				console.warn('[WPShadow A11y] Image without alt text:', img);
			});

			return issues;
		},

		/**
		 * Enhance keyboard navigation
		 */
		enhanceKeyboardNavigation: function() {
			const self = this;

			// Add keyboard support to custom interactive elements
			document.addEventListener('keydown', function(e) {
				const target = e.target;

				// Handle Enter/Space on role="button" elements
				if (target.getAttribute('role') === 'button' && (e.key === 'Enter' || e.key === ' ')) {
					e.preventDefault();
					target.click();
				}

				// Handle Escape to close modals/dropdowns
				if (e.key === 'Escape') {
					const modal = document.querySelector('.wps-modal[style*="display: block"]');
					if (modal) {
						const closeBtn = modal.querySelector('.wps-modal-close, [data-dismiss="modal"]');
						if (closeBtn) {
							closeBtn.click();
						}
					}

					// Close open dropdowns
					const openDropdowns = document.querySelectorAll('.wps-dropdown.is-open');
					openDropdowns.forEach(function(dropdown) {
						dropdown.classList.remove('is-open');
					});
				}

				// Arrow key navigation in lists
				if (target.matches('.wps-dropdown-list li, .wps-menu-item')) {
					self.handleArrowKeyNavigation(e, target);
				}
			});

			// Trap focus in modals
			document.addEventListener('focusin', function(e) {
				const modal = document.querySelector('.wps-modal[style*="display: block"]');
				if (modal && !modal.contains(e.target)) {
					e.preventDefault();
					const focusable = modal.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
					if (focusable.length > 0) {
						focusable[0].focus();
					}
				}
			});
		},

		/**
		 * Handle arrow key navigation in lists
		 * 
		 * @param {Event} e - Keyboard event
		 * @param {Element} current - Current element
		 */
		handleArrowKeyNavigation: function(e, current) {
			if (e.key !== 'ArrowUp' && e.key !== 'ArrowDown') {
				return;
			}

			e.preventDefault();

			const parent = current.parentElement;
			const items = Array.from(parent.children).filter(function(el) {
				return el.offsetParent !== null; // Visible items only
			});

			const currentIndex = items.indexOf(current);
			let nextIndex;

			if (e.key === 'ArrowDown') {
				nextIndex = (currentIndex + 1) % items.length;
			} else {
				nextIndex = (currentIndex - 1 + items.length) % items.length;
			}

			items[nextIndex].focus();
		},

		/**
		 * Monitor focus indicators
		 */
		monitorFocusIndicators: function() {
			// Detect keyboard vs mouse usage
			let usingKeyboard = false;

			document.addEventListener('keydown', function(e) {
				if (e.key === 'Tab') {
					usingKeyboard = true;
					document.body.classList.add('wps-keyboard-nav');
				}
			});

			document.addEventListener('mousedown', function() {
				usingKeyboard = false;
				document.body.classList.remove('wps-keyboard-nav');
			});

			// Ensure focus indicators are visible during keyboard navigation
			document.addEventListener('focus', function(e) {
				if (usingKeyboard && e.target.matches('button, a, input, select, textarea, [tabindex]')) {
					// Check if element has visible focus indicator
					const styles = window.getComputedStyle(e.target);
					const outline = styles.getPropertyValue('outline');
					const boxShadow = styles.getPropertyValue('box-shadow');

					if (outline === 'none' && !boxShadow.includes('0 0 0')) {
						console.warn('[WPShadow A11y] Element missing focus indicator:', e.target);
					}
				}
			}, true);
		},

		/**
		 * Detect and respect reduced motion preferences
		 */
		detectReducedMotion: function() {
			const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

			if (prefersReducedMotion) {
				document.body.classList.add('wps-reduced-motion');
				
				// Log for debugging
				console.info('[WPShadow A11y] Reduced motion preference detected');
			}

			// Listen for changes
			window.matchMedia('(prefers-reduced-motion: reduce)').addEventListener('change', function(e) {
				if (e.matches) {
					document.body.classList.add('wps-reduced-motion');
					console.info('[WPShadow A11y] Reduced motion enabled');
				} else {
					document.body.classList.remove('wps-reduced-motion');
					console.info('[WPShadow A11y] Reduced motion disabled');
				}
			});
		},

		/**
		 * Check color contrast (simplified check)
		 * 
		 * @param {string} foreground - Foreground color (hex)
		 * @param {string} background - Background color (hex)
		 * @return {Object} Contrast ratio and WCAG compliance
		 */
		checkContrast: function(foreground, background) {
			const fgRgb = this.hexToRgb(foreground);
			const bgRgb = this.hexToRgb(background);

			if (!fgRgb || !bgRgb) {
				return null;
			}

			const ratio = this.getContrastRatio(fgRgb, bgRgb);

			return {
				ratio: ratio,
				passAA: ratio >= 4.5,
				passAAA: ratio >= 7,
				passAALarge: ratio >= 3,
				passAAALarge: ratio >= 4.5
			};
		},

		/**
		 * Convert hex color to RGB
		 * 
		 * @param {string} hex - Hex color
		 * @return {Object} RGB object
		 */
		hexToRgb: function(hex) {
			const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
			return result ? {
				r: parseInt(result[1], 16),
				g: parseInt(result[2], 16),
				b: parseInt(result[3], 16)
			} : null;
		},

		/**
		 * Calculate contrast ratio
		 * 
		 * @param {Object} rgb1 - First RGB color
		 * @param {Object} rgb2 - Second RGB color
		 * @return {number} Contrast ratio
		 */
		getContrastRatio: function(rgb1, rgb2) {
			const l1 = this.getLuminance(rgb1);
			const l2 = this.getLuminance(rgb2);
			const lighter = Math.max(l1, l2);
			const darker = Math.min(l1, l2);
			return (lighter + 0.05) / (darker + 0.05);
		},

		/**
		 * Calculate relative luminance
		 * 
		 * @param {Object} rgb - RGB color
		 * @return {number} Luminance
		 */
		getLuminance: function(rgb) {
			const rsRGB = rgb.r / 255;
			const gsRGB = rgb.g / 255;
			const bsRGB = rgb.b / 255;

			const r = rsRGB <= 0.03928 ? rsRGB / 12.92 : Math.pow((rsRGB + 0.055) / 1.055, 2.4);
			const g = gsRGB <= 0.03928 ? gsRGB / 12.92 : Math.pow((gsRGB + 0.055) / 1.055, 2.4);
			const b = bsRGB <= 0.03928 ? bsRGB / 12.92 : Math.pow((bsRGB + 0.055) / 1.055, 2.4);

			return 0.2126 * r + 0.7152 * g + 0.0722 * b;
		}
	};

	// Initialize on DOM ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function() {
			WPShadowA11y.init();
		});
	} else {
		WPShadowA11y.init();
	}

})();
