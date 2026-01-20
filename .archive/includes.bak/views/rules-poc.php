<?php
/**
 * Rules PoC view.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $rules_state ) || ! is_array( $rules_state ) ) {
	$rules_state = wpshadow_rules_get_state();
}

$draft_blocks     = $rules_state['draft']['blocks'] ?? array();
$published_blocks = $rules_state['published']['blocks'] ?? array();
$history_count    = isset( $rules_state['history'] ) && is_array( $rules_state['history'] ) ? count( $rules_state['history'] ) : 0;

$trigger_library = array(
	array(
		'id'          => 'condition_any_page',
		'type'        => 'condition',
		'label'       => __( 'When any page loads', 'wpshadow' ),
		'description' => __( 'Scope: whole site', 'wpshadow' ),
		'tag'         => __( 'Condition', 'wpshadow' ),
	),
	array(
		'id'          => 'condition_logged_in',
		'type'        => 'condition',
		'label'       => __( 'When logged-in users', 'wpshadow' ),
		'description' => __( 'Scope: authenticated traffic', 'wpshadow' ),
		'tag'         => __( 'Condition', 'wpshadow' ),
	),
	array(
		'id'          => 'time_sunday',
		'type'        => 'time',
		'label'       => __( 'When Sunday 2:00 AM', 'wpshadow' ),
		'description' => __( 'Quiet hours window', 'wpshadow' ),
		'tag'         => __( 'Time', 'wpshadow' ),
	),
	array(
		'id'          => 'time_daily_three_am',
		'type'        => 'time',
		'label'       => __( 'When daily 3:00 AM', 'wpshadow' ),
		'description' => __( 'Daily low-traffic slot', 'wpshadow' ),
		'tag'         => __( 'Time', 'wpshadow' ),
	),
);

$action_library = array(
	array(
		'id'          => 'action_strip_css',
		'type'        => 'action',
		'label'       => __( 'Strip excess CSS', 'wpshadow' ),
		'description' => __( 'Remove unused inline styles', 'wpshadow' ),
		'tag'         => __( 'Performance', 'wpshadow' ),
	),
	array(
		'id'          => 'action_strip_emojis',
		'type'        => 'action',
		'label'       => __( 'Strip emojis', 'wpshadow' ),
		'description' => __( 'Remove emoji scripts/styles', 'wpshadow' ),
		'tag'         => __( 'Performance', 'wpshadow' ),
	),
	array(
		'id'          => 'action_cache_page',
		'type'        => 'action',
		'label'       => __( 'Cache page', 'wpshadow' ),
		'description' => __( 'Store rendered output', 'wpshadow' ),
		'tag'         => __( 'Speed', 'wpshadow' ),
	),
	array(
		'id'          => 'action_email_report',
		'type'        => 'action',
		'label'       => __( 'Email site health report', 'wpshadow' ),
		'description' => __( 'Send PDF snapshot to admins', 'wpshadow' ),
		'tag'         => __( 'Reporting', 'wpshadow' ),
	),
	array(
		'id'          => 'action_cache_warm',
		'type'        => 'action',
		'label'       => __( 'Run cache warmup', 'wpshadow' ),
		'description' => __( 'Pre-build cache during off-peak', 'wpshadow' ),
		'tag'         => __( 'Performance', 'wpshadow' ),
	),
);

$draft_updated    = $rules_state['draft']['updated_at'] ?? '';
$published_updated = $rules_state['published']['updated_at'] ?? '';
?>
<div class="wrap wpshadow-rules-poc">
	<h1><?php esc_html_e( 'Rules (Beta)', 'wpshadow' ); ?></h1>
	<p class="description">
		<?php esc_html_e( 'Drag, edit, and save draft/published rule flows. Rollback stubbed for now.', 'wpshadow' ); ?>
	</p>

	<style>
		.wpshadow-rules-grid { display: grid; grid-template-columns: 280px 1fr; gap: 18px; align-items: start; margin-top: 16px; }
		.wpshadow-rules-panel { background: #fff; border: 1px solid #d8dde3; border-radius: 6px; padding: 12px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
		.wpshadow-rules-canvas { background: #f7f9fb; border: 1px solid #d8dde3; border-radius: 6px; padding: 14px; min-height: 360px; box-shadow: inset 0 0 0 1px #eef2f5; position: relative; }
		.wpshadow-rules-canvas.empty { display: grid; place-items: center; color: #667085; font-size: 13px; text-align: center; }
		.wpshadow-block { border-radius: 10px; padding: 10px 12px; color: #fff; margin-bottom: 10px; position: relative; box-shadow: 0 2px 4px rgba(0,0,0,0.08); cursor: grab; }
		.wpshadow-block.condition { background: linear-gradient(135deg, #3b82f6, #2563eb); }
		.wpshadow-block.action { background: linear-gradient(135deg, #10b981, #059669); }
		.wpshadow-block.time { background: linear-gradient(135deg, #6366f1, #4f46e5); }
		.wpshadow-block .label { font-weight: 700; display: block; font-size: 14px; }
		.wpshadow-block .desc { font-size: 12px; opacity: 0.95; margin-top: 3px; }
		.wpshadow-block .chip { display: inline-block; background: rgba(255,255,255,0.16); border-radius: 999px; padding: 2px 8px; font-size: 11px; margin-top: 6px; }
		.wpshadow-legend { font-size: 12px; color: #555; margin: 0 0 6px; }
		.wpshadow-section { margin-top: 28px; }
		.wpshadow-dropzone { border: 1px dashed #cbd5e1; border-radius: 8px; padding: 12px; margin-bottom: 10px; background: #f8fafc; color: #94a3b8; text-align: center; font-size: 12px; transition: background 0.15s ease, border-color 0.15s ease; }
		.wpshadow-dropzone.is-hover { border-color: #2563eb; background: #e0ebff; color: #1d4ed8; }
		.wpshadow-block-controls { display: flex; gap: 8px; margin-top: 8px; flex-wrap: wrap; }
		.wpshadow-block-controls button { background: rgba(255,255,255,0.15); color: #fff; border: none; border-radius: 6px; padding: 5px 8px; font-size: 11px; cursor: pointer; }
		.wpshadow-block-controls button:hover { background: rgba(255,255,255,0.22); }
		.wpshadow-edit-panel { background: #fff; color: #0f172a; border-radius: 8px; padding: 10px; margin-top: 8px; border: 1px solid #d8dde3; }
		.wpshadow-edit-panel label { display: block; font-size: 12px; font-weight: 600; margin-top: 6px; }
		.wpshadow-edit-panel input, .wpshadow-edit-panel textarea, .wpshadow-edit-panel select { width: 100%; font-size: 12px; margin-top: 3px; border-radius: 4px; border: 1px solid #cbd5e1; padding: 6px; }
		.wpshadow-edit-panel textarea { min-height: 60px; }
		.wpshadow-rules-status { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 10px; font-size: 12px; color: #334155; }
		.wpshadow-status-chip { background: #f1f5f9; border: 1px solid #d8dde3; border-radius: 999px; padding: 6px 10px; }
		.wpshadow-actions-row { display: flex; gap: 10px; align-items: center; margin-top: 16px; }
		.wpshadow-actions-row button { border-radius: 6px; border: 1px solid #0f172a; padding: 8px 12px; font-weight: 600; cursor: pointer; }
		.wpshadow-actions-row .primary { background: #0f172a; color: #fff; }
		.wpshadow-actions-row .secondary { background: #fff; color: #0f172a; }
		.wpshadow-actions-row .ghost { background: #f8fafc; color: #475569; border-color: #cbd5e1; cursor: not-allowed; }
		.wpshadow-rules-panel .wpshadow-block { cursor: pointer; }
		.wpshadow-library-item { margin-bottom: 10px; }
		.wpshadow-library-item button { margin-top: 6px; }
		.wpshadow-empty-library { font-size: 12px; color: #475569; }
		.wpshadow-validation { margin-top: 10px; padding: 10px 12px; border-radius: 6px; border: 1px solid #e2e8f0; background: #f8fafc; color: #334155; font-size: 12px; }
		.wpshadow-validation.error { border-color: #fecaca; background: #fff1f2; color: #991b1b; }
		.wpshadow-validation.success { border-color: #bbf7d0; background: #f0fdf4; color: #14532d; }
		.wpshadow-actions-row button[disabled] { opacity: 0.6; cursor: not-allowed; }
	</style>

	<?php $canvas_data = array(
		'triggers'       => $trigger_library,
		'actions'        => $action_library,
		'draftBlocks'    => $draft_blocks,
		'publishedAt'    => $published_updated,
		'draftUpdatedAt' => $draft_updated,
		'historyCount'   => $history_count,
		'historyList'    => isset( $rules_state['history'] ) && is_array( $rules_state['history'] ) ? $rules_state['history'] : array(),
	); ?>

	<form method="post" id="wpshadow-rules-form">
		<?php wp_nonce_field( 'wpshadow_rules_save', 'wpshadow_rules_nonce' ); ?>
		<input type="hidden" name="wpshadow_rules_mode" id="wpshadow_rules_mode" value="draft" />
		<input type="hidden" name="wpshadow_rules_json" id="wpshadow_rules_json" value="<?php echo esc_attr( wp_json_encode( $draft_blocks ) ); ?>" />
		<input type="hidden" name="wpshadow_rules_rollback_index" id="wpshadow_rules_rollback_index" value="" />

		<div class="wpshadow-rules-status">
			<span class="wpshadow-status-chip"><?php esc_html_e( 'Draft is editable and stored in this screen.', 'wpshadow' ); ?></span>
			<span class="wpshadow-status-chip"><?php esc_html_e( 'Publish locks in a version and records minimal history (stub for rollback).', 'wpshadow' ); ?></span>
		</div>

		<div class="wpshadow-rules-grid">
			<div class="wpshadow-rules-panel">
				<p class="wpshadow-legend" style="margin-bottom:10px;">
					<?php esc_html_e( 'Templates', 'wpshadow' ); ?>
				</p>
				<div class="wpshadow-library" id="wpshadow-templates" aria-label="Template presets">
					<div class="wpshadow-library-item">
						<button type="button" class="button" id="wpshadow-template-cache-warmup"><?php esc_html_e( 'Daily Cache Warmup', 'wpshadow' ); ?></button>
					</div>
					<div class="wpshadow-library-item">
						<button type="button" class="button" id="wpshadow-template-health-email"><?php esc_html_e( 'Weekly Health Email', 'wpshadow' ); ?></button>
					</div>
				</div>
				<p class="wpshadow-legend"><?php esc_html_e( 'Triggers (conditions & schedules)', 'wpshadow' ); ?></p>
				<div class="wpshadow-library" id="wpshadow-triggers" aria-label="Trigger block library">
					<?php foreach ( $trigger_library as $block ) : ?>
						<div class="wpshadow-library-item">
							<div class="wpshadow-block <?php echo esc_attr( $block['type'] ); ?>" draggable="true" data-block="<?php echo esc_attr( wp_json_encode( $block ) ); ?>">
								<span class="label"><?php echo esc_html( $block['label'] ); ?></span>
								<span class="desc"><?php echo esc_html( $block['description'] ); ?></span>
								<span class="chip"><?php echo esc_html( $block['tag'] ); ?></span>
							</div>
							<button type="button" class="button button-small" data-add-block='<?php echo esc_attr( wp_json_encode( $block ) ); ?>'><?php esc_html_e( 'Add to canvas', 'wpshadow' ); ?></button>
						</div>
					<?php endforeach; ?>
					<?php if ( empty( $trigger_library ) ) : ?>
						<p class="wpshadow-empty-library"><?php esc_html_e( 'No trigger blocks available yet.', 'wpshadow' ); ?></p>
					<?php endif; ?>
				</div>

				<p class="wpshadow-legend" style="margin-top:14px;">
					<?php esc_html_e( 'Actions', 'wpshadow' ); ?>
				</p>
				<div class="wpshadow-library" id="wpshadow-actions" aria-label="Action block library">
					<?php foreach ( $action_library as $block ) : ?>
						<div class="wpshadow-library-item">
							<div class="wpshadow-block <?php echo esc_attr( $block['type'] ); ?>" draggable="true" data-block="<?php echo esc_attr( wp_json_encode( $block ) ); ?>">
								<span class="label"><?php echo esc_html( $block['label'] ); ?></span>
								<span class="desc"><?php echo esc_html( $block['description'] ); ?></span>
								<span class="chip"><?php echo esc_html( $block['tag'] ); ?></span>
							</div>
							<button type="button" class="button button-small" data-add-block='<?php echo esc_attr( wp_json_encode( $block ) ); ?>'><?php esc_html_e( 'Add to canvas', 'wpshadow' ); ?></button>
						</div>
					<?php endforeach; ?>
					<?php if ( empty( $action_library ) ) : ?>
						<p class="wpshadow-empty-library"><?php esc_html_e( 'No action blocks available yet.', 'wpshadow' ); ?></p>
					<?php endif; ?>
				</div>
			</div>

			<div class="wpshadow-rules-canvas" id="wpshadow-canvas" aria-label="Rule flow canvas"></div>
		</div>

		<div id="wpshadow-validation" class="wpshadow-validation" aria-live="polite"></div>

		<?php if ( $history_count > 0 ) : ?>
		<div class="wpshadow-section">
			<h2><?php esc_html_e( 'Rollback History', 'wpshadow' ); ?></h2>
			<p class="wpshadow-legend"><?php esc_html_e( 'Select a snapshot to restore on rollback.', 'wpshadow' ); ?></p>
			<div id="wpshadow-history-list" aria-label="Rollback snapshots">
				<?php foreach ( (array) $rules_state['history'] as $idx => $snapshot ) : ?>
					<?php
						$label = isset( $snapshot['updated_at'] ) && $snapshot['updated_at'] ? $snapshot['updated_at'] : __( 'Unknown date', 'wpshadow' );
						$count = isset( $snapshot['blocks'] ) && is_array( $snapshot['blocks'] ) ? count( $snapshot['blocks'] ) : 0;
					?>
					<div class="wpshadow-library-item" style="display:flex; align-items:center; gap:10px;">
						<span><?php echo esc_html( sprintf( __( 'Snapshot %d — %s (%d blocks)', 'wpshadow' ), (int) $idx + 1, $label, (int) $count ) ); ?></span>
						<button type="button" class="button button-small" data-rollback-index="<?php echo esc_attr( (string) $idx ); ?>"><?php esc_html_e( 'Select', 'wpshadow' ); ?></button>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php endif; ?>

		<?php $rollback_disabled = ( $history_count < 1 ); ?>
		<div class="wpshadow-actions-row">
			<button type="submit" id="wpshadow-btn-draft" class="secondary" data-save-mode="draft"><?php esc_html_e( 'Save Draft', 'wpshadow' ); ?></button>
			<button type="submit" id="wpshadow-btn-publish" class="primary" data-save-mode="publish"><?php esc_html_e( 'Publish', 'wpshadow' ); ?></button>
			<button type="submit" id="wpshadow-btn-rollback" class="ghost" data-save-mode="rollback" <?php disabled( $rollback_disabled ); ?> title="<?php esc_attr_e( 'Restore last published snapshot', 'wpshadow' ); ?>"><?php esc_html_e( 'Rollback', 'wpshadow' ); ?></button>
			<button type="button" id="wpshadow-btn-run" class="secondary" title="<?php esc_attr_e( 'Run evaluator stub with published rules', 'wpshadow' ); ?>"><?php esc_html_e( 'Run Now', 'wpshadow' ); ?></button>
			<button type="button" id="wpshadow-btn-undo" class="secondary" title="<?php esc_attr_e( 'Undo last change', 'wpshadow' ); ?>"><?php esc_html_e( 'Undo', 'wpshadow' ); ?></button>
			<button type="button" id="wpshadow-btn-reset" class="secondary" title="<?php esc_attr_e( 'Clear the canvas', 'wpshadow' ); ?>"><?php esc_html_e( 'Reset', 'wpshadow' ); ?></button>
			<button type="button" id="wpshadow-btn-export" class="secondary" title="<?php esc_attr_e( 'Copy JSON to clipboard', 'wpshadow' ); ?>"><?php esc_html_e( 'Copy JSON', 'wpshadow' ); ?></button>
			<button type="button" id="wpshadow-btn-import" class="secondary" title="<?php esc_attr_e( 'Load JSON from prompt', 'wpshadow' ); ?>"><?php esc_html_e( 'Load JSON', 'wpshadow' ); ?></button>
		</div>
	</form>

	<div class="wpshadow-section">
		<h2><?php esc_html_e( 'Version state', 'wpshadow' ); ?></h2>
		<ul class="wpshadow-legend">
			<li><?php echo esc_html( sprintf( __( 'Draft last saved: %s', 'wpshadow' ), $draft_updated ? $draft_updated : __( 'Not saved yet', 'wpshadow' ) ) ); ?></li>
			<li><?php echo esc_html( sprintf( __( 'Published last saved: %s', 'wpshadow' ), $published_updated ? $published_updated : __( 'Not published yet', 'wpshadow' ) ) ); ?></li>
			<li><?php echo esc_html( sprintf( __( 'Rollback history slots filled: %d', 'wpshadow' ), (int) $history_count ) ); ?></li>
		</ul>
	</div>

	<script>
	(function() {
		const data = <?php echo wp_json_encode( $canvas_data ); ?>;
		const canvas = document.getElementById('wpshadow-canvas');
		const libraries = Array.from(document.querySelectorAll('.wpshadow-library'));
		const form = document.getElementById('wpshadow-rules-form');
		const jsonField = document.getElementById('wpshadow_rules_json');
		const modeField = document.getElementById('wpshadow_rules_mode');
		const rollbackIndexField = document.getElementById('wpshadow_rules_rollback_index');
		const validationBox = document.getElementById('wpshadow-validation');
		const btnPublish = document.getElementById('wpshadow-btn-publish');
		const btnDraft = document.getElementById('wpshadow-btn-draft');
		const btnRollback = document.getElementById('wpshadow-btn-rollback');
		const btnUndo = document.getElementById('wpshadow-btn-undo');
		const btnReset = document.getElementById('wpshadow-btn-reset');
		const btnExport = document.getElementById('wpshadow-btn-export');
		const btnImport = document.getElementById('wpshadow-btn-import');
		const btnRun = document.getElementById('wpshadow-btn-run');
		const btnTemplateWarmup = document.getElementById('wpshadow-template-cache-warmup');
		const btnTemplateHealth = document.getElementById('wpshadow-template-health-email');
		const historyListEl = document.getElementById('wpshadow-history-list');
		const allowedTypes = ['condition', 'action', 'time'];
		let dragPayload = null;
		let canvasState = (data && Array.isArray(data.draftBlocks)) ? cloneBlocks(data.draftBlocks) : [];
		let lastState = null;
		const historyList = Array.isArray(data.historyList) ? data.historyList : [];

		function safeParse(dataString) {
			try {
				return JSON.parse(dataString);
			} catch (error) {
				return null;
			}
		}

		function cloneBlocks(blocks) {
			return blocks.map(function(block) {
				return {
					id: block.id || 'block_' + Date.now() + Math.random(),
					type: allowedTypes.includes(block.type) ? block.type : 'action',
					label: block.label || '',
					description: block.description || '',
					tag: block.tag || ''
				};
			});
		}

		function updateSerialized() {
			jsonField.value = JSON.stringify(canvasState);
		}

		function validateCanvasState() {
			let hasTrigger = false;
			let hasAction = false;
			let messages = [];
			if (canvasState.length === 0) {
				messages.push('<?php echo esc_js( __( 'Add at least one block.', 'wpshadow' ) ); ?>');
			}
			canvasState.forEach(function(b, idx) {
				if (['condition','time'].includes(b.type)) hasTrigger = true;
				if (b.type === 'action') hasAction = true;
			});
			const firstType = canvasState[0] ? canvasState[0].type : '';
			if (canvasState.length && !['condition','time'].includes(firstType)) {
				messages.push('<?php echo esc_js( __( 'Start your flow with a trigger (condition/time).', 'wpshadow' ) ); ?>');
			}
			if (!hasTrigger || !hasAction) {
				messages.push('<?php echo esc_js( __( 'Include at least one trigger and one action.', 'wpshadow' ) ); ?>');
			}
			return {
				valid: messages.length === 0,
				messages
			};
		}

		function renderValidation() {
			const result = validateCanvasState();
			validationBox.innerHTML = '';
			validationBox.className = 'wpshadow-validation';
			if (!result.valid) {
				validationBox.classList.add('error');
				const list = document.createElement('ul');
				result.messages.forEach(function(msg) {
					const li = document.createElement('li');
					li.textContent = msg;
					list.appendChild(li);
				});
				validationBox.appendChild(list);
				btnPublish.setAttribute('disabled', 'disabled');
			} else {
				validationBox.classList.add('success');
				validationBox.textContent = '<?php echo esc_js( __( 'Flow looks good. You can publish.', 'wpshadow' ) ); ?>';
				btnPublish.removeAttribute('disabled');
			}
		}

		function renderCanvas() {
			canvas.innerHTML = '';
			if (!canvasState.length) {
				canvas.classList.add('empty');
				canvas.textContent = '<?php echo esc_js( __( 'Drop blocks here to build your rule', 'wpshadow' ) ); ?>';
				updateSerialized();
				renderValidation();
				return;
			}

			canvas.classList.remove('empty');
			canvasState.forEach(function(block, index) {
				canvas.appendChild(createDropzone(index));
				canvas.appendChild(createBlockEl(block, index));
			});
			canvas.appendChild(createDropzone(canvasState.length));
			updateSerialized();
			renderValidation();
		}

		function createDropzone(index) {
			const zone = document.createElement('div');
			zone.className = 'wpshadow-dropzone';
			zone.textContent = '<?php echo esc_js( __( 'Drop here', 'wpshadow' ) ); ?>';
			zone.dataset.index = index;
			zone.addEventListener('dragover', function(event) {
				event.preventDefault();
				zone.classList.add('is-hover');
			});
			zone.addEventListener('dragleave', function() {
				zone.classList.remove('is-hover');
			});
			zone.addEventListener('drop', function(event) {
				event.preventDefault();
				zone.classList.remove('is-hover');
				const dropIndex = parseInt(zone.dataset.index, 10) || 0;
				handleDrop(dropIndex);
			});
			return zone;
		}

		function createBlockEl(block, index) {
			const wrapper = document.createElement('div');
			wrapper.className = 'wpshadow-block ' + block.type;
			wrapper.setAttribute('draggable', 'true');
			wrapper.dataset.index = index;

			const label = document.createElement('span');
			label.className = 'label';
			label.textContent = block.label || '<?php echo esc_js( __( 'Untitled block', 'wpshadow' ) ); ?>';
			wrapper.appendChild(label);

			if (block.description) {
				const desc = document.createElement('span');
				desc.className = 'desc';
				desc.textContent = block.description;
				wrapper.appendChild(desc);
			}

			if (block.tag) {
				const chip = document.createElement('span');
				chip.className = 'chip';
				chip.textContent = block.tag;
				wrapper.appendChild(chip);
			}

			const controls = document.createElement('div');
			controls.className = 'wpshadow-block-controls';

			const editBtn = document.createElement('button');
			editBtn.type = 'button';
			editBtn.textContent = '<?php echo esc_js( __( 'Edit', 'wpshadow' ) ); ?>';
			editBtn.addEventListener('click', function() {
				toggleEdit(wrapper, block, index);
			});

			const dupBtn = document.createElement('button');
			dupBtn.type = 'button';
			dupBtn.textContent = '<?php echo esc_js( __( 'Duplicate', 'wpshadow' ) ); ?>';
			dupBtn.addEventListener('click', function() {
				lastState = cloneBlocks(canvasState);
				const clone = cloneBlocks([block])[0];
				canvasState.splice(index+1, 0, clone);
				renderCanvas();
			});

			const upBtn = document.createElement('button');
			upBtn.type = 'button';
			upBtn.textContent = '<?php echo esc_js( __( 'Move Up', 'wpshadow' ) ); ?>';
			upBtn.addEventListener('click', function() {
				if (index > 0) {
					lastState = cloneBlocks(canvasState);
					const moved = canvasState.splice(index, 1)[0];
					canvasState.splice(index-1, 0, moved);
					renderCanvas();
				}
			});

			const downBtn = document.createElement('button');
			downBtn.type = 'button';
			downBtn.textContent = '<?php echo esc_js( __( 'Move Down', 'wpshadow' ) ); ?>';
			downBtn.addEventListener('click', function() {
				if (index < canvasState.length - 1) {
					lastState = cloneBlocks(canvasState);
					const moved = canvasState.splice(index, 1)[0];
					canvasState.splice(index+1, 0, moved);
					renderCanvas();
				}
			});

			const removeBtn = document.createElement('button');
			removeBtn.type = 'button';
			removeBtn.textContent = '<?php echo esc_js( __( 'Remove', 'wpshadow' ) ); ?>';
			removeBtn.addEventListener('click', function() {
				lastState = cloneBlocks(canvasState);
				canvasState.splice(index, 1);
				renderCanvas();
			});

			controls.appendChild(editBtn);
			controls.appendChild(dupBtn);
			controls.appendChild(upBtn);
			controls.appendChild(downBtn);
			controls.appendChild(removeBtn);
			wrapper.appendChild(controls);

			wrapper.addEventListener('dragstart', function() {
				dragPayload = { source: 'canvas', index: index };
			});

			return wrapper;
		}

		function toggleEdit(wrapper, block, index) {
			const existing = wrapper.querySelector('.wpshadow-edit-panel');
			if (existing) {
				existing.remove();
				return;
			}

			const panel = document.createElement('div');
			panel.className = 'wpshadow-edit-panel';

			panel.appendChild(makeLabel('type', '<?php echo esc_js( __( 'Block type', 'wpshadow' ) ); ?>'));
			const typeSelect = document.createElement('select');
			['condition', 'action', 'time'].forEach(function(type) {
				const opt = document.createElement('option');
				opt.value = type;
				opt.textContent = type.charAt(0).toUpperCase() + type.slice(1);
				if (block.type === type) {
					opt.selected = true;
				}
				typeSelect.appendChild(opt);
			});
			typeSelect.addEventListener('change', function(event) {
				canvasState[index].type = allowedTypes.includes(event.target.value) ? event.target.value : 'action';
				renderCanvas();
			});
			panel.appendChild(typeSelect);

			panel.appendChild(makeLabel('label', '<?php echo esc_js( __( 'Label', 'wpshadow' ) ); ?>'));
			const labelInput = document.createElement('input');
			labelInput.value = block.label || '';
			labelInput.addEventListener('input', function(event) {
				canvasState[index].label = event.target.value;
				updateSerialized();
			});
			panel.appendChild(labelInput);

			panel.appendChild(makeLabel('description', '<?php echo esc_js( __( 'Description', 'wpshadow' ) ); ?>'));
			const descInput = document.createElement('textarea');
			descInput.value = block.description || '';
			descInput.addEventListener('input', function(event) {
				canvasState[index].description = event.target.value;
				updateSerialized();
			});
			panel.appendChild(descInput);

			panel.appendChild(makeLabel('tag', '<?php echo esc_js( __( 'Tag', 'wpshadow' ) ); ?>'));
			const tagInput = document.createElement('input');
			tagInput.value = block.tag || '';
			tagInput.addEventListener('input', function(event) {
				canvasState[index].tag = event.target.value;
				updateSerialized();
			});
			panel.appendChild(tagInput);

			wrapper.appendChild(panel);
		}

		function makeLabel(forId, text) {
			const label = document.createElement('label');
			label.setAttribute('for', forId);
			label.textContent = text;
			return label;
		}

		function handleDrop(dropIndex) {
			if (!dragPayload) {
				return;
			}

			if (dragPayload.source === 'canvas') {
				const fromIndex = dragPayload.index;
				lastState = cloneBlocks(canvasState);
				const moved = canvasState.splice(fromIndex, 1)[0];
				const targetIndex = fromIndex < dropIndex ? dropIndex - 1 : dropIndex;
				canvasState.splice(targetIndex, 0, moved);
			} else if (dragPayload.source === 'library' && dragPayload.block) {
				lastState = cloneBlocks(canvasState);
				const clone = cloneBlocks([dragPayload.block])[0];
				canvasState.splice(dropIndex, 0, clone);
			}

			dragPayload = null;
			renderCanvas();
		}

		libraries.forEach(function(libraryEl) {
			libraryEl.addEventListener('dragstart', function(event) {
				const target = event.target.closest('[data-block]');
				if (!target) {
					return;
				}
				const payload = target.dataset.block ? safeParse(target.dataset.block) : null;
				dragPayload = { source: 'library', block: payload };
			});

			libraryEl.addEventListener('click', function(event) {
				const addBtn = event.target.closest('[data-add-block]');
				if (!addBtn) {
					return;
				}
				const payload = addBtn.dataset.addBlock ? safeParse(addBtn.dataset.addBlock) : null;
				if (payload) {
					lastState = cloneBlocks(canvasState);
					canvasState.push(cloneBlocks([payload])[0]);
					renderCanvas();
				}
			});
		});

		form.addEventListener('submit', function() {
			updateSerialized();
		});

		form.querySelectorAll('[data-save-mode]').forEach(function(button) {
			button.addEventListener('click', function() {
				modeField.value = button.getAttribute('data-save-mode') || 'draft';
				updateSerialized();
			});
		});

		// Templates
		if (btnTemplateWarmup) {
			btnTemplateWarmup.addEventListener('click', function() {
				lastState = cloneBlocks(canvasState);
				canvasState = cloneBlocks([
					{ id: 'time_daily_three_am', type: 'time', label: '<?php echo esc_js( __( 'When daily 3:00 AM', 'wpshadow' ) ); ?>', description: '<?php echo esc_js( __( 'Daily low-traffic slot', 'wpshadow' ) ); ?>', tag: '<?php echo esc_js( __( 'Time', 'wpshadow' ) ); ?>' },
					{ id: 'action_cache_warm', type: 'action', label: '<?php echo esc_js( __( 'Run cache warmup', 'wpshadow' ) ); ?>', description: '<?php echo esc_js( __( 'Pre-build cache during off-peak', 'wpshadow' ) ); ?>', tag: '<?php echo esc_js( __( 'Performance', 'wpshadow' ) ); ?>' }
				]);
				renderCanvas();
			});
		}

		if (btnTemplateHealth) {
			btnTemplateHealth.addEventListener('click', function() {
				lastState = cloneBlocks(canvasState);
				canvasState = cloneBlocks([
					{ id: 'time_sunday', type: 'time', label: '<?php echo esc_js( __( 'When Sunday 2:00 AM', 'wpshadow' ) ); ?>', description: '<?php echo esc_js( __( 'Quiet hours window', 'wpshadow' ) ); ?>', tag: '<?php echo esc_js( __( 'Time', 'wpshadow' ) ); ?>' },
					{ id: 'action_email_report', type: 'action', label: '<?php echo esc_js( __( 'Email site health report', 'wpshadow' ) ); ?>', description: '<?php echo esc_js( __( 'Send PDF snapshot to admins', 'wpshadow' ) ); ?>', tag: '<?php echo esc_js( __( 'Reporting', 'wpshadow' ) ); ?>' }
				]);
				renderCanvas();
			});
		}

		// Rollback selection handlers
		if (historyListEl) {
			historyListEl.addEventListener('click', function(event) {
				const btn = event.target.closest('[data-rollback-index]');
				if (!btn) return;
				const idx = btn.getAttribute('data-rollback-index');
				rollbackIndexField.value = idx;
				Array.from(historyListEl.querySelectorAll('[data-rollback-index]')).forEach(function(b){ b.classList.remove('button-primary'); });
				btn.classList.add('button-primary');
			});
		}

		// Run Now stub: show a success message simulating evaluator run
		if (btnRun) {
			btnRun.addEventListener('click', function() {
				validationBox.className = 'wpshadow-validation success';
				validationBox.textContent = '<?php echo esc_js( __( 'Run Now: evaluator stub executed with current published rules.', 'wpshadow' ) ); ?>';
			});
		}

		btnUndo.addEventListener('click', function() {
			if (Array.isArray(lastState)) {
				canvasState = cloneBlocks(lastState);
				lastState = null;
				renderCanvas();
			}
		});

		btnReset.addEventListener('click', function() {
			lastState = cloneBlocks(canvasState);
			canvasState = [];
			renderCanvas();
		});

		btnExport.addEventListener('click', function() {
			const json = JSON.stringify(canvasState, null, 2);
			navigator.clipboard && navigator.clipboard.writeText(json).then(function(){
				validationBox.className = 'wpshadow-validation success';
				validationBox.textContent = '<?php echo esc_js( __( 'Copied JSON to clipboard.', 'wpshadow' ) ); ?>';
			}).catch(function(){
				alert('<?php echo esc_js( __( 'Copy failed. Your browser may not support clipboard API here.', 'wpshadow' ) ); ?>');
			});
		});

		btnImport.addEventListener('click', function() {
			const input = prompt('<?php echo esc_js( __( 'Paste rule JSON:', 'wpshadow' ) ); ?>');
			if (!input) return;
			try {
				const parsed = JSON.parse(input);
				if (Array.isArray(parsed)) {
					canvasState = cloneBlocks(parsed);
					renderCanvas();
				} else {
					alert('<?php echo esc_js( __( 'Invalid JSON format. Expected an array of blocks.', 'wpshadow' ) ); ?>');
				}
			} catch (e) {
				alert('<?php echo esc_js( __( 'Invalid JSON. Please check and try again.', 'wpshadow' ) ); ?>');
			}
		});

		renderCanvas();
	})();
	</script>
</div>
