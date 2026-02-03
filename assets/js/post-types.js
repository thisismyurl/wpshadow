/**
 * Post Types Page JavaScript
 *
 * Handles custom post type activation/deactivation.
 *
 * @package WPShadow
 * @since   1.26033.1530
 */

(function($) {
	'use strict';

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
			$(document).on('click', '.wpshadow-activate-cpt', this.handleActivate.bind(this));
			$(document).on('click', '.wpshadow-deactivate-cpt', this.handleDeactivate.bind(this));
		},

		/**
		 * Handle post type activation
		 */
		handleActivate: function(e) {
			e.preventDefault();
			
			const $button = $(e.currentTarget);
			const postType = $button.data('post-type');
			const $card = $button.closest('.wpshadow-cpt-card');
			
			// Update button state
			$button.prop('disabled', true).text(wpshadowPostTypes.strings.activating);
			
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
						
						// Reload page to update UI
						setTimeout(function() {
							window.location.reload();
						}, 1000);
					} else {
						PostTypesManager.showNotice('error', response.data.message || wpshadowPostTypes.strings.error);
						$button.prop('disabled', false).text(wpshadowPostTypes.strings.activate);
					}
				},
				error: function() {
					PostTypesManager.showNotice('error', wpshadowPostTypes.strings.error);
					$button.prop('disabled', false).text(wpshadowPostTypes.strings.activate);
				}
			});
		},

		/**
		 * Handle post type deactivation
		 */
		handleDeactivate: function(e) {
			e.preventDefault();
			
			const $button = $(e.currentTarget);
			const postType = $button.data('post-type');
			const $card = $button.closest('.wpshadow-cpt-card');
			
			// Confirm deactivation
			if (!confirm(wpshadowPostTypes.strings.confirm_deactivate)) {
				return;
			}
			
			// Update button state
			$button.prop('disabled', true).text(wpshadowPostTypes.strings.deactivating);
			
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
						
						// Reload page to update UI
						setTimeout(function() {
							window.location.reload();
						}, 1000);
					} else {
						PostTypesManager.showNotice('error', response.data.message || wpshadowPostTypes.strings.error);
						$button.prop('disabled', false).text(wpshadowPostTypes.strings.deactivate);
					}
				},
				error: function() {
					PostTypesManager.showNotice('error', wpshadowPostTypes.strings.error);
					$button.prop('disabled', false).text(wpshadowPostTypes.strings.deactivate);
				}
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
