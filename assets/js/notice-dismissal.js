/**
 * WPS Notice Dismissal Handler
 *
 * Handles persistent dismissal of admin notices via AJAX.
 *
 * @package WPSHADOW_CoreSupport
 * @since 1.2601.0822
 */

(function($) {
	'use strict';

	$(document).ready(function() {
		// Handle notice dismissal
		$(document).on('click', '.notice.is-dismissible[data-notice-key] .notice-dismiss', function(e) {
			const $notice = $(this).closest('.notice');
			const noticeKey = $notice.data('notice-key');
			const dismissDuration = $notice.data('dismiss-duration'); // Custom duration in seconds

			if (!noticeKey) {
				return;
			}

			// Send AJAX request to persist dismissal
			$.ajax({
				url: wpsNotices.ajaxUrl,
				type: 'POST',
				data: {
					action: 'wpshadow_dismiss_notice',
					nonce: wpsNotices.nonce,
					notice_key: noticeKey,
					duration: dismissDuration || null
				},
				success: function(response) {
					if (response.success) {
						// WordPress already handles the visual dismissal
						console.log('[WPS] Notice dismissed:', noticeKey);
					} else {
						console.error('[WPS] Failed to dismiss notice:', response.data.message);
					}
				},
				error: function(xhr, status, error) {
					console.error('[WPS] AJAX error dismissing notice:', error);
				}
			});
		});
	});
})(jQuery);


