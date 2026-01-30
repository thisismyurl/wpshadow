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
			this.achievementTabs();
			this.rewardRedemption();
			this.achievementNotifications();
		},

		/**
		 * Handle achievement category tabs
		 */
		achievementTabs: function() {
			$('.tab-button').on('click', function() {
				const category = $(this).data('category');

				// Update active tab
				$('.tab-button').removeClass('active');
				$(this).addClass('active');

				// Filter achievements
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

				// Confirm redemption
				if (!confirm('Are you sure you want to redeem this reward?')) {
					return;
				}

				button.prop('disabled', true).text('Processing...');

				$.ajax({
