/**
 * CPT Drag & Drop Ordering
 *
 * Provides drag-and-drop functionality for reordering custom post types
 * in the WordPress admin interface.
 *
 * @package WPShadow
 * @since   1.6034.1215
 */

(function($) {
	'use strict';

	/**
	 * CPT Drag & Drop Handler
	 */
	const WPShadowDragDrop = {
		
		/**
		 * Initialize the drag & drop functionality.
		 */
		init: function() {
			this.setupSortable();
			this.addInstructions();
		},

		/**
		 * Setup jQuery UI Sortable on the posts table.
		 */
		setupSortable: function() {
			const $table = $('#the-list');
			
			if (!$table.length) {
				return;
			}

			// Make table rows sortable.
			$table.sortable({
				items: 'tr',
				cursor: 'move',
				axis: 'y',
				containment: 'parent',
				tolerance: 'pointer',
				distance: 5,
				opacity: 0.8,
				helper: function(e, tr) {
					const $originals = tr.children();
					const $helper = tr.clone();
					$helper.children().each(function(index) {
						$(this).width($originals.eq(index).width());
					});
					$helper.addClass('wpshadow-drag-helper');
					return $helper;
				},
				start: function(e, ui) {
					ui.placeholder.height(ui.item.height());
					ui.item.addClass('wpshadow-dragging');
				},
				stop: function(e, ui) {
					ui.item.removeClass('wpshadow-dragging');
				},
				update: function(e, ui) {
					WPShadowDragDrop.updateOrder();
				}
			});

			// Add drag handle to each row.
			$table.find('tr').each(function() {
				const $row = $(this);
				const $titleCell = $row.find('.column-title');
				
				if ($titleCell.length) {
					$titleCell.prepend('<span class="wpshadow-drag-handle dashicons dashicons-menu" title="' + 
						wpShadowDragDrop.i18n.dragToReorder + '"></span>');
				}
			});
		},

		/**
		 * Add instructions to the screen.
		 */
		addInstructions: function() {
			const $tablenav = $('.tablenav.top');
			
			if (!$tablenav.length) {
				return;
			}

			const instructions = $('<div class="wpshadow-drag-instructions">')
				.html('<span class="dashicons dashicons-info"></span> ' + 
					wpShadowDragDrop.i18n.instructions);

			$tablenav.after(instructions);
		},

		/**
		 * Update the post order via AJAX.
		 */
		updateOrder: function() {
			const order = [];
			
			// Collect post IDs in current order.
			$('#the-list tr').each(function() {
				const postId = $(this).attr('id');
				if (postId) {
					const id = postId.replace('post-', '');
					if (id) {
						order.push(parseInt(id, 10));
					}
				}
			});

			// Show loading indicator.
			this.showLoading();

			// Send AJAX request.
			$.ajax({
				url: wpShadowDragDrop.ajaxUrl,
				type: 'POST',
				data: {
					action: 'wpshadow_update_post_order',
					nonce: wpShadowDragDrop.nonce,
					order: order,
					post_type: wpShadowDragDrop.postType
				},
				success: function(response) {
					WPShadowDragDrop.hideLoading();
					
					if (response.success) {
						WPShadowDragDrop.showNotice(response.data.message, 'success');
					} else {
						WPShadowDragDrop.showNotice(response.data.message, 'error');
					}
				},
				error: function(xhr, status, error) {
					WPShadowDragDrop.hideLoading();
					WPShadowDragDrop.showNotice(
						wpShadowDragDrop.i18n.error || 'An error occurred while updating the order.',
						'error'
					);
					console.error('AJAX Error:', status, error);
				}
			});
		},

		/**
		 * Show loading indicator.
		 */
		showLoading: function() {
			if (!$('.wpshadow-drag-loading').length) {
				$('#the-list').before(
					'<div class="wpshadow-drag-loading">' +
					'<span class="spinner is-active"></span> ' +
					(wpShadowDragDrop.i18n.updating || 'Updating order...') +
					'</div>'
				);
			}
		},

		/**
		 * Hide loading indicator.
		 */
		hideLoading: function() {
			$('.wpshadow-drag-loading').fadeOut(300, function() {
				$(this).remove();
			});
		},

		/**
		 * Show admin notice.
		 *
		 * @param {string} message Notice message.
		 * @param {string} type    Notice type (success, error, warning, info).
		 */
		showNotice: function(message, type) {
			type = type || 'info';
			
			const $notice = $('<div class="notice notice-' + type + ' is-dismissible wpshadow-drag-notice">')
				.html('<p>' + message + '</p>');

			// Add close button.
			$notice.append(
				'<button type="button" class="notice-dismiss">' +
				'<span class="screen-reader-text">Dismiss this notice.</span>' +
				'</button>'
			);

			// Insert notice.
			var $slot = $('#wpshadow-page-notices');
			if ($slot.length) {
				$slot.append($notice);
			} else if ($('.wrap').length) {
				$('.wrap').first().prepend($notice);
			}

			// Handle dismiss button.
			$notice.find('.notice-dismiss').on('click', function() {
				$notice.fadeOut(300, function() {
					$(this).remove();
				});
			});

			// Auto-dismiss success notices.
			if (type === 'success') {
				setTimeout(function() {
					$notice.fadeOut(300, function() {
						$(this).remove();
					});
				}, 3000);
			}
		}
	};

	// Initialize on document ready.
	$(document).ready(function() {
		// Set default i18n strings if not provided.
		if (typeof wpShadowDragDrop !== 'undefined') {
			wpShadowDragDrop.i18n = wpShadowDragDrop.i18n || {};
			wpShadowDragDrop.i18n.dragToReorder = wpShadowDragDrop.i18n.dragToReorder || 'Drag to reorder';
			wpShadowDragDrop.i18n.instructions = wpShadowDragDrop.i18n.instructions || 
				'Drag and drop rows to reorder posts. Changes are saved automatically.';
			wpShadowDragDrop.i18n.updating = wpShadowDragDrop.i18n.updating || 'Updating order...';
			wpShadowDragDrop.i18n.error = wpShadowDragDrop.i18n.error || 'An error occurred.';

			WPShadowDragDrop.init();
		}
	});

})(jQuery);
