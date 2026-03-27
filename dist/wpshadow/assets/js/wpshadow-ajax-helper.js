/**
 * WPShadow AJAX Helper
 *
 * Centralized AJAX utility to reduce code duplication across JavaScript files.
 * Provides consistent error handling, loading states, and nonce management.
 *
 * @package WPShadow
 * @since   1.6031.1500
 */

(function ($, window) {
	'use strict';

	/**
	 * WPShadow AJAX Helper Object
	 */
	window.wpShadowAjax = {
		/**
		 * Default options for AJAX requests
		 */
		defaults: {
			showLoading: true,
			showErrors: true,
			errorMessage: 'An error occurred. Please try again.',
			timeout: 30000 // 30 seconds
		},

		/**
		 * Make a POST request to WordPress AJAX handler.
		 *
		 * @param {string} action - Action name (will be prefixed with 'wpshadow_')
		 * @param {Object} data - Data to send
		 * @param {Object} options - Configuration options
		 * @param {Function} options.success - Success callback
		 * @param {Function} options.error - Error callback
		 * @param {Function} options.beforeSend - Before send callback
		 * @param {Function} options.complete - Complete callback
		 * @param {boolean} options.showLoading - Show loading indicator
		 * @param {boolean} options.showErrors - Show error alerts
		 * @param {string} options.errorMessage - Default error message
		 * @param {number} options.timeout - Request timeout in milliseconds
		 * @returns {jqXHR} jQuery XHR object
		 */
		post: function (action, data, options) {
			options = $.extend( {}, this.defaults, options || {} );

			// Ensure action is prefixed
			if (action.indexOf( 'wpshadow_' ) !== 0) {
				action = 'wpshadow_' + action;
			}

			// Add nonce if available
			var requestData = $.extend(
				{
					action: action,
					nonce: this.getNonce()
				},
				data || {}
			);

			var self           = this;
			var loadingElement = options.loadingElement || null;

			return $.ajax(
				{
					url: ajaxurl || window.ajaxurl,
					type: 'POST',
					data: requestData,
					timeout: options.timeout,
					beforeSend: function (jqXHR, settings) {
						if (options.showLoading && loadingElement) {
							self.showLoading( loadingElement );
						}
						if (options.beforeSend) {
							return options.beforeSend( jqXHR, settings );
						}
					},
					success: function (response, textStatus, jqXHR) {
						if (response && response.success) {
							if (options.success) {
								options.success( response.data || {}, response, textStatus, jqXHR );
							}
						} else {
							var errorMsg = response && response.data && response.data.message
							? response.data.message
							: options.errorMessage;

							if (options.error) {
								options.error( errorMsg, response );
							} else if (options.showErrors) {
								self.showError( errorMsg );
							}
						}
					},
					error: function (jqXHR, textStatus, errorThrown) {
						var errorMsg = options.errorMessage;

						if (textStatus === 'timeout') {
							errorMsg = 'Request timed out. Please try again.';
						} else if (jqXHR.status === 403) {
							errorMsg = 'Permission denied. Please refresh the page and try again.';
						} else if (jqXHR.status === 500) {
							errorMsg = 'Server error occurred. Please try again later.';
						}

						if (options.error) {
							options.error( errorMsg, jqXHR, textStatus, errorThrown );
						} else if (options.showErrors) {
							self.showError( errorMsg );
						}
					},
					complete: function (jqXHR, textStatus) {
						if (options.showLoading && loadingElement) {
							self.hideLoading( loadingElement );
						}
						if (options.complete) {
							options.complete( jqXHR, textStatus );
						}
					}
				}
			);
		},

		/**
		 * Make a GET request to WordPress AJAX handler.
		 *
		 * @param {string} action - Action name (will be prefixed with 'wpshadow_')
		 * @param {Object} data - Data to send
		 * @param {Object} options - Configuration options
		 * @returns {jqXHR} jQuery XHR object
		 */
		get: function (action, data, options) {
			options          = options || {};
			var originalType = options.type;
			options.type     = 'GET';

			var result = this.post( action, data, options );

			// Restore original type if it was set
			if (originalType) {
				options.type = originalType;
			}

			return result;
		},

		/**
		 * Get nonce from localized script data.
		 *
		 * @returns {string} Nonce value
		 */
		getNonce: function () {
			// Try multiple possible nonce locations
			if (window.wpShadowData && window.wpShadowData.nonce) {
				return window.wpShadowData.nonce;
			}
			if (window.wpshadow && window.wpshadow.nonce) {
				return window.wpshadow.nonce;
			}
			if (window.wpshadowAjax && window.wpshadowAjax.nonce) {
				return window.wpshadowAjax.nonce;
			}
			console.warn( 'WPShadow AJAX Helper: No nonce found in localized data' );
			return '';
		},

		/**
		 * Show loading state on element.
		 *
		 * @param {jQuery|string} element - Element to show loading on
		 */
		showLoading: function (element) {
			var $element = $( element );

			// Save original state
			if ( ! $element.data( 'wpshadow-original-html' )) {
				$element.data( 'wpshadow-original-html', $element.html() );
			}
			if ( ! $element.data( 'wpshadow-original-disabled' )) {
				$element.data( 'wpshadow-original-disabled', $element.prop( 'disabled' ) );
			}

			// Add loading class and disable
			$element.addClass( 'wpshadow-loading' );
			if ($element.is( 'button, input, select, textarea' )) {
				$element.prop( 'disabled', true );
			}

			// Add spinner if button
			if ($element.is( 'button' ) || $element.hasClass( 'button' )) {
				var spinnerHtml = '<span class="spinner is-active" style="float: none; margin: 0 5px 0 0;"></span>';
				$element.html( spinnerHtml + $element.text() );
			}
		},

		/**
		 * Hide loading state on element.
		 *
		 * @param {jQuery|string} element - Element to hide loading from
		 */
		hideLoading: function (element) {
			var $element = $( element );

			// Restore original state
			var originalHtml     = $element.data( 'wpshadow-original-html' );
			var originalDisabled = $element.data( 'wpshadow-original-disabled' );

			$element.removeClass( 'wpshadow-loading' );

			if (originalHtml) {
				$element.html( originalHtml );
				$element.removeData( 'wpshadow-original-html' );
			}

			if ($element.is( 'button, input, select, textarea' )) {
				$element.prop( 'disabled', originalDisabled || false );
				$element.removeData( 'wpshadow-original-disabled' );
			}
		},

		/**
		 * Show error message to user.
		 *
		 * @param {string} message - Error message
		 * @param {Object} options - Display options
		 */
		showError: function (message, options) {
			options = $.extend(
				{
					type: 'error',
					dismissible: true,
					duration: 5000
				},
				options || {}
			);

			// Use WordPress admin notice if available
			if ($( '.wrap' ).length) {
				this.showAdminNotice( message, options );
			} else {
				// Fallback to modal
				if (window.WPShadowModal && typeof window.WPShadowModal.alert === 'function') {
					window.WPShadowModal.alert(
						{
							title: 'Notification',
							message: message,
							type: options.type === 'error' ? 'danger' : 'info'
						}
					);
				} else {
					window.alert( message );
				}
			}
		},

		/**
		 * Show success message to user.
		 *
		 * @param {string} message - Success message
		 * @param {Object} options - Display options
		 */
		showSuccess: function (message, options) {
			options = $.extend(
				{
					type: 'success',
					dismissible: true,
					duration: 3000
				},
				options || {}
			);

			this.showAdminNotice( message, options );
		},

		/**
		 * Show admin notice.
		 *
		 * @param {string} message - Notice message
		 * @param {Object} options - Display options
		 */
		showAdminNotice: function (message, options) {
			var noticeClass = 'notice notice-' + options.type;
			if (options.dismissible) {
				noticeClass += ' is-dismissible';
			}

			var $notice = $( '<div class="' + noticeClass + '"><p>' + message + '</p></div>' );
			var $slot   = $( '#wpshadow-page-notices' );
			if ($slot.length) {
				$slot.append( $notice );
			} else if ($( '.wrap' ).length) {
				$( '.wrap' ).first().prepend( $notice );
			}

			// Auto dismiss after duration
			if (options.duration > 0) {
				setTimeout(
					function () {
						$notice.fadeOut(
							function () {
								$( this ).remove();
							}
						);
					},
					options.duration
				);
			}

			// Initialize WordPress dismiss button
			if (options.dismissible && window.wp && window.wp.notices) {
				window.wp.notices.init();
			}
		},

		/**
		 * Confirm action with user.
		 *
		 * @param {string} message - Confirmation message
		 * @param {Function} callback - Callback if confirmed
		 * @returns {boolean} True if confirmed
		 */
		confirm: function (message, callback) {
			if (window.WPShadowModal && typeof window.WPShadowModal.confirm === 'function') {
				window.WPShadowModal.confirm(
					{
						title: 'Please Confirm',
						message: message,
						confirmText: 'Continue',
						cancelText: 'Cancel',
						type: 'warning',
						onConfirm: function () {
							if (callback) {
								callback();
							}
						},
						onCancel: function () {}
					}
				);
				return true;
			}

			var confirmed = window.confirm( message );
			if (confirmed && callback) {
				callback();
			}
			return confirmed;
		}
	};

	// Alias for backward compatibility
	window.wpsAjax = window.wpShadowAjax;

})( jQuery, window );
