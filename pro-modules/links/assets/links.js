/**
 * WPShadow Links Manager
 *
 * Manages link interactions and ad-blocker resistant redirects.
 */

(function($) {
	'use strict';

	const WPShadowLinks = {
		hideAffiliateText: wpshadowLinks.hideAffiliate || false,

		init: function() {
			this.bindEvents();
		},

		bindEvents: function() {
			// Handle managed link clicks for ad-blocker resistance
			$(document).on('click', '.wpshadow-managed-link', $.proxy(this.handleLinkClick, this));
		},

		handleLinkClick: function(e) {
			e.preventDefault();
			
			const $link = $(e.currentTarget);
			const linkId = $link.data('link-id');
			const url = $link.attr('href');

			// For non-affiliate links or if feature disabled, redirect directly
			if (!linkId || !wpshadowLinks.nonce) {
				window.location.href = url;
				return;
			}

			// For affiliate links or ad-blocker resistance, use AJAX redirect
			$.ajax({
				url: wpshadowLinks.ajaxUrl,
				type: 'POST',
				data: {
					action: 'wpshadow_link_click',
					link_id: linkId,
					_wpnonce: wpshadowLinks.nonce
				},
				success: function(response) {
					if (response.success && response.data.url) {
						// Check if should open in new tab
						if ($link.attr('target') === '_blank') {
							window.open(response.data.url, '_blank');
						} else {
							window.location.href = response.data.url;
						}
					} else {
						// Fallback to direct URL
						handleLinkRedirect($link, url);
					}
				},
				error: function() {
					// Fallback to direct URL on error
					handleLinkRedirect($link, url);
				}
			});
		}
	};

	function handleLinkRedirect($link, url) {
		if ($link.attr('target') === '_blank') {
			window.open(url, '_blank');
		} else {
			window.location.href = url;
		}
	}

	// Initialize on document ready
	$(document).ready(function() {
		WPShadowLinks.init();
	});

})(jQuery);
