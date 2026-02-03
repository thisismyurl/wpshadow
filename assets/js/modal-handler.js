/**
 * WPShadow Modal Handler
 *
 * Handles all modal functionality including:
 * - Display rules (time, scroll, exit intent)
 * - Animations (fade, slide, zoom)
 * - Cookie management (frequency control)
 * - Accessibility (keyboard nav, focus trap)
 *
 * @package WPShadow
 * @since   1.6034.1530
 */

(function($) {
	'use strict';

	/**
	 * WPShadow Modal Class
	 */
	class WPShadowModal {
		constructor(element) {
			this.$modal = $(element);
			this.modalId = this.$modal.data('modal-id') || this.$modal.attr('id');
			this.trigger = this.$modal.data('trigger') || 'time';
			this.triggerValue = parseInt(this.$modal.data('trigger-value'), 10) || 3;
			this.frequency = this.$modal.data('frequency') || 'always';
			this.overlayClose = this.$modal.data('overlay-close') !== 'false';
			this.escClose = this.$modal.data('esc-close') !== 'false';
			this.isOpen = false;
			this.triggered = false;
			
			this.init();
		}

		/**
		 * Initialize modal
		 */
		init() {
			// Check if modal should be shown based on frequency
			if (!this.shouldShow()) {
				return;
			}

			// Set up event listeners
			this.setupEvents();

			// Set up trigger based on type
			this.setupTrigger();
		}

		/**
		 * Check if modal should be shown based on frequency rules
		 */
		shouldShow() {
			const cookieName = 'wpshadow_modal_' + this.modalId;
			const cookieValue = this.getCookie(cookieName);

			if (this.frequency === 'always') {
				return true;
			}

			if (this.frequency === 'permanent' && cookieValue === 'closed') {
				return false;
			}

			if (cookieValue) {
				const lastShown = parseInt(cookieValue, 10);
				const now = Date.now();
				const diff = now - lastShown;

				// Once per session (30 minutes)
				if (this.frequency === 'once' && diff < 30 * 60 * 1000) {
					return false;
				}

				// Once per day
				if (this.frequency === 'daily' && diff < 24 * 60 * 60 * 1000) {
					return false;
				}

				// Once per week
				if (this.frequency === 'weekly' && diff < 7 * 24 * 60 * 60 * 1000) {
					return false;
				}
			}

			return true;
		}

		/**
		 * Set up event listeners
		 */
		setupEvents() {
			const self = this;

			// Close button
			this.$modal.find('.wpshadow-modal__close').on('click', function(e) {
				e.preventDefault();
				self.close();
			});

			// Overlay click
			if (this.overlayClose) {
				this.$modal.find('.wpshadow-modal__overlay').on('click', function(e) {
					e.preventDefault();
					self.close();
				});
			}

			// ESC key
			if (this.escClose) {
				$(document).on('keydown.wpshadow-modal-' + this.modalId, function(e) {
					if (e.key === 'Escape' && self.isOpen) {
						self.close();
					}
				});
			}

			// Manual trigger buttons (for inline blocks with visible trigger)
			$('[data-modal-target="' + this.modalId + '"]').on('click', function(e) {
				e.preventDefault();
				self.open();
			});
		}

		/**
		 * Set up trigger based on type
		 */
		setupTrigger() {
			const self = this;

			switch (this.trigger) {
				case 'immediate':
					setTimeout(function() {
						self.open();
					}, 100);
					break;

				case 'time':
					setTimeout(function() {
						self.open();
					}, this.triggerValue * 1000);
					break;

				case 'scroll':
					this.setupScrollTrigger();
					break;

				case 'exit':
					this.setupExitIntentTrigger();
					break;
			}
		}

		/**
		 * Set up scroll percentage trigger
		 */
		setupScrollTrigger() {
			const self = this;
			let scrollTimeout;

			// Check if this is a block-based modal with scroll trigger point
			const $scrollTrigger = $('.wpshadow-modal-scroll-trigger[data-modal-target="' + this.modalId + '"]');
			
			if ($scrollTrigger.length > 0) {
				// Scroll to specific block position
				const observer = new IntersectionObserver(function(entries) {
					entries.forEach(function(entry) {
						if (entry.isIntersecting && !self.triggered) {
							self.triggered = true;
							self.open();
							observer.disconnect();
						}
					});
				}, {
					threshold: 0.1
				});

				observer.observe($scrollTrigger[0]);
			} else {
				// Scroll percentage trigger
				$(window).on('scroll.wpshadow-modal-' + this.modalId, function() {
					if (self.triggered) {
						return;
					}

					clearTimeout(scrollTimeout);
					scrollTimeout = setTimeout(function() {
						const scrollPercent = ($(window).scrollTop() / ($(document).height() - $(window).height())) * 100;
						
						if (scrollPercent >= self.triggerValue) {
							self.triggered = true;
							self.open();
							$(window).off('scroll.wpshadow-modal-' + self.modalId);
						}
					}, 100);
				});
			}
		}

		/**
		 * Set up exit intent trigger
		 */
		setupExitIntentTrigger() {
			const self = this;
			let exitIntentShown = false;

			$(document).on('mouseleave.wpshadow-modal-' + this.modalId, function(e) {
				// Only trigger if mouse leaves from top of page (actual exit)
				if (e.clientY < 50 && !exitIntentShown && !self.triggered) {
					exitIntentShown = true;
					self.triggered = true;
					self.open();
				}
			});
		}

		/**
		 * Open modal
		 */
		open() {
			if (this.isOpen) {
				return;
			}

			this.isOpen = true;
			
			// Add open class for animation
			this.$modal.fadeIn(300).addClass('is-open');
			
			// Prevent body scroll
			$('body').addClass('wpshadow-modal-open').css('overflow', 'hidden');

			// Set cookie for frequency tracking
			if (this.frequency !== 'always') {
				const cookieName = 'wpshadow_modal_' + this.modalId;
				const value = this.frequency === 'permanent' ? 'shown' : Date.now().toString();
				const days = this.frequency === 'weekly' ? 7 : (this.frequency === 'daily' ? 1 : 0.02); // 30 minutes for session
				this.setCookie(cookieName, value, days);
			}

			// Focus management
			this.trapFocus();

			// Trigger event
			$(document).trigger('wpshadow:modal:opened', [this.modalId]);
		}

		/**
		 * Close modal
		 */
		close() {
			if (!this.isOpen) {
				return;
			}

			this.isOpen = false;

			// Remove open class and fade out
			this.$modal.removeClass('is-open').fadeOut(300);

			// Restore body scroll
			$('body').removeClass('wpshadow-modal-open').css('overflow', '');

			// Set permanent cookie if frequency is permanent
			if (this.frequency === 'permanent') {
				const cookieName = 'wpshadow_modal_' + this.modalId;
				this.setCookie(cookieName, 'closed', 365); // 1 year
			}

			// Trigger event
			$(document).trigger('wpshadow:modal:closed', [this.modalId]);
		}

		/**
		 * Trap focus within modal (accessibility)
		 */
		trapFocus() {
			const $focusable = this.$modal.find('a[href], button:not([disabled]), textarea, input, select');
			const $firstFocusable = $focusable.first();
			const $lastFocusable = $focusable.last();

			// Focus first element
			setTimeout(function() {
				$firstFocusable.focus();
			}, 100);

			// Tab key handling
			this.$modal.on('keydown.focus-trap', function(e) {
				if (e.key !== 'Tab') {
					return;
				}

				// Shift + Tab
				if (e.shiftKey) {
					if ($(document.activeElement).is($firstFocusable)) {
						e.preventDefault();
						$lastFocusable.focus();
					}
				}
				// Tab
				else {
					if ($(document.activeElement).is($lastFocusable)) {
						e.preventDefault();
						$firstFocusable.focus();
					}
				}
			});
		}

		/**
		 * Set cookie
		 */
		setCookie(name, value, days) {
			const expires = new Date();
			expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
			document.cookie = name + '=' + value + ';expires=' + expires.toUTCString() + ';path=/';
		}

		/**
		 * Get cookie
		 */
		getCookie(name) {
			const nameEQ = name + '=';
			const ca = document.cookie.split(';');
			for (let i = 0; i < ca.length; i++) {
				let c = ca[i];
				while (c.charAt(0) === ' ') {
					c = c.substring(1, c.length);
				}
				if (c.indexOf(nameEQ) === 0) {
					return c.substring(nameEQ.length, c.length);
				}
			}
			return null;
		}
	}

	/**
	 * Initialize all modals
	 */
	function initModals() {
		$('.wpshadow-modal').each(function() {
			new WPShadowModal(this);
		});
	}

	/**
	 * Public API
	 */
	window.WPShadowModal = {
		/**
		 * Open modal by ID
		 */
		open: function(modalId) {
			const $modal = $('#' + modalId);
			if ($modal.length && $modal.data('modal-instance')) {
				$modal.data('modal-instance').open();
			}
		},

		/**
		 * Close modal by ID
		 */
		close: function(modalId) {
			const $modal = $('#' + modalId);
			if ($modal.length && $modal.data('modal-instance')) {
				$modal.data('modal-instance').close();
			}
		},

		/**
		 * Close all modals
		 */
		closeAll: function() {
			$('.wpshadow-modal.is-open').each(function() {
				if ($(this).data('modal-instance')) {
					$(this).data('modal-instance').close();
				}
			});
		}
	};

	/**
	 * Initialize on document ready
	 */
	$(document).ready(function() {
		initModals();
	});

})(jQuery);
