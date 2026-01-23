<?php
/**
 * Visual Workflow Builder - Scratch-style block-based automation interface
 *
 * @package WPShadow
 * @subpackage Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'read' ) ) {
	wp_die( 'Insufficient permissions.' );
}

// Get available blocks
$triggers = \WPShadow\Workflow\Block_Registry::get_triggers();
$actions  = \WPShadow\Workflow\Block_Registry::get_actions();
?>

<div class="wrap wpshadow-workflow-builder">
	<h1>
		<span class="dashicons dashicons-block-default" style="margin-right: 8px;"></span>
		Visual Workflow Builder
	</h1>
	<p style="color: #666; font-size: 14px;">Build automation workflows using visual blocks. Create "if-then" rules like Scratch programming.</p>

	<div class="wpshadow-builder-container">
		<!-- Left Sidebar: Block Palette -->
		<div class="wpshadow-block-palette">
			<h3>Triggers (IF)</h3>
			<div class="wpshadow-block-list">
				<?php foreach ( $triggers as $id => $block ) : ?>
					<?php
					// Check if this is the current trigger when editing
					$is_current_trigger = false;
					if ( ! empty( $_GET['workflow'] ) && ! empty( $blocks ) ) {
						foreach ( $blocks as $block_item ) {
							if ( 'trigger' === $block_item['type'] && $id === $block_item['id'] ) {
								$is_current_trigger = true;
								break;
							}
						}
					}
					?>
					<div class="wpshadow-block-item <?php echo $is_current_trigger ? 'wpshadow-block-current' : ''; ?>" draggable="true" data-block-id="<?php echo esc_attr( $id ); ?>" data-block-type="trigger" style="background-color: <?php echo esc_attr( $block['color'] ); ?>; position: relative;">
						<?php if ( $is_current_trigger ) : ?>
							<span class="dashicons dashicons-yes wps-flex-items-center-justify-center-rounded"></span>
						<?php endif; ?>
						<span class="dashicons <?php echo esc_attr( $block['icon'] ); ?>" style="margin-right: 8px;"></span>
						<strong><?php echo esc_html( $block['label'] ); ?></strong>
						<small><?php echo esc_html( $block['description'] ); ?></small>
					</div>
				<?php endforeach; ?>
			</div>

			<h3 style="margin-top: 20px;">Actions (THEN)</h3>
			<div class="wpshadow-block-list">
				<?php foreach ( $actions as $id => $block ) : ?>
					<div class="wpshadow-block-item" draggable="true" data-block-id="<?php echo esc_attr( $id ); ?>" data-block-type="action" style="background-color: <?php echo esc_attr( $block['color'] ); ?>;">
						<span class="dashicons <?php echo esc_attr( $block['icon'] ); ?>" style="margin-right: 8px;"></span>
						<strong><?php echo esc_html( $block['label'] ); ?></strong>
						<small><?php echo esc_html( $block['description'] ); ?></small>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<!-- Center: Canvas -->
		<div class="wpshadow-canvas">
			<div class="wpshadow-workflow-title">
				<input type="text" id="wpshadow-workflow-name" placeholder="Workflow Name" value="" class="wps-p-8-rounded-4" />
			</div>

			<div class="wpshadow-canvas-area" id="wpshadow-canvas">
				<div class="wps-p-60">
					<span class="dashicons dashicons-block-default" style="font-size: 48px; opacity: 0.3;"></span>
					<p>Drag blocks here to build your workflow</p>
					<p style="font-size: 12px;">1. Start with a TRIGGER (IF condition)</p>
					<p style="font-size: 12px;">2. Add ACTIONS (THEN what to do)</p>
				</div>
			</div>

			<div class="wpshadow-workflow-actions">
				<button id="wpshadow-save-workflow" class="button button-primary">
					<span class="dashicons dashicons-cloud-saved" style="margin-right: 5px;"></span>
					Save Workflow
				</button>
				<button id="wpshadow-test-workflow" class="button">
					<span class="dashicons dashicons-media-play" style="margin-right: 5px;"></span>
					Test
				</button>
				<button id="wpshadow-clear-canvas" class="button button-secondary">
					<span class="dashicons dashicons-trash" style="margin-right: 5px;"></span>
					Clear
				</button>
			</div>
		</div>

		<!-- Right Sidebar: Block Inspector -->
		<div class="wpshadow-inspector">
			<h3>Block Settings</h3>
			<div id="wpshadow-inspector-content" class="wps-p-15-rounded-4">
				<p style="color: #999; text-align: center;">Select a block to configure</p>
			</div>
		</div>
	</div>

	<!-- Block Configuration Modal -->
	<div id="wpshadow-block-modal" class="wpshadow-modal wps-none">
		<div class="wpshadow-modal-content" style="max-width: 600px;">
			<button class="wpshadow-modal-close">&times;</button>
			<h2 id="wpshadow-block-modal-title">Configure Block</h2>
			<div id="wpshadow-block-config-form"></div>
			<div style="margin-top: 20px; text-align: right;">
				<button id="wpshadow-block-config-save" class="button button-primary">Save</button>
				<button class="button wpshadow-modal-close">Cancel</button>
			</div>
		</div>
	</div>
</div>

<style>
.wpshadow-workflow-builder {
	max-width: 1400px;
}

.wpshadow-builder-container {
	display: grid;
	grid-template-columns: 250px 1fr 300px;
	gap: 15px;
	margin-top: 20px;
	min-height: 600px;
}

.wpshadow-block-palette {
	background: #f9f9f9;
	border: 1px solid #ddd;
	border-radius: 8px;
	padding: 15px;
	overflow-y: auto;
	max-height: 800px;
}

.wpshadow-block-palette h3 {
	margin: 15px 0 10px 0;
	font-size: 14px;
	color: #333;
}

.wpshadow-block-list {
	display: flex;
	flex-direction: column;
	gap: 8px;
}

.wpshadow-block-item {
	padding: 12px;
	border-radius: 6px;
	cursor: grab;
	color: white;
	font-size: 13px;
	user-select: none;
	display: flex;
	align-items: center;
	gap: 8px;
	transition: transform 0.2s, box-shadow 0.2s;
}

.wpshadow-block-item:hover {
	transform: translateX(4px);
	box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.wpshadow-block-item.wpshadow-block-current {
	border: 2px solid #fff;
	box-shadow: 0 0 0 3px rgba(34, 113, 177, 0.3), inset 0 0 0 1px rgba(255, 255, 255, 0.3);
}

.wpshadow-block-item strong {
	display: block;
	font-weight: 600;
	line-height: 1.2;
}

.wpshadow-block-item small {
	display: block;
	opacity: 0.9;
	font-size: 11px;
	line-height: 1.2;
}

.wpshadow-canvas {
	background: white;
	border: 2px dashed #ddd;
	border-radius: 8px;
	padding: 20px;
	display: flex;
	flex-direction: column;
}

.wpshadow-workflow-title {
	margin-bottom: 20px;
}

.wpshadow-canvas-area {
	flex: 1;
	background: linear-gradient(135deg, #f0f0f0 0%, #fafafa 100%);
	border: 1px solid #e0e0e0;
	border-radius: 6px;
	min-height: 400px;
	padding: 20px;
	display: flex;
	align-items: center;
	justify-content: center;
	position: relative;
	margin-bottom: 15px;
}

.wpshadow-canvas-area.drag-over {
	background: #e3f2fd;
	border-color: #2196f3;
}

.wpshadow-workflow-actions {
	display: flex;
	gap: 10px;
}

.wpshadow-inspector {
	background: #f9f9f9;
	border: 1px solid #ddd;
	border-radius: 8px;
	padding: 15px;
	overflow-y: auto;
	max-height: 800px;
}

.wpshadow-inspector h3 {
	margin: 0 0 15px 0;
	font-size: 14px;
	color: #333;
}

.wpshadow-modal {
	display: none;
	position: fixed;
	z-index: 9999;
	left: 0;
	top: 0;
	width: 100%;
	height: 100%;
	background: rgba(0, 0, 0, 0.5);
	align-items: center;
	justify-content: center;
}

.wpshadow-modal-content {
	background: white;
	border-radius: 8px;
	padding: 30px;
	position: relative;
	box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
	max-height: 90vh;
	overflow-y: auto;
}

.wpshadow-modal-close {
	position: absolute;
	top: 15px;
	right: 15px;
	background: transparent;
	border: none;
	font-size: 28px;
	cursor: pointer;
	color: #999;
	line-height: 1;
	padding: 0;
}

.wpshadow-modal-close:hover {
	color: #333;
}

.wpshadow-canvas-block {
	background: white;
	border-left: 4px solid;
	border-radius: 6px;
	padding: 15px;
	margin-bottom: 10px;
	box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
	cursor: grab;
	position: relative;
	display: flex;
	align-items: center;
	justify-content: space-between;
	transition: box-shadow 0.2s;
}

.wpshadow-canvas-block:hover {
	box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.wpshadow-canvas-block.trigger {
	border-left-color: #3b82f6;
	background: #f0f8ff;
}

.wpshadow-canvas-block.action {
	border-left-color: #10b981;
	background: #f0fdf4;
}

.wpshadow-canvas-block-content {
	flex: 1;
	display: flex;
	align-items: center;
	gap: 10px;
}

.wpshadow-canvas-block-label {
	font-weight: 600;
	color: #333;
}

.wpshadow-canvas-block-config {
	font-size: 12px;
	color: #666;
	margin-top: 5px;
}

.wpshadow-canvas-block-actions {
	display: flex;
	gap: 8px;
}

.wpshadow-canvas-block-actions button {
	background: transparent;
	border: none;
	cursor: pointer;
	padding: 4px 8px;
	color: #999;
	transition: color 0.2s;
}

.wpshadow-canvas-block-actions button:hover {
	color: #333;
}

.wpshadow-canvas-block-actions .dashicons {
	font-size: 16px;
	width: 16px;
	height: 16px;
}

/* Form Styles */
.wpshadow-form-group {
	margin-bottom: 15px;
}

