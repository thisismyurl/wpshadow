/**
 * TIMU Dashboard Drag and Drop
 * Custom metabox drag-and-drop functionality
 *
 * @package TIMU_CORE_SUPPORT
 */

(function($) {
	'use strict';

	const TimuDashboardDrag = {
		/**
		 * Initialize drag and drop.
		 */
		init: function() {
			if (!$('.timu-metabox-holder').length) {
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
			$('.timu-metabox-container').sortable({
				placeholder: 'timu-metabox-placeholder',
				connectWith: '.timu-metabox-container',
				handle: '.timu-metabox-handle',
				cursor: 'move',
				opacity: 0.65,
				tolerance: 'pointer',
				items: '.timu-metabox',
				stop: function() {
					TimuDashboardDrag.saveState();
				}
			});
		},

		/**
		 * Bind toggle collapse/expand.
		 */
		bindToggle: function() {
			$(document).on('click', '.timu-metabox-toggle', function(e) {
				e.preventDefault();
				const $metabox = $(this).closest('.timu-metabox');
				const $content = $metabox.find('.timu-metabox-content');
				
				$metabox.toggleClass('closed');
				$content.slideToggle(200);
				
				TimuDashboardDrag.saveState();
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
			$('.timu-metabox-container').each(function() {
				const containerId = $(this).data('container');
				state.order[containerId] = [];
				
				$(this).find('.timu-metabox').each(function() {
					const metaboxId = $(this).data('metabox-id');
					state.order[containerId].push(metaboxId);
					
					if ($(this).hasClass('closed')) {
						state.closed.push(metaboxId);
					}
				});
			});

			// Save via AJAX
			$.post(ajaxurl, {
				action: 'timu_save_metabox_state',
				nonce: timuDashboardDrag.nonce,
				state: JSON.stringify(state)
			});
		},

		/**
		 * Load saved state and apply it.
		 */
		loadState: function() {
			const savedState = timuDashboardDrag.savedState;
			if (!savedState) {
				return;
			}

			try {
				const state = JSON.parse(savedState);
				
				// Apply closed state
				if (state.closed && state.closed.length) {
					state.closed.forEach(function(metaboxId) {
						const $metabox = $('.timu-metabox[data-metabox-id="' + metaboxId + '"]');
						$metabox.addClass('closed');
						$metabox.find('.timu-metabox-content').hide();
					});
				}

				// Apply order
				if (state.order) {
					Object.keys(state.order).forEach(function(containerId) {
						const $container = $('.timu-metabox-container[data-container="' + containerId + '"]');
						const order = state.order[containerId];
						
						order.forEach(function(metaboxId) {
							const $metabox = $('.timu-metabox[data-metabox-id="' + metaboxId + '"]');
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
		TimuDashboardDrag.init();
	});

})(jQuery);
