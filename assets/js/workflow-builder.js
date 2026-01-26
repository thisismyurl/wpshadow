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
		canvasZoom: 1,
		canvasPosition: { x: 0, y: 0 },
		isPanning: false,
		lastPanPosition: { x: 0, y: 0 },

		/**
		 * Initialize the workflow builder
		 */
		init: function() {
			this.setupCanvas();
			this.bindEvents();
			this.setupAccessibility();
			this.addCanvasControls();
			this.addSearchFilter();
			this.announceToScreenReader('Workflow builder loaded');
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
		 * Add canvas controls (zoom, pan, reset)
		 */
		addCanvasControls: function() {
			const controlsHTML = `
				<div class="wps-canvas-controls" role="toolbar" aria-label="Canvas controls">
					<button id="wps-zoom-in" class="wps-zoom-btn" aria-label="Zoom in" title="Zoom in">
						<span class="dashicons dashicons-plus-alt2"></span>
					</button>
					<span class="wps-zoom-level" role="status" aria-live="polite">100%</span>
					<button id="wps-zoom-out" class="wps-zoom-btn" aria-label="Zoom out" title="Zoom out">
						<span class="dashicons dashicons-minus"></span>
					</button>
					<button id="wps-zoom-reset" class="wps-zoom-btn" aria-label="Reset zoom" title="Reset zoom">
						<span class="dashicons dashicons-image-rotate"></span>
					</button>
				</div>
			`;
			$('.wps-workflow-canvas').append(controlsHTML);

			// Bind zoom controls
			$('#wps-zoom-in').on('click', () => this.zoomCanvas(0.1));
			$('#wps-zoom-out').on('click', () => this.zoomCanvas(-0.1));
			$('#wps-zoom-reset').on('click', () => this.resetCanvas());

			// Mouse wheel zoom
			$('.wps-workflow-canvas').on('wheel', (e) => {
				if (e.ctrlKey || e.metaKey) {
					e.preventDefault();
					const delta = e.originalEvent.deltaY > 0 ? -0.05 : 0.05;
					this.zoomCanvas(delta);
				}
			});

			// Pan controls
			let isPanning = false;
			let startX = 0;
			let startY = 0;

			$('.wps-canvas-viewport').on('mousedown', (e) => {
				if (e.which === 2 || (e.which === 1 && e.shiftKey)) { // Middle click or Shift+click
					isPanning = true;
					startX = e.clientX - this.canvasPosition.x;
					startY = e.clientY - this.canvasPosition.y;
					$('.wps-canvas-content').addClass('panning');
					e.preventDefault();
				}
			});

			$(document).on('mousemove', (e) => {
				if (isPanning) {
					this.canvasPosition.x = e.clientX - startX;
					this.canvasPosition.y = e.clientY - startY;
					this.updateCanvasTransform();
				}
			});

			$(document).on('mouseup', () => {
				if (isPanning) {
					isPanning = false;
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
				} else {
					// Update connections after block removal
					WorkflowBuilder.updateConnections();
				}
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

			// Remove any existing config panel
			$('.wps-block-config-panel').remove();

			// Build config panel HTML
			const configHTML = this.buildConfigPanel(block, blockDef);
			$('body').append(configHTML);

			// Show panel with animation
			setTimeout(() => $('.wps-block-config-panel').addClass('active'), 10);

			// Bind config events
			this.bindConfigEvents(blockId);

			this.announceToScreenReader('Configuration panel opened for ' + blockDef.label);
		},

		/**
		 * Build configuration panel HTML
		 */
		buildConfigPanel: function(block, blockDef) {
			const fields = blockDef.fields || {};
			let fieldsHTML = '';

			Object.keys(fields).forEach(fieldKey => {
				const field = fields[fieldKey];
				const value = block.config[fieldKey] || field.default || '';

				fieldsHTML += `<div class="wps-config-field">`;
				fieldsHTML += `<label for="config_${fieldKey}">${field.label}</label>`;

				switch (field.type) {
					case 'select':
						fieldsHTML += `<select id="config_${fieldKey}" name="${fieldKey}">`;
						Object.keys(field.options || {}).forEach(optKey => {
							const selected = value === optKey ? 'selected' : '';
							fieldsHTML += `<option value="${optKey}" ${selected}>${field.options[optKey]}</option>`;
						});
						fieldsHTML += `</select>`;
						break;

					case 'textarea':
						fieldsHTML += `<textarea id="config_${fieldKey}" name="${fieldKey}" rows="4">${value}</textarea>`;
						break;

					case 'checkbox_group':
						Object.keys(field.options || {}).forEach(optKey => {
							const checked = (value || []).includes(optKey) ? 'checked' : '';
							fieldsHTML += `
								<div>
									<label>
										<input type="checkbox" name="${fieldKey}[]" value="${optKey}" ${checked}>
										${field.options[optKey]}
									</label>
								</div>
							`;
						});
						break;

					case 'number':
						fieldsHTML += `<input type="number" id="config_${fieldKey}" name="${fieldKey}" value="${value}">`;
						break;

					case 'time':
						fieldsHTML += `<input type="time" id="config_${fieldKey}" name="${fieldKey}" value="${value}">`;
						break;

					default:
						fieldsHTML += `<input type="text" id="config_${fieldKey}" name="${fieldKey}" value="${value}">`;
				}

				fieldsHTML += `</div>`;
			});

			return `
				<div class="wps-block-config-panel" role="dialog" aria-labelledby="config-panel-title">
					<h4 id="config-panel-title">Configure: ${blockDef.label}</h4>
					<form id="wps-config-form" data-block-id="${block.id}">
						${fieldsHTML}
						<div style="margin-top: 1.5rem; display: flex; gap: 0.5rem;">
							<button type="submit" class="wps-btn primary">Save Configuration</button>
							<button type="button" class="wps-btn ghost wps-close-config">Cancel</button>
						</div>
					</form>
				</div>
			`;
		},

		/**
		 * Bind configuration panel events
		 */
		bindConfigEvents: function(blockId) {
			// Save config
			$('#wps-config-form').on('submit', (e) => {
				e.preventDefault();
				this.saveBlockConfig(blockId);
			});

			// Close panel
			$('.wps-close-config').on('click', () => {
				$('.wps-block-config-panel').removeClass('active');
				setTimeout(() => $('.wps-block-config-panel').remove(), 300);
				$('.wps-block').removeClass('selected');
				this.selectedBlock = null;
			});

			// Close on Escape
			$(document).on('keydown.config', (e) => {
				if (e.key === 'Escape') {
					$('.wps-close-config').trigger('click');
				}
			});
		},

		/**
		 * Save block configuration
		 */
		saveBlockConfig: function(blockId) {
			const block = this.blocks.find(b => b.id === blockId);
			if (!block) return;

			const $form = $('#wps-config-form');
			const config = {};

			// Collect form data
			$form.find('input, select, textarea').each(function() {
				const $field = $(this);
				const name = $field.attr('name');
				
				if ($field.attr('type') === 'checkbox') {
					if (!config[name.replace('[]', '')]) {
						config[name.replace('[]', '')] = [];
					}
					if ($field.is(':checked')) {
						config[name.replace('[]', '')].push($field.val());
					}
				} else {
					config[name] = $field.val();
				}
			});

			// Update block config
			block.config = config;

			// Update block display
			this.updateBlockDisplay(blockId);

			// Close panel
			$('.wps-close-config').trigger('click');

			this.announceToScreenReader(wpshadowWorkflow.strings.configSaved);
			this.showNotification('success', wpshadowWorkflow.strings.configSaved);
		},

		/**
		 * Update block display after configuration
		 */
		updateBlockDisplay: function(blockId) {
			const block = this.blocks.find(b => b.id === blockId);
			if (!block) return;

			const $block = $(`[data-block-id="${blockId}"]`);
			const allBlocks = { ...wpshadowWorkflow.triggers, ...wpshadowWorkflow.actions };
			const blockDef = allBlocks[block.blockId] || {};

			// Build config summary
			let configText = blockDef.description;
			if (Object.keys(block.config).length > 0) {
				configText = 'Configured: ';
				const configParts = [];
				Object.keys(block.config).forEach(key => {
					const value = block.config[key];
					if (Array.isArray(value)) {
						configParts.push(`${key}=${value.join(', ')}`);
					} else {
						configParts.push(`${key}=${value}`);
					}
				});
				configText += configParts.join(', ');
			}

			$block.find('.wps-block-config').html(`<p>${configText}</p>`);
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
