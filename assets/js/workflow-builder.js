/**
 * WPShadow Workflow Builder - Enhanced Interactions
 * 
 * Modern drag-and-drop workflow builder with accessibility support
 * 
 * @package WPShadow
 * @since   1.2601.2148
 */

(function($) {
	'use strict';

	// Workflow Builder State
	const WorkflowBuilder = {
		blocks: [],
		selectedBlock: null,
		draggedBlock: null,

		/**
		 * Initialize the workflow builder
		 */
		init: function() {
			this.bindEvents();
			this.setupAccessibility();
			this.announceToScreenReader('Workflow builder loaded');
		},

		/**
		 * Bind all event handlers
		 */
		bindEvents: function() {
			// Palette block drag events
			$('.wps-block-item').on('dragstart', this.handlePaletteDragStart.bind(this));
			$('.wps-block-item').on('dragend', this.handlePaletteDragEnd.bind(this));

			// Canvas drop events
			$('#wps-canvas').on('dragover', this.handleCanvasDragOver.bind(this));
			$('#wps-canvas').on('dragleave', this.handleCanvasDragLeave.bind(this));
			$('#wps-canvas').on('drop', this.handleCanvasDrop.bind(this));

			// Toolbar actions
			$('#wps-save-workflow').on('click', this.handleSaveWorkflow.bind(this));
			$('#wps-test-workflow').on('click', this.handleTestWorkflow.bind(this));
			$('#wps-clear-canvas').on('click', this.handleClearCanvas.bind(this));

			// Keyboard shortcuts
			$(document).on('keydown', this.handleKeyboardShortcuts.bind(this));
		},

		/**
		 * Setup accessibility features
		 */
		setupAccessibility: function() {
			// Add ARIA labels to palette
			$('.wps-workflow-palette').attr({
				'role': 'toolbar',
				'aria-label': 'Workflow blocks'
			});

			// Add ARIA labels to canvas
			$('#wps-canvas').attr({
				'role': 'main',
				'aria-label': 'Workflow canvas',
				'aria-describedby': 'canvas-instructions'
			});

			// Make blocks keyboard navigable
			$('.wps-block-item').attr({
				'role': 'button',
				'tabindex': '0'
			});

			// Enable keyboard activation for blocks
			$('.wps-block-item').on('keydown', function(e) {
				if (e.key === 'Enter' || e.key === ' ') {
					e.preventDefault();
					$(this).trigger('dragstart');
					// Add to canvas via keyboard
					const blockId = $(this).data('block-id');
					const blockType = $(this).data('block-type');
					WorkflowBuilder.addBlockToCanvas(blockId, blockType);
				}
			});
		},

		/**
		 * Handle palette block drag start
		 */
		handlePaletteDragStart: function(e) {
			const $block = $(e.currentTarget);
			const blockId = $block.data('block-id');
			const blockType = $block.data('block-type');

			this.draggedBlock = { blockId, blockType, isNew: true };

			e.originalEvent.dataTransfer.effectAllowed = 'copy';
			e.originalEvent.dataTransfer.setData('text/plain', JSON.stringify(this.draggedBlock));

			$block.addClass('dragging');
			this.announceToScreenReader(wpshadowWorkflow.strings.dragBlock + ': ' + blockId);
		},

		/**
		 * Handle palette block drag end
		 */
		handlePaletteDragEnd: function(e) {
			$(e.currentTarget).removeClass('dragging');
			this.draggedBlock = null;
		},

		/**
		 * Handle canvas drag over
		 */
		handleCanvasDragOver: function(e) {
			e.preventDefault();
			e.originalEvent.dataTransfer.dropEffect = 'copy';
			$('#wps-canvas').addClass('drag-over');
		},

		/**
		 * Handle canvas drag leave
		 */
		handleCanvasDragLeave: function(e) {
			$('#wps-canvas').removeClass('drag-over');
		},

		/**
		 * Handle canvas drop
		 */
		handleCanvasDrop: function(e) {
			e.preventDefault();
			$('#wps-canvas').removeClass('drag-over');

			try {
				const data = JSON.parse(e.originalEvent.dataTransfer.getData('text/plain'));
				this.addBlockToCanvas(data.blockId, data.blockType);
				this.announceToScreenReader(wpshadowWorkflow.strings.dropSuccess);
			} catch (error) {
				console.error('Failed to parse drop data:', error);
				$('#wps-canvas').addClass('drag-invalid');
				setTimeout(() => $('#wps-canvas').removeClass('drag-invalid'), 500);
			}
		},

		/**
		 * Add a block to the canvas
		 */
		addBlockToCanvas: function(blockId, blockType) {
			const uniqueId = 'block_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);

			const blockData = {
				id: uniqueId,
				type: blockType,
				blockId: blockId,
				config: {}
			};

			this.blocks.push(blockData);

			// Get block definition
			const allBlocks = {
				...wpshadowWorkflow.triggers,
				...wpshadowWorkflow.actions
			};

			const blockDef = allBlocks[blockId] || {};

			// Remove empty state if present
			$('#wps-canvas').find('[data-empty-state]').remove();

			// Create blocks container if it doesn't exist
			if ($('.wps-canvas-blocks-container').length === 0) {
				$('#wps-canvas').append('<div class="wps-canvas-blocks-container"></div>');
			}

			// Create block HTML
			const blockHTML = this.createBlockHTML(uniqueId, blockType, blockDef);
			$('.wps-canvas-blocks-container').append(blockHTML);

			// Bind block actions
			this.bindBlockActions(uniqueId);

			// Animate block appearance
			$(`[data-block-id="${uniqueId}"]`).hide().fadeIn(300);
		},

		/**
		 * Create block HTML
		 */
		createBlockHTML: function(uniqueId, blockType, blockDef) {
			const iconClass = blockDef.icon || 'dashicons-block-default';
			const label = blockDef.label || 'Block';
			const description = blockDef.description || 'Click to configure';

			return `
				<div class="wps-block ${blockType}" data-block-id="${uniqueId}" role="listitem" tabindex="0" aria-label="${blockType}: ${label}">
					<div class="wps-block-header">
						<div class="wps-block-label">
							<span class="dashicons ${iconClass}" aria-hidden="true"></span>
							<span>${blockType === 'trigger' ? 'WHEN' : 'THEN'}: ${label}</span>
						</div>
						<button class="wps-block-remove" data-block-id="${uniqueId}" aria-label="Remove ${label} block">
							×
						</button>
					</div>
					<div class="wps-block-config">
						<p>${description}</p>
					</div>
					<div class="wps-block-connector" aria-hidden="true"></div>
				</div>
			`;
		},

		/**
		 * Bind actions for a specific block
		 */
		bindBlockActions: function(blockId) {
			const $block = $(`[data-block-id="${blockId}"]`);

			// Click to configure
			$block.on('click', (e) => {
				if (!$(e.target).hasClass('wps-block-remove')) {
					this.openBlockConfig(blockId);
				}
			});

			// Remove button
			$block.find('.wps-block-remove').on('click', (e) => {
				e.stopPropagation();
				this.removeBlock(blockId);
			});

			// Keyboard navigation
			$block.on('keydown', (e) => {
				if (e.key === 'Enter' || e.key === ' ') {
					e.preventDefault();
					this.openBlockConfig(blockId);
				} else if (e.key === 'Delete' || e.key === 'Backspace') {
					e.preventDefault();
					this.removeBlock(blockId);
				}
			});
		},

		/**
		 * Remove a block from the canvas
		 */
		removeBlock: function(blockId) {
			// Remove from state
			this.blocks = this.blocks.filter(b => b.id !== blockId);

			// Remove from DOM with animation
			$(`[data-block-id="${blockId}"]`).fadeOut(300, function() {
				$(this).remove();

				// Show empty state if no blocks left
				if (WorkflowBuilder.blocks.length === 0) {
					WorkflowBuilder.showEmptyState();
				}
			});

			this.announceToScreenReader(wpshadowWorkflow.strings.blockRemoved);
		},

		/**
		 * Open block configuration
		 */
		openBlockConfig: function(blockId) {
			const block = this.blocks.find(b => b.id === blockId);
			if (!block) return;

			const allBlocks = {
				...wpshadowWorkflow.triggers,
				...wpshadowWorkflow.actions
			};

			const blockDef = allBlocks[block.blockId] || {};

			// Mark block as selected
			$('.wps-block').removeClass('selected');
			$(`[data-block-id="${blockId}"]`).addClass('selected');

			this.selectedBlock = block;

			// Show configuration panel (future enhancement)
			// For now, use console log
			console.log('Configure block:', block, blockDef);
		},

		/**
		 * Show empty state
		 */
		showEmptyState: function() {
			const emptyHTML = `
				<div class="wps-canvas-empty" data-empty-state>
					<span class="dashicons dashicons-block-default"></span>
					<h3>${'Build Your Workflow'}</h3>
					<p>${'Drag blocks from the left to get started'}</p>
					<ol class="wps-steps">
						<li data-step="1">Add a TRIGGER block (IF condition)</li>
						<li data-step="2">Add ACTION blocks (THEN what to do)</li>
						<li data-step="3">Configure each block</li>
						<li data-step="4">Save and test your workflow</li>
					</ol>
				</div>
			`;
			$('#wps-canvas').html(emptyHTML);
		},

		/**
		 * Handle save workflow
		 */
		handleSaveWorkflow: function(e) {
			e.preventDefault();

			const name = $('#wps-workflow-name').val() || 'Untitled Workflow';

			if (this.blocks.length === 0) {
				alert(wpshadowWorkflow.strings.noBlocks);
				$('#wps-workflow-name').focus();
				return;
			}

			// Show loading state
			const $btn = $('#wps-save-workflow');
			const originalText = $btn.html();
			$btn.html('<span class="dashicons dashicons-update"></span> Saving...').prop('disabled', true);

			// Simulate save (replace with AJAX call)
			setTimeout(() => {
				$btn.html(originalText).prop('disabled', false);
				this.announceToScreenReader(wpshadowWorkflow.strings.saveSuccess);
				this.showNotification('success', wpshadowWorkflow.strings.saveSuccess);
			}, 1000);

			console.log('Saving workflow:', { name, blocks: this.blocks });
		},

		/**
		 * Handle test workflow
		 */
		handleTestWorkflow: function(e) {
			e.preventDefault();

			if (this.blocks.length === 0) {
				alert(wpshadowWorkflow.strings.noBlocks);
				return;
			}

			// Show loading state
			const $btn = $('#wps-test-workflow');
			const originalText = $btn.html();
			$btn.html('<span class="dashicons dashicons-update"></span> Testing...').prop('disabled', true);

			// Simulate test (replace with AJAX call)
			setTimeout(() => {
				$btn.html(originalText).prop('disabled', false);
				this.showTestResults();
			}, 1500);

			console.log('Testing workflow:', this.blocks);
		},

		/**
		 * Show test results
		 */
		showTestResults: function() {
			const resultsHTML = `
				<div class="wps-test-results" role="dialog" aria-labelledby="test-results-title">
					<h4 id="test-results-title">Test Results</h4>
					${this.blocks.map((block, index) => {
						const allBlocks = { ...wpshadowWorkflow.triggers, ...wpshadowWorkflow.actions };
						const blockDef = allBlocks[block.blockId] || {};
						const success = Math.random() > 0.2; // Simulate success/failure
						return `
							<div class="wps-test-step ${success ? 'success' : 'error'}">
								<span class="dashicons dashicons-${success ? 'yes-alt' : 'dismiss'}"></span>
								<div>
									<strong>${blockDef.label || block.blockId}</strong>
									<p>${success ? 'Executed successfully' : 'Failed to execute'}</p>
								</div>
							</div>
						`;
					}).join('')}
					<button class="wps-btn ghost" onclick="$(this).closest('.wps-test-results').remove()">Close</button>
				</div>
			`;

			// Remove any existing test results
			$('.wps-test-results').remove();

			// Add new test results
			$('body').append(resultsHTML);

			this.announceToScreenReader(wpshadowWorkflow.strings.testSuccess);
		},

		/**
		 * Handle clear canvas
		 */
		handleClearCanvas: function(e) {
			e.preventDefault();

			if (this.blocks.length === 0) return;

			if (confirm(wpshadowWorkflow.strings.clearConfirm)) {
				$('.wps-canvas-blocks-container').fadeOut(300, () => {
					this.blocks = [];
					this.selectedBlock = null;
					this.showEmptyState();
					$('.wps-canvas-blocks-container').remove();
					this.announceToScreenReader('Canvas cleared');
				});
			}
		},

		/**
		 * Handle keyboard shortcuts
		 */
		handleKeyboardShortcuts: function(e) {
			// Ctrl+S or Cmd+S to save
			if ((e.ctrlKey || e.metaKey) && e.key === 's') {
				e.preventDefault();
				$('#wps-save-workflow').trigger('click');
			}

			// Escape to deselect
			if (e.key === 'Escape') {
				$('.wps-block').removeClass('selected');
				this.selectedBlock = null;
			}
		},

		/**
		 * Show notification
		 */
		showNotification: function(type, message) {
			const notification = `
				<div class="notice notice-${type} is-dismissible" style="margin: 1rem 0;">
					<p>${message}</p>
					<button type="button" class="notice-dismiss">
						<span class="screen-reader-text">Dismiss this notice.</span>
					</button>
				</div>
			`;

			$('.wps-workflow-builder').prepend(notification);

			// Auto-dismiss after 3 seconds
			setTimeout(() => {
				$('.notice').fadeOut(300, function() { $(this).remove(); });
			}, 3000);
		},

		/**
		 * Announce to screen reader
		 */
		announceToScreenReader: function(message) {
			const $announcer = $('#wps-sr-announcer');
			if ($announcer.length === 0) {
				$('body').append('<div id="wps-sr-announcer" class="sr-only" role="status" aria-live="polite" aria-atomic="true"></div>');
			}
			$('#wps-sr-announcer').text(message);
		}
	};

	// Initialize on document ready
	$(document).ready(function() {
		if ($('.wps-workflow-builder').length > 0) {
			WorkflowBuilder.init();
		}
	});

})(jQuery);
