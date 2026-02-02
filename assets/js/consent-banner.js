/**
 * WPShadow Consent Banner JavaScript
 *
 * Handles user interactions with the first-run consent banner
 *
 * @package WPShadow
 */

(function($) {
	'use strict';

	/**
	 * Get consent nonce from localized script data
	 */
	function getConsentNonce() {
		return typeof wpshadow !== 'undefined' && wpshadow.consent_nonce 
			? wpshadow.consent_nonce 
			: '';
	}

	/**
	 * Handle dismiss consent (with increasing delay)
	 */
	function handleDismissConsent($banner, $button, buttonText) {
		// Disable all buttons during request
		const $allButtons = $banner.find('button');
		$allButtons.prop('disabled', true);
		if ($button && buttonText) {
			$button.text(buttonText);
		}

		$.ajax({
			url: ajaxurl,
			method: 'POST',
			data: {
				action: 'wpshadow_dismiss_consent',
				nonce: getConsentNonce()
			},
			success: function(response) {
				if (response.success) {
					// Fade out banner
					$banner.fadeOut(300, function() {
						$banner.remove();
					});
					
					// Show brief confirmation if message provided
					if (response.data && response.data.message) {
						const $notice = $('<div class="notice notice-info is-dismissible" style="margin: 10px 0;"><p>' + response.data.message + '</p></div>');
						$('h1').first().after($notice);
						
						// Auto-dismiss notice after 5 seconds
						setTimeout(function() {
							$notice.fadeOut(300, function() {
								$(this).remove();
							});
						}, 5000);
					}
				} else {
					// Show error message
					WPShadowModal.alert({
						title: 'Dismiss Failed',
						message: response.data && response.data.message ? response.data.message : 'Failed to dismiss. Please try again.',
						type: 'warning'
					});
					$allButtons.prop('disabled', false);
					if ($button && buttonText) {
						$button.text(buttonText.replace('...', ''));
					}
				}
			},
			error: function() {
				WPShadowModal.alert({
					title: 'Network Error',
					message: 'Network error occurred. Please try again.',
					type: 'danger'
				});
				$allButtons.prop('disabled', false);
				if ($button && buttonText) {
					$button.text(buttonText.replace('...', ''));
				}
			}
		});
	}

	/**
	 * Initialize consent banner handlers
	 */
	function initConsentBanner() {
		const $banner = $('#wpshadow-consent-banner');

		if (!$banner.length) {
			return;
		}

		// Handle dismiss X button (top-right corner)
		$banner.on('click', '.wpshadow-consent-dismiss', function(e) {
			e.preventDefault();
			handleDismissConsent($banner, null, null);
		});

		// Handle "Save preferences" button
		$banner.on('click', '.wpshadow-consent-accept', function(e) {
			e.preventDefault();
			
			const telemetry = $banner.find('input[name="anonymized_telemetry"]').is(':checked');
			
			// Disable buttons during request
			const $acceptBtn = $(this);
			const $dismissBtn = $banner.find('.wpshadow-consent-dismiss');
			$acceptBtn.prop('disabled', true).text('Saving...');
			$dismissBtn.prop('disabled', true);

			$.ajax({
				url: ajaxurl,
				method: 'POST',
				data: {
					action: 'wpshadow_save_consent',
					nonce: getConsentNonce(),
					telemetry: telemetry ? '1' : '0'
				},
				success: function(response) {
					if (response.success) {
						// Show success message briefly
						$banner.addClass('wpshadow-consent-success');
						$banner.html(
							'<div class="wpshadow-consent-success-message">' +
							'<p>✓ ' + 
							(response.data.message || 'Preferences saved. Thank you!') +
							'</p></div>'
						);
						
						// Hide banner after 2 seconds
						setTimeout(function() {
							$banner.fadeOut(300, function() {
								$banner.remove();
							});
						}, 2000);
					} else {
						// Show error message
						WPShadowModal.alert({
							title: 'Save Failed',
							message: response.data.message || 'Failed to save preferences. Please try again.',
							type: 'warning'
						});
						$acceptBtn.prop('disabled', false).text('Save preferences');
						$dismissBtn.prop('disabled', false);
					}
				},
				error: function() {
					WPShadowModal.alert({
						title: 'Save Failed',
						message: 'Failed to save preferences. Please try again.',
						type: 'danger'
					});
					$acceptBtn.prop('disabled', false).text('Save preferences');
					$dismissBtn.prop('disabled', false);
				}
			});
		});

		// Handle keyboard navigation
		$banner.on('keydown', function(e) {
			// Close on Escape key
			if (e.key === 'Escape') {
				handleDismissConsent($banner, null, null);
			}
		});
	}

	// Initialize when document is ready
	$(document).ready(function() {
		initConsentBanner();
	});

})(jQuery);
