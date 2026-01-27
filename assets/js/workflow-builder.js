/**
 * WPShadow Workflow Builder - Enhanced Interactions
 * 
 * Modern drag-and-drop workflow builder with accessibility support
 * Phase 3 Enhancement: Epic #667/#686 - Scratch-style visual blocks
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
		canvasZoom: 1,
		canvasPosition: { x: 0, y: 0 },
		isPanning: false,
		lastPanPosition: { x: 0, y: 0 },
		touchedBlock: null,
		touchStartPos: { x: 0, y: 0 },
		validationTimeout: null,
		draggedElement: null,
		zoomLevel: 1,
		configPanel: null,

		/**
		 * Initialize the workflow builder
		 */
		init: function() {
			this.setupCanvas();
			this.bindEvents();
			this.setupAccessibility();
			this.addCanvasControls();
			this.addSearchFilter();


			this.createCanvasControls();
			this.announceToScreenReader('Workflow builder loaded. Press Tab to navigate blocks, Enter to configure.');
		},

		/**
		 * Setup canvas with SVG connections and viewport
		 */
		setupCanvas: function() {
			const $canvas = $('#wps-canvas');
			
			// Wrap canvas content in viewport for zoom/pan
			$canvas.wrapInner('<div class="wps-canvas-viewport"><div class="wps-canvas-content"></div></div>');
			
			// Add SVG layer for connections
			$('.wps-canvas-viewport').prepend('<svg class="wps-workflow-connections"><g></g></svg>');
		},

		/**
		 * Add canvas controls (removed zoom buttons per #1677)
		 */
		addCanvasControls: function() {
			// Zoom controls removed - users can use browser zoom
			// Keeping pan controls functional
		},

			// Mouse wheel zoom
			$('.wps-workflow-canvas').on('wheel', (e) => {
				if (e.ctrlKey || e.metaKey) {
					e.preventDefault();
					const delta = e.originalEvent.deltaY > 0 ? -0.05 : 0.05;
					this.zoomCanvas(delta);
				}
			});

			// Pan controls
			$('.wps-canvas-viewport').on('mousedown', (e) => {
				if (e.which === 2 || (e.which === 1 && e.shiftKey)) { // Middle click or Shift+click
					this.isPanning = true;
					const startX = e.clientX - this.canvasPosition.x;
					const startY = e.clientY - this.canvasPosition.y;
					this.lastPanPosition = { x: startX, y: startY };
					$('.wps-canvas-content').addClass('panning');
					e.preventDefault();
				}
			});

			$(document).on('mousemove', (e) => {
				if (this.isPanning) {
					this.canvasPosition.x = e.clientX - this.lastPanPosition.x;
					this.canvasPosition.y = e.clientY - this.lastPanPosition.y;
					this.updateCanvasTransform();
				}
			});

			$(document).on('mouseup', () => {
				if (this.isPanning) {
					this.isPanning = false;
					$('.wps-canvas-content').removeClass('panning');
				}
			});
		},

		/**
		 * Add search/filter functionality to block palette
		 */
		addSearchFilter: function() {
			const searchHTML = `
				<div class="wps-palette-search">
					<span class="dashicons dashicons-search"></span>
					<input type="text" 
						   id="wps-block-search" 
						   placeholder="Search blocks..." 
						   aria-label="Search workflow blocks"
					/>
				</div>
			`;
			$('.wps-workflow-palette').prepend(searchHTML);

			$('#wps-block-search').on('input', (e) => {
				const query = e.target.value.toLowerCase();
				$('.wps-block-item').each(function() {
					const $block = $(this);
					const text = $block.text().toLowerCase();
					if (text.includes(query)) {
						$block.removeClass('hidden').show();
					} else {
						$block.addClass('hidden').hide();
					}
				});
			});
		},

		/**
		 * Zoom canvas
		 */
		zoomCanvas: function(delta) {
			this.canvasZoom = Math.max(0.5, Math.min(2, this.canvasZoom + delta));
			this.updateCanvasTransform();
			$('.wps-zoom-level').text(Math.round(this.canvasZoom * 100) + '%');
			this.announceToScreenReader('Zoom level: ' + Math.round(this.canvasZoom * 100) + '%');
		},

		/**
		 * Reset canvas zoom and position
		 */
		resetCanvas: function() {
			this.canvasZoom = 1;
			this.canvasPosition = { x: 0, y: 0 };
			this.updateCanvasTransform();
			$('.wps-zoom-level').text('100%');
			this.announceToScreenReader('Canvas reset to 100%');
		},

		/**
		 * Update canvas transform
		 */
		updateCanvasTransform: function() {
			const transform = `translate(${this.canvasPosition.x}px, ${this.canvasPosition.y}px) scale(${this.canvasZoom})`;
			$('.wps-canvas-content').css('transform', transform);
			
			// Update connections
			this.updateConnections();
		},

		/**
		 * Draw SVG connections between blocks
		 */
		updateConnections: function() {
			const $svg = $('.wps-workflow-connections g');
			$svg.empty();

			const blocks = $('.wps-block').toArray();
			for (let i = 0; i < blocks.length - 1; i++) {
				const $block1 = $(blocks[i]);
				const $block2 = $(blocks[i + 1]);

				const rect1 = $block1[0].getBoundingClientRect();
				const rect2 = $block2[0].getBoundingClientRect();
				const canvasRect = $('.wps-canvas-viewport')[0].getBoundingClientRect();

				// Calculate positions relative to canvas
				const x1 = rect1.left + rect1.width / 2 - canvasRect.left;
				const y1 = rect1.bottom - canvasRect.top;
				const x2 = rect2.left + rect2.width / 2 - canvasRect.left;
				const y2 = rect2.top - canvasRect.top;

				// Create curved path
				const midY = (y1 + y2) / 2;
				const path = `M ${x1} ${y1} C ${x1} ${midY}, ${x2} ${midY}, ${x2} ${y2}`;

				const pathEl = document.createElementNS('http://www.w3.org/2000/svg', 'path');
				pathEl.setAttribute('d', path);
				pathEl.setAttribute('class', 'trigger-to-action');
				$svg.append(pathEl);
			}
		},

		/**
		 * Bind all event handlers
		 */
		bindEvents: function() {
			// Palette block drag events (desktop)
			$('.wps-block-item').on('dragstart', this.handlePaletteDragStart.bind(this));
			$('.wps-block-item').on('dragend', this.handlePaletteDragEnd.bind(this));

			// Touch events for mobile
			$('.wps-block-item').on('touchstart', this.handlePaletteTouchStart.bind(this));
			$('.wps-block-item').on('touchend', this.handlePaletteTouchEnd.bind(this));

			// Canvas drop events
			$('#wps-canvas').on('dragover', this.handleCanvasDragOver.bind(this));
			$('#wps-canvas').on('dragleave', this.handleCanvasDragLeave.bind(this));
			$('#wps-canvas').on('drop', this.handleCanvasDrop.bind(this));

			// Canvas touch events
			$('#wps-canvas').on('touchmove', this.handleCanvasTouchMove.bind(this));
			$('#wps-canvas').on('touchend', this.handleCanvasTouchEnd.bind(this));

			// Toolbar actions
			$('#wps-save-workflow').on('click', this.handleSaveWorkflow.bind(this));
			$('#wps-test-workflow').on('click', this.handleTestWorkflow.bind(this));
			$('#wps-clear-canvas').on('click', this.handleClearCanvas.bind(this));

			// Keyboard shortcuts
			$(document).on('keydown', this.handleKeyboardShortcuts.bind(this));
			
			// Canvas reordering
			$(document).on('dragstart', '.wps-block', this.handleBlockDragStart.bind(this));
			$(document).on('dragend', '.wps-block', this.handleBlockDragEnd.bind(this));
			$(document).on('dragover', '.wps-block', this.handleBlockDragOver.bind(this));
			$(document).on('drop', '.wps-block', this.handleBlockDrop.bind(this));
		},

		/**
		 * Setup touch events for mobile devices
		 */
		setupTouchEvents: function() {
			// Touch start on palette blocks
			$('.wps-block-item').on('touchstart', (e) => {
				const touch = e.originalEvent.touches[0];
				this.touchedBlock = $(e.currentTarget);
				this.touchStartPos = { x: touch.clientX, y: touch.clientY };
				
				this.touchedBlock.addClass('dragging');
			});

			// Touch move
			$(document).on('touchmove', (e) => {
				if (!this.touchedBlock) return;

				const touch = e.originalEvent.touches[0];
				const deltaX = touch.clientX - this.touchStartPos.x;
				const deltaY = touch.clientY - this.touchStartPos.y;

				// Show visual feedback for drag
				if (Math.abs(deltaX) > 10 || Math.abs(deltaY) > 10) {
					$('#wps-canvas').addClass('drag-over');
				}
			});

			// Touch end
			$(document).on('touchend', (e) => {
				if (!this.touchedBlock) return;

				$('.wps-block-item').removeClass('dragging');
				$('#wps-canvas').removeClass('drag-over');

				const touch = e.originalEvent.changedTouches[0];
				const canvasRect = $('#wps-canvas')[0].getBoundingClientRect();

				// Check if touch ended over canvas
				if (
					touch.clientX >= canvasRect.left &&
					touch.clientX <= canvasRect.right &&
					touch.clientY >= canvasRect.top &&
					touch.clientY <= canvasRect.bottom
				) {
					const blockId = this.touchedBlock.data('block-id');
					const blockType = this.touchedBlock.data('block-type');
					this.addBlockToCanvas(blockId, blockType);
				}

				this.touchedBlock = null;
			});
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
				'tabindex': '0',
				'aria-pressed': 'false'
			});

			// Enable keyboard activation for blocks
			$('.wps-block-item').on('keydown', function(e) {
				if (e.key === 'Enter' || e.key === ' ') {
					e.preventDefault();
					const blockId = $(this).data('block-id');
					const blockType = $(this).data('block-type');
					WorkflowBuilder.addBlockToCanvas(blockId, blockType);
					WorkflowBuilder.announceToScreenReader('Block added to canvas');
				}
			});
			
			// Add keyboard hint
			if ($('.wps-keyboard-hint').length === 0) {
				$('.wps-workflow-builder').append(
					'<div class="wps-keyboard-hint" role="status" aria-live="polite">' +
					'Keyboard: Tab to navigate, Enter to add/configure, Delete to remove, Arrow keys to reorder' +
					'</div>'
				);
			}
		},

		/**
		 * Create configuration panel
		 */
		createConfigPanel: function() {
			if ($('#wps-config-panel').length > 0) return;
			
			const panelHTML = `
				<div id="wps-config-panel" class="wps-block-config-panel" role="dialog" aria-labelledby="config-panel-title" aria-modal="true">
					<div class="wps-config-panel-header">
						<h4 id="config-panel-title">
							<span class="dashicons dashicons-admin-generic" aria-hidden="true"></span>
							Configure Block
						</h4>
						<button class="wps-config-panel-close" aria-label="Close configuration panel">
							×
						</button>
					</div>
					<div class="wps-config-panel-body" id="wps-config-panel-body">
						<!-- Dynamic content -->
					</div>
					<div class="wps-config-panel-footer">
						<button class="wps-btn ghost" id="wps-config-cancel">Cancel</button>
						<button class="wps-btn primary" id="wps-config-save">Save Changes</button>
					</div>
				</div>
			`;
			
			$('body').append(panelHTML);
			this.configPanel = $('#wps-config-panel');
			
			// Bind panel events
			$('.wps-config-panel-close, #wps-config-cancel').on('click', this.closeConfigPanel.bind(this));
			$('#wps-config-save').on('click', this.saveBlockConfig.bind(this));
			
			// Focus trap
			this.configPanel.on('keydown', this.handleConfigPanelKeydown.bind(this));
		},

		/**
		 * Create canvas controls (zoom/pan)
		 */
		createCanvasControls: function() {
			if ($('.wps-canvas-controls').length > 0) return;
			
			const controlsHTML = `
				<div class="wps-canvas-controls" role="toolbar" aria-label="Canvas controls">
					<button class="wps-canvas-control-btn" id="wps-zoom-out" aria-label="Zoom out" title="Zoom out">
						<span class="dashicons dashicons-minus" aria-hidden="true"></span>
					</button>
					<span class="wps-canvas-zoom-level" id="wps-zoom-level" aria-live="polite">100%</span>
					<button class="wps-canvas-control-btn" id="wps-zoom-in" aria-label="Zoom in" title="Zoom in">
						<span class="dashicons dashicons-plus" aria-hidden="true"></span>
					</button>
					<button class="wps-canvas-control-btn" id="wps-zoom-reset" aria-label="Reset zoom" title="Reset zoom (100%)">
						<span class="dashicons dashicons-image-crop" aria-hidden="true"></span>
					</button>
				</div>
			`;
			
			$('.wps-workflow-canvas-wrapper').append(controlsHTML);
			
			// Bind zoom events
			$('#wps-zoom-in').on('click', this.zoomIn.bind(this));
			$('#wps-zoom-out').on('click', this.zoomOut.bind(this));
			$('#wps-zoom-reset').on('click', this.zoomReset.bind(this));
		},

		/**
		 * Validate workflow
		 */
		validateWorkflow: function() {
			let isValid = false;
			let message = '';

			const triggerBlocks = this.blocks.filter(b => b.type === 'trigger');
			const actionBlocks = this.blocks.filter(b => b.type === 'action');

			if (this.blocks.length === 0) {
				message = 'Add exactly one trigger and one action to create a workflow';
			} else if (triggerBlocks.length === 0) {
				message = 'Add exactly one trigger (IF condition)';
			} else if (triggerBlocks.length > 1) {
				message = 'Only one trigger is allowed per workflow';
			} else if (actionBlocks.length === 0) {
				message = 'Add exactly one action (THEN what to do)';
			} else if (actionBlocks.length > 1) {
				message = 'Only one action is allowed per workflow';
			} else {
				isValid = true;
				message = 'Workflow is ready to save';
			}

			// Update indicator
			const $indicator = $('.wps-workflow-validation');
			$indicator.removeClass('valid invalid');
			$indicator.addClass(isValid ? 'valid' : 'invalid');
			$indicator.find('.dashicons').removeClass().addClass('dashicons').addClass(
				isValid ? 'dashicons-yes-alt' : 'dashicons-info'
			);
			$indicator.find('.wps-validation-message').text(message);

			// Show indicator briefly
			$indicator.addClass('show');
			clearTimeout(this.validationTimeout);
			this.validationTimeout = setTimeout(() => {
				$indicator.removeClass('show');
			}, 3000);

			return isValid;
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
		 * Handle palette block touch start (mobile)
		 */
		handlePaletteTouchStart: function(e) {
			const $block = $(e.currentTarget);
			const blockId = $block.data('block-id');
			const blockType = $block.data('block-type');

			this.draggedBlock = { blockId, blockType, isNew: true };
			$block.addClass('dragging');

			// Provide haptic feedback if available
			if (navigator.vibrate) {
				navigator.vibrate(50);
			}

			this.announceToScreenReader(wpshadowWorkflow.strings.dragBlock + ': ' + blockId);
		},

		/**
		 * Handle palette block touch end (mobile)
		 */
		handlePaletteTouchEnd: function(e) {
			const $block = $(e.currentTarget);
			$block.removeClass('dragging');

			// Check if touch ended over canvas
			const touch = e.originalEvent.changedTouches[0];
			const canvasEl = document.getElementById('wps-canvas');
			const canvasRect = canvasEl.getBoundingClientRect();

			if (touch.clientX >= canvasRect.left && touch.clientX <= canvasRect.right &&
			    touch.clientY >= canvasRect.top && touch.clientY <= canvasRect.bottom) {
				if (this.draggedBlock) {
					this.addBlockToCanvas(this.draggedBlock.blockId, this.draggedBlock.blockType);
					this.announceToScreenReader(wpshadowWorkflow.strings.dropSuccess);
				}
			}

			this.draggedBlock = null;
		},

		/**
		 * Handle canvas touch move (mobile)
		 */
		handleCanvasTouchMove: function(e) {
			if (this.draggedBlock) {
				e.preventDefault();
				$('#wps-canvas').addClass('drag-over');
			}
		},

		/**
		 * Handle canvas touch end (mobile)
		 */
		handleCanvasTouchEnd: function(e) {
			$('#wps-canvas').removeClass('drag-over');
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
				
				if (data.isNew) {
					// Adding new block from palette
					this.addBlockToCanvas(data.blockId, data.blockType);
					this.announceToScreenReader(wpshadowWorkflow.strings.dropSuccess);
				}
			} catch (error) {
				console.error('Failed to parse drop data:', error);
				$('#wps-canvas').addClass('drag-invalid');
				setTimeout(() => $('#wps-canvas').removeClass('drag-invalid'), 500);
			}
		},

		/**
		 * Handle canvas block drag start (reordering)
		 */
		handleBlockDragStart: function(e) {
			const $block = $(e.currentTarget);
			const blockId = $block.data('block-id');
			
			this.draggedElement = $block;
			this.draggedBlock = this.blocks.find(b => b.id === blockId);
			
			$block.addClass('dragging');
			e.originalEvent.dataTransfer.effectAllowed = 'move';
			e.originalEvent.dataTransfer.setData('text/plain', JSON.stringify({ 
				blockId: blockId, 
				isReorder: true 
			}));
			
			this.announceToScreenReader('Dragging block. Drop on another block to reorder.');
		},

		/**
		 * Handle canvas block drag end
		 */
		handleBlockDragEnd: function(e) {
			$(e.currentTarget).removeClass('dragging');
			$('.wps-block-drop-placeholder').remove();
			this.draggedElement = null;
		},

		/**
		 * Handle canvas block drag over (for reordering)
		 */
		handleBlockDragOver: function(e) {
			if (!this.draggedElement) return;
			
			e.preventDefault();
			e.stopPropagation();
			
			const $target = $(e.currentTarget);
			if ($target.hasClass('dragging')) return;
			
			const rect = e.currentTarget.getBoundingClientRect();
			const midpoint = rect.top + rect.height / 2;
			const insertBefore = e.clientY < midpoint;
			
			$('.wps-block-drop-placeholder').remove();
			
			if (insertBefore) {
				$target.before('<div class="wps-block-drop-placeholder"></div>');
			} else {
				$target.after('<div class="wps-block-drop-placeholder"></div>');
			}
		},

		/**
		 * Handle canvas block drop (reordering)
		 */
		handleBlockDrop: function(e) {
			if (!this.draggedElement) return;
			
			e.preventDefault();
			e.stopPropagation();
			
			const $target = $(e.currentTarget);
			if ($target.hasClass('dragging')) return;
			
			const rect = e.currentTarget.getBoundingClientRect();
			const midpoint = rect.top + rect.height / 2;
			const insertBefore = e.clientY < midpoint;
			
			// Reorder DOM
			if (insertBefore) {
				$target.before(this.draggedElement);
			} else {
				$target.after(this.draggedElement);
			}
			
			// Reorder state array
			const draggedId = this.draggedElement.data('block-id');
			const targetId = $target.data('block-id');
			const draggedIndex = this.blocks.findIndex(b => b.id === draggedId);
			const targetIndex = this.blocks.findIndex(b => b.id === targetId);
			
			if (draggedIndex !== -1 && targetIndex !== -1) {
				const [movedBlock] = this.blocks.splice(draggedIndex, 1);
				const newTargetIndex = insertBefore ? targetIndex : targetIndex + 1;
				this.blocks.splice(newTargetIndex > draggedIndex ? newTargetIndex - 1 : newTargetIndex, 0, movedBlock);
			}
			
			$('.wps-block-drop-placeholder').remove();
			this.announceToScreenReader('Block reordered');
		},

		/**
		 * Zoom in
		 */
		zoomIn: function() {
			const levels = [0.75, 1, 1.25, 1.5];
			const currentIndex = levels.indexOf(this.zoomLevel);
			
			if (currentIndex < levels.length - 1) {
				this.zoomLevel = levels[currentIndex + 1];
				this.applyZoom();
			}
		},

		/**
		 * Zoom out
		 */
		zoomOut: function() {
			const levels = [0.75, 1, 1.25, 1.5];
			const currentIndex = levels.indexOf(this.zoomLevel);
			
			if (currentIndex > 0) {
				this.zoomLevel = levels[currentIndex - 1];
				this.applyZoom();
			}
		},

		/**
		 * Reset zoom
		 */
		zoomReset: function() {
			this.zoomLevel = 1;
			this.applyZoom();
		},

		/**
		 * Apply zoom level
		 */
		applyZoom: function() {
			const $canvas = $('#wps-canvas');
			$canvas.attr('data-zoom', this.zoomLevel);
			$canvas.addClass('zoomed');
			$('#wps-zoom-level').text(Math.round(this.zoomLevel * 100) + '%');
			
			// Update button states
			$('#wps-zoom-out').prop('disabled', this.zoomLevel <= 0.75);
			$('#wps-zoom-in').prop('disabled', this.zoomLevel >= 1.5);
			
			this.announceToScreenReader('Zoom level: ' + Math.round(this.zoomLevel * 100) + '%');
		},

		/**
		 * Add a block to the canvas
		 */
		addBlockToCanvas: function(blockId, blockType) {
			const triggerCount = this.blocks.filter(b => b.type === 'trigger').length;
			const actionCount = this.blocks.filter(b => b.type === 'action').length;

			if ('trigger' === blockType && triggerCount >= 1) {
				const message = wpshadowWorkflow.strings.singleTrigger || 'Only one trigger is allowed per workflow';
				
				// Issue #1677: Use accessible modal instead of alert
				if (typeof WPSModal !== 'undefined') {
					WPSModal.show({
						title: 'Trigger Limit Reached',
						message: message + '<br><br>Need multiple triggers? Upgrade to WPShadow Pro for unlimited workflow complexity.',
						confirmText: 'Got It',
						type: 'warning'
					});
				} else {
					this.showNotification('error', message);
				}
				
				this.announceToScreenReader(message);
				return;
			}

			if ('action' === blockType && actionCount >= 1) {
				const message = wpshadowWorkflow.strings.singleAction || 'Only one action is allowed per workflow';
				this.showNotification('error', message);
				this.announceToScreenReader(message);
				return;
			}

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
			$('.wps-canvas-content').find('[data-empty-state]').remove();

			// Create blocks container if it doesn't exist
			if ($('.wps-canvas-blocks-container').length === 0) {
				$('.wps-canvas-content').append('<div class="wps-canvas-blocks-container"></div>');
			}

			// Create block HTML
			const blockHTML = this.createBlockHTML(uniqueId, blockType, blockDef);
			$('.wps-canvas-blocks-container').append(blockHTML);

			// Bind block actions
			this.bindBlockActions(uniqueId);

			// Animate block appearance
			$(`[data-block-id="${uniqueId}"]`).hide().fadeIn(300, () => {
				// Update connections after animation
				this.updateConnections();
				// Validate workflow
				this.validateWorkflow();
				
				// Issue #1677: Hide triggers panel after first trigger is added
				if (blockType === 'trigger') {
					$('.wps-palette-section:has([data-block-type="trigger"])').fadeOut(300);
					this.announceToScreenReader('Trigger added. Only one trigger allowed per workflow.');
				}
				
				// Issue #1677: Show actions panel once trigger exists
				if (blockType === 'trigger' && $('.wps-palette-section:has([data-block-type="action"])').is(':hidden')) {
					$('.wps-palette-section:has([data-block-type="action"])').fadeIn(300);
					this.announceToScreenReader('Actions panel now available.');
				}
			});
		},

		/**
		 * Create block HTML
		 */
		createBlockHTML: function(uniqueId, blockType, blockDef) {
			const iconClass = blockDef.icon || 'dashicons-block-default';
			const label = blockDef.label || 'Block';
			const description = blockDef.description || 'Click to configure';

			return `
				<div class="wps-block ${blockType}" 
				     data-block-id="${uniqueId}" 
				     role="listitem" 
				     tabindex="0" 
				     draggable="true"
				     aria-label="${blockType}: ${label}. Press Enter to configure, Delete to remove, or drag to reorder.">
					<div class="wps-block-header">
						<div class="wps-block-label">
							<span class="dashicons ${iconClass}" aria-hidden="true"></span>
							<span>${blockType === 'trigger' ? 'WHEN' : 'THEN'}: ${label}</span>
						</div>
						<button class="wps-block-remove" 
						        data-block-id="${uniqueId}" 
						        aria-label="Remove ${label} block"
						        tabindex="0">
							×
						</button>
					</div>
					<div class="wps-block-config">
						<p>${description}</p>
					</div>
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
				} else if (e.ctrlKey && e.key === 'ArrowUp') {
					// Move block up
					e.preventDefault();
					this.moveBlockUp(blockId);
				} else if (e.ctrlKey && e.key === 'ArrowDown') {
					// Move block down
					e.preventDefault();
					this.moveBlockDown(blockId);
				}
			});
		},

		/**
		 * Move block up in the order
		 */
		moveBlockUp: function(blockId) {
			const $block = $(`[data-block-id="${blockId}"]`);
			const $prev = $block.prev('.wps-block');
			
			if ($prev.length) {
				$block.insertBefore($prev);
				$block.focus();
				
				// Update state array
				const index = this.blocks.findIndex(b => b.id === blockId);
				if (index > 0) {
					const [block] = this.blocks.splice(index, 1);
					this.blocks.splice(index - 1, 0, block);
				}
				
				this.announceToScreenReader('Block moved up');
			}
		},

		/**
		 * Move block down in the order
		 */
		moveBlockDown: function(blockId) {
			const $block = $(`[data-block-id="${blockId}"]`);
			const $next = $block.next('.wps-block');
			
			if ($next.length) {
				$block.insertAfter($next);
				$block.focus();
				
				// Update state array
				const index = this.blocks.findIndex(b => b.id === blockId);
				if (index < this.blocks.length - 1) {
					const [block] = this.blocks.splice(index, 1);
					this.blocks.splice(index + 1, 0, block);
				}
				
				this.announceToScreenReader('Block moved down');
			}
		},

		/**
		 * Remove a block from the canvas
		 */
		removeBlock: function(blockId) {
			// Get block info before removing
			const block = this.blocks.find(b => b.id === blockId);
			const blockType = block ? block.type : null;
			
			// Remove from state
			this.blocks = this.blocks.filter(b => b.id !== blockId);

			// Remove from DOM with animation
			$(`[data-block-id="${blockId}"]`).fadeOut(300, () => {
				$(`[data-block-id="${blockId}"]`).remove();

				// Show empty state if no blocks left
				if (this.blocks.length === 0) {
					this.showEmptyState();
				} else {
					// Update connections after block removal
					this.updateConnections();
				}
				
				// Issue #1677: Show triggers panel when trigger removed
				if (blockType === 'trigger') {
					$('.wps-palette-section:has([data-block-type="trigger"])').fadeIn(300);
					// Hide actions if no triggers remain
					const remainingTriggers = this.blocks.filter(b => b.type === 'trigger').length;
					if (remainingTriggers === 0) {
						$('.wps-palette-section:has([data-block-type="action"])').fadeOut(300);
					}
				}
				
				// Validate workflow
				this.validateWorkflow();
			});

			this.announceToScreenReader(wpshadowWorkflow.strings.blockRemoved);
		},

		/**
		 * Open block configuration panel
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

			// Build config form
			let formHTML = `<p><strong>${blockDef.label || block.blockId}</strong></p>`;
			formHTML += `<p class="description">${blockDef.description || ''}</p>`;
			
			if (blockDef.fields) {
				Object.keys(blockDef.fields).forEach(fieldKey => {
					const field = blockDef.fields[fieldKey];
					const value = block.config[fieldKey] || field.default || '';
					
					formHTML += `<div class="wps-config-field">`;
					formHTML += `<label for="config-${fieldKey}">${field.label}</label>`;
					
					if (field.type === 'select') {
						formHTML += `<select id="config-${fieldKey}" name="${fieldKey}">`;
						Object.keys(field.options).forEach(optKey => {
							const selected = value === optKey ? 'selected' : '';
							formHTML += `<option value="${optKey}" ${selected}>${field.options[optKey]}</option>`;
						});
						formHTML += `</select>`;
					} else if (field.type === 'textarea') {
						formHTML += `<textarea id="config-${fieldKey}" name="${fieldKey}" rows="4">${value}</textarea>`;
					} else if (field.type === 'checkbox') {
						const checked = value ? 'checked' : '';
						formHTML += `<input type="checkbox" id="config-${fieldKey}" name="${fieldKey}" ${checked} />`;
					} else {
						formHTML += `<input type="${field.type || 'text'}" id="config-${fieldKey}" name="${fieldKey}" value="${value}" />`;
					}
					
					formHTML += `</div>`;
				});
			}
			
			$('#wps-config-panel-body').html(formHTML);
			this.configPanel.addClass('active');
			
			// Focus first field
			setTimeout(() => {
				$('#wps-config-panel-body').find('input, select, textarea').first().focus();
			}, 100);
			
			this.announceToScreenReader('Configuration panel opened. Use Tab to navigate fields.');
		},

		/**
		 * Close configuration panel
		 */
		closeConfigPanel: function() {
			this.configPanel.removeClass('active');
			$('.wps-block').removeClass('selected');
			this.selectedBlock = null;
			
			// Return focus to canvas
			$('#wps-canvas').focus();
			this.announceToScreenReader('Configuration panel closed');
		},

		/**
		 * Save block configuration
		 */
		saveBlockConfig: function() {
			if (!this.selectedBlock) return;
			
			// Gather form data
			const formData = {};
			$('#wps-config-panel-body').find('input, select, textarea').each(function() {
				const $field = $(this);
				const name = $field.attr('name');
				
				if ($field.is(':checkbox')) {
					formData[name] = $field.is(':checked');
				} else {
					formData[name] = $field.val();
				}
			});
			
			// Update block config
			this.selectedBlock.config = formData;
			
			// Update block display
			const $block = $(`[data-block-id="${this.selectedBlock.id}"]`);
			$block.find('.wps-block-config p').text('Configured');
			
			this.closeConfigPanel();
			this.announceToScreenReader(wpshadowWorkflow.strings.configSaved);
		},

		/**
		 * Handle config panel keyboard navigation (focus trap)
		 */
		handleConfigPanelKeydown: function(e) {
			if (!this.configPanel.hasClass('active')) return;
			
			// Escape to close
			if (e.key === 'Escape') {
				e.preventDefault();
				this.closeConfigPanel();
				return;
			}
			
			// Tab focus trap
			if (e.key === 'Tab') {
				const $focusable = this.configPanel.find('button, input, select, textarea, [tabindex="0"]');
				const $first = $focusable.first();
				const $last = $focusable.last();
				
				if (e.shiftKey && document.activeElement === $first[0]) {
					e.preventDefault();
					$last.focus();
				} else if (!e.shiftKey && document.activeElement === $last[0]) {
					e.preventDefault();
					$first.focus();
				}
			}
		},

		/**
		 * Show empty state
		 */
		showEmptyState: function() {
			const emptyHTML = `
				<div class="wps-canvas-empty" data-empty-state>
					<span class="dashicons dashicons-block-default"></span>
					<h3>${wpshadowWorkflow.strings.buildWorkflow || 'Build Your Workflow'}</h3>
					<p>${wpshadowWorkflow.strings.dragBlocks || 'Drag blocks from the left to get started'}</p>
					<ol class="wps-steps">
						<li data-step="1">${wpshadowWorkflow.strings.step1 || 'Add a TRIGGER block (IF condition)'}</li>
						<li data-step="2">${wpshadowWorkflow.strings.step2 || 'Add ACTION blocks (THEN what to do)'}</li>
						<li data-step="3">${wpshadowWorkflow.strings.step3 || 'Configure each block'}</li>
						<li data-step="4">${wpshadowWorkflow.strings.step4 || 'Save and test your workflow'}</li>
					</ol>
				</div>
			`;
			$('.wps-canvas-content').html(emptyHTML);
		},

		/**
		 * Handle save workflow
		 */
		handleSaveWorkflow: function(e) {
			e.preventDefault();

			let name = $('#wps-workflow-name').val().trim();
			
			// Issue #1677: Generate ridiculous name if empty
			if (!name) {
				name = this.generateRidiculousName();
				$('#wps-workflow-name').val(name);
				this.showNotification('info', 'We gave your workflow a magnificently ridiculous name!');
			}

			// Validate workflow before saving
			if (!this.validateWorkflow()) {
				this.showNotification('error', 'Please complete your workflow before saving');
				return;
			}

			// Show loading state
			const $btn = $('#wps-save-workflow');
			const originalText = $btn.html();
			$btn.html('<span class="dashicons dashicons-update"></span> Saving...').prop('disabled', true);

			// Prepare workflow data
			const workflowData = {
				action: 'wpshadow_save_workflow',
				nonce: wpshadowWorkflow.nonce,
				name: name,
				blocks: this.blocks,
				enabled: true
			};

			// AJAX save
			$.post(wpshadowWorkflow.ajaxUrl, workflowData)
				.done((response) => {
					if (response.success) {
						this.announceToScreenReader(wpshadowWorkflow.strings.saveSuccess);
						this.showNotification('success', wpshadowWorkflow.strings.saveSuccess);
						
						// Store workflow ID if returned
						if (response.data && response.data.workflow_id) {
							$('#wps-canvas').data('workflow-id', response.data.workflow_id);
						}
					} else {
						this.showNotification('error', response.data?.message || wpshadowWorkflow.strings.saveError);
					}
				})
				.fail(() => {
					this.showNotification('error', wpshadowWorkflow.strings.saveError);
				})
				.always(() => {
					$btn.html(originalText).prop('disabled', false);
				});

			console.log('Saving workflow:', { name, blocks: this.blocks });
		},

		/**
		 * Handle test workflow
		 */
		handleTestWorkflow: function(e) {
			e.preventDefault();

			if (this.blocks.length === 0) {
				if (window.WPShadowDesign && typeof window.WPShadowDesign.alert === 'function') {
					window.WPShadowDesign.alert(wpshadowWorkflow.strings.testTitle || 'Workflow Test', wpshadowWorkflow.strings.noBlocks, 'warning');
				} else {
					alert(wpshadowWorkflow.strings.noBlocks);
				}
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

			const clearCanvas = () => {
				$('.wps-canvas-blocks-container').fadeOut(300, () => {
					this.blocks = [];
					this.selectedBlock = null;
					this.showEmptyState();
					$('.wps-canvas-blocks-container').remove();
					this.announceToScreenReader('Canvas cleared');
				});
			};

			if (window.WPShadowDesign && typeof window.WPShadowDesign.confirm === 'function') {
				window.WPShadowDesign.confirm(wpshadowWorkflow.strings.clearConfirm, clearCanvas);
				return;
			}

			if (confirm(wpshadowWorkflow.strings.clearConfirm)) {
				clearCanvas();
			}
		},

		/**
		 * Handle keyboard shortcuts
		 */
		handleKeyboardShortcuts: function(e) {
			// Ignore if typing in input
			if ($(e.target).is('input, textarea, select')) return;
			
			// Ctrl+S or Cmd+S to save
			if ((e.ctrlKey || e.metaKey) && e.key === 's') {
				e.preventDefault();
				$('#wps-save-workflow').trigger('click');
			}

			// Escape to deselect or close panel
			if (e.key === 'Escape') {
				if (this.configPanel && this.configPanel.hasClass('active')) {
					this.closeConfigPanel();
				} else {
					$('.wps-block').removeClass('selected');
					this.selectedBlock = null;
				}
			}
			
			// Arrow keys for block navigation
			if (['ArrowUp', 'ArrowDown'].includes(e.key)) {
				e.preventDefault();
				const $blocks = $('.wps-block');
				const $focused = $blocks.filter(':focus');
				
				if ($focused.length) {
					const currentIndex = $blocks.index($focused);
					let newIndex;
					
					if (e.key === 'ArrowUp' && currentIndex > 0) {
						newIndex = currentIndex - 1;
					} else if (e.key === 'ArrowDown' && currentIndex < $blocks.length - 1) {
						newIndex = currentIndex + 1;
					}
					
					if (newIndex !== undefined) {
						$blocks.eq(newIndex).focus();
						this.announceToScreenReader('Moved to block ' + (newIndex + 1) + ' of ' + $blocks.length);
					}
				} else if ($blocks.length > 0) {
					// Focus first block if none focused
					$blocks.first().focus();
					this.announceToScreenReader('Focused first block');
				}
			}
			
			// Zoom shortcuts
			if (e.ctrlKey || e.metaKey) {
				if (e.key === '+' || e.key === '=') {
					e.preventDefault();
					this.zoomIn();
				} else if (e.key === '-') {
					e.preventDefault();
					this.zoomOut();
				} else if (e.key === '0') {
					e.preventDefault();
					this.zoomReset();
				}
			}

			// Arrow key navigation between blocks
			if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(e.key)) {
				this.handleArrowNavigation(e);
			}

			// Ctrl+F or Cmd+F to focus search
			if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
				e.preventDefault();
				$('#wps-block-search').focus();
			}
		},

		/**
		 * Handle arrow key navigation between blocks
		 */
		handleArrowNavigation: function(e) {
			const $blocks = $('.wps-block');
			if ($blocks.length === 0) return;

			const $focused = $(':focus');
			let currentIndex = -1;

			// Find currently focused block
			$blocks.each(function(index) {
				if ($(this).is($focused)) {
					currentIndex = index;
					return false;
				}
			});

			// Navigate based on arrow key
			let newIndex = currentIndex;
			if (e.key === 'ArrowDown' || e.key === 'ArrowRight') {
				e.preventDefault();
				newIndex = currentIndex < $blocks.length - 1 ? currentIndex + 1 : 0;
			} else if (e.key === 'ArrowUp' || e.key === 'ArrowLeft') {
				e.preventDefault();
				newIndex = currentIndex > 0 ? currentIndex - 1 : $blocks.length - 1;
			}

			// Focus new block
			if (newIndex !== currentIndex && newIndex >= 0) {
				$blocks.eq(newIndex).focus();
				this.announceToScreenReader('Block ' + (newIndex + 1) + ' of ' + $blocks.length);
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
		 * Generate ridiculous workflow name (Issue #1677)
		 */
		generateRidiculousName: function() {
			const adjectives = [
				'Magnificently', 'Ridiculously', 'Spectacularly', 'Absurdly', 'Wonderfully',
				'Hilariously', 'Preposterous', 'Outrageous', 'Fantastically', 'Ludicrously',
				'Incredibly', 'Astonishingly', 'Remarkably', 'Extraordinarily', 'Inexplicably'
			];
			const nouns = [
				'Suspicious', 'Questionable', 'Mysterious', 'Peculiar', 'Bizarre',
				'Enigmatic', 'Cryptic', 'Curious', 'Unusual', 'Eccentric',
				'Whimsical', 'Quirky', 'Odd', 'Strange', 'Unconventional'
			];
			const things = [
				'Automation', 'Workflow', 'Sequence', 'Process', 'Routine',
				'Mechanism', 'Protocol', 'Procedure', 'Operation', 'System',
				'Algorithm', 'Pattern', 'Strategy', 'Method', 'Scheme'
			];
			
			const adjective = adjectives[Math.floor(Math.random() * adjectives.length)];
			const noun = nouns[Math.floor(Math.random() * nouns.length)];
			const thing = things[Math.floor(Math.random() * things.length)];
			const number = Math.floor(Math.random() * 100) + 1;
			
			return `${adjective} ${noun} ${thing} #${number}`;
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
		},

		/**
		 * Create configuration panel
		 */
		createConfigPanel: function() {
			if ($('.wps-block-config-panel').length > 0) return;

			const panelHTML = `
				<div class="wps-block-config-panel" role="dialog" aria-labelledby="config-panel-title" aria-hidden="true">
					<div class="wps-config-panel-header">
						<h4 id="config-panel-title">
							<span class="dashicons dashicons-admin-generic" aria-hidden="true"></span>
							Configure Block
						</h4>
						<button type="button" class="wps-config-panel-close" aria-label="Close configuration panel">
							×
						</button>
					</div>
					<div class="wps-config-panel-body" id="wps-config-panel-content">
						<!-- Configuration fields will be dynamically inserted here -->
					</div>
				</div>
			`;

			$('body').append(panelHTML);

			// Bind close button
			$('.wps-config-panel-close').on('click', () => {
				$('.wps-block-config-panel').removeClass('active').attr('aria-hidden', 'true');
				$('.wps-block').removeClass('selected');
				this.selectedBlock = null;
			});
		},

		/**
		 * Create keyboard shortcuts panel
		 */
		createShortcutsPanel: function() {
			if ($('.wps-shortcuts-panel').length > 0) return;

			const shortcuts = [
				{ label: 'Save workflow', keys: ['Ctrl/Cmd', 'S'] },
				{ label: 'Test workflow', keys: ['Ctrl/Cmd', 'T'] },
				{ label: 'Clear canvas', keys: ['Ctrl/Cmd', 'K'] },
				{ label: 'Show shortcuts', keys: ['?'] },
				{ label: 'Close panel/Deselect', keys: ['Esc'] },
				{ label: 'Delete selected block', keys: ['Del/Backspace'] }
			];

			const shortcutsHTML = shortcuts.map(s => `
				<li>
					<span class="wps-shortcut-label">${s.label}</span>
					<div class="wps-shortcut-keys">
						${s.keys.map(k => `<kbd class="wps-key">${k}</kbd>`).join('<span style="margin: 0 0.25rem;">+</span>')}
					</div>
				</li>
			`).join('');

			const panelHTML = `
				<div class="wps-shortcuts-backdrop"></div>
				<div class="wps-shortcuts-panel" role="dialog" aria-labelledby="shortcuts-title" aria-modal="true">
					<h3 id="shortcuts-title">
						<span class="dashicons dashicons-keyboard-hide" aria-hidden="true"></span>
						Keyboard Shortcuts
					</h3>
					<ul class="wps-shortcuts-list">
						${shortcutsHTML}
					</ul>
					<button type="button" class="wps-btn ghost wps-close-shortcuts" style="width: 100%; margin-top: 1rem;">
						Close
					</button>
				</div>
			`;

			$('body').append(panelHTML);

			// Bind close button event
			$('.wps-close-shortcuts').on('click', () => {
				this.toggleShortcutsPanel();
			});

			// Close on backdrop click
			$('.wps-shortcuts-backdrop').on('click', () => {
				this.toggleShortcutsPanel();
			});

			// Add help button to toolbar
			if ($('#wps-show-shortcuts').length === 0) {
				$('.wps-workflow-toolbar').append(`
					<button type="button" id="wps-show-shortcuts" class="wps-btn ghost" aria-label="Show keyboard shortcuts">
						<span class="dashicons dashicons-keyboard-hide" aria-hidden="true"></span>
						Shortcuts
					</button>
				`);

				$('#wps-show-shortcuts').on('click', () => {
					this.toggleShortcutsPanel();
				});
			}
		},

		/**
		 * Toggle keyboard shortcuts panel
		 */
		toggleShortcutsPanel: function() {
			$('.wps-shortcuts-panel, .wps-shortcuts-backdrop').toggleClass('active');
			
			if ($('.wps-shortcuts-panel').hasClass('active')) {
				$('.wps-shortcuts-panel').focus();
				this.announceToScreenReader('Keyboard shortcuts panel opened');
			} else {
				this.announceToScreenReader('Keyboard shortcuts panel closed');
			}
		}
	};

	// Initialize on document ready
	$(document).ready(function() {
		if ($('.wps-workflow-builder').length > 0) {
			WorkflowBuilder.init();
		}
	});

})(jQuery);
