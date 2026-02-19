jQuery(document).ready(function($) {
	if (window.WPShadowModal && typeof window.WPShadowModal.openStatic === 'function') {
		window.WPShadowModal.openStatic('wpshadow-welcome-modal', { returnFocus: document.activeElement });
	} else {
		$('#wpshadow-welcome-modal').addClass('wpshadow-modal-show');
	}

	$('#wpshadow-welcome-continue, #wpshadow-welcome-skip').on('click', function(e) {
		e.preventDefault();

		var telemetry = $('#wpshadow-consent-telemetry').is(':checked');
		var skipped = $(this).attr('id') === 'wpshadow-welcome-skip';

		$.ajax({
			url: wpshadowWelcome.ajax_url,
			type: 'POST',
			data: {
				action: 'wpshadow_complete_welcome',
				nonce: wpshadowWelcome.nonce,
				anonymized_telemetry: telemetry ? '1' : '0',
				skipped: skipped ? '1' : '0'
			},
			success: function(response) {
				if (response.success) {
					if (window.WPShadowModal && typeof window.WPShadowModal.closeStatic === 'function') {
						window.WPShadowModal.closeStatic('wpshadow-welcome-modal');
					} else {
						$('#wpshadow-welcome-modal').removeClass('wpshadow-modal-show');
					}

					setTimeout(function() {
						$('#wpshadow-welcome-modal').remove();
					}, 300);

					if (!skipped && response.data.redirect) {
						window.location.href = response.data.redirect;
					}
				} else {
					if (window.WPShadowModal && typeof window.WPShadowModal.alert === 'function') {
						window.WPShadowModal.alert({
							title: 'Something Went Wrong',
							message: wpshadowWelcome.strings.error,
							type: 'danger'
						});
					} else {
						window.alert(wpshadowWelcome.strings.error);
					}
				}
			},
			error: function() {
				if (window.WPShadowModal && typeof window.WPShadowModal.alert === 'function') {
					window.WPShadowModal.alert({
						title: 'Something Went Wrong',
						message: wpshadowWelcome.strings.error,
						type: 'danger'
					});
				} else {
					window.alert(wpshadowWelcome.strings.error);
				}
			}
		});
	});
});
