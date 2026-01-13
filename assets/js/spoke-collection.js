/**
 * Spoke Collection Gallery JavaScript
 *
 * Handles interactions, animations, and AJAX requests for the Spoke Collection interface.
 *
 * @package WPS_WP_SUPPORT_THISISMYURL
 * @since 1.2601.73002
 */

(function($) {
	'use strict';

	/**
	 * Spoke Collection Controller
	 */
	const SpokeCollection = {
		/**
		 * Initialize the controller
		 */
		init: function() {
			this.bindEvents();
			this.animateProgress();
			this.checkForNewMilestones();
		},

		/**
		 * Bind event listeners
		 */
		bindEvents: function() {
			// Install spoke button
			$(document).on('click', '.wps-install-spoke', this.handleInstall.bind(this));

			// Activate spoke button
			$(document).on('click', '.wps-activate-spoke', this.handleActivate.bind(this));

			// Deactivate spoke button
			$(document).on('click', '.wps-deactivate-spoke', this.handleDeactivate.bind(this));

			// Close milestone modal
			$(document).on('click', '.wps-close-modal, .wps-modal-overlay', this.closeMilestoneModal.bind(this));

			// Prevent modal content clicks from closing modal
			$(document).on('click', '.wps-modal-content', function(e) {
				e.stopPropagation();
			});
		},

		/**
		 * Handle spoke installation
		 */
		handleInstall: function(e) {
			e.preventDefault();
			const $button = $(e.currentTarget);
			const spoke = $button.data('spoke');
			const $card = $button.closest('.wps-spoke-card');

			// Show loading state
			$button.addClass('is-loading').prop('disabled', true);

			// Make AJAX request to install spoke
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'wps_install_spoke',
					spoke: spoke,
					nonce: wpsSpokeCollection.nonce
				},
				success: function(response) {
					if (response.success) {
						// Show success animation
						SpokeCollection.showInstallAnimation($card, spoke);

						// Update card state after animation
						setTimeout(function() {
							SpokeCollection.updateCardState($card, 'unlocked');
							$button.removeClass('is-loading').prop('disabled', false);

							// Show toast notification
							SpokeCollection.showToast(response.data.message || 'Spoke installed successfully!', 'success');

							// Check for milestones
							if (response.data.milestones && response.data.milestones.length > 0) {
								SpokeCollection.showMilestoneModal(response.data.milestones[0]);
							}
						}, 2000);
					} else {
						// Show error
						$button.removeClass('is-loading').prop('disabled', false);
						SpokeCollection.showToast(response.data || 'Installation failed. Please try again.', 'error');
					}
				},
				error: function() {
					$button.removeClass('is-loading').prop('disabled', false);
					SpokeCollection.showToast('An error occurred. Please try again.', 'error');
				}
			});
		},

		/**
		 * Handle spoke activation
		 */
		handleActivate: function(e) {
			e.preventDefault();
			const $button = $(e.currentTarget);
			const spoke = $button.data('spoke');
			const $card = $button.closest('.wps-spoke-card');

			// Show loading state
			$button.addClass('is-loading').prop('disabled', true);

			// Make AJAX request to activate spoke
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'wps_activate_spoke',
					spoke: spoke,
					nonce: wpsSpokeCollection.nonce
				},
				success: function(response) {
					if (response.success) {
						// Show activation animation
						SpokeCollection.showActivationAnimation($card, spoke);

						// Update card state after animation
						setTimeout(function() {
							SpokeCollection.updateCardState($card, 'active');
							$button.removeClass('is-loading').prop('disabled', false);

							// Show toast notification
							SpokeCollection.showToast(response.data.message || 'Spoke activated successfully!', 'success');

							// Check for milestones
							if (response.data.milestones && response.data.milestones.length > 0) {
								SpokeCollection.showMilestoneModal(response.data.milestones[0]);
							}

							// Reload page to update metrics
							setTimeout(function() {
								location.reload();
							}, 1500);
						}, 2000);
					} else {
						$button.removeClass('is-loading').prop('disabled', false);
						SpokeCollection.showToast(response.data || 'Activation failed. Please try again.', 'error');
					}
				},
				error: function() {
					$button.removeClass('is-loading').prop('disabled', false);
					SpokeCollection.showToast('An error occurred. Please try again.', 'error');
				}
			});
		},

		/**
		 * Handle spoke deactivation
		 */
		handleDeactivate: function(e) {
			e.preventDefault();
			const $button = $(e.currentTarget);
			const spoke = $button.data('spoke');
			const $card = $button.closest('.wps-spoke-card');

			const confirmMessage = wpsSpokeCollection.i18n.confirmDeactivate || 'Are you sure you want to deactivate this spoke?';
			if (!confirm(confirmMessage)) {
				return;
			}

			// Show loading state
			$button.addClass('is-loading').prop('disabled', true);

			// Make AJAX request to deactivate spoke
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'wps_deactivate_spoke',
					spoke: spoke,
					nonce: wpsSpokeCollection.nonce
				},
				success: function(response) {
					if (response.success) {
						// Update card state
						SpokeCollection.updateCardState($card, 'unlocked');
						$button.removeClass('is-loading').prop('disabled', false);

						// Show toast notification
						SpokeCollection.showToast(response.data.message || 'Spoke deactivated successfully!', 'success');

						// Reload page to update metrics
						setTimeout(function() {
							location.reload();
						}, 1000);
					} else {
						$button.removeClass('is-loading').prop('disabled', false);
						SpokeCollection.showToast(response.data || 'Deactivation failed. Please try again.', 'error');
					}
				},
				error: function() {
					$button.removeClass('is-loading').prop('disabled', false);
					SpokeCollection.showToast('An error occurred. Please try again.', 'error');
				}
			});
		},

		/**
		 * Show installation animation
		 */
		showInstallAnimation: function($card, spoke) {
			const $icon = $card.find('.wps-spoke-icon');
			const $badge = $card.find('.wps-spoke-badge');

			// Lock fades away
			$badge.find('.dashicons-lock').fadeOut(300, function() {
				$badge.find('.dashicons').remove();
				$badge.append('<span class="dashicons dashicons-unlock"></span>');
				$badge.fadeIn(300);
			});

			// Icon animates from grayed to partial color
			$icon.animate({ opacity: 0.7 }, 800);

			// Show confetti (optional - basic implementation)
			this.showConfetti($card);

			// Show toast
			this.showToast(spoke.toUpperCase() + ' Spoke Unlocked!', 'success');
		},

		/**
		 * Show activation animation
		 */
		showActivationAnimation: function($card, spoke) {
			const $icon = $card.find('.wps-spoke-icon');
			const $badge = $card.find('.wps-spoke-badge');

			// Icon glows/pulses
			$icon.css({
				animation: 'wps-glow-pulse 1s ease-in-out 3'
			});

			// Badge updates
			setTimeout(function() {
				$badge.find('.dashicons-unlock').fadeOut(200, function() {
					$(this).remove();
					$badge.append('<span class="dashicons dashicons-yes"></span>');
					$badge.find('.dashicons').fadeIn(200);
				});
			}, 500);

			// Icon fully colored
			setTimeout(function() {
				$icon.animate({ opacity: 1 }, 500);
			}, 1000);
		},

		/**
		 * Update card status state
		 */
		updateCardState: function($card, newState) {
			// Remove all status classes
			$card.removeClass('wps-spoke-locked wps-spoke-unlocked wps-spoke-active wps-spoke-mastered');

			// Add new status class
			$card.addClass('wps-spoke-' + newState);

			// Update status badge
			const $badge = $card.find('.wps-spoke-badge');
			$badge.removeClass('wps-badge-locked wps-badge-unlocked wps-badge-active wps-badge-mastered');
			$badge.addClass('wps-badge-' + newState);

			// Update action buttons
			const $actions = $card.find('.wps-spoke-actions');
			const spoke = $card.data('spoke');

			$actions.empty();

			if (newState === 'locked') {
				$actions.html(
					'<button class="button button-primary wps-install-spoke" data-spoke="' + spoke + '">' +
					'<span class="dashicons dashicons-download"></span>' +
					wpsSpokeCollection.i18n.install +
					'</button>'
				);
			} else if (newState === 'unlocked') {
				$actions.html(
					'<button class="button button-primary wps-activate-spoke" data-spoke="' + spoke + '">' +
					'<span class="dashicons dashicons-yes"></span>' +
					wpsSpokeCollection.i18n.activate +
					'</button>'
				);
			} else if (newState === 'active' || newState === 'mastered') {
				$actions.html(
					'<button class="button wps-deactivate-spoke" data-spoke="' + spoke + '">' +
					'<span class="dashicons dashicons-no"></span>' +
					wpsSpokeCollection.i18n.deactivate +
					'</button>'
				);
			}

			// Update status text
			const $statusText = $card.find('.wps-spoke-status-text');
			const statusTexts = {
				locked: wpsSpokeCollection.i18n.notInstalled,
				unlocked: wpsSpokeCollection.i18n.readyToActivate,
				active: wpsSpokeCollection.i18n.activeProcessing,
				mastered: wpsSpokeCollection.i18n.mastered
			};

			$statusText.text(statusTexts[newState] || '');
		},

		/**
		 * Show milestone achievement modal
		 */
		showMilestoneModal: function(milestone) {
			const $modal = $('#wps-milestone-modal');

			// Update modal content
			$modal.find('#wps-milestone-name').text(milestone.name || '');
			$modal.find('#wps-milestone-description').text(milestone.description || '');
			$modal.find('#wps-milestone-reward').text(milestone.reward || '');

			// Show modal with animation
			$modal.fadeIn(300);
			$modal.find('.wps-modal-content').addClass('wps-animate-bounce');
		},

		/**
		 * Close milestone modal
		 */
		closeMilestoneModal: function(e) {
			e.preventDefault();
			const $modal = $('#wps-milestone-modal');
			$modal.fadeOut(300);
			$modal.find('.wps-modal-content').removeClass('wps-animate-bounce');
		},

		/**
		 * Show toast notification
		 */
		showToast: function(message, type) {
			const toastClass = type === 'success' ? 'updated' : 'error';
			const $toast = $('<div class="notice ' + toastClass + ' is-dismissible"><p>' + message + '</p></div>');

			// Add to page
			$('.wps-spoke-collection h1').after($toast);

			// Auto-dismiss after 5 seconds
			setTimeout(function() {
				$toast.fadeOut(300, function() {
					$(this).remove();
				});
			}, 5000);

			// Make dismissible
			$toast.find('.notice-dismiss').on('click', function() {
				$toast.fadeOut(300, function() {
					$(this).remove();
				});
			});
		},

		/**
		 * Show confetti animation (simple version)
		 */
		showConfetti: function($card) {
			// Create confetti particles
			for (let i = 0; i < 20; i++) {
				const $confetti = $('<div class="wps-confetti"></div>');
				$confetti.css({
					position: 'absolute',
					top: '50%',
					left: '50%',
					width: '8px',
					height: '8px',
					background: this.getRandomColor(),
					transform: 'translate(-50%, -50%)',
					borderRadius: '50%',
					pointerEvents: 'none',
					zIndex: 1000
				});

				$card.css('position', 'relative').append($confetti);

				// Animate particles
				const angle = (Math.PI * 2 * i) / 20;
				const distance = 100 + Math.random() * 50;
				const x = Math.cos(angle) * distance;
				const y = Math.sin(angle) * distance;

				$confetti.animate({
					left: (50 + x) + '%',
					top: (50 + y) + '%',
					opacity: 0
				}, 1000, function() {
					$(this).remove();
				});
			}
		},

		/**
		 * Get random confetti color
		 */
		getRandomColor: function() {
			const colors = ['#2271b1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'];
			return colors[Math.floor(Math.random() * colors.length)];
		},

		/**
		 * Animate progress circles
		 */
		animateProgress: function() {
			$('.wps-progress-circle').each(function() {
				const $circle = $(this);
				const progress = parseInt($circle.data('progress'), 10) || 0;
				const $fill = $circle.find('.wps-progress-fill');
				const circumference = 2 * Math.PI * 35; // radius = 35

				// Set initial state
				$fill.css('stroke-dashoffset', circumference);

				// Animate to final state
				setTimeout(function() {
					const offset = circumference * (1 - progress / 100);
					$fill.css('stroke-dashoffset', offset);
				}, 100);
			});
		},

		/**
		 * Check for new milestones on page load
		 */
		checkForNewMilestones: function() {
			// This is handled by PHP in the view, but we can add additional checks here
			// if needed for real-time updates
		}
	};

	// Initialize on document ready
	$(document).ready(function() {
		if ($('.wps-spoke-collection').length) {
			SpokeCollection.init();
		}
	});

})(jQuery);
