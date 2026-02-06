/**
 * Post Types Page JavaScript
 *
 * Handles custom post type activation/deactivation.
 *
 * @package WPShadow
 * @since   1.6033.1530
 */

(function($) {
	'use strict';

	function showConfirm(message, onConfirm) {
		if (window.WPShadowModal && typeof window.WPShadowModal.confirm === 'function') {
			window.WPShadowModal.confirm({
				title: 'Please Confirm',
				message: message,
				confirmText: 'Continue',
				cancelText: 'Cancel',
				type: 'warning',
				onConfirm: onConfirm,
				onCancel: function() {}
			});
			return;
		}

		if (window.confirm(message)) {
			onConfirm();
		}
	}

	const PostTypesManager = {
		/**
		 * Initialize the manager
		 */
		init: function() {
			this.bindEvents();
		},

		/**
		 * Bind event handlers
		 */
		bindEvents: function() {
			$(document).on('change', '.wpshadow-cpt-toggle', this.handleToggle.bind(this));
		},

		/**
		 * Handle toggle change
		 */
		handleToggle: function(e) {
			const $toggle = $(e.currentTarget);
			const postType = $toggle.data('post-type');
			const isActivating = $toggle.is(':checked');
			
			if (isActivating) {
				this.activatePostType($toggle, postType);
			} else {
				this.deactivatePostType($toggle, postType);
			}
		},

		/**
		 * Activate post type
		 */
		activatePostType: function($toggle, postType) {
			const $card = $toggle.closest('.wps-card');
			
			// Update toggle state
			$toggle.prop('disabled', true);
			$card.find('.wpshadow-toggle-label').text(wpshadowPostTypes.strings.activating);
			
			// Make AJAX request
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'wpshadow_toggle_post_type',
					nonce: wpshadowPostTypes.nonce,
					post_type: postType,
					action_type: 'activate'
				},
				success: function(response) {
					if (response.success) {
						// Show success message
						PostTypesManager.showNotice('success', wpshadowPostTypes.strings.activated);
						
						// Update card visually without reload
						$card.addClass('wps-card--active');
						$toggle.prop('disabled', false).prop('checked', true);
						$card.find('.wpshadow-toggle-label').text(wpshadowPostTypes.strings.active);
					} else {
						PostTypesManager.showNotice('error', response.data.message || wpshadowPostTypes.strings.error);
						$toggle.prop('disabled', false).prop('checked', false);
						$card.find('.wpshadow-toggle-label').text(wpshadowPostTypes.strings.inactive);
					}
				},
				error: function() {
					PostTypesManager.showNotice('error', wpshadowPostTypes.strings.error);
					$toggle.prop('disabled', false).prop('checked', false);
					$card.find('.wpshadow-toggle-label').text(wpshadowPostTypes.strings.inactive);
				}
			});
		},

		/**
		 * Deactivate post type
		 */
		deactivatePostType: function($toggle, postType) {
			const $card = $toggle.closest('.wps-card');
			
			// Confirm deactivation
			showConfirm(wpshadowPostTypes.strings.confirm_deactivate, function() {
				// Update toggle state
				$toggle.prop('disabled', true);
				$card.find('.wpshadow-toggle-label').text(wpshadowPostTypes.strings.deactivating);
				
				// Make AJAX request
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'wpshadow_toggle_post_type',
						nonce: wpshadowPostTypes.nonce,
						post_type: postType,
						action_type: 'deactivate'
					},
					success: function(response) {
						if (response.success) {
							// Show success message
							PostTypesManager.showNotice('success', wpshadowPostTypes.strings.deactivated);
							
							// Update card visually without reload
							$card.removeClass('wps-card--active');
							$toggle.prop('disabled', false).prop('checked', false);
							$card.find('.wpshadow-toggle-label').text(wpshadowPostTypes.strings.inactive);
						} else {
							PostTypesManager.showNotice('error', response.data.message || wpshadowPostTypes.strings.error);
							$toggle.prop('disabled', false).prop('checked', true);
							$card.find('.wpshadow-toggle-label').text(wpshadowPostTypes.strings.active);
						}
					},
					error: function() {
						PostTypesManager.showNotice('error', wpshadowPostTypes.strings.error);
						$toggle.prop('disabled', false).prop('checked', true);
						$card.find('.wpshadow-toggle-label').text(wpshadowPostTypes.strings.active);
					}
				});
			});
		},

		/**
		 * Show admin notice
		 */
		showNotice: function(type, message) {
			const noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
			const $notice = $('<div class="notice ' + noticeClass + ' is-dismissible"><p>' + message + '</p></div>');
			
			$('.wpshadow-post-types .wps-page-header').after($notice);
			
			// Auto-dismiss after 5 seconds
			setTimeout(function() {
				$notice.fadeOut(function() {
					$(this).remove();
				});
			}, 5000);
		}
	};

	// Initialize when ready
	$(document).ready(function() {
		PostTypesManager.init();
	});

})(jQuery);
