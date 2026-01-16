/**
 * WPS Postbox State Handler
 * Custom state saving for postboxes that accounts for hub/spoke context
 *
 * @package WPSHADOW_CORE_SUPPORT
 */

(function($) {
	'use strict';

	$(document).ready(function() {
		// No-op; keep for future ready hooks
	});

	// Override postboxes.save_state to use our custom AJAX handler
	if (typeof postboxes !== 'undefined') {
		const originalSaveState = postboxes.save_state;
		
		postboxes.save_state = function(page) {
			// Use our custom state key
			if (typeof wpsPostboxState !== 'undefined' && wpsPostboxState.stateKey) {
				page = wpsPostboxState.stateKey;
			}
			
			// Get closed postboxes
			var closed = $('.postbox').filter('.closed').map(function() {
				return this.id;
			}).get();
			
			// Use our custom AJAX handler
			$.post(ajaxurl, {
				action: 'wpshadow_save_postbox_state',
				page: page,
				closed: closed,
				nonce: wpsPostboxState.nonce
			}, function(response) {
				// Optional: handle response if needed
			});
		};
		
		// Override save_order to use our custom AJAX handler
		const originalSaveOrder = postboxes.save_order;
		
		postboxes.save_order = function(page) {
			if (typeof wpsPostboxState !== 'undefined' && wpsPostboxState.stateKey) {
				page = wpsPostboxState.stateKey;
			}
			
			// Get the order
			var order = {};
			$('.postbox-container').each(function() {
				var containerId = $(this).attr('id');
				order[containerId] = [];
				$(this).find('.postbox').each(function() {
					order[containerId].push(this.id);
				});
			});
			
			// Use our custom AJAX handler
			$.post(ajaxurl, {
				action: 'wpshadow_save_postbox_order',
				page: page,
				order: order,
				nonce: wpsPostboxState.nonce
			}, function(response) {
				// Optional: handle response if needed
			});
		};
	}

})(jQuery);


