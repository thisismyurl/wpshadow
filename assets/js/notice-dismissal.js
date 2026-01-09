/**
 * TIMU Notice Dismissal Handler
 *
 * Handles persistent dismissal of admin notices via AJAX.
 *
 * @package TIMU_CoreSupport
 * @since 1.2601.0822
 */

(function($) {
	'use strict';

	$(document).ready(function() {
		// Handle notice dismissal
		$(document).on('click', '.notice.is-dismissible[data-notice-key] .notice-dismiss', function(e) {
			const $notice = $(this).closest('.notice');
			const noticeKey = $notice.data('notice-key');

			if (!noticeKey) {
				return;
			}

			// Send AJAX request to persist dismissal
			$.ajax({
				url: timuNotices.ajaxUrl,
				type: 'POST',
				data: {
					action: 'timu_dismiss_notice',
					nonce: timuNotices.nonce,
					notice_key: noticeKey
				},
				success: function(response) {
					if (response.success) {
						// WordPress already handles the visual dismissal
						console.log('[TIMU] Notice dismissed:', noticeKey);
					} else {
						console.error('[TIMU] Failed to dismiss notice:', response.data.message);
					}
				},
				error: function(xhr, status, error) {
					console.error('[TIMU] AJAX error dismissing notice:', error);
				}
			});
		});
	});
})(jQuery);
