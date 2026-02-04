/**
 * WPShadow Dashboard Setup Widget
 *
 * Handles "Skip for now" button with modal confirmation
 * Issue #1669: Replace JavaScript dialog with accessible modal
 *
 * @package WPShadow
 * @since 1.6030.210200
 */

(function($) {
	'use strict';

	$(function() {
		// Handle "Skip for now" button
		$('.wpshadow-setup-skip').on('click', function(e) {
			e.preventDefault();
			
			const $link = $(this);
			const nonce = $link.data('nonce');
			
			// Use centralized modal instead of confirm()
			WPShadowModal.confirm({
				title: 'Skip Setup?',
				message: 'WPShadow works best when configured. You can always complete setup later from the Settings page.\n\nAre you sure you want to skip setup for now?',
				confirmText: 'Skip Setup',
				cancelText: 'Continue Setup',
				type: 'warning',
				onConfirm: function() {
					// User confirmed - dismiss the widget
					$.ajax({
						url: ajaxurl,
						type: 'POST',
						data: {
							action: 'wpshadow_dismiss_setup',
							nonce: nonce
						},
						success: function(response) {
							if (response.success) {
								// Remove the widget from dashboard
								$('#wpshadow_setup_widget').fadeOut(300, function() {
									$(this).remove();
								});
								
								// Show success message
								WPShadowModal.alert({
									title: 'Setup Skipped',
									message: 'You can complete setup anytime from WPShadow → Settings.',
									type: 'info'
								});
							}
						},
						error: function() {
							WPShadowModal.alert({
								title: 'Error',
								message: 'Could not dismiss setup widget. Please try again.',
								type: 'danger'
							});
						}
					});
				}
			});
		});
	});

})(jQuery);
