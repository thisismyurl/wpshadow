/**
 * Core Support Admin Scripts
 *
 * @package TIMU_CORE_SUPPORT
 */

(function($) {
	'use strict';

	/**
	 * Initialize dashboard functionality.
	 */
	const TimuDashboard = {
		/**
		 * Initialize.
		 */
		init: function() {
			this.bindEvents();
			this.initFilters();
		},

		/**
		 * Initialize filter UI (no-op if filter removed).
		 */
		initFilters: function() {
			const $filter = $('#timu-filter-type');
			if ($filter.length === 0) {
				return; // Filter UI removed; skip.
			}
			// Restore from localStorage when present.
			const saved = localStorage.getItem('timu-filter-type');
			if (saved) {
				$filter.val(saved);
				$filter.trigger('change');
			}
		},

		/**
		 * Bind event listeners.
		 */
		bindEvents: function() {
			// Module toggle switches.
			$('.timu-module-toggle').on('change', this.handleToggle);

			// Hub collapse/expand.
			$(document).on('click', '.timu-hub-toggle', this.handleHubToggle);

			// Filter dropdown.
			$('#timu-filter-type').on('change', this.handleFilter);

			// Install buttons (both original and "Install and Activate" links).
			$(document).on('click', '.timu-btn-install, .timu-btn-install-activate', this.handleInstall);

			// Update buttons.
			$(document).on('click', '.timu-btn-update', this.handleUpdate);

			// Activate/Deactivate links.
			$(document).on('click', '.timu-activate, .timu-deactivate', this.handleToggleLink);
		},

		/**
		 * Handle collapsing/expanding spokes under a hub row.
		 *
		 * @param {Event} e - Click event.
		 */
		handleHubToggle: function(e) {
			e.preventDefault();
			const $btn = $(this);
			const group = $btn.data('group');
			if (!group) {
				return;
			}

			const isExpanded = $btn.attr('aria-expanded') === 'true';
			const $spokes = $( 'tr[data-parent="' + group + '"]' );

			if (isExpanded) {
				$spokes.addClass('timu-spokes-hidden');
				$btn.attr('aria-expanded', 'false').addClass('is-collapsed');
			} else {
				$spokes.removeClass('timu-spokes-hidden');
				$btn.attr('aria-expanded', 'true').removeClass('is-collapsed');
			}
		},

		/**
		 * Handle module toggle.
		 *
		 * @param {Event} e - Change event.
		 */
		handleToggle: function(e) {
			const $toggle = $(this);
			const slug = $toggle.data('slug');
			const enabled = $toggle.is(':checked');
			const $card = $toggle.closest('.timu-module-card');
			const isNetwork = window.location.pathname.includes('/wp-admin/network/');
			const isInstalled = $toggle.data('installed') === true || $toggle.data('installed') === 'true';

			if (!isInstalled) {
				$toggle.prop('checked', false);
				TimuDashboard.showNotice('warning', timuAdminData.i18n.installFirst);
				return;
			}

			// Disable toggle during request.
			$toggle.prop('disabled', true);

			// Add loading state.
			$card.addClass('timu-loading');

			// Send AJAX request.
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'timu_toggle_module',
					nonce: timuAdminData.toggleNonce,
					slug: slug,
					enabled: enabled ? 'true' : 'false',
					network: isNetwork ? 'true' : 'false'
				},
				success: function(response) {
					if (response.success) {
						// Update card state.
						if (enabled) {
							$card.removeClass('timu-module-disabled').addClass('timu-module-enabled');
							$card.find('.timu-badge-disabled')
								.removeClass('timu-badge-disabled')
								.addClass('timu-badge-enabled')
								.text(timuAdminData.i18n.enabled);
						} else {
							$card.removeClass('timu-module-enabled').addClass('timu-module-disabled');
							$card.find('.timu-badge-enabled')
								.removeClass('timu-badge-enabled')
								.addClass('timu-badge-disabled')
								.text(timuAdminData.i18n.disabled);
						}

						// Show success notice.
						TimuDashboard.showNotice('success', response.data.message);

						// Update stats.
						TimuDashboard.updateStats();
					} else {
						// Revert toggle state.
						$toggle.prop('checked', !enabled);

						// Show error notice.
						TimuDashboard.showNotice('error', response.data.message);
					}
				},
				error: function(xhr, status, error) {
					// Revert toggle state.
					$toggle.prop('checked', !enabled);

					// Show error notice.
					TimuDashboard.showNotice('error', timuAdminData.i18n.ajaxError);

					console.error('TIMU Toggle Error:', error);
				},
				complete: function() {
					// Remove loading state.
					$card.removeClass('timu-loading');
					$toggle.prop('disabled', false);
				}
			});
		},

		/**
		 * Handle module install.
		 *
		 * @param {Event} e - Click event.
		 */
		handleInstall: function(e) {
			e.preventDefault();

			const $btn = $(this);
			const $card = $btn.closest('.timu-module-card');
			const slug = $btn.data('slug');
			const isNetwork = window.location.pathname.includes('/wp-admin/network/');

			if (!slug) {
				TimuDashboard.showNotice('error', timuAdminData.i18n.ajaxError);
				return;
			}

			// Disable button and show loading state.
			$btn.prop('disabled', true).text(timuAdminData.i18n.installing);
			$card.addClass('timu-loading');

			// Send AJAX request.
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'timu_install_module',
					nonce: timuAdminData.actionNonce,
					slug: slug
				},
				success: function(response) {
					if (response.success) {
						// Show success notice.
						TimuDashboard.showNotice('success', response.data.message);

						// Refresh dashboard after 1.5 seconds.
						setTimeout(function() {
							location.reload();
						}, 1500);
					} else {
						// Show error notice.
						TimuDashboard.showNotice('error', response.data.message);
					}
				},
				error: function(xhr, status, error) {
					// Show error notice.
					TimuDashboard.showNotice('error', timuAdminData.i18n.ajaxError);
					console.error('TIMU Install Error:', error);
				},
				complete: function() {
					// Re-enable button and remove loading state.
					$card.removeClass('timu-loading');
					$btn.prop('disabled', false).text(timuAdminData.i18n.install);
				}
			});
		},

		/**
		 * Handle module update.
		 *
		 * @param {Event} e - Click event.
		 */
		handleUpdate: function(e) {
			e.preventDefault();

			const $btn = $(this);
			const $card = $btn.closest('.timu-module-card');
			const slug = $btn.data('slug');
			const isNetwork = window.location.pathname.includes('/wp-admin/network/');

			if (!slug) {
				TimuDashboard.showNotice('error', timuAdminData.i18n.ajaxError);
				return;
			}

			// Disable button and show loading state.
			$btn.prop('disabled', true).text(timuAdminData.i18n.updating);
			$card.addClass('timu-loading');

			// Send AJAX request.
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'timu_update_module',
					nonce: timuAdminData.actionNonce,
					slug: slug
				},
				success: function(response) {
					if (response.success) {
						// Show success notice.
						TimuDashboard.showNotice('success', response.data.message);

						// Refresh dashboard after 1.5 seconds.
						setTimeout(function() {
							location.reload();
						}, 1500);
					} else {
						// Show error notice.
						TimuDashboard.showNotice('error', response.data.message);
					}
				},
				error: function(xhr, status, error) {
					// Show error notice.
					TimuDashboard.showNotice('error', timuAdminData.i18n.ajaxError);
					console.error('TIMU Update Error:', error);
				},
				complete: function() {
					// Re-enable button and remove loading state.
					$card.removeClass('timu-loading');
					$btn.prop('disabled', false).text(timuAdminData.i18n.update);
				}
			});
		},

		/**
		 * Handle activate/deactivate link clicks.
		 *
		 * @param {Event} e - Click event.
		 */
		handleToggleLink: function(e) {
			e.preventDefault();

			const $link = $(this);
			const $card = $link.closest('.timu-module-card');
			const slug = $link.data('slug');
			const isActivate = $link.hasClass('timu-activate');
			const isNetwork = window.location.pathname.includes('/wp-admin/network/');

			if (!slug) {
				TimuDashboard.showNotice('error', timuAdminData.i18n.ajaxError);
				return;
			}

			// Disable link and show loading state.
			$link.addClass('timu-loading');
			$card.addClass('timu-loading');

			// Send AJAX request (use same endpoint as toggle switches).
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'timu_toggle_module',
					nonce: timuAdminData.toggleNonce,
					slug: slug,
					enabled: isActivate ? 'true' : 'false',
					network: isNetwork ? 'true' : 'false'
				},
				success: function(response) {
					if (response.success) {
						// Show success notice.
						TimuDashboard.showNotice('success', response.data.message);

						// Refresh dashboard after 1 second.
						setTimeout(function() {
							location.reload();
						}, 1000);
					} else {
						// Show error notice.
						TimuDashboard.showNotice('error', response.data.message);
					}
				},
				error: function(xhr, status, error) {
					// Show error notice.
					TimuDashboard.showNotice('error', timuAdminData.i18n.ajaxError);
					console.error('TIMU Toggle Error:', error);
				},
				complete: function() {
					// Remove loading state.
					$link.removeClass('timu-loading');
					$card.removeClass('timu-loading');
				}
			});
		},

		/**
		 * Handle filter change.
		 *
		 * @param {Event} e - Change event.
		 */
		handleFilter: function(e) {
			const filterType = $(this).val();
			const $cards = $('.timu-module-card');

			// Save filter preference.
			localStorage.setItem('timu-filter-type', filterType);

			// Filter cards.
			if (filterType === 'all') {
				$cards.show();
			} else {
				$cards.hide();
				$cards.filter('[data-type="' + filterType + '"]').show();
			}

			// Check if any cards are visible.
			const visibleCount = $cards.filter(':visible').length;
			if (visibleCount === 0) {
				TimuDashboard.showNoResults();
			} else {
				$('.timu-no-results').remove();
			}
		},

		/**
		 * Show "no results" message.
		 */
		showNoResults: function() {
			if ($('.timu-no-results').length) {
				return;
			}

			const $message = $('<div class="timu-no-results">')
				.html('<span class="dashicons dashicons-info"></span><p>' + timuAdminData.i18n.noResults + '</p>');

			$('.timu-modules-grid').append($message);
		},

		/**
		 * Update statistics counters.
		 */
		updateStats: function() {
			const $cards = $('.timu-module-card');
			const installed = $cards.filter('[data-status="installed"], [data-status="update"]');
			const totalInstalled = installed.length;
			const enabled = installed.filter('.timu-module-enabled').length;
			const available = $cards.filter('[data-status="available"]').length;
			const updates = $cards.filter('[data-status="update"]').length;

			$('.timu-stat-card').eq(0).find('.timu-stat-value').text(totalInstalled);
			$('.timu-stat-card').eq(1).find('.timu-stat-value').text(enabled);
			$('.timu-stat-card').eq(2).find('.timu-stat-value').text(available);
			$('.timu-stat-card').eq(3).find('.timu-stat-value').text(updates);
		},

		/**
		 * Show admin notice.
		 *
		 * @param {string} type - Notice type (success, error, warning, info).
		 * @param {string} message - Notice message.
		 */
		showNotice: function(type, message) {
			const $notice = $('<div class="notice notice-' + type + ' is-dismissible">')
				.html('<p>' + message + '</p>');

			// Insert after page title.
			$('.wrap > h1').after($notice);

			// Initialize dismiss functionality.
			$(document).trigger('wp-updates-notice-added');

			// Auto-dismiss after 5 seconds.
			setTimeout(function() {
				$notice.fadeOut(function() {
					$(this).remove();
				});
			}, 5000);
		}
	};

	/**
	 * Initialize on document ready.
	 */
	$(document).ready(function() {
		if ($('.timu-dashboard-wrap').length) {
			TimuDashboard.init();
		}
	});

})(jQuery);

/* @changelog
 * [1.2601.71920] - 2026-01-07 19:35
 * - Issue #33: In-dashboard install/update flows
 * - Added handleInstall() method to trigger AJAX install action
 * - Added handleUpdate() method to trigger AJAX update action
 * - Integrated install/update button click handlers with event delegation
 * - Dashboard auto-reloads after successful installation/update
 * - Button states update with loading text and disabled state
 * - Error handling with localized i18n messages
 * - Binds to .timu-btn-install and .timu-btn-update button classes
 *
 * [1.2601.71900] - 2026-01-07 18:45
 * - Block toggles for non-installed modules and show warning
 * - Update stats to reflect available/update counts from catalog
 * - Prepare UI for bundled catalog updater flow
 * [1.2601.71701] - 2026-01-07 17:17
 * - Initial admin JavaScript
 * - jQuery-based initialization
 *
 * [1.2601.71712] - 2026-01-07 17:17
 * - Added TimuDashboard module for dashboard functionality
 * - Implemented module toggle handler with AJAX
 * - Added filter functionality for hub/spoke types
 * - Implemented stats counter updates
 * - Added admin notice system with auto-dismiss
 * - LocalStorage persistence for filter preferences
 */
