/**
 * WPS Dashboard Drag and Drop
 * Custom metabox drag-and-drop functionality
 *
 * @package WPSHADOW_CORE_SUPPORT
 */

(function($) {
	'use strict';

	const WPShadowDashboardDrag = {
		/**
		 * Initialize drag and drop.
		 */
		init: function() {
			if (!$('.wps-metabox-holder').length) {
				return;
			}

			this.enableSortable();
			this.bindToggle();
			this.loadState();
		},

		/**
		 * Enable sortable on metabox containers.
		 */
		enableSortable: function() {
			$('.wps-metabox-container').sortable({
				placeholder: 'wps-metabox-placeholder',
				connectWith: '.wps-metabox-container',
				handle: '.wps-metabox-handle',
				cursor: 'move',
				opacity: 0.65,
				tolerance: 'pointer',
				items: '.wps-metabox',
				stop: function() {
					WPShadowDashboardDrag.saveState();
				}
			});
		},

		/**
		 * Bind toggle collapse/expand.
		 */
		bindToggle: function() {
			$(document).on('click', '.wps-metabox-toggle', function(e) {
				e.preventDefault();
				const $metabox = $(this).closest('.wps-metabox');
				const $content = $metabox.find('.wps-metabox-content');
				
				$metabox.toggleClass('closed');
				$content.slideToggle(200);
				
				WPShadowDashboardDrag.saveState();
			});
		},

		/**
		 * Save metabox state (order and closed state).
		 */
		saveState: function() {
			const state = {
				order: {},
				closed: []
			};

			// Get order for each container
			$('.wps-metabox-container').each(function() {
				const containerId = $(this).data('container');
				state.order[containerId] = [];
				
				$(this).find('.wps-metabox').each(function() {
					const metaboxId = $(this).data('metabox-id');
					state.order[containerId].push(metaboxId);
					
					if ($(this).hasClass('closed')) {
						state.closed.push(metaboxId);
					}
				});
			});

			// Save via AJAX
			$.post(ajaxurl, {
				action: 'wpshadow_save_metabox_state',
				nonce: wpsDashboardDrag.nonce,
				state: JSON.stringify(state)
			});
		},

		/**
		 * Load saved state and apply it.
		 */
		loadState: function() {
			const savedState = wpsDashboardDrag.savedState;
			if (!savedState) {
				return;
			}

			try {
				const state = JSON.parse(savedState);
				
				// Apply closed state
				if (state.closed && state.closed.length) {
					state.closed.forEach(function(metaboxId) {
						const $metabox = $('.wps-metabox[data-metabox-id="' + metaboxId + '"]');
						$metabox.addClass('closed');
						$metabox.find('.wps-metabox-content').hide();
					});
				}

				// Apply order
				if (state.order) {
					Object.keys(state.order).forEach(function(containerId) {
						const $container = $('.wps-metabox-container[data-container="' + containerId + '"]');
						const order = state.order[containerId];
						
						order.forEach(function(metaboxId) {
							const $metabox = $('.wps-metabox[data-metabox-id="' + metaboxId + '"]');
							if ($metabox.length) {
								$container.append($metabox);
							}
						});
					});
				}
			} catch (e) {
				// Invalid saved state, ignore
			}
		}
	};

	/**
	 * Initialize on document ready.
	 */
	$(document).ready(function() {
		WPShadowDashboardDrag.init();
	});

})(jQuery);


