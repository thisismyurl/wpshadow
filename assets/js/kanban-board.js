/**
 * Kanban Board Drag & Drop Functionality
 * Handles card movement between columns and status updates
 */

jQuery(document).ready(function ($) {
	let draggedElement = null;
	let dragSource = null;

	/**
	 * Initialize Kanban board
	 */
	function initKanban() {
		setupDragAndDrop();
		setupCardActions();
		updateAllColumnCounts();
	}

	/**
	 * Setup drag and drop event listeners
	 */
	function setupDragAndDrop() {
		// Drag start
		$(document).on('dragstart', '.finding-card', function (e) {
			draggedElement = this;
			dragSource = $(this).closest('.kanban-column').data('status');

			$(this).addClass('dragging');
			e.originalEvent.dataTransfer.effectAllowed = 'move';
			e.originalEvent.dataTransfer.setData('text/html', this.innerHTML);

			// Add visual feedback
			setTimeout(() => {
				$(this).css('opacity', '0.5');
			}, 0);
		});

		// Drag over column
		$(document).on('dragover', '.kanban-column-content', function (e) {
			e.preventDefault();
			e.originalEvent.dataTransfer.dropEffect = 'move';

			const $column = $(this).closest('.kanban-column');
			$column.addClass('drag-over');
		});

		// Drag over column content (update drop effect)
		$(document).on('dragover', '.kanban-column', function (e) {
			e.preventDefault();
			e.originalEvent.dataTransfer.dropEffect = 'move';
		});

		// Drag leave
		$(document).on('dragleave', '.kanban-column-content', function (e) {
			const $column = $(this).closest('.kanban-column');

			// Only remove if we're actually leaving the column
			if (e.target === this) {
				$column.removeClass('drag-over');
			}
		});

		$(document).on('dragleave', '.kanban-column', function (e) {
			if (e.target === this) {
				$(this).removeClass('drag-over');
			}
		});

		// Drop on column
		$(document).on('drop', '.kanban-column-content', function (e) {
			e.preventDefault();
			e.stopPropagation();

			if (!draggedElement) return;

			const $column = $(this).closest('.kanban-column');
			const newStatus = $column.data('status');
			const $card = $(draggedElement);
			const findingId = $card.data('finding-id');
			const oldStatus = dragSource;

			$column.removeClass('drag-over');

			// Don't move if dropping in same column
			if (newStatus === oldStatus) {
				resetDragState();
				return;
			}

			// If dragged to auto-fix column, trigger the fix
			if (newStatus === 'automated') {
				triggerAutoFix(findingId, $card, $column);
			} else {
				// Save status change via AJAX
				changeKanbanStatus(
					findingId,
					newStatus,
					oldStatus,
					$card,
					$column
				);
			}
		});

		// Drop on column header
		$(document).on('drop', '.kanban-column', function (e) {
			// Delegate to content area
			if ($(e.target).closest('.kanban-column-content').length === 0) {
				$(this).find('.kanban-column-content').trigger('drop');
			}
		});

		// Drag end
		$(document).on('dragend', '.finding-card', function (e) {
			resetDragState();
		});
	}

	/**
	 * Setup card action buttons
	 */
	function setupCardActions() {
		// Remove finding from board
		$(document).on('click', '.finding-remove-btn', function (e) {
			e.preventDefault();
			e.stopPropagation();

			const findingId = $(this).data('finding-id');
			const $card = $(this).closest('.finding-card');
			const currentStatus = $card.closest('.kanban-column').data('status');

			if (confirm('Remove this finding from the board?')) {
				changeKanbanStatus(
					findingId,
					'ignored',
					currentStatus,
					$card,
					null,
					true // isRemove
				);
			}
		});

		// Auto-fix from card
		$(document).on('click', '.finding-autofix', function (e) {
			e.preventDefault();
			e.stopPropagation();

			const findingId = $(this).data('finding-id');
			const $btn = $(this);
			const $card = $btn.closest('.finding-card');

			$btn.prop('disabled', true).text('Fixing...');

			$.post(ajaxurl, {
				action: 'wpshadow_autofix_finding',
				nonce: $('[name="wpshadow_nonce"]').val() || '',
				finding_id: findingId
			}, function (response) {
				if (response.success) {
					// Show success state
					const $successDiv = $(
						'<div style="padding: 15px; background: #e8f5e9; border-left: 4px solid #4caf50; border-radius: 4px; text-align: center;">' +
						'<strong style="color: #2e7d32;">✓ Fixed!</strong>' +
						'<p style="margin: 8px 0 0 0; font-size: 12px; color: #555;">' + escapeHtml(response.data.message) + '</p>' +
						'</div>'
					);

					$card.html($successDiv);

					// Move to Fixed column after delay
					setTimeout(() => {
						const $fixedColumn = $('.kanban-column[data-status="fixed"]');
						$card.detach();
						$fixedColumn.find('.kanban-column-content').append($card);
						updateAllColumnCounts();

						// Restore card appearance
						$card.html(response.data.card_html || '').fadeIn();
					}, 1500);

				} else {
					const message = response.data && response.data.message
						? response.data.message
						: 'Could not auto-fix';
					alert('Error: ' + message);
					$btn.prop('disabled', false).text('Fix Now');
				}
			});
		});

		// Finding details
		$(document).on('click', '.finding-details', function (e) {
			e.preventDefault();
			e.stopPropagation();

			const findingId = $(this).data('finding-id');
			const $card = $(this).closest('.finding-card');

			// Show details modal (placeholder - implement as needed)
			const title = $card.find('.finding-title').text();
			const description = $card.find('.finding-description').text();
			const threat = $card.find('.finding-threat').text();

			alert(
				'Finding Details:\n\n' +
				'Title: ' + title + '\n\n' +
				'Description: ' + description + '\n\n' +
				'Threat: ' + threat
			);
			// TODO: Implement proper modal interface
		});

		// Learn More links (already have target="_blank")
	}

	/**
	 * Trigger auto-fix when dropped in automated column
	 */
	function triggerAutoFix(findingId, $card, $targetColumn) {
		// Move card to column first
		$card.css('opacity', '1').detach();
		$targetColumn.find('.kanban-column-content .kanban-empty-message').hide();
		$targetColumn.find('.kanban-column-content').append($card);
		updateAllColumnCounts();
		resetDragState();

		// Show fixing state
		$card.css('opacity', '0.6');
		$card.find('.finding-card-actions').html(
			'<span style="color: #ff9800; font-weight: 500;">⟳ Fixing...</span>'
		);

		// Check if it's a "big" fix (for demo, check if threat > 60)
		const threat = parseInt($card.data('threat') || 50);
		const isBigFix = threat > 60;

		if (isBigFix) {
			const runOvernight = confirm(
				'This is a significant fix that may take time.\n\n' +
				'Would you like to schedule it to run overnight?\n' +
				'We\'ll send you an email when it\'s complete.'
			);

			if (runOvernight) {
				// Schedule overnight
				$.post(ajaxurl, {
					action: 'wpshadow_schedule_overnight_fix',
					nonce: $('[name="wpshadow_kanban_nonce"]').val(),
					finding_id: findingId
				}, function (response) {
					if (response.success) {
						$card.css('opacity', '1');
						$card.find('.finding-card-actions').html(
							'<span style="color: #2196f3;">📅 Scheduled for overnight</span>'
						);
						alert('Fix scheduled! We\'ll email you when it\'s done.');
					} else {
						$card.css('opacity', '1');
						$card.find('.finding-card-actions').html('');
						alert('Error scheduling fix: ' + (response.data?.message || 'Unknown error'));
					}
				});
				return;
			}
		}

		// Run fix immediately
		$.post(ajaxurl, {
			action: 'wpshadow_autofix_finding',
			nonce: $('[name="wpshadow_kanban_nonce"]').val(),
			finding_id: findingId
		}, function (response) {
			if (response.success) {
				// Move to fixed column
				const $fixedColumn = $('.kanban-column[data-status="fixed"]');
				$card.css('opacity', '1').detach();
				$fixedColumn.find('.kanban-column-content .kanban-empty-message').hide();
				$fixedColumn.find('.kanban-column-content').append($card);
				$card.find('.finding-card-actions').html(
					'<span style="color: #4caf50; font-weight: 500;">✓ Fixed</span>'
				);
				updateAllColumnCounts();

				// Update status in backend
				changeKanbanStatus(findingId, 'fixed', 'automated', $card, $fixedColumn, false);
			} else {
				$card.css('opacity', '1');
				$card.find('.finding-card-actions').html('');
				alert('Auto-fix failed: ' + (response.data?.message || 'Unknown error'));
			}
		}).fail(() => {
			$card.css('opacity', '1');
			$card.find('.finding-card-actions').html('');
			alert('Connection error during auto-fix');
		});
	}

	/**
	 * Change finding status via AJAX
	 */
	function changeKanbanStatus(
		findingId,
		newStatus,
		oldStatus,
		$card,
		$targetColumn,
		isRemove = false
	) {
		$.post(ajaxurl, {
			action: 'wpshadow_change_finding_status',
			nonce: $('[name="wpshadow_kanban_nonce"]').val() || $('[data-nonce="wpshadow_kanban"]').attr('data-nonce') || '',
			finding_id: findingId,
			new_status: newStatus
		}, function (response) {
			if (response.success) {
				if (isRemove) {
					// Fade out and remove
					$card.fadeOut(300, function () {
						$(this).remove();
						updateAllColumnCounts();
					});
				} else if ($targetColumn) {
					// Move to target column
					$card.css('opacity', '1').detach();
					$targetColumn.find('.kanban-column-content .kanban-empty-message').hide();
					$targetColumn.find('.kanban-column-content').append($card);
					updateAllColumnCounts();
				}
			} else {
				const message = response.data && response.data.message
					? response.data.message
					: 'Could not update status';
				alert('Error: ' + message);
				resetDragState();
			}
		}).fail(() => {
			alert('Connection error. Please try again.');
			resetDragState();
		});
	}

	/**
	 * Update all column counts
	 */
	function updateAllColumnCounts() {
		$('.kanban-column').each(function () {
			const count = $(this).find('.finding-card').length;
			const $header = $(this).find('h3');
			const $countSpan = $header.find('.column-count');

			if ($countSpan.length) {
				$countSpan.text(count);
			} else {
				$header.append(' <span class="column-count">' + count + '</span>');
			}
		});
	}

	/**
	 * Reset drag state
	 */
	function resetDragState() {
		$('.finding-card').css('opacity', '1').removeClass('dragging');
		$('.kanban-column').removeClass('drag-over');
		draggedElement = null;
		dragSource = null;
	}

	/**
	 * Escape HTML to prevent XSS
	 */
	function escapeHtml(unsafe) {
		return unsafe
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#039;');
	}

	// Initialize on ready
	initKanban();

	// Re-initialize on AJAX updates
	$(document).on('wpshadow-kanban-updated', function () {
		initKanban();
	});
});

/**
 * Update Recent Activity log
 */
function updateRecentActivity() {
	$.post(ajaxurl, {
		action: 'wpshadow_get_recent_activity',
		nonce: $('[name="wpshadow_kanban_nonce"]').val()
	}, function(response) {
		if (response.success && response.data.activity) {
			const $tbody = $('#wpshadow-activity-log tbody');
			$tbody.empty();
			response.data.activity.forEach(function(entry) {
				$tbody.append(
					'<tr style="border-bottom: 1px solid #e9ecef;">' +
					'<td style="padding: 12px; color: #495057;">' + entry.action + '</td>' +
					'<td style="padding: 12px; color: #6c757d; font-size: 13px;">' + entry.time + '</td>' +
					'</tr>'
				);
			});
		}
	});
}

// Call update activity on status change
const originalChangeKanbanStatus = window.changeKanbanStatus || changeKanbanStatus;
window.changeKanbanStatus = changeKanbanStatus;
$(document).ajaxComplete(function(event, xhr, settings) {
	if (settings.data && settings.data.indexOf('wpshadow_change_finding_status') > -1) {
		updateRecentActivity();
	}
});