.wpshadow-form-group label {
	display: block;
	margin-bottom: 5px;
	font-weight: 600;
	color: #333;
	font-size: 14px;
}

.wpshadow-form-group input[type="text"],
.wpshadow-form-group input[type="email"],
.wpshadow-form-group input[type="number"],
.wpshadow-form-group input[type="time"],
.wpshadow-form-group select,
.wpshadow-form-group textarea {
	width: 100%;
	padding: 8px 12px;
	border: 1px solid #ddd;
	border-radius: 4px;
	font-family: inherit;
	font-size: 14px;
}

.wpshadow-form-group textarea {
	min-height: 80px;
	resize: vertical;
}

.wpshadow-form-group input[type="checkbox"] {
	margin-right: 8px;
}

.wpshadow-checkbox-group {
	display: flex;
	flex-wrap: wrap;
	gap: 10px;
	margin-top: 8px;
}

.wpshadow-checkbox-group label {
	display: flex;
	align-items: center;
	margin-bottom: 0;
	font-weight: normal;
}

@media (max-width: 1200px) {
	.wpshadow-builder-container {
		grid-template-columns: 1fr;
	}

	.wpshadow-block-palette,
	.wpshadow-inspector {
		max-height: 300px;
	}
}
</style>

<script>
jQuery(document).ready(function($) {
	let workflowBlocks = [];
	let selectedBlock = null;

	// Drag from palette
	$('.wpshadow-block-item').on('dragstart', function(e) {
		const blockId = $(this).data('block-id');
		const blockType = $(this).data('block-type');
		e.originalEvent.dataTransfer.setData('text/plain', JSON.stringify({
			blockId: blockId,
			blockType: blockType,
			isNew: true
		}));
	});

	// Drag over canvas
	$('#wpshadow-canvas').on('dragover', function(e) {
		e.preventDefault();
		$(this).addClass('drag-over');
	});

	$('#wpshadow-canvas').on('dragleave', function() {
		$(this).removeClass('drag-over');
	});

	// Drop on canvas
	$('#wpshadow-canvas').on('drop', function(e) {
		e.preventDefault();
		$(this).removeClass('drag-over');

		const data = JSON.parse(e.originalEvent.dataTransfer.getData('text/plain'));
		addBlockToCanvas(data.blockId, data.blockType, true);
	});

	function addBlockToCanvas(blockId, blockType, isNew = false) {
		const blockId_unique = 'block_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
		
		const blockData = {
			id: blockId_unique,
			type: blockType,
			blockId: blockId,
			config: {}
		};

		workflowBlocks.push(blockData);

		// Get block definition
		const allBlocks = {
			...<?php echo wp_json_encode( $triggers ); ?>,
			...<?php echo wp_json_encode( $actions ); ?>
		};

		const blockDef = allBlocks[blockId] || {};
		const borderColor = blockDef.color || '#999';

		const blockHTML = `
			<div class="wpshadow-canvas-block" data-block-id="${blockId_unique}" style="border-left-color: ${borderColor};">
				<div class="wpshadow-canvas-block-content">
					<span class="dashicons ${blockDef.icon || 'dashicons-block-default'}"></span>
					<div>
						<div class="wpshadow-canvas-block-label">${blockDef.label || 'Block'}</div>
						<div class="wpshadow-canvas-block-config">Click to configure</div>
					</div>
				</div>
				<div class="wpshadow-canvas-block-actions">
					<button class="wpshadow-block-config-btn" data-block-id="${blockId_unique}" title="Edit">
						<span class="dashicons dashicons-edit"></span>
					</button>
					<button class="wpshadow-block-delete-btn" data-block-id="${blockId_unique}" title="Delete">
						<span class="dashicons dashicons-trash"></span>
					</button>
				</div>
			</div>
		`;

		const $canvasArea = $('#wpshadow-canvas');
		if ($canvasArea.find('.wpshadow-canvas-block').length === 0) {
			$canvasArea.html(blockHTML);
		} else {
			$canvasArea.append(blockHTML);
		}

		// Clear empty state
		if ($canvasArea.find('div:contains("Drag blocks")').length > 0) {
			$canvasArea.find('div:contains("Drag blocks")').closest('div').remove();
		}

		bindBlockActions();
	}

	function bindBlockActions() {
		$('.wpshadow-block-config-btn').off('click').on('click', function() {
			const blockElementId = $(this).data('block-id');
			const block = workflowBlocks.find(b => b.id === blockElementId);
			if (block) {
				openBlockConfig(block);
			}
		});

		$('.wpshadow-block-delete-btn').off('click').on('click', function() {
			const blockElementId = $(this).data('block-id');
			workflowBlocks = workflowBlocks.filter(b => b.id !== blockElementId);
			$(`[data-block-id="${blockElementId}"]`).remove();
		});
	}

	function openBlockConfig(block) {
		const allBlocks = {
			...<?php echo wp_json_encode( $triggers ); ?>,
			...<?php echo wp_json_encode( $actions ); ?>
		};

		const blockDef = allBlocks[block.blockId] || {};
		
		$('#wpshadow-block-modal-title').text('Configure: ' + blockDef.label);
		$('#wpshadow-block-config-form').html(renderBlockForm(block, blockDef));
		
		$('#wpshadow-block-modal').css('display', 'flex');
		selectedBlock = block;
	}

	function renderBlockForm(block, blockDef) {
		let html = '';
		const fields = blockDef.fields || {};

		for (const fieldName in fields) {
			const field = fields[fieldName];
			const value = block.config[fieldName] || field.default || '';

			html += `<div class="wpshadow-form-group">`;
			html += `<label for="${fieldName}">${field.label}</label>`;

			if (field.type === 'text' || field.type === 'email') {
				html += `<input type="${field.type}" id="${fieldName}" name="${fieldName}" value="${value}" />`;
			} else if (field.type === 'number') {
				html += `<input type="number" id="${fieldName}" name="${fieldName}" value="${value}" />`;
			} else if (field.type === 'time') {
				html += `<input type="time" id="${fieldName}" name="${fieldName}" value="${value}" />`;
			} else if (field.type === 'textarea') {
				html += `<textarea id="${fieldName}" name="${fieldName}">${value}</textarea>`;
			} else if (field.type === 'select') {
				html += `<select id="${fieldName}" name="${fieldName}">`;
				for (const optValue in field.options) {
					const selected = optValue === value ? 'selected' : '';
					html += `<option value="${optValue}" ${selected}>${field.options[optValue]}</option>`;
				}
				html += `</select>`;
			} else if (field.type === 'checkbox_group') {
				html += `<div class="wpshadow-checkbox-group">`;
				for (const optValue in field.options) {
					const checked = (Array.isArray(value) && value.includes(optValue)) ? 'checked' : '';
					html += `<label><input type="checkbox" name="${fieldName}" value="${optValue}" ${checked} /> ${field.options[optValue]}</label>`;
				}
				html += `</div>`;
			} else if (field.type === 'checkbox') {
				const checked = value ? 'checked' : '';
				html += `<input type="checkbox" id="${fieldName}" name="${fieldName}" ${checked} />`;
			}

			html += `</div>`;
		}

		return html;
	}

	$('#wpshadow-block-config-save').on('click', function() {
		if (!selectedBlock) return;

		// Collect form data
		$('#wpshadow-block-config-form').find('input, select, textarea').each(function() {
			const $elem = $(this);
			const name = $elem.attr('name');
			
			if ($elem.attr('type') === 'checkbox') {
				selectedBlock.config[name] = $elem.prop('checked');
			} else {
				selectedBlock.config[name] = $elem.val();
			}
		});

		$('#wpshadow-block-modal').css('display', 'none');
		
		// Update UI
		console.log('Block config saved', selectedBlock);
	});

	$('#wpshadow-clear-canvas').on('click', function() {
		if (confirm('Clear all blocks?')) {
			workflowBlocks = [];
			$('#wpshadow-canvas').html(`
				<div class="wps-p-60">
					<span class="dashicons dashicons-block-default" style="font-size: 48px; opacity: 0.3;"></span>
					<p>Drag blocks here to build your workflow</p>
				</div>
			`);
		}
	});

	$('#wpshadow-save-workflow').on('click', function() {
		const name = $('#wpshadow-workflow-name').val() || 'Untitled Workflow';
		
		if (workflowBlocks.length === 0) {
			alert('Add blocks to your workflow first');
			return;
		}

		console.log('Saving workflow:', {
			name: name,
			blocks: workflowBlocks
		});

		alert('Workflow saved (demo)');
	});

	$('#wpshadow-test-workflow').on('click', function() {
		if (workflowBlocks.length === 0) {
			alert('Add blocks to test');
			return;
		}

		console.log('Testing workflow:', workflowBlocks);
		alert('Workflow test started (demo)');
	});

	// Modal close
	$('.wpshadow-modal-close').on('click', function() {
		$(this).closest('.wpshadow-modal').css('display', 'none');
	});

	$('.wpshadow-modal').on('click', function(e) {
		if (e.target === this) {
			$(this).css('display', 'none');
		}
	});
});
</script>

<?php
