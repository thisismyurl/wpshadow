/**
 * Privacy Dashboard Page Scripts
 *
 * @package WPShadow
 * @since   0.6004.0300
 */

(function ($) {
	'use strict';

	function showAlert(message, type, title) {
		if (window.WPShadowModal && typeof window.WPShadowModal.alert === 'function') {
			window.WPShadowModal.alert(
				{
					title: title || 'Notice',
					message: message,
					type: type || 'info'
				}
			);
			return;
		}
		window.alert( message );
	}

	function showConfirm(message, onConfirm) {
		if (window.WPShadowModal && typeof window.WPShadowModal.confirm === 'function') {
			window.WPShadowModal.confirm(
				{
					title: 'Please Confirm',
					message: message,
					confirmText: 'Continue',
					cancelText: 'Cancel',
					type: 'warning',
					onConfirm: onConfirm,
					onCancel: function () {}
				}
			);
			return;
		}
		if (window.confirm( message )) {
			onConfirm();
		}
	}

	function insertNotice($notice) {
		var $slot = $( '#wpshadow-page-notices' );
		if ($slot.length) {
			$slot.append( $notice );
			return;
		}
		var $wrap = $( '.wrap' ).first();
		if ($wrap.length) {
			$wrap.prepend( $notice );
			return;
		}
		$notice.prependTo( 'body' );
	}

	/**
	 * Initialize privacy consent form handler.
	 */
	function initConsentForm() {
		$( '#wpshadow-consent-form' ).on(
			'submit',
			function (e) {
				e.preventDefault();

				var telemetry = $( '#wpshadow-consent-form input[name="anonymized_telemetry"]' ).val() === '1';

				$.ajax(
					{
						url: ajaxurl,
						type: 'POST',
						data: {
							action: 'wpshadow_update_consent',
							nonce: $( '#wpshadow_consent_nonce' ).val(),
							anonymized_telemetry: telemetry ? '1' : '0'
						},
						success: function (response) {
							if (response.success) {
								// Show success message
								var $notice = $(
									'<div class="notice notice-success is-dismissible"><p>' +
									(wpshadowPrivacy.strings.consent_saved || 'Privacy preferences saved successfully.') +
									'</p></div>'
								);
								insertNotice( $notice );
								$notice.delay( 3000 ).fadeOut();
							}
						},
						error: function () {
							var $notice = $(
								'<div class="notice notice-error is-dismissible"><p>' +
								(wpshadowPrivacy.strings.consent_error || 'Failed to save preferences. Please try again.') +
								'</p></div>'
							);
							insertNotice( $notice );
						}
					}
				);
			}
		);
	}

	/**
	 * Initialize data deletion handler with confirmation.
	 */
	function initDataDeletion() {
		$( '#wpshadow-delete-data-btn' ).on(
			'click',
			function () {
				showConfirm(
					wpshadowPrivacy.strings.delete_confirm ||
					'Are you sure? This will permanently delete all WPShadow data. This action cannot be undone.',
					function () {

						var $button = $( '#wpshadow-delete-data-btn' );
						$button.prop( 'disabled', true ).text( wpshadowPrivacy.strings.deleting || 'Deleting...' );

						$.ajax(
							{
								url: ajaxurl,
								type: 'POST',
								data: {
									action: 'wpshadow_delete_data',
									nonce: wpshadowPrivacy.nonce
								},
								success: function (response) {
									if (response.success) {
										showAlert( wpshadowPrivacy.strings.delete_success || 'All data deleted successfully.', 'success', 'Data Deleted' );
										window.location.reload();
									}
								},
								error: function () {
									$button.prop( 'disabled', false ).text( wpshadowPrivacy.strings.delete_data || 'Delete All Data' );
									showAlert( wpshadowPrivacy.strings.delete_error || 'Deletion failed. Please try again.', 'danger', 'Deletion Failed' );
								}
							}
						);
					}
				);
			}
		);
	}

	// Initialize on document ready
	$( document ).ready(
		function () {
			initConsentForm();
			initDataDeletion();
		}
	);

})( jQuery );
