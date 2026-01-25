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
	 * Initialize consent banner handlers
	 */
	function initConsentBanner() {
		const $banner = $('#wpshadow-consent-banner');

		if (!$banner.length) {
			return;
		}

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
						$banner.html(
							'<div style="text-align: center; padding: 20px;">' +
							'<p style="margin: 0; color: #059669; font-weight: 600;">✓ ' + 
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
						alert(response.data.message || 'Failed to save preferences. Please try again.');
						$acceptBtn.prop('disabled', false).text('Save preferences');
						$dismissBtn.prop('disabled', false);
					}
				},
				error: function() {
					alert('Failed to save preferences. Please try again.');
					$acceptBtn.prop('disabled', false).text('Save preferences');
					$dismissBtn.prop('disabled', false);
				}
			});
		});

		// Handle "Not now" button
		$banner.on('click', '.wpshadow-consent-dismiss', function(e) {
			e.preventDefault();
			
			// Disable buttons during request
			const $dismissBtn = $(this);
			const $acceptBtn = $banner.find('.wpshadow-consent-accept');
			$dismissBtn.prop('disabled', true).text('Dismissing...');
			$acceptBtn.prop('disabled', true);

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
					} else {
						alert(response.data.message || 'Failed to dismiss. Please try again.');
						$dismissBtn.prop('disabled', false).text('Not now');
						$acceptBtn.prop('disabled', false);
					}
				},
				error: function() {
					alert('Failed to dismiss. Please try again.');
					$dismissBtn.prop('disabled', false).text('Not now');
					$acceptBtn.prop('disabled', false);
				}
			});
		});

		// Handle keyboard navigation
		$banner.on('keydown', function(e) {
			// Close on Escape key
			if (e.key === 'Escape') {
				$banner.find('.wpshadow-consent-dismiss').trigger('click');
			}
		});
	}

	/**
	 * Get consent nonce from inline script or create one
	 */
	function getConsentNonce() {
		// Try to get from existing wpshadow object
		if (typeof wpshadow !== 'undefined' && wpshadow.consent_nonce) {
			return wpshadow.consent_nonce;
		}
		
		// Fallback: try to generate from WordPress nonce
		if (typeof wpApiSettings !== 'undefined' && wpApiSettings.nonce) {
			return wpApiSettings.nonce;
		}
		
		// Last resort: empty string (will be validated server-side)
		return '';
	}

	// Initialize when document is ready
	$(document).ready(function() {
		initConsentBanner();
	});

})(jQuery);
