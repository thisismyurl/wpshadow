/**
 * Kanban Board Drag & Drop Functionality
 * Handles card movement between columns and status updates
 */

jQuery(document).ready(function ($) {
	let draggedElement = null;
	let dragSource = null;
	const $statusBox = $('#wpshadow-kanban-status');

	function setStatus(message, tone = 'info') {
		if (!$statusBox.length) {
			return;
		}

		const tones = {
			info: { bg: '#eff6ff', border: '#dbeafe', color: '#0b5cad' },
			success: { bg: '#ecfdf3', border: '#bbf7d0', color: '#166534' },
			error: { bg: '#fef2f2', border: '#fecdd3', color: '#b91c1c' }
		};

		const palette = tones[tone] || tones.info;
		$statusBox.css({
			display: 'block',
			background: palette.bg,
			border: '1px solid ' + palette.border,
			color: palette.color
		}).text(message);
	}

	function getKanbanNonce() {
		if (window.wpshadowKanban && window.wpshadowKanban.kanban_nonce) {
			return window.wpshadowKanban.kanban_nonce;
		}
		return $('[name="wpshadow_kanban_nonce"]').val() || '';
	}

	function getAjaxUrl() {
		if (window.wpshadowKanban && window.wpshadowKanban.ajax_url) {
			return window.wpshadowKanban.ajax_url;
		}
		if (typeof ajaxurl !== 'undefined') {
			return ajaxurl;
		}
		return '';
	}

	function showAlert(title, message, tone) {
		if (window.WPShadowDesign && typeof window.WPShadowDesign.alert === 'function') {
			window.WPShadowDesign.alert(title, message, tone || 'info');
			return;
		}

		WPShadowModal.alert({
			title: title || 'Alert',
			message: message,
			type: tone || 'info'
		});
	}

	function confirmAction(message, onConfirm, onCancel) {
		if (window.WPShadowDesign && typeof window.WPShadowDesign.confirm === 'function') {
			window.WPShadowDesign.confirm(message, onConfirm, onCancel);
			return;
		}

		WPShadowModal.confirm({
			title: 'Confirm',
			message: message,
			onConfirm: function() {
				onConfirm();
			},
			onCancel: function() {
				if (onCancel) {
					onCancel();
				}
			}
		});
	}

	/**
	 * Initialize Kanban board
	 */
	function initKanban() {
		setupDragAndDrop();
		setupCardActions();
		setupKeyboardNavigation();
		updateAllColumnCounts();
	}

	/**
	 * Setup keyboard navigation for accessibility (CANON requirement)
	 * Allows keyboard users to move cards between columns
	 */
	function setupKeyboardNavigation() {
		let selectedCard = null;

		// Enter key on card: show move menu
		$(document).on('keydown', '.finding-card', function (e) {
			if (e.key === 'Enter' || e.key === ' ') {
				e.preventDefault();
				selectedCard = $(this);
				const findingId = selectedCard.data('finding-id');
				const currentColumn = selectedCard.closest('.kanban-column').data('status');
				
				showKeyboardMoveMenu(selectedCard, currentColumn);
			}
		});

		// Escape key: cancel operation
		$(document).on('keydown', function (e) {
			if (e.key === 'Escape') {
				hideKeyboardMoveMenu();
				selectedCard = null;
			}
		});
	}

	/**
	 * Show keyboard move menu for moving cards
	 */
	function showKeyboardMoveMenu(card, currentColumn) {
		// Remove any existing menu
		$('.wps-keyboard-move-menu').remove();

		const columns = [
			{ status: 'detected', label: 'Detected' },
			{ status: 'manual', label: 'User to Fix' },
			{ status: 'automated', label: 'Fix Now' },
			{ status: 'fixed', label: 'Workflows' }
		];

		let menuHtml = '<div class="wps-keyboard-move-menu" role="menu" aria-label="Move card to column">';
		menuHtml += '<div class="wps-keyboard-move-menu-header">';
		menuHtml += '<strong>Move to:</strong>';
	menuHtml += '<button type="button" class="wps-keyboard-move-menu-close" aria-label="Close move menu">×</button>';
		columns.forEach((col, index) => {
			if (col.status !== currentColumn) {
				menuHtml += '<button class="wps-keyboard-move-option" data-target="' + col.status + '" data-index="' + index + '" role="menuitem">';
				menuHtml += col.label;
				menuHtml += '</button>';
			}
		});
		
		menuHtml += '</div>';
		menuHtml += '</div>';

		const $menu = $(menuHtml);
		$('body').append($menu);

		// Position menu near the card
		const cardOffset = card.offset();
		$menu.css({
			top: cardOffset.top + 20,
			left: cardOffset.left + card.outerWidth() / 2 - $menu.outerWidth() / 2
		});

		// Focus first option
		$menu.find('.wps-keyboard-move-option').first().focus();

		// Handle menu interactions
		$menu.on('click', '.wps-keyboard-move-option', function () {
			const targetColumn = $(this).data('target');
			moveCardToColumn(card, targetColumn);
			hideKeyboardMoveMenu();
		});

		$menu.on('click', '.wps-keyboard-move-menu-close', function () {
			hideKeyboardMoveMenu();
		});

		// Arrow key navigation within menu
		$menu.on('keydown', '.wps-keyboard-move-option', function (e) {
			const $options = $menu.find('.wps-keyboard-move-option');
			const currentIndex = $options.index(this);

			if (e.key === 'ArrowDown') {
				e.preventDefault();
				const nextIndex = (currentIndex + 1) % $options.length;
				$options.eq(nextIndex).focus();
			} else if (e.key === 'ArrowUp') {
				e.preventDefault();
				const prevIndex = (currentIndex - 1 + $options.length) % $options.length;
				$options.eq(prevIndex).focus();
			} else if (e.key === 'Enter' || e.key === ' ') {
				e.preventDefault();
				$(this).click();
			} else if (e.key === 'Escape') {
				e.preventDefault();
				hideKeyboardMoveMenu();
			}
		});

		// Announce to screen readers
		announceToScreenReader('Move menu opened. Use arrow keys to navigate, Enter to select, Escape to cancel.');
	}

	/**
	 * Hide keyboard move menu
	 */
	function hideKeyboardMoveMenu() {
		$('.wps-keyboard-move-menu').remove();
	}

	/**
	 * Move card to target column (keyboard navigation)
	 */
	function moveCardToColumn(card, targetColumn) {
		const findingId = card.data('finding-id');
		const currentColumn = card.closest('.kanban-column').data('status');
		const $targetColumn = $('.kanban-column[data-status="' + targetColumn + '"]');

		if (targetColumn === 'automated') {
			executeOnetimeFix(findingId, card, $targetColumn, currentColumn);
		} else if (targetColumn === 'fixed') {
			showWorkflowCreationModal(findingId, card, $targetColumn, currentColumn);
		} else {
			changeKanbanStatus(findingId, targetColumn, currentColumn, card, $targetColumn);
		}

		announceToScreenReader('Moving card to ' + targetColumn + ' column');
	}

	/**
	 * Announce message to screen readers via ARIA live region
	 */
	function announceToScreenReader(message) {
		let $liveRegion = $('#wpshadow-aria-live');
		
		if (!$liveRegion.length) {
			$liveRegion = $('<div id="wpshadow-aria-live" role="status" aria-live="polite" aria-atomic="true" class="wps-sr-only"></div>');
			$('body').append($liveRegion);
		}

		$liveRegion.text(message);
		
		// Clear after announcement
		setTimeout(() => {
			$liveRegion.text('');
		}, 1000);
	}

	/**
	 * Auto-run Quick Scan on dashboard load to populate kanban
	 */
	function autoRunQuickScanOnLoad() {
		const nonce = getKanbanNonce();
		const ajaxUrl = getAjaxUrl();

		if (!ajaxUrl || !nonce) {
			return; // Silently skip if config missing
		}

		// Only auto-run if quick scan has never been run (Last scanned: not yet)
		const lastRunElement = $('span').filter(function() {
			return $(this).text().includes('Last scanned: not yet');
		});

		if (lastRunElement.length === 0) {
			return; // Last run exists, don't auto-run
		}

		setStatus('Refreshing findings with quick scan...', 'info');
		$.post(ajaxUrl, {
			action: 'wpshadow_run_quick_checks',
			nonce: nonce
		}, function (response) {
			if (response.success) {
				setStatus('Quick scan complete. Board is current.', 'success');
				setTimeout(() => window.location.reload(), 1000);
			}
		}).fail(function () {
			// Silent fail - board will use existing findings
		});
	}

	// Quick checks (low impact)
	$(document).on('click', '#wpshadow-kanban-run-quick', function (e) {
		e.preventDefault();
		const nonce = getKanbanNonce();
		if (!ajaxUrl || !nonce) {
			setStatus('Missing AJAX configuration for quick checks.', 'error');
							$targetColumn.find('.kanban-column-content .kanban-empty-message').remove();
							$targetColumn.find('.kanban-column-content').append($card);
			return;
		}

		const $btn = $(this);
		$btn.prop('disabled', true).text('Running quick checks...');
		setStatus('Running quick checks now. This keeps the board fresh.', 'info');

		$.post(ajaxUrl, {
			action: 'wpshadow_run_quick_checks',
			nonce: nonce
		}, function (response) {
			if (response.success) {
				setStatus('Quick checks finished. Refreshing board...', 'success');
				// Wait a moment for option to persist, then reload
				setTimeout(() => window.location.reload(), 1500);
			} else {
				const message = response.data && response.data.message ? response.data.message : 'Could not run quick checks.';
				setStatus(message, 'error');
			}
		}).fail(function () {
			setStatus('Connection error while running quick checks.', 'error');
		}).always(function () {
			$btn.prop('disabled', false).text('Run quick checks');
		});
	});

	// Schedule Deep Scan button handler - creates persistent workflow
	$(document).on('click', '#wpshadow-kanban-schedule-deep', function (e) {
		e.preventDefault();
		const nonce = getKanbanNonce();
		const ajaxUrl = getAjaxUrl();
		const $btn = $(this);

		if (!ajaxUrl || !nonce) {
			setStatus('Missing AJAX configuration for scheduling deep scan.', 'error');
			return;
		}

		// Create workflow
		$btn.prop('disabled', true).text('Creating workflow...');
		setStatus('Creating automated deep scan workflow...', 'info');

		$.post(ajaxUrl, {
			action: 'wpshadow_create_heavy_test_workflow',
			nonce: nonce,
			schedule_time: '02:00',
			user_email: (window.wpshadowKanban && window.wpshadowKanban.user_email) || ''
		}, function (response) {
			if (response.success) {
				setStatus('✓ Workflow created! Deep scans will run automatically at 2:00 AM daily. Check Automation Builder to customize.', 'success');
			} else {
				const message = response.data && response.data.message ? response.data.message : 'Could not create workflow.';
				setStatus(message, 'error');
			}
		}).fail(function () {
			setStatus('Connection error while creating workflow.', 'error');
		}).always(function () {
			$btn.prop('disabled', false).text('Schedule Deep Scan (recommended)');
		});
	});

	// Run Deep Scan button handler - runs immediately with off-peak check
	$(document).on('click', '#wpshadow-kanban-run-deep', function (e) {
		e.preventDefault();
		const nonce = getKanbanNonce();
		const ajaxUrl = getAjaxUrl();
		const $btn = $(this);

		if (!ajaxUrl || !nonce) {
			setStatus('Missing AJAX configuration for deep scan.', 'error');
			return;
		}

		// Run once now with off-peak check
		const runHeavyNow = function () {
			$btn.prop('disabled', true).text('Running deep scan...');
			setStatus('Running deep scan now. This may take a moment.', 'info');

			$.post(ajaxUrl, {
				action: 'wpshadow_run_heavy_tests',
				nonce: nonce
			}, function (response) {
				if (response.success) {
					setStatus('Deep scan completed. Refreshing board...', 'success');
					setTimeout(() => window.location.reload(), 1500);
				} else {
					const message = response.data && response.data.message ? response.data.message : 'Could not run deep scan.';
					setStatus(message, 'error');
				}
			}).fail(function () {
				setStatus('Connection error while running deep scan.', 'error');
			}).always(function () {
				$btn.prop('disabled', false).text('Run Deep Scan');
			});
		};

		if (typeof window.wpshadowCheckSlowdown === 'function') {
			const intercepted = window.wpshadowCheckSlowdown('deep-scan', runHeavyNow);
			if (intercepted) {
				setStatus('Deep scan queued for off-peak. Confirm in the modal.', 'info');
				$btn.prop('disabled', false).text('Run Deep Scan');
				return;
			}
		}

		runHeavyNow();
	});

	$(document).on('wpshadow:offpeakScheduled', function (event, op) {
		const label = op && op.type ? op.type.replace(/-/g, ' ') : 'heavy tests';
		setStatus('Scheduled ' + label + ' for off-peak. We will email results.', 'success');
	});

	$(document).on('wpshadow:offpeakRunNow', function (event, op) {
		const label = op && op.type ? op.type.replace(/-/g, ' ') : 'heavy tests';
		setStatus('Running ' + label + ' now as requested.', 'info');
	});

	/**
	 * Show autofix modal for workflow options
	 */
	let pendingAutofixData = null;

	function showAutofixModal(findingId, $card, $targetColumn) {
		pendingAutofixData = { findingId, $card, $targetColumn };
		$('#wpshadow-autofix-modal').fadeIn(200);
	}

	// Autofix modal - Always button
	$(document).on('click', '#wpshadow-autofix-always', function() {
		if (!pendingAutofixData) return;

		const { findingId, $card, $targetColumn } = pendingAutofixData;
		const $btn = $(this);

		$btn.prop('disabled', true).text('Creating workflow...');
		$('#wpshadow-autofix-modal').fadeOut(200);

		setStatus('Creating persistent auto-fix workflow...', 'info');

		$.post(getAjaxUrl(), {
			action: 'wpshadow_create_autofix_workflow',
			nonce: getKanbanNonce(),
			finding_id: findingId,
			is_persistent: true,
			user_email: (window.wpshadowKanban && window.wpshadowKanban.user_email) || ''
		}, function(response) {
			if (response.success) {
				if (response.data.already_exists) {
					setStatus('Persistent workflow already exists. Issue will be auto-fixed automatically.', 'success');
				} else {
					setStatus('Persistent auto-fix workflow created! This issue will be fixed automatically from now on.', 'success');
				}

				// Mark card as automated
				$card.find('.finding-card-actions').html(
					'<span style="color: #4caf50; font-weight: 500;">✓ Auto-fix enabled</span>'
				);

				// Update status
				changeKanbanStatus(findingId, 'automated', 'detected', $card, $targetColumn, false);
			} else {
				setStatus('Error creating workflow: ' + (response.data?.message || 'Unknown error'), 'error');
			}
		}).fail(function() {
			setStatus('Connection error creating workflow.', 'error');
		}).always(function() {
			$btn.prop('disabled', false).text('Create Workflow');
			pendingAutofixData = null;
		});
	});

	// Autofix modal - Just Once button
	$(document).on('click', '#wpshadow-autofix-once', function() {
		if (!pendingAutofixData) return;

		const { findingId, $card, $targetColumn } = pendingAutofixData;
		const $btn = $(this);

		$btn.prop('disabled', true).text('Scheduling...');
		$('#wpshadow-autofix-modal').fadeOut(200);

		setStatus('Scheduling one-time auto-fix...', 'info');

		$.post(getAjaxUrl(), {
			action: 'wpshadow_create_autofix_workflow',
			nonce: getKanbanNonce(),
			finding_id: findingId,
			is_persistent: false,
			user_email: (window.wpshadowKanban && window.wpshadowKanban.user_email) || ''
		}, function(response) {
			if (response.success) {
				setStatus('One-time fix scheduled. Will run in the background shortly.', 'success');

				// Mark card as fixing
				$card.find('.finding-card-actions').html(
					'<span style="color: #ff9800; font-weight: 500;">⟳ Fixing soon...</span>'
				);

				// Update status
				changeKanbanStatus(findingId, 'automated', 'detected', $card, $targetColumn, false);

				// Check status after 60 seconds
				setTimeout(() => {
					setStatus('One-time fix should be complete. Refresh to see results.', 'info');
				}, 60000);
			} else {
				setStatus('Error scheduling fix: ' + (response.data?.message || 'Unknown error'), 'error');
			}
		}).fail(function() {
			setStatus('Connection error scheduling fix.', 'error');
		}).always(function() {
			$btn.prop('disabled', false).text('Just Once');
			pendingAutofixData = null;
		});
	});

	// Close autofix modal
	$(document).on('click', '.wpshadow-autofix-modal-close, #wpshadow-autofix-modal', function(e) {
		if (e.target === this) {
			$('#wpshadow-autofix-modal').fadeOut(200);
			pendingAutofixData = null;
		}
	});

	// Family Fix Modal - "Fix This Only" button
	$(document).on('click', '#wpshadow-family-fix-this-only', function() {
		if (!window.pendingFamilyFix) return;

		const { findingId, $card, $targetColumn, oldStatus } = window.pendingFamilyFix;
		const $btn = $(this);

		$btn.prop('disabled', true).text('Fixing...');
		$('#wpshadow-family-fix-modal').fadeOut(200);

		// Call apply fix with fix_all_family: false
		executeSimpleOnetimeFix(findingId, $card, $targetColumn, oldStatus);
		
		window.pendingFamilyFix = null;
	});

	// Family Fix Modal - "Fix All Related Issues" button
	$(document).on('click', '#wpshadow-family-fix-all', function() {
		if (!window.pendingFamilyFix) return;

		const { findingId, $card, $targetColumn, oldStatus, familyIds } = window.pendingFamilyFix;
		const $btn = $(this);

		$btn.prop('disabled', true).text('Fixing all...');
		$('#wpshadow-family-fix-modal').fadeOut(200);

		setStatus('Fixing all related issues...', 'info');

		// Apply fix to all family members
		$.post(getAjaxUrl(), {
			action: 'wpshadow_apply_family_fix',
			nonce: getKanbanNonce(),
			finding_id: findingId,
			fix_all_family: true,
			family_ids: familyIds
		}, function(response) {
			if (response.success) {
				const message = response.data?.message || 'All issues fixed!';
				const count = response.data?.successful_count || 0;
				const timeSaved = response.data?.time_saved || 0;
				
				let statusMsg = '✓ ' + message;
				if (timeSaved > 0) {
					statusMsg += ' (saved ~' + timeSaved + ' minutes)';
				}
				
				setStatus(statusMsg, 'success');
				
				// Update the primary card
				$card.find('.finding-card-actions').html(
					'<span style="color: #4caf50; font-weight: 500;">✓ Fixed (+ ' + (count - 1) + ' related)</span>'
				);

				// Update status
				changeKanbanStatus(findingId, 'automated', oldStatus, $card, $targetColumn, false);
				
				// Refresh Kanban to show other fixes
				setTimeout(() => {
					$('#wpshadow-refresh-kanban').click();
				}, 2000);
			} else {
				setStatus('✕ Error: ' + (response.data?.message || 'Could not fix issues'), 'error');
			}
		}).fail(function() {
			setStatus('✕ Connection error fixing issues.', 'error');
		}).always(function() {
			$btn.prop('disabled', false).text('Fix All Related Issues');
		});

		window.pendingFamilyFix = null;
	});

	// Close family fix modal
	$(document).on('click', '.wpshadow-family-fix-modal-close, #wpshadow-family-fix-modal', function(e) {
		if (e.target === this) {
			$('#wpshadow-family-fix-modal').fadeOut(200);
			window.pendingFamilyFix = null;
		}
	});

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

			// Enhanced visual feedback
			setTimeout(() => {
				$(this).css('opacity', '0.5');
			}, 0);

			// Announce to screen readers
			const cardTitle = $(this).find('.wps-card-title, .finding-title').text();
			announceToScreenReader('Moving: ' + cardTitle);
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

			// If dragged to auto-fix column, execute one-time fix
			if (newStatus === 'automated') {
				executeOnetimeFix(findingId, $card, $column, oldStatus);
			} else if (newStatus === 'fixed') {
				// If dragged to workflows column, show workflow creation modal
				showWorkflowCreationModal(findingId, $card, $column, oldStatus);
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

		const removeFromBoard = function() {
			// Just hide the card from UI
			$card.fadeOut(300, function() {
				$(this).remove();
				// Update column count
				const $column = $('.kanban-column[data-status="' + currentStatus + '"]');
				const count = $column.find('.finding-card').length;
				$column.find('h3 span[style*="float: right"]').text(count);
			});
			setStatus('Finding removed from view.', 'success');
		};

		confirmAction(
			'REMOVE finding from board?\n\nThis will hide it from view but the finding will still be recorded.',
			removeFromBoard
		);
	});

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
					showAlert('Auto-fix failed', message, 'error');
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

			const detailsMessage = 'Title: ' + title + '\n\nDescription: ' + description + '\n\nThreat: ' + threat;
			showAlert('Finding Details', detailsMessage, 'info');
			// TODO: Implement proper modal interface
		});

		// Learn More links (already have target="_blank")
	}

	/**
	 * Execute one-time auto-fix when dropped in automated column
	 */
	function executeOnetimeFix(findingId, $card, $targetColumn, oldStatus) {
		// Move card to column first
		$card.css('opacity', '1').detach();
		$targetColumn.find('.kanban-column-content .kanban-empty-message').hide();
		$targetColumn.find('.kanban-column-content').append($card);
		updateAllColumnCounts();
		resetDragState();

		// Get family info for this finding (Philosophy #9: Smart grouping)
		setStatus('Checking for related issues...', 'info');

		$.post(getAjaxUrl(), {
			action: 'wpshadow_get_finding_family',
			nonce: getKanbanNonce(),
			finding_id: findingId
		}, function(response) {
			if (response.success && response.data) {
				const familyInfo = response.data;
				
				// If finding has family and there are other family members detected, show smart modal
				if (familyInfo.has_family && familyInfo.family_member_count > 0) {
					showFamilyFixModal(findingId, familyInfo, $card, $targetColumn, oldStatus);
				} else {
					// No family or no family members - execute simple one-time fix
					executeSimpleOnetimeFix(findingId, $card, $targetColumn, oldStatus);
				}
			} else {
				// Error getting family info, proceed with simple fix
				executeSimpleOnetimeFix(findingId, $card, $targetColumn, oldStatus);
			}
		}).fail(function() {
			setStatus('Connection error checking for related issues.', 'error');
			executeSimpleOnetimeFix(findingId, $card, $targetColumn, oldStatus);
		});
	}

	/**
	 * Execute simple one-time fix (no family)
	 */
	function executeSimpleOnetimeFix(findingId, $card, $targetColumn, oldStatus) {
		setStatus('Fixing issue...', 'info');

		$.post(getAjaxUrl(), {
			action: 'wpshadow_apply_family_fix',
			nonce: getKanbanNonce(),
			finding_id: findingId,
			fix_all_family: false,
			family_ids: []
		}, function(response) {
			if (response.success) {
				setStatus('✓ ' + (response.data?.message || 'Issue fixed!'), 'success');
				
				// Mark card as fixing
				$card.find('.finding-card-actions').html(
					'<span style="color: #4caf50; font-weight: 500;">✓ Fixed!</span>'
				);

				// Update status
				changeKanbanStatus(findingId, 'automated', oldStatus, $card, $targetColumn, false);
			} else {
				setStatus('✕ Error: ' + (response.data?.message || 'Could not fix issue'), 'error');
			}
		}).fail(function() {
			setStatus('✕ Connection error fixing issue.', 'error');
		});
	}

	/**
	 * Show family-aware fix modal (Philosophy #9: Show Value)
	 * Offers to fix just this issue or all related issues in the family
	 */
	function showFamilyFixModal(findingId, familyInfo, $card, $targetColumn, oldStatus) {
		const modal = $('#wpshadow-family-fix-modal');
		if (!modal.length) {
			console.error('Family fix modal not found in DOM');
			executeSimpleOnetimeFix(findingId, $card, $targetColumn, oldStatus);
			return;
		}

		// Update modal content
		const familyTitle = escapeHtml(familyInfo.family_label || 'Related Issues');
		const familyCount = familyInfo.family_member_count;
		const relatedList = familyInfo.family_members.map(m => 
			'<li style="padding: 4px 0; color: #555;">' + escapeHtml(m.title) + '</li>'
		).join('');

		// Update modal HTML
		modal.find('.family-title').text(familyTitle);
		modal.find('.family-count').text(familyCount);
		modal.find('.family-list').html(relatedList);

		// Store modal state
		window.pendingFamilyFix = {
			findingId: findingId,
			$card: $card,
			$targetColumn: $targetColumn,
			oldStatus: oldStatus,
			familyIds: familyInfo.family_members.map(m => m.id)
		};

		// Show modal
		modal.fadeIn(200);
		setStatus('', 'info'); // Clear status message
	}

	/**
	 * Show workflow creation modal when dropped in Workflows column
	 */
	function showWorkflowCreationModal(findingId, $card, $targetColumn, oldStatus) {
		// Move card to column first
		$card.css('opacity', '1').detach();
		$targetColumn.find('.kanban-column-content .kanban-empty-message').remove();
		$targetColumn.find('.kanban-column-content').append($card);
		updateAllColumnCounts();
		resetDragState();

		// Create regular workflow with default settings
		setStatus('Creating workflow...', 'info');

		$.post(getAjaxUrl(), {
			action: 'wpshadow_create_regular_workflow',
			nonce: getKanbanNonce(),
			finding_id: findingId,
			user_email: (window.wpshadowKanban && window.wpshadowKanban.user_email) || ''
		}, function(response) {
			if (response.success) {
				const workflow = response.data.workflow;
				const needsConfiguration = response.data.needs_configuration;

				if (needsConfiguration) {
					setStatus('Workflow created (settings needed). Opening configuration...', 'info');
					// Open workflow edit page
					window.location.href = workflow.edit_url;
				} else {
					setStatus('Workflow created with default settings!', 'success');
					// Update status to fixed
					changeKanbanStatus(findingId, 'fixed', oldStatus, $card, $targetColumn, false);
				}
			} else {
				setStatus('Error creating workflow: ' + (response.data?.message || 'Unknown error'), 'error');
			}
		}).fail(function() {
			setStatus('Connection error creating workflow.', 'error');
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
		const findingTitle = $card.find('.finding-card-title, .finding-title').first().text();
		// Show loading state
		$card.addClass('wps-loading');

		$.post(ajaxurl, {
			action: 'wpshadow_change_finding_status',
			nonce: $('[name="wpshadow_kanban_nonce"]').val() || '',
			finding_id: findingId,
			new_status: newStatus
		}, function (response) {
			$card.removeClass('wps-loading');

			if (response.success) {
				if (isRemove) {
					// Fade out and remove
					$card.fadeOut(300, function () {
						$(this).remove();
						updateAllColumnCounts();
					});
				} else if ($targetColumn) {
					// Remove empty message if it exists
					$targetColumn.find('.kanban-column-content .kanban-empty-message, .wps-kanban-empty').remove();
					
					// Move to target column with success animation
					$card.css('opacity', '1').detach();
					$targetColumn.find('.kanban-column-content').append($card);
					
					// Add success animation
					$card.addClass('success');
					setTimeout(() => {
						$card.removeClass('success');
					}, 500);
					
					updateAllColumnCounts();
					
					// Announce status change for accessibility
					if (findingTitle && oldStatus !== newStatus) {
						announceStatusChange(findingTitle, oldStatus, newStatus);
					}

					// Announce success to screen readers
					const statusLabels = {
						'detected': 'Detected',
						'manual': 'User to Fix',
						'automated': 'Fix Now',
						'fixed': 'Workflows'
					};
					announceToScreenReader('Moved to ' + (statusLabels[newStatus] || newStatus) + ' column');
				}
			} else {
				// Error animation
				$card.addClass('error');
				setTimeout(() => {
					$card.removeClass('error');
				}, 500);

				const message = response.data && response.data.message
					? response.data.message
					: 'Could not update status';
				setStatus('Error: ' + message, 'error');
				resetDragState();
			}
		}).fail(() => {
			$card.removeClass('wps-loading');
			
			// Error animation
			$card.addClass('error');
			setTimeout(() => {
				$card.removeClass('error');
			}, 500);

			setStatus('Connection error. Please try again.', 'error');
			resetDragState();
		});
	}

	/**
	 * Update all column counts
	 */
	function updateAllColumnCounts() {
		$('.kanban-column').each(function () {
			const $column = $(this);
			const status = $column.data('status');
			let count;

			// Only count finding-card elements directly in this column's kanban-column-content
			const $content = $column.find('.kanban-column-content');
			
			if (status === 'detected') {
				// For detected column, show visible count / total count
				const visibleCount = $content.find('> .finding-card:not(.wpshadow-hidden-finding)').length;
				const totalCount = $content.find('> .finding-card').length;
				count = visibleCount;
				if (totalCount > visibleCount) {
					count = visibleCount + ' / ' + totalCount;
				}
			} else {
				// Other columns show total count
				count = $content.find('> .finding-card').length;
			}

			// Update count badge (supports both old and new structure)
			const $countBadge = $column.find('.wps-kanban-count-badge, .column-count, .kanban-column-count');
			if ($countBadge.length) {
				$countBadge.text(count);
				$countBadge.attr('aria-label', count + ' items');
			} else {
				// Fallback for old structure
				const $header = $column.find('h3');
				const $countSpan = $header.find('span:last');
				if ($countSpan.length) {
					$countSpan.text(count);
				} else {
					$header.append(' <span class="kanban-column-count">' + count + '</span>');
					$header.append(' <span class="column-count" style="color: #999; font-weight: 400; float: right;">' + count + '</span>');
				}
			}
		});
	}

	/**
	 * Announce status change to screen readers
	 * Creates ARIA live region announcement for accessibility
	 */
	function announceStatusChange(findingTitle, oldStatus, newStatus) {
		const statusLabels = {
			'detected': 'Detected',
			'manual': 'User to Fix',
			'automated': 'Fix Now',
			'fixed': 'Workflows'
		};

		const message = findingTitle + ' moved from ' + 
			(statusLabels[oldStatus] || oldStatus) + ' to ' + 
			(statusLabels[newStatus] || newStatus);

		// Create or update ARIA live region
		let $liveRegion = $('#wpshadow-kanban-announcer');
		if (!$liveRegion.length) {
			$liveRegion = $('<div>', {
				id: 'wpshadow-kanban-announcer',
				'aria-live': 'polite',
				'aria-atomic': 'true',
				'class': 'screen-reader-text'
			}).appendTo('body');
		}

		// Update announcement
		$liveRegion.text(message);

		// Clear after a moment to allow for next announcement
		setTimeout(function() {
			$liveRegion.text('');
		}, 1000);
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

	// Dismiss scan panels (per-user)
	$(document).on('click', '.wpshadow-dismiss-scan', function(e) {
		e.preventDefault();
		const scan = $(this).data('scan');
		const $panel = $(this).closest('div[style*="border-radius: 6px"]');
		$.post(ajaxurl, {
			action: 'wpshadow_dismiss_scan_panel',
			nonce: $('[name="wpshadow_kanban_nonce"]').val() || '',
			scan: scan
		}, function(response) {
			if (response.success) {
				$panel.slideUp(200, function(){ $(this).remove(); });
			} else {
				const message = response.data && response.data.message ? response.data.message : 'Unknown error';
				showAlert('Dismiss failed', 'Could not dismiss: ' + message, 'error');
			}
		}).fail(function(){
			showAlert('Connection error', 'Could not dismiss scan panel.', 'error');
		});
	});

	// Restore scan panels link
	$(document).on('click', '#wpshadow-restore-panels', function(e) {
		e.preventDefault();
		$.post(ajaxurl, {
			action: 'wpshadow_restore_scan_panels',
			nonce: $('[name="wpshadow_kanban_nonce"]').val() || ''
		}, function(response){
			if (response.success) {
				location.reload();
			} else {
				const message = response.data && response.data.message ? response.data.message : 'Unknown error';
				showAlert('Restore failed', 'Could not restore: ' + message, 'error');
			}
		}).fail(function(){
			showAlert('Connection error', 'Could not restore panels.', 'error');
		});
	});

	/**
	 * Auto-refresh Kanban board to check for changes
	 * - Verifies tasks are still outstanding
	 * - Auto-closes resolved issues
	 * - Adds newly discovered issues
	 */
	function autoRefreshKanban() {
		if (!$('.kanban-column').length) {
			return;
		}

		const nonce = getKanbanNonce();
		const ajaxUrl = getAjaxUrl();

		if (!ajaxUrl || !nonce) {
			return;
		}

		$.post(ajaxUrl, {
			action: 'wpshadow_refresh_kanban_board',
			nonce: nonce
		}, function(response) {
			if (response.success && response.data) {
				const currentFindings = response.data.findings || {};
				const statusManager = response.data.status_manager || {};

				// Track changes
				let changesDetected = false;
				const addedFindings = [];
				const resolvedFindings = [];
				const currentFindingIds = [];

				// Check each status column
				$('.kanban-column').each(function() {
					const $column = $(this);
					const status = $column.data('status');
					const $cards = $column.find('.finding-card');

					// Get current card IDs in this column
					$cards.each(function() {
						const cardId = $(this).data('finding-id');
						currentFindingIds.push(cardId);

						// Check if this finding is still valid
						if (currentFindings[status]) {
							const stillExists = currentFindings[status].some(f => f.id === cardId);
							if (!stillExists) {
								// Finding resolved - move to fixed or remove
								resolvedFindings.push({ id: cardId, from: status });
								changesDetected = true;
							}
						}
					});
				});

				// Check for new findings
				Object.keys(currentFindings).forEach(function(status) {
					if (currentFindings[status] && Array.isArray(currentFindings[status])) {
						currentFindings[status].forEach(function(finding) {
							if (!currentFindingIds.includes(finding.id)) {
								addedFindings.push({ finding: finding, status: status });
								changesDetected = true;
							}
						});
					}
				});

				// Apply changes if detected
				if (changesDetected) {
					// Auto-close resolved findings
					resolvedFindings.forEach(function(resolved) {
						const $card = $('.finding-card[data-finding-id="' + resolved.id + '"]');
						if ($card.length) {
							$card.fadeOut(400, function() {
								$(this).remove();
								updateColumnCounts();
							});
						}
					});

					// Add new findings
					addedFindings.forEach(function(item) {
						addFindingCard(item.finding, item.status);
					});

					// Update counts
					updateColumnCounts();

					// Show notification
					if (resolvedFindings.length > 0 || addedFindings.length > 0) {
						let message = '';
						if (resolvedFindings.length > 0) {
							message += resolvedFindings.length + ' issue(s) resolved. ';
						}
						if (addedFindings.length > 0) {
							message += addedFindings.length + ' new issue(s) detected.';
						}
						setStatus(message.trim(), 'success');
					}
				}
			}
		}).fail(function() {
			// Silently fail - don't interrupt user workflow
		});
	}

	/**
	 * Add a finding card to a column
	 */
	function addFindingCard(finding, status) {
		const $column = $('.kanban-column[data-status="' + status + '"]');
		const $content = $column.find('.kanban-column-content');

		if (!$content.length) return;

		// Build card HTML
		const threatLevel = finding.threat_level || 50;
		const threatColor = finding.color || '#2196f3';
		const category = finding.category || 'general';
		const title = escapeHtml(finding.title || 'Issue');
		const description = escapeHtml(finding.description || '');

		const cardHtml = 
			'<div class="finding-card" data-finding-id="' + finding.id + '" draggable="true" style="' +
			'background: white; border-left: 4px solid ' + threatColor + '; padding: 12px; margin-bottom: 10px; ' +
			'border-radius: 4px; cursor: move; box-shadow: 0 1px 3px rgba(0,0,0,0.1); animation: slideIn 0.3s ease-out;">' +
			'<div style="font-weight: 600; color: #333; margin-bottom: 6px; font-size: 13px;">' + title + '</div>' +
			'<div style="font-size: 12px; color: #666; margin-bottom: 8px; line-height: 1.4;">' + description + '</div>' +
			'<div style="display: flex; gap: 6px; flex-wrap: wrap; align-items: center;">' +
			'<span style="font-size: 11px; padding: 3px 8px; background: #f3f4f6; border-radius: 3px; color: #6b7280;">' +
			category + '</span>' +
			'<span style="font-size: 11px; padding: 3px 8px; background: ' + threatColor + '20; border-radius: 3px; color: ' + threatColor + ';">' +
			'Threat: ' + threatLevel + '</span>' +
			'</div>' +
			'</div>';

		$content.append(cardHtml);
	}

	/**
	 * Update column counts
	 */
	function updateColumnCounts() {
		$('.kanban-column').each(function() {
			const $column = $(this);
			const count = $column.find('.finding-card').length;
			$column.find('h3 span[style*="float: right"]').text(count);
		});
	}

	if ($('.kanban-column').length) {
		// Start auto-refresh every 2 minutes
		setInterval(autoRefreshKanban, 120000); // 120000ms = 2 minutes

		// Also refresh when page becomes visible (user returns to tab)
		$(document).on('visibilitychange', function() {
			if (!document.hidden) {
				setTimeout(autoRefreshKanban, 2000); // Wait 2 seconds after tab becomes visible
			}
		});

		// Auto-run quick scan on page load
		autoRunQuickScanOnLoad();
	}

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
	window.changeKanbanStatus = changeKanbanStatus;

	// Call update activity on status change
	$(document).ajaxComplete(function(event, xhr, settings) {
		if (settings.data && settings.data.indexOf('wpshadow_change_finding_status') > -1) {
			updateRecentActivity();
		}
	});
});
