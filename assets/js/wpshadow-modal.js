/**
 * WPShadow Centralized Modal System
 *
 * Replaces all confirm(), alert(), and prompt() with accessible modals.
 * Reusable throughout the plugin.
 *
 * Philosophy #8: Inspire Confidence
 * CANON Accessibility: WCAG AA compliant, keyboard navigable, screen reader compatible
 *
 * @package WPShadow
 * @since 1.2601.210200
 */

(function($) {
	'use strict';

	window.WPShadowModal = {
		
		/**
		 * Show confirmation modal (replaces confirm())
		 * 
		 * @param {Object} options Configuration object
		 * @param {string} options.title Modal title
		 * @param {string} options.message Modal message
		 * @param {string} options.confirmText Confirm button text (default: "Confirm")
		 * @param {string} options.cancelText Cancel button text (default: "Cancel")
		 * @param {Function} options.onConfirm Callback when confirmed
		 * @param {Function} options.onCancel Callback when cancelled (optional)
		 * @param {string} options.type Modal type: 'info', 'warning', 'danger', 'success' (default: 'info')
		 */
		confirm: function(options) {
			const defaults = {
				title: 'Confirm Action',
				message: 'Are you sure?',
				confirmText: 'Confirm',
				cancelText: 'Cancel',
				type: 'info',
				onConfirm: function() {},
				onCancel: function() {}
			};
			
			const opts = $.extend({}, defaults, options);
			this._showModal('confirm', opts);
		},

		/**
		 * Show alert modal (replaces alert())
		 * 
		 * @param {Object} options Configuration object
		 * @param {string} options.title Modal title
		 * @param {string} options.message Modal message
		 * @param {string} options.okText OK button text (default: "OK")
		 * @param {Function} options.onOk Callback when OK clicked
		 * @param {string} options.type Modal type: 'info', 'warning', 'danger', 'success' (default: 'info')
		 */
		alert: function(options) {
			const defaults = {
				title: 'Notice',
				message: '',
				okText: 'OK',
				type: 'info',
				onOk: function() {}
			};
			
			const opts = $.extend({}, defaults, options);
			this._showModal('alert', opts);
		},

		/**
		 * Show prompt modal (replaces prompt())
		 * 
		 * @param {Object} options Configuration object
		 * @param {string} options.title Modal title
		 * @param {string} options.message Modal message
		 * @param {string} options.placeholder Input placeholder
		 * @param {string} options.defaultValue Default input value
		 * @param {string} options.submitText Submit button text (default: "Submit")
		 * @param {string} options.cancelText Cancel button text (default: "Cancel")
		 * @param {Function} options.onSubmit Callback with input value
		 * @param {Function} options.onCancel Callback when cancelled (optional)
		 */
		prompt: function(options) {
			const defaults = {
				title: 'Input Required',
				message: '',
				placeholder: '',
				defaultValue: '',
				submitText: 'Submit',
				cancelText: 'Cancel',
				onSubmit: function(value) {},
				onCancel: function() {}
			};
			
			const opts = $.extend({}, defaults, options);
			this._showModal('prompt', opts);
		},

		/**
		 * Internal: Show modal
		 */
		_showModal: function(modalType, options) {
			const self = this;
			const modalId = 'wpshadow-modal-' + Date.now();
			
			// Icon based on type
			const icons = {
				info: '<span class="dashicons dashicons-info" style="color: #2271b1;"></span>',
				warning: '<span class="dashicons dashicons-warning" style="color: #d63638;"></span>',
				danger: '<span class="dashicons dashicons-dismiss" style="color: #d63638;"></span>',
				success: '<span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span>'
			};
			
			const icon = icons[options.type] || icons.info;
			
			// Build modal HTML
			let modalHTML = `
				<div id="${modalId}" class="wpshadow-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="${modalId}-title">
					<div class="wpshadow-modal" role="document">
						<button type="button" class="wpshadow-modal-close" aria-label="Close modal">
							<span class="dashicons dashicons-no-alt"></span>
						</button>
						<div class="wpshadow-modal-header">
							${icon}
							<h2 id="${modalId}-title" class="wpshadow-modal-title">${options.title}</h2>
						</div>
						<div class="wpshadow-modal-body">
							<p>${options.message}</p>
			`;
			
			if (modalType === 'prompt') {
				modalHTML += `
					<input type="text" 
						   class="wpshadow-modal-input" 
						   placeholder="${options.placeholder}" 
						   value="${options.defaultValue}"
						   aria-label="${options.title}"
						   style="width: 100%; margin-top: 10px; padding: 8px; border: 1px solid #8c8f94; border-radius: 4px;">
				`;
			}
			
			modalHTML += '</div><div class="wpshadow-modal-footer">';
			
			if (modalType === 'confirm' || modalType === 'prompt') {
				modalHTML += `
					<button type="button" class="wpshadow-modal-btn wpshadow-modal-btn-secondary wpshadow-modal-cancel">
						${options.cancelText || 'Cancel'}
					</button>
					<button type="button" class="wpshadow-modal-btn wpshadow-modal-btn-primary wpshadow-modal-confirm">
						${options.confirmText || options.submitText}
					</button>
				`;
			} else {
				modalHTML += `
					<button type="button" class="wpshadow-modal-btn wpshadow-modal-btn-primary wpshadow-modal-ok">
						${options.okText}
					</button>
				`;
			}
			
			modalHTML += '</div></div></div>';
			
			// Append to body
			$('body').append(modalHTML);
			
			const $modal = $('#' + modalId);
			const $overlay = $modal;
			
			// Focus trap
			const focusableElements = $modal.find('button, input, [tabindex]:not([tabindex="-1"])');
			const firstFocusable = focusableElements.first();
			const lastFocusable = focusableElements.last();
			
			// Trap focus within modal
			$modal.on('keydown', function(e) {
				if (e.key === 'Tab') {
					if (e.shiftKey) {
						if (document.activeElement === firstFocusable[0]) {
							e.preventDefault();
							lastFocusable.focus();
						}
					} else {
						if (document.activeElement === lastFocusable[0]) {
							e.preventDefault();
							firstFocusable.focus();
						}
					}
				}
			});
			
			// Show modal
			setTimeout(function() {
				$modal.addClass('wpshadow-modal-show');
				firstFocusable.focus();
			}, 10);
			
			// Close handlers
			function closeModal() {
				$modal.removeClass('wpshadow-modal-show');
				setTimeout(function() {
					$modal.remove();
				}, 300);
			}
			
			// Click outside to close
			$overlay.on('click', function(e) {
				if ($(e.target).hasClass('wpshadow-modal-overlay')) {
					if (modalType === 'confirm' || modalType === 'prompt') {
						options.onCancel();
					}
					closeModal();
				}
			});
			
			// Close button
			$modal.find('.wpshadow-modal-close').on('click', function() {
				if (modalType === 'confirm' || modalType === 'prompt') {
					options.onCancel();
				}
				closeModal();
			});
			
			// ESC key
			$(document).on('keydown.wpshadowModal', function(e) {
				if (e.key === 'Escape') {
					if (modalType === 'confirm' || modalType === 'prompt') {
						options.onCancel();
					}
					closeModal();
					$(document).off('keydown.wpshadowModal');
				}
			});
			
			// Action buttons
			$modal.find('.wpshadow-modal-cancel').on('click', function() {
				options.onCancel();
				closeModal();
			});
			
			$modal.find('.wpshadow-modal-ok').on('click', function() {
				options.onOk();
				closeModal();
			});
			
			$modal.find('.wpshadow-modal-confirm').on('click', function() {
				if (modalType === 'prompt') {
					const value = $modal.find('.wpshadow-modal-input').val();
					options.onSubmit(value);
				} else {
					options.onConfirm();
				}
				closeModal();
			});
			
			// Enter key on input
			if (modalType === 'prompt') {
				$modal.find('.wpshadow-modal-input').on('keypress', function(e) {
					if (e.key === 'Enter') {
						const value = $(this).val();
						options.onSubmit(value);
						closeModal();
					}
				});
			}
		}
	};

})(jQuery);
