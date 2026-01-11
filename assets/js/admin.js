/**
 * Core Support Admin Scripts
 *
 * @package WPS_CORE_SUPPORT
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
			this.syncInitialStates();
		},

		/**
		 * Initialize filter UI (no-op if filter removed).
		 */
		initFilters: function() {
			const $filter = $('#wps-filter-type');
			if ($filter.length === 0) {
				return; // Filter UI removed; skip.
			}
			// Restore from localStorage when present.
			const saved = localStorage.getItem('wps-filter-type');
			if (saved) {
				$filter.val(saved);
				$filter.trigger('change');
			}
		},

		/**
		 * Apply initial card states based on toggle values.
		 */
		syncInitialStates: function() {
			$('.wps-module-toggle').each(function() {
				const $toggle = $(this);
				const $card = $toggle.closest('.wps-module-card');
				if (!$card.length) {
					return;
				}

				TimuDashboard.applyModuleState($card, $toggle.is(':checked'));
			});
		},

		/**
		 * Apply enabled/disabled UI state to a module card.
		 *
		 * @param {jQuery} $card - Module card element.
		 * @param {boolean} enabled - Whether the module is enabled.
		 */
		applyModuleState: function($card, enabled) {
			$card.toggleClass('wps-module-enabled', enabled);
			$card.toggleClass('wps-module-disabled', !enabled);

			if (!$card.hasClass('wps-widget-module-card')) {
				return;
			}

			$card.toggleClass('wps-module-card-inactive', !enabled);

			const $link = $card.find('.wps-module-link').first();
			if ($link.length) {
				const storedUrl = $link.attr('data-url') || '';
				if (!enabled) {
					if ($link.attr('href')) {
						$link.attr('data-url', $link.attr('href'));
					}
					$link.removeAttr('href')
						.attr('aria-disabled', 'true')
						.attr('tabindex', '-1')
						.addClass('is-link-disabled');
				} else if (storedUrl) {
					$link.attr('href', storedUrl)
						.removeAttr('aria-disabled')
						.removeAttr('tabindex')
						.removeClass('is-link-disabled');
				} else {
					$link.removeAttr('aria-disabled')
						.removeAttr('tabindex')
						.removeClass('is-link-disabled');
				}
			}
		},

		/**
		 * Bind event listeners.
		 */
		bindEvents: function() {
			// Module toggle switches.
			$('.wps-module-toggle').on('change', this.handleToggle);

			// Hub collapse/expand.
			$(document).on('click', '.wps-hub-toggle', this.handleHubToggle);

			// Filter dropdown.
			$('#wps-filter-type').on('change', this.handleFilter);

			// Install buttons (both original and "Install and Activate" links).
			$(document).on('click', '.wps-btn-install, .wps-btn-install-activate', this.handleInstall);

			// Update buttons.
			$(document).on('click', '.wps-btn-update', this.handleUpdate);

			// Activate/Deactivate links.
			$(document).on('click', '.wps-activate, .wps-deactivate', this.handleToggleLink);
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
				$spokes.addClass('wps-spokes-hidden');
				$btn.attr('aria-expanded', 'false').addClass('is-collapsed');
			} else {
				$spokes.removeClass('wps-spokes-hidden');
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
			const $card = $toggle.closest('.wps-module-card');
			const isNetwork = window.location.pathname.includes('/wp-admin/network/');
			// jQuery .data() parses '1'/'0' to numbers 1/0, so check for multiple types.
			const installedData = $toggle.data('installed');
			const isInstalled = installedData === 1 || installedData === '1' || installedData === true || installedData === 'true';

		if (!slug) {
			$toggle.prop('checked', false);
			TimuDashboard.showNotice('error', wpsAdminData.i18n.ajaxError);
			return;
		}

		if (!isInstalled) {
			$toggle.prop('checked', false);
			TimuDashboard.showNotice('warning', wpsAdminData.i18n.installFirst);
			return;

			// Disable toggle during request.
			$toggle.prop('disabled', true);

			// Add loading state.
			$card.addClass('wps-loading');

			// Send AJAX request.
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'wps_toggle_module',
					nonce: wpsAdminData.toggleNonce,
					slug: slug,
					enabled: enabled ? 'true' : 'false',
					network: isNetwork ? 'true' : 'false'
				},
				success: function(response) {
					if (response.success) {
						// Update card state.
						TimuDashboard.applyModuleState($card, enabled);
						if (enabled) {
							$card.find('.wps-badge-disabled')
								.removeClass('wps-badge-disabled')
								.addClass('wps-badge-enabled')
								.text(wpsAdminData.i18n.enabled);
						} else {
							$card.find('.wps-badge-enabled')
								.removeClass('wps-badge-enabled')
								.addClass('wps-badge-disabled')
								.text(wpsAdminData.i18n.disabled);
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
					TimuDashboard.showNotice('error', wpsAdminData.i18n.ajaxError);

					console.error('WPS Toggle Error:', error);
				},
				complete: function() {
					// Remove loading state.
					$card.removeClass('wps-loading');
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
			const $card = $btn.closest('.wps-module-card');
			const slug = $btn.data('slug');
			const isNetwork = window.location.pathname.includes('/wp-admin/network/');

			if (!slug) {
				TimuDashboard.showNotice('error', wpsAdminData.i18n.ajaxError);
				return;
			}

			// Disable button and show loading state.
			$btn.prop('disabled', true).text(wpsAdminData.i18n.installing);
			$card.addClass('wps-loading');

			// Send AJAX request.
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'wps_install_module',
					nonce: wpsAdminData.actionNonce,
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
					TimuDashboard.showNotice('error', wpsAdminData.i18n.ajaxError);
					console.error('WPS Install Error:', error);
				},
				complete: function() {
					// Re-enable button and remove loading state.
					$card.removeClass('wps-loading');
					$btn.prop('disabled', false).text(wpsAdminData.i18n.install);
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
			const $card = $btn.closest('.wps-module-card');
			const slug = $btn.data('slug');
			const isNetwork = window.location.pathname.includes('/wp-admin/network/');

			if (!slug) {
				TimuDashboard.showNotice('error', wpsAdminData.i18n.ajaxError);
				return;
			}

			// Disable button and show loading state.
			$btn.prop('disabled', true).text(wpsAdminData.i18n.updating);
			$card.addClass('wps-loading');

			// Send AJAX request.
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'wps_update_module',
					nonce: wpsAdminData.actionNonce,
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
					TimuDashboard.showNotice('error', wpsAdminData.i18n.ajaxError);
					console.error('WPS Update Error:', error);
				},
				complete: function() {
					// Re-enable button and remove loading state.
					$card.removeClass('wps-loading');
					$btn.prop('disabled', false).text(wpsAdminData.i18n.update);
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
			const $card = $link.closest('.wps-module-card');
			const slug = $link.data('slug');
			const isActivate = $link.hasClass('wps-activate');
			const isNetwork = window.location.pathname.includes('/wp-admin/network/');

			if (!slug) {
				TimuDashboard.showNotice('error', wpsAdminData.i18n.ajaxError);
				return;
			}

			// Disable link and show loading state.
			$link.addClass('wps-loading');
			$card.addClass('wps-loading');

			// Send AJAX request (use same endpoint as toggle switches).
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'wps_toggle_module',
					nonce: wpsAdminData.toggleNonce,
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
					TimuDashboard.showNotice('error', wpsAdminData.i18n.ajaxError);
					console.error('WPS Toggle Error:', error);
				},
				complete: function() {
					// Remove loading state.
					$link.removeClass('wps-loading');
					$card.removeClass('wps-loading');
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
			const $cards = $('.wps-module-card');

			// Save filter preference.
			localStorage.setItem('wps-filter-type', filterType);

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
				$('.wps-no-results').remove();
			}
		},

		/**
		 * Show "no results" message.
		 */
		showNoResults: function() {
			if ($('.wps-no-results').length) {
				return;
			}

			const $message = $('<div class="wps-no-results">')
				.html('<span class="dashicons dashicons-info"></span><p>' + wpsAdminData.i18n.noResults + '</p>');

			$('.wps-modules-grid').append($message);
		},

		/**
		 * Update statistics counters.
		 */
		updateStats: function() {
			const $cards = $('.wps-module-card');
			const installed = $cards.filter('[data-status="installed"], [data-status="update"]');
			const totalInstalled = installed.length;
			const enabled = installed.filter('.wps-module-enabled').length;
			const available = $cards.filter('[data-status="available"]').length;
			const updates = $cards.filter('[data-status="update"]').length;

			$('.wps-stat-card').eq(0).find('.wps-stat-value').text(totalInstalled);
			$('.wps-stat-card').eq(1).find('.wps-stat-value').text(enabled);
			$('.wps-stat-card').eq(2).find('.wps-stat-value').text(available);
			$('.wps-stat-card').eq(3).find('.wps-stat-value').text(updates);
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
		// Initialize if modules grid or dashboard widgets are present.
		if ($('.wps-dashboard-wrap').length || $('.wps-module-toggle').length || $('#dashboard-widgets').length) {
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
 * - Binds to .wps-btn-install and .wps-btn-update button classes
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


