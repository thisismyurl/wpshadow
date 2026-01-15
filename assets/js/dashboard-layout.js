/**
 * Dashboard Layout Manager - Client Side
 * Handles drag/drop saving and bulk apply prompt.
 *
 * @package WPS_WP_SUPPORT_THISISMYURL
 * @since 1.2601.74000
 */

(function($) {
	'use strict';

	var wpsDashboardLayout = {
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
					wpsDashboardLayout.hookPostboxEvents();
				}
			});
		},

		/**
		 * Hook into postbox events after initialization.
		 */
		hookPostboxEvents: function() {
			// Detect when widgets are reordered.
			$('#dashboard-widgets .meta-box-sortables').on('sortstop', function() {
				wpsDashboardLayout.saveLayout(true); // true = show prompt
			});

			// Detect when widgets are opened/closed (toggle state change).
			$(document).on('postbox-toggled', function() {
				wpsDashboardLayout.saveLayout(false); // false = don't show prompt for toggle
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
				wpsDashboardLayout.saveLayout(false);
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
				url: wps_dashboard_layout.ajaxUrl,
				method: 'POST',
				data: {
					action: 'wps_save_dashboard_layout',
					nonce: wps_dashboard_layout.nonce,
					context: wps_dashboard_layout.context,
					network: wps_dashboard_layout.network,
					layout: JSON.stringify(layout)
				},
				success: function(response) {
					if (response.success && showPrompt) {
						wpsDashboardLayout.showApplyPrompt(layout);
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
			if (wps_dashboard_layout.context === 'core' || this.hasChildModules()) {
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
			if (wps_dashboard_layout.context === 'core') {
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
			$('#wps-apply-layout-modal').remove();

			var modalHTML = '<div id="wps-apply-layout-modal" class="wps-modal-overlay" style="display:none;">' +
				'<div class="wps-modal-content">' +
				'<h2>' + wps_dashboard_layout.applyPrompt + '</h2>' +
				'<div class="wps-modal-body">' +
				'<label><input type="radio" name="wps-apply-scope" value="this" checked> ' + wps_dashboard_layout.applyThis + '</label><br>' +
				'<label><input type="radio" name="wps-apply-scope" value="children"> ' + wps_dashboard_layout.applyChildren + '</label><br>' +
				'<label><input type="radio" name="wps-apply-scope" value="all"> ' + wps_dashboard_layout.applyAll + '</label>' +
				'</div>' +
				'<div class="wps-modal-footer">' +
				'<button class="button button-primary wps-apply-confirm">' + wps_dashboard_layout.apply + '</button> ' +
				'<button class="button wps-apply-cancel">' + wps_dashboard_layout.cancel + '</button>' +
				'</div>' +
				'</div>' +
				'</div>';

			$('body').append(modalHTML);

			// Add simple CSS for modal.
			if (!$('#wps-modal-styles').length) {
				$('head').append(
					'<style id="wps-modal-styles">' +
					'.wps-modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 100000; display: flex; align-items: center; justify-content: center; }' +
					'.wps-modal-content { background: #fff; padding: 20px; border-radius: 4px; max-width: 500px; width: 90%; box-shadow: 0 5px 15px rgba(0,0,0,0.3); }' +
					'.wps-modal-content h2 { margin-top: 0; }' +
					'.wps-modal-body { margin: 20px 0; }' +
					'.wps-modal-body label { display: block; margin: 10px 0; cursor: pointer; }' +
					'.wps-modal-footer { text-align: right; border-top: 1px solid #ddd; padding-top: 15px; margin-top: 15px; }' +
					'</style>'
				);
			}

			// Show modal with animation.
			$('#wps-apply-layout-modal').fadeIn(200);

			// Bind events.
			$('.wps-apply-confirm').on('click', function() {
				var scope = $('input[name="wps-apply-scope"]:checked').val();
				wpsDashboardLayout.applyToScope(layout, scope);
				$('#wps-apply-layout-modal').fadeOut(200, function() {
					$(this).remove();
				});
			});

			$('.wps-apply-cancel').on('click', function() {
				$('#wps-apply-layout-modal').fadeOut(200, function() {
					$(this).remove();
				});
			});

			// Close on overlay click.
			$('#wps-apply-layout-modal').on('click', function(e) {
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
				url: wps_dashboard_layout.ajaxUrl,
				method: 'POST',
				data: {
					action: 'wps_apply_dashboard_layout',
					nonce: wps_dashboard_layout.nonce,
					context: wps_dashboard_layout.context,
					network: wps_dashboard_layout.network,
					scope: scope,
					layout: JSON.stringify(layout)
				},
				success: function(response) {
					if (response.success) {
						// Show success message.
						wpsDashboardLayout.showNotice(response.data.message, 'success');
					} else {
						wpsDashboardLayout.showNotice(response.data.message || 'Failed to apply layout.', 'error');
					}
				},
				error: function() {
					wpsDashboardLayout.showNotice('Failed to apply layout.', 'error');
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
		if (typeof wps_dashboard_layout !== 'undefined') {
			wpsDashboardLayout.init();
		}
		
		// Handle database stats refresh button.
		$(document).on('click', '.wps-refresh-database-stats', function(e) {
			e.preventDefault();
			
			var button = $(this);
			var container = $('#wps-database-stats-container');
			var spinner = button.siblings('.wps-refresh-spinner');
			
			// Disable button and show spinner.
			button.prop('disabled', true);
			spinner.show();
			
			// Make AJAX request.
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'wps_refresh_database_stats',
					nonce: WPS_dashboard_layout.moduleNonce
				},
				success: function(response) {
					if (response.success && response.data && response.data.stats) {
						// Update the widget content.
						var stats = response.data.stats;
						
						// Update total size.
						container.find('.wps-widget-content > div:first-child > div:first-child').text(
							formatBytes(stats.total_size)
						);
						
						// Update quick stats.
						var statsBoxes = container.find('.wps-widget-content > div:nth-child(2) > div');
						$(statsBoxes[0]).find('div:first-child').text(stats.table_count);
						$(statsBoxes[1]).find('div:first-child').text(Number(stats.expired_transients).toLocaleString());
						$(statsBoxes[2]).find('div:first-child').text(Number(stats.revisions).toLocaleString());
						$(statsBoxes[3]).find('div:first-child').text(Number(stats.autodrafts).toLocaleString());
						
						// Show success message briefly.
						if (typeof WPS_dashboard_layout !== 'undefined' && WPS_dashboard_layout.refreshSuccess) {
							var successMsg = $('<div class="notice notice-success inline" style="margin: 10px 0; padding: 8px;"><p>' + 
								WPS_dashboard_layout.refreshSuccess + '</p></div>');
							container.prepend(successMsg);
							setTimeout(function() {
								successMsg.fadeOut(function() { $(this).remove(); });
							}, 3000);
						}
					} else {
						// Show error message.
						var errorText = 'Failed to refresh database statistics.';
						if (typeof WPS_dashboard_layout !== 'undefined' && WPS_dashboard_layout.refreshError) {
							errorText = WPS_dashboard_layout.refreshError;
						}
						if (response.data && response.data.message) {
							errorText = response.data.message;
						}
						var errorMsg = $('<div class="notice notice-error inline" style="margin: 10px 0; padding: 8px;"><p>' + 
							errorText + '</p></div>');
						container.prepend(errorMsg);
						setTimeout(function() {
							errorMsg.fadeOut(function() { $(this).remove(); });
						}, 5000);
					}
				},
				error: function() {
					// Show error message.
					var errorText = 'An error occurred while refreshing database statistics.';
					if (typeof WPS_dashboard_layout !== 'undefined' && WPS_dashboard_layout.refreshError) {
						errorText = WPS_dashboard_layout.refreshError;
					}
					var errorMsg = $('<div class="notice notice-error inline" style="margin: 10px 0; padding: 8px;"><p>' + 
						errorText + '</p></div>');
					container.prepend(errorMsg);
					setTimeout(function() {
						errorMsg.fadeOut(function() { $(this).remove(); });
					}, 5000);
				},
				complete: function() {
					// Re-enable button and hide spinner.
					button.prop('disabled', false);
					spinner.hide();
				}
			});
		});
	});
	
	/**
	 * Format bytes to human-readable format.
	 * 
	 * @param {number} bytes - Bytes to format.
	 * @return {string} Formatted string.
	 */
	function formatBytes(bytes) {
		if (bytes === 0) return '0 B';
		var k = 1024;
		var sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
		var i = Math.floor(Math.log(bytes) / Math.log(k));
		return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
	}

})(jQuery);


