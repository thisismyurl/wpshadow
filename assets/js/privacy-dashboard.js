/**
 * Privacy Dashboard Page Scripts
 *
 * @package WPShadow
 * @since   1.2604.0300
 */

(function($) {
	'use strict';

	/**
	 * Initialize privacy consent form handler.
	 */
	function initConsentForm() {
		$('#wpshadow-consent-form').on('submit', function(e) {
			e.preventDefault();

			var telemetry = $('#consent-telemetry').is(':checked');

			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'wpshadow_update_consent',
					nonce: $('#wpshadow_consent_nonce').val(),
					anonymized_telemetry: telemetry ? '1' : '0'
				},
				success: function(response) {
					if (response.success) {
						// Show success message
						$('<div class="notice notice-success is-dismissible"><p>' + 
							(wpshadowPrivacy.strings.consent_saved || 'Privacy preferences saved successfully.') + 
							'</p></div>')
							.insertAfter('.wps-page-header')
							.delay(3000)
							.fadeOut();
					}
				},
				error: function() {
					$('<div class="notice notice-error is-dismissible"><p>' + 
						(wpshadowPrivacy.strings.consent_error || 'Failed to save preferences. Please try again.') + 
						'</p></div>')
						.insertAfter('.wps-page-header');
				}
			});
		});
	}

	/**
	 * Initialize data export handler.
	 */
	function initDataExport() {
		$('#wpshadow-export-data-btn').on('click', function() {
			var $button = $(this);
			$button.prop('disabled', true).text(wpshadowPrivacy.strings.exporting || 'Exporting...');

			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'wpshadow_export_data',
					nonce: wpshadowPrivacy.nonce
				},
				success: function(response) {
					if (response.success && response.data.download_url) {
						window.location.href = response.data.download_url;
						$button.prop('disabled', false).text(wpshadowPrivacy.strings.export_data || 'Export My Data');
					}
				},
				error: function() {
					$button.prop('disabled', false).text(wpshadowPrivacy.strings.export_data || 'Export My Data');
					alert(wpshadowPrivacy.strings.export_error || 'Export failed. Please try again.');
				}
			});
		});
	}

	/**
	 * Initialize data deletion handler with confirmation.
	 */
	function initDataDeletion() {
		$('#wpshadow-delete-data-btn').on('click', function() {
			if (!confirm(wpshadowPrivacy.strings.delete_confirm || 
				'Are you sure? This will permanently delete all WPShadow data. This action cannot be undone.')) {
				return;
			}

			var $button = $(this);
			$button.prop('disabled', true).text(wpshadowPrivacy.strings.deleting || 'Deleting...');

			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'wpshadow_delete_data',
					nonce: wpshadowPrivacy.nonce
				},
				success: function(response) {
					if (response.success) {
						alert(wpshadowPrivacy.strings.delete_success || 'All data deleted successfully.');
						window.location.reload();
					}
				},
				error: function() {
					$button.prop('disabled', false).text(wpshadowPrivacy.strings.delete_data || 'Delete All Data');
					alert(wpshadowPrivacy.strings.delete_error || 'Deletion failed. Please try again.');
				}
			});
		});
	}

	// Initialize on document ready
	$(document).ready(function() {
		initConsentForm();
		initDataExport();
		initDataDeletion();
	});

})(jQuery);
