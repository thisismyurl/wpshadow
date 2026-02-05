/**
 * WPShadow Exit Interview
 *
 * Handles the exit interview modal when users deactivate the plugin.
 *
 * Philosophy:
 * - Commandment #1 (Helpful Neighbor) - Friendly, non-pushy
 * - CANON: Accessibility First - Keyboard nav, screen reader support
 *
 * @package WPShadow
 * @since   1.6030.2148
 */

(function($) {
	'use strict';

	let deactivateLink = null;

	/**
	 * Initialize exit interview
	 */
	function init() {
		// Find the deactivate link for WPShadow
		const pluginSlug = wpshadowExitInterview.plugin_slug;
		const deactivateLinkSelector = `tr[data-slug="${pluginSlug.split('/')[0]}"] .deactivate a`;
		deactivateLink = $(deactivateLinkSelector);

		if (!deactivateLink.length) {
			return;
		}

		// Intercept deactivate link
		deactivateLink.on('click', function(e) {
			e.preventDefault();
			showModal();
		});

		// Set up modal interactions
		setupModalInteractions();

		// Set up form submission
		setupFormSubmission();
	}

	/**
	 * Show the exit interview modal
	 */
	function showModal() {
		if (window.WPShadowModal && typeof window.WPShadowModal.openStatic === 'function') {
			window.WPShadowModal.openStatic('wpshadow-exit-interview-modal', {
				returnFocus: deactivateLink ? deactivateLink[0] : document.activeElement
			});
		} else {
			$('#wpshadow-exit-interview-modal').addClass('wpshadow-modal-show');
		}

		// Announce to screen readers
		announceToScreenReader('Exit interview dialog opened');
	}

	/**
	 * Hide the exit interview modal
	 */
	function hideModal() {
		if (window.WPShadowModal && typeof window.WPShadowModal.closeStatic === 'function') {
			window.WPShadowModal.closeStatic('wpshadow-exit-interview-modal');
		} else {
			$('#wpshadow-exit-interview-modal').removeClass('wpshadow-modal-show');
		}

		// Announce to screen readers
		announceToScreenReader('Exit interview dialog closed');
	}

	/**
	 * Set up modal interactions (close, escape key, etc.)
	 */
	function setupModalInteractions() {
		const $modal = $('#wpshadow-exit-interview-modal');

		// Close button
		$modal.find('.wpshadow-modal-close').on('click', function() {
			hideModal();
		});

		// Overlay click to close (when static helper is not present)
		$modal.on('click', function(e) {
			if (e.target === this) {
				hideModal();
			}
		});

		// Skip button - deactivate without feedback
		$('#wpshadow-exit-skip').on('click', function() {
			hideModal();
			proceedWithDeactivation();
		});

		// Show/hide details field based on reason selection
		$('#wpshadow-exit-reason').on('change', function() {
			const reason = $(this).val();
			const $detailsGroup = $('#wpshadow-exit-details-group');
			
			if (reason && reason !== '') {
				$detailsGroup.slideDown(200);
			} else {
				$detailsGroup.slideUp(200);
			}
		});
	}

	/**
	 * Set up form submission
	 */
	function setupFormSubmission() {
		const $form = $('#wpshadow-exit-interview-form');
		const $submitButton = $('#wpshadow-exit-submit');
		const $message = $('.wpshadow-form-message');

		$form.on('submit', function(e) {
			e.preventDefault();

			// Validate form
			const reason = $('#wpshadow-exit-reason').val();
			if (!reason) {
				showMessage('error', 'Please select a reason for deactivating.');
				$('#wpshadow-exit-reason').focus();
				return;
			}

			// Disable submit button
			$submitButton.prop('disabled', true).text('Submitting...');

			// Prepare data
			const formData = {
				action: 'wpshadow_submit_exit_interview',
				nonce: wpshadowExitInterview.nonce,
				reason: reason,
				details: $('#wpshadow-exit-details').val(),
				allow_contact: $('#wpshadow-exit-contact').is(':checked') ? 1 : 0
			};

			// Submit via AJAX
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: formData,
				success: function(response) {
					if (response.success) {
						showMessage('success', response.data.message);
						
						// Wait a moment, then proceed with deactivation
						setTimeout(function() {
							hideModal();
							proceedWithDeactivation();
						// Focus trapping handled by shared modal helper.
		// Announce to screen readers
		announceToScreenReader(message);
	}

	/**
	 * Proceed with plugin deactivation
	 */
	function proceedWithDeactivation() {
		if (deactivateLink && deactivateLink.length) {
			// Navigate to the deactivate URL
			window.location.href = deactivateLink.attr('href');
		}
	}

	/**
	 * Trap focus within modal for accessibility
	 *
	 * @param {jQuery} $modal The modal element
	 */
	function trapFocus($modal) {
		const focusableElements = $modal.find('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
		const firstFocusable = focusableElements.first();
		const lastFocusable = focusableElements.last();

		$modal.on('keydown', function(e) {
			if (e.key !== 'Tab') {
				return;
			}

			// Shift + Tab
			if (e.shiftKey) {
				if (document.activeElement === firstFocusable[0]) {
					e.preventDefault();
					lastFocusable.focus();
				}
			} 
			// Tab
			else {
				if (document.activeElement === lastFocusable[0]) {
					e.preventDefault();
					firstFocusable.focus();
				}
			}
		});
	}

	/**
	 * Announce message to screen readers
	 *
	 * @param {string} message Message to announce
	 */
	function announceToScreenReader(message) {
		const $announcer = $('<div>')
			.attr({
				'role': 'status',
				'aria-live': 'polite',
				'aria-atomic': 'true'
			})
			.addClass('screen-reader-text')
			.text(message)
			.appendTo('body');

		// Remove after announcement
		setTimeout(function() {
			$announcer.remove();
		}, 1000);
	}

	// Initialize on document ready
	$(document).ready(init);

})(jQuery);
