/**
 * Dashboard Layout Manager - Client Side
 * Handles drag/drop saving and bulk apply prompt.
 *
 * @package TIMU_WP_SUPPORT_THISISMYURL
 * @since 1.2601.74000
 */

(function($) {
	'use strict';

	var timuDashboardLayout = {
		/**
		 * Initialize layout management.
		 */
		init: function() {
			// Hook into postbox save events.
			if (typeof postboxes !== 'undefined') {
				// Monitor for layout changes.
				$('.postbox-container').on('sortupdate', this.onLayoutChanged.bind(this));
				$('.postbox .hndle, .postbox .handle').on('click', this.onWidgetToggled.bind(this));
			}

			// Initialize after WordPress postboxes are ready.
			$(document).ready(function() {
				if (typeof postboxes !== 'undefined') {
					timuDashboardLayout.hookPostboxEvents();
				}
			});
		},

		/**
		 * Hook into postbox events after initialization.
		 */
		hookPostboxEvents: function() {
			// Detect when widgets are reordered.
			$('#dashboard-widgets .meta-box-sortables').on('sortstop', function() {
				timuDashboardLayout.saveLayout(true); // true = show prompt
			});

			// Detect when widgets are opened/closed (toggle state change).
			$(document).on('postbox-toggled', function() {
				timuDashboardLayout.saveLayout(false); // false = don't show prompt for toggle
			});
		},

		/**
		 * Handle layout changed event.
		 *
		 * @param {Event} event Sortable event.
		 */
		onLayoutChanged: function(event) {
			this.saveLayout(true); // Show prompt when manually reordered.
		},

		/**
		 * Handle widget toggle event.
		 *
		 * @param {Event} event Click event.
		 */
		onWidgetToggled: function(event) {
			// Don't show prompt for simple toggle, only for reorder.
			setTimeout(function() {
				timuDashboardLayout.saveLayout(false);
			}, 100);
		},

		/**
		 * Save current layout and optionally show apply prompt.
		 *
		 * @param {boolean} showPrompt Whether to show bulk apply prompt.
		 */
		saveLayout: function(showPrompt) {
			var layout = this.getCurrentLayout();

			// Save via AJAX.
			$.ajax({
				url: timu_dashboard_layout.ajaxUrl,
				method: 'POST',
				data: {
					action: 'timu_save_dashboard_layout',
					nonce: timu_dashboard_layout.nonce,
					context: timu_dashboard_layout.context,
					network: timu_dashboard_layout.network,
					layout: JSON.stringify(layout)
				},
				success: function(response) {
					if (response.success && showPrompt) {
						timuDashboardLayout.showApplyPrompt(layout);
					}
				},
				error: function() {
					console.error('Failed to save dashboard layout.');
				}
			});
		},

		/**
		 * Get current layout from DOM.
		 *
		 * @return {Object} Layout object with normal and side arrays.
		 */
		getCurrentLayout: function() {
			var layout = {
				normal: [],
				side: []
			};

			// Get widgets from normal column.
			$('#postbox-container-1 .postbox').each(function() {
				layout.normal.push($(this).attr('id'));
			});

			// Get widgets from side column.
			$('#postbox-container-2 .postbox').each(function() {
				layout.side.push($(this).attr('id'));
			});

			return layout;
		},

		/**
		 * Show bulk apply prompt modal.
		 *
		 * @param {Object} layout Current layout to apply.
		 */
		showApplyPrompt: function(layout) {
			// Check if we have children or other modules.
			if (timu_dashboard_layout.context === 'core' || this.hasChildModules()) {
				this.renderPromptModal(layout);
			}
		},

		/**
		 * Check if current context has child modules.
		 *
		 * @return {boolean} True if children exist.
		 */
		hasChildModules: function() {
			// For core, always has children (hubs).
			if (timu_dashboard_layout.context === 'core') {
				return true;
			}

			// For hubs, check if spokes exist (would need to be passed from PHP).
			// For now, assume yes.
			return true;
		},

		/**
		 * Render the apply prompt modal.
		 *
		 * @param {Object} layout Layout to apply.
		 */
		renderPromptModal: function(layout) {
			// Remove existing modal if any.
			$('#timu-apply-layout-modal').remove();

			var modalHTML = '<div id="timu-apply-layout-modal" class="timu-modal-overlay" style="display:none;">' +
				'<div class="timu-modal-content">' +
				'<h2>' + timu_dashboard_layout.applyPrompt + '</h2>' +
				'<div class="timu-modal-body">' +
				'<label><input type="radio" name="timu-apply-scope" value="children" checked> ' + timu_dashboard_layout.applyChildren + '</label><br>' +
				'<label><input type="radio" name="timu-apply-scope" value="all"> ' + timu_dashboard_layout.applyAll + '</label>' +
				'</div>' +
				'<div class="timu-modal-footer">' +
				'<button class="button button-primary timu-apply-confirm">' + timu_dashboard_layout.apply + '</button> ' +
				'<button class="button timu-apply-cancel">' + timu_dashboard_layout.cancel + '</button>' +
				'</div>' +
				'</div>' +
				'</div>';

			$('body').append(modalHTML);

			// Add simple CSS for modal.
			if (!$('#timu-modal-styles').length) {
				$('head').append(
					'<style id="timu-modal-styles">' +
					'.timu-modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 100000; display: flex; align-items: center; justify-content: center; }' +
					'.timu-modal-content { background: #fff; padding: 20px; border-radius: 4px; max-width: 500px; width: 90%; box-shadow: 0 5px 15px rgba(0,0,0,0.3); }' +
					'.timu-modal-content h2 { margin-top: 0; }' +
					'.timu-modal-body { margin: 20px 0; }' +
					'.timu-modal-body label { display: block; margin: 10px 0; cursor: pointer; }' +
					'.timu-modal-footer { text-align: right; border-top: 1px solid #ddd; padding-top: 15px; margin-top: 15px; }' +
					'</style>'
				);
			}

			// Show modal with animation.
			$('#timu-apply-layout-modal').fadeIn(200);

			// Bind events.
			$('.timu-apply-confirm').on('click', function() {
				var scope = $('input[name="timu-apply-scope"]:checked').val();
				timuDashboardLayout.applyToScope(layout, scope);
				$('#timu-apply-layout-modal').fadeOut(200, function() {
					$(this).remove();
				});
			});

			$('.timu-apply-cancel').on('click', function() {
				$('#timu-apply-layout-modal').fadeOut(200, function() {
					$(this).remove();
				});
			});

			// Close on overlay click.
			$('#timu-apply-layout-modal').on('click', function(e) {
				if (e.target === this) {
					$(this).fadeOut(200, function() {
						$(this).remove();
					});
				}
			});
		},

		/**
		 * Apply layout to specified scope.
		 *
		 * @param {Object} layout Layout configuration.
		 * @param {string} scope Scope: 'children' or 'all'.
		 */
		applyToScope: function(layout, scope) {
			$.ajax({
				url: timu_dashboard_layout.ajaxUrl,
				method: 'POST',
				data: {
					action: 'timu_apply_dashboard_layout',
					nonce: timu_dashboard_layout.nonce,
					context: timu_dashboard_layout.context,
					network: timu_dashboard_layout.network,
					scope: scope,
					layout: JSON.stringify(layout)
				},
				success: function(response) {
					if (response.success) {
						// Show success message.
						timuDashboardLayout.showNotice(response.data.message, 'success');
					} else {
						timuDashboardLayout.showNotice(response.data.message || 'Failed to apply layout.', 'error');
					}
				},
				error: function() {
					timuDashboardLayout.showNotice('Failed to apply layout.', 'error');
				}
			});
		},

		/**
		 * Show admin notice.
		 *
		 * @param {string} message Notice message.
		 * @param {string} type Notice type (success, error, warning).
		 */
		showNotice: function(message, type) {
			var noticeClass = 'notice notice-' + type + ' is-dismissible';
			var noticeHTML = '<div class="' + noticeClass + '"><p>' + message + '</p></div>';

			// Insert after h1 or at top of .wrap.
			if ($('.wrap h1').length) {
				$('.wrap h1').after(noticeHTML);
			} else {
				$('.wrap').prepend(noticeHTML);
			}

			// Auto-dismiss after 5 seconds.
			setTimeout(function() {
				$('.wrap .notice').fadeOut(400, function() {
					$(this).remove();
				});
			}, 5000);
		}
	};

	// Initialize on document ready.
	$(document).ready(function() {
		if (typeof timu_dashboard_layout !== 'undefined') {
			timuDashboardLayout.init();
		}
	});

})(jQuery);
