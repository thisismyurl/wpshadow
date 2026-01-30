/**
 * WPShadow Gamification JavaScript
 *
 * Phase 8: Gamification System - JS
 *
 * @package WPShadow
 * @since   1.2604.0400
 */

(function($) {
	'use strict';

	const WPShadowGamification = {
		/**
		 * Initialize gamification features
		 */
		init: function() {
			if ( typeof wpShadowGamification === 'undefined' ) {
				return;
			}

			this.achievementTabs();
			this.rewardRedemption();
			this.earnActions();
		},

		/**
		 * Handle achievement category tabs
		 */
		achievementTabs: function() {
			$('.tab-button').on('click', function() {
				const category = $(this).data('category');

				$('.tab-button').removeClass('active');
				$(this).addClass('active');

				if (category === 'all') {
					$('.achievement-card').show();
				} else {
					$('.achievement-card').hide();
					$(`.achievement-card[data-category="${category}"]`).show();
				}
			});
		},

		/**
		 * Handle reward redemption
		 */
		rewardRedemption: function() {
			$('.redeem-reward').on('click', function(e) {
				e.preventDefault();

				const button = $(this);
				const rewardId = button.data('reward-id');

				if (!confirm('Are you sure you want to redeem this reward?')) {
					return;
				}

				button.prop('disabled', true).text('Processing...');

				$.ajax({
					url: wpShadowGamification.ajaxurl,
					method: 'POST',
					data: {
						action: 'wpshadow_redeem_reward',
						nonce: wpShadowGamification.nonce,
						reward_id: rewardId
					},
					success: function(response) {
						if (response.success) {
							WPShadowGamification.showNotice('success', response.data.message || 'Reward redeemed.');
							window.location.reload();
						} else {
							WPShadowGamification.showNotice('error', response.data.message || 'Unable to redeem reward.');
							button.prop('disabled', false).text('Redeem');
						}
					},
					error: function() {
						WPShadowGamification.showNotice('error', 'Network error. Please try again.');
						button.prop('disabled', false).text('Redeem');
					}
				});
			});
		},

		/**
		 * Handle earn-action claims
		 */
		earnActions: function() {
			$('.wpshadow-earn-action').on('click', function(e) {
				e.preventDefault();

				const button = $(this);
				const actionId = button.data('action-id');
				const actionUrl = button.data('action-url');

				if (actionUrl) {
					window.open(actionUrl, '_blank', 'noopener');
				}

				button.prop('disabled', true).text('Claiming...');

				$.ajax({
					url: wpShadowGamification.ajaxurl,
					method: 'POST',
					data: {
						action: 'wpshadow_claim_earn_action',
						nonce: wpShadowGamification.nonce,
						action_id: actionId
					},
					success: function(response) {
						if (response.success) {
							WPShadowGamification.showNotice('success', response.data.message || 'Points awarded.');
							button.text('Claimed');
						} else {
							WPShadowGamification.showNotice('error', response.data.message || 'Unable to claim points.');
							button.prop('disabled', false).text('Claim Points');
						}
					},
					error: function() {
						WPShadowGamification.showNotice('error', 'Network error. Please try again.');
						button.prop('disabled', false).text('Claim Points');
					}
				});
			});
		},

		/**
		 * Display admin notice
		 */
		showNotice: function(type, message) {
			const noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
			const notice = `<div class="notice ${noticeClass} is-dismissible"><p>${message}</p></div>`;

			$('.wpshadow-gamification-page h1').after(notice);
		}
	};

	$(document).ready(function() {
		WPShadowGamification.init();
	});

})(jQuery);
