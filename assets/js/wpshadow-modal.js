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
 * @since 1.6030.210200
 */

(function ($) {
	'use strict';

	window.WPShadowModal = {

		/**
		 * Get focusable elements within a modal container.
		 *
		 * @param {jQuery} $root Modal root element
		 * @return {jQuery} Focusable elements
		 */
		_getFocusable: function ($root) {
			return $root.find( 'button, input, select, textarea, [href], [tabindex]:not([tabindex="-1"])' );
		},

		/**
		 * Trap focus within a modal container.
		 *
		 * @param {jQuery} $root Modal root element
		 */
		_trapFocus: function ($root) {
			const $focusable = this._getFocusable( $root );
			const $first     = $focusable.first();
			const $last      = $focusable.last();

			$root.off( 'keydown.wpshadowModalTrap' ).on(
				'keydown.wpshadowModalTrap',
				function (e) {
					if (e.key !== 'Tab') {
						return;
					}

					if (e.shiftKey && document.activeElement === $first[0]) {
						e.preventDefault();
						$last.focus();
						return;
					}

					if ( ! e.shiftKey && document.activeElement === $last[0]) {
						e.preventDefault();
						$first.focus();
					}
				}
			);
		},

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
		confirm: function (options) {
			const defaults = {
				title: 'Confirm Action',
				message: 'Are you sure?',
				confirmText: 'Confirm',
				cancelText: 'Cancel',
				type: 'info',
				onConfirm: function () {},
				onCancel: function () {}
			};

			const opts = $.extend( {}, defaults, options );
			this._showModal( 'confirm', opts );
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
		alert: function (options) {
			const defaults = {
				title: 'Notice',
				message: '',
				okText: 'OK',
				type: 'info',
				onOk: function () {}
			};

			const opts = $.extend( {}, defaults, options );
			this._showModal( 'alert', opts );
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
		prompt: function (options) {
			const defaults = {
				title: 'Input Required',
				message: '',
				placeholder: '',
				defaultValue: '',
				submitText: 'Submit',
				cancelText: 'Cancel',
				onSubmit: function (value) {},
				onCancel: function () {}
			};

			const opts = $.extend( {}, defaults, options );
			this._showModal( 'prompt', opts );
		},

		/**
		 * Internal: Show modal
		 */
		_showModal: function (modalType, options) {
			const self    = this;
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
			let modalHTML                = `
				< div id                 = "${modalId}" class = "wpshadow-modal-overlay" role = "dialog" aria - modal = "true" aria - labelledby = "${modalId}-title" >
					< div class          = "wpshadow-modal" role = "document" >
						< button type    = "button" class = "wpshadow-modal-close" aria - label = "Close modal" >
							< span class = "dashicons dashicons-no-alt" > < / span >
						< / button >
						< div class = "wpshadow-modal-header" >
							${icon}
							< h2 id = "${modalId}-title" class = "wpshadow-modal-title" > ${options.title} < / h2 >
						< / div >
						< div class = "wpshadow-modal-body" >
							< p > ${options.message} < / p >
			`;

			if (modalType === 'prompt') {
				modalHTML               += `
					< input type         = "text"
							class        = "wpshadow-modal-input"
							placeholder  = "${options.placeholder}"
							value        = "${options.defaultValue}"
							aria - label = "${options.title}"
							style        = "width: 100%; margin-top: 10px; padding: 8px; border: 1px solid #8c8f94; border-radius: 4px;" >
				`;
			}

			modalHTML += '</div><div class="wpshadow-modal-footer">';

			if (modalType === 'confirm' || modalType === 'prompt') {
				modalHTML        += `
					< button type = "button" class = "wpshadow-modal-btn wpshadow-modal-btn-secondary wpshadow-modal-cancel" >
						${options.cancelText || 'Cancel'}
					< / button >
					< button type                  = "button" class = "wpshadow-modal-btn wpshadow-modal-btn-primary wpshadow-modal-confirm" >
						${options.confirmText || options.submitText}
					< / button >
				`;
			} else {
				modalHTML        += `
					< button type = "button" class = "wpshadow-modal-btn wpshadow-modal-btn-primary wpshadow-modal-ok" >
						${options.okText}
					< / button >
				`;
			}

			modalHTML += '</div></div></div>';

			// Append to body
			$( 'body' ).append( modalHTML );

			const $modal   = $( '#' + modalId );
			const $overlay = $modal;
			$modal.attr( 'aria-hidden', 'false' );

			// Trap focus within modal
			this._trapFocus( $modal );

			// Show modal
			setTimeout(
				function () {
					$modal.addClass( 'wpshadow-modal-show' );
					self._getFocusable( $modal ).first().focus();
				},
				10
			);

			// Close handlers
			function closeModal() {
				$modal.removeClass( 'wpshadow-modal-show' );
				setTimeout(
					function () {
						$modal.remove();
					},
					300
				);
			}

			// Click outside to close
			$overlay.on(
				'click',
				function (e) {
					if ($( e.target ).hasClass( 'wpshadow-modal-overlay' )) {
						if (modalType === 'confirm' || modalType === 'prompt') {
							options.onCancel();
						}
						closeModal();
					}
				}
			);

			// Close button
			$modal.find( '.wpshadow-modal-close' ).on(
				'click',
				function () {
					if (modalType === 'confirm' || modalType === 'prompt') {
						options.onCancel();
					}
					closeModal();
				}
			);

			// ESC key
			$( document ).on(
				'keydown.wpshadowModal',
				function (e) {
					if (e.key === 'Escape') {
						if (modalType === 'confirm' || modalType === 'prompt') {
							options.onCancel();
						}
						closeModal();
						$( document ).off( 'keydown.wpshadowModal' );
					}
				}
			);

			// Action buttons
			$modal.find( '.wpshadow-modal-cancel' ).on(
				'click',
				function () {
					options.onCancel();
					closeModal();
				}
			);

			$modal.find( '.wpshadow-modal-ok' ).on(
				'click',
				function () {
					options.onOk();
					closeModal();
				}
			);

			$modal.find( '.wpshadow-modal-confirm' ).on(
				'click',
				function () {
					if (modalType === 'prompt') {
						const value = $modal.find( '.wpshadow-modal-input' ).val();
						options.onSubmit( value );
					} else {
						options.onConfirm();
					}
					closeModal();
				}
			);

			// Enter key on input
			if (modalType === 'prompt') {
				$modal.find( '.wpshadow-modal-input' ).on(
					'keypress',
					function (e) {
						if (e.key === 'Enter') {
							const value = $( this ).val();
							options.onSubmit( value );
							closeModal();
						}
					}
				);
			}
		},

		/**
		 * Open a static modal already in the DOM.
		 *
		 * @param {string} modalId Modal element ID
		 * @param {Object} options Optional overrides
		 */
		openStatic: function (modalId, options) {
			const $overlay = $( '#' + modalId );
			if ( ! $overlay.length) {
				return;
			}

			const opts = $.extend(
				{
					overlayClose: $overlay.data( 'overlay-close' ) !== false,
					escClose: $overlay.data( 'esc-close' ) !== false,
					returnFocus: document.activeElement
				},
				options || {}
			);

			$overlay.data( 'wpshadowReturnFocus', opts.returnFocus );
			$overlay.data( 'wpshadowOverlayClose', opts.overlayClose );
			$overlay.data( 'wpshadowEscClose', opts.escClose );
			$overlay.attr( 'aria-hidden', 'false' );
			$overlay.addClass( 'wpshadow-modal-show' );
			this._trapFocus( $overlay );

			const $focusTarget = this._getFocusable( $overlay ).first();
			if ($focusTarget.length) {
				$focusTarget.focus();
			}
		},

		/**
		 * Close a static modal in the DOM.
		 *
		 * @param {string} modalId Modal element ID
		 */
		closeStatic: function (modalId) {
			const $overlay = $( '#' + modalId );
			if ( ! $overlay.length) {
				return;
			}

			$overlay.removeClass( 'wpshadow-modal-show' );
			$overlay.attr( 'aria-hidden', 'true' );

			const returnFocus = $overlay.data( 'wpshadowReturnFocus' );
			if (returnFocus && returnFocus.focus) {
				returnFocus.focus();
			}
		}
	};

	// Static modal open/close bindings
	$(
		function () {
			$( document ).on(
				'click',
				'[data-wpshadow-modal-open]',
				function (e) {
					e.preventDefault();
					const modalId = $( this ).data( 'wpshadowModalOpen' );
					if (modalId) {
						window.WPShadowModal.openStatic( modalId, { returnFocus: this } );
					}
				}
			);

			$( document ).on(
				'click',
				'[data-wpshadow-modal-close]',
				function (e) {
					e.preventDefault();
					const modalId = $( this ).data( 'wpshadowModalClose' );
					if (modalId) {
						window.WPShadowModal.closeStatic( modalId );
					}
				}
			);

			$( document ).on(
				'click',
				'.wpshadow-modal-overlay',
				function (e) {
					const $overlay = $( this );
					if ($overlay.data( 'wpshadow-modal' ) !== 'static') {
						return;
					}
					if ($overlay.hasClass( 'wpshadow-modal-show' ) && e.target === this) {
						if ($overlay.data( 'overlay-close' ) !== false) {
							window.WPShadowModal.closeStatic( $overlay.attr( 'id' ) );
						}
					}
				}
			);

			$( document ).on(
				'keydown',
				function (e) {
					if (e.key !== 'Escape') {
						return;
					}

					const $openModal = $( '.wpshadow-modal-overlay[data-wpshadow-modal="static"].wpshadow-modal-show' ).last();
					if ($openModal.length && $openModal.data( 'esc-close' ) !== false) {
						window.WPShadowModal.closeStatic( $openModal.attr( 'id' ) );
					}
				}
			);
		}
	);

})( jQuery );
