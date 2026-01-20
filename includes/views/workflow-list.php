<?php
/**
 * Workflow List View (Workflow Builder dashboard)
 *
 * @package WPShadow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$workflows = \WPShadow\Workflow\Workflow_Manager::get_workflows();

// Filter out temporary Kanban workflows
$hidden_workflow_ids = \WPShadow\Workflow\Kanban_Workflow_Helper::get_hidden_workflow_ids();
$workflows = array_filter( $workflows, function( $workflow ) use ( $hidden_workflow_ids ) {
	return ! in_array( $workflow['id'], $hidden_workflow_ids, true );
} );
?>

<div class="wrap wpshadow-workflow-list">
	<h1>
		<?php esc_html_e( 'Automation Workflows', 'wpshadow' ); ?>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-workflows&action=create' ) ); ?>" class="page-title-action">
			<?php esc_html_e( 'Create Workflow', 'wpshadow' ); ?>
		</a>
	</h1>

	<p class="description">
		<?php esc_html_e( 'Create automated workflows that trigger actions based on events, schedules, or conditions.', 'wpshadow' ); ?>
	</p>

	<?php if ( empty( $workflows ) ) : ?>
		<!-- Create Workflow Button Section -->
		<div class="workflow-cta-section">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-workflows&action=create' ) ); ?>" class="button button-primary button-hero">
				<?php esc_html_e( 'Create a Workflow', 'wpshadow' ); ?>
			</a>
		</div>

		<!-- Empty State -->
		<div class="wpshadow-empty-state">
			<div class="empty-state-icon">
				<span class="dashicons dashicons-networking"></span>
			</div>
			<h2><?php esc_html_e( 'No Workflows Yet', 'wpshadow' ); ?></h2>
			<p><?php esc_html_e( 'Get started with one of these popular workflow examples or build your own.', 'wpshadow' ); ?></p>
			
			<div class="empty-state-examples">
				<h3><?php esc_html_e( 'Quick Start Examples:', 'wpshadow' ); ?></h3>
				<div id="example-list" class="example-list">
					<!-- Populated by JavaScript -->
				</div>
			</div>
		</div>
	<?php else : ?>
		<!-- Workflow List -->
		<div class="wpshadow-workflows">
			<?php foreach ( $workflows as $workflow ) : ?>
				<?php
				$trigger_label = get_trigger_summary( $workflow );
				$action_label = get_action_summary( $workflow );
				$is_enabled = ! isset( $workflow['enabled'] ) || $workflow['enabled'];
				?>
				<div class="workflow-card <?php echo $is_enabled ? 'enabled' : 'disabled'; ?>" data-workflow-id="<?php echo esc_attr( $workflow['id'] ); ?>">
					<div class="workflow-header">
						<div class="workflow-status">
							<label class="workflow-toggle">
								<input type="checkbox" class="workflow-enable-toggle" <?php checked( $is_enabled ); ?>>
								<span class="toggle-slider"></span>
							</label>
						</div>
						<div class="workflow-info">
							<h3 class="workflow-name"><?php echo esc_html( $workflow['name'] ); ?></h3>
							<p class="workflow-summary">
								<span class="workflow-trigger">
									<span class="dashicons dashicons-clock"></span>
									<?php echo esc_html( $trigger_label ); ?>
								</span>
								<span class="workflow-actions">
									<span class="dashicons dashicons-admin-tools"></span>
									<?php echo esc_html( $action_label ); ?>
								</span>
							</p>
						</div>
					</div>

				<div class="workflow-buttons">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-workflows&action=edit&workflow=' . $workflow['id'] ) ); ?>" class="button button-small button-primary">
							<?php esc_html_e( 'Edit', 'wpshadow' ); ?>
						</a>
						<button class="button button-small workflow-test-btn" data-workflow-id="<?php echo esc_attr( $workflow['id'] ); ?>">
							<?php esc_html_e( 'Test', 'wpshadow' ); ?>
						</button>
						<button class="button button-small workflow-run-btn" data-workflow-id="<?php echo esc_attr( $workflow['id'] ); ?>">
							<?php esc_html_e( 'Run Now', 'wpshadow' ); ?>
						</button>
						<button class="button button-small button-link-delete workflow-delete-btn" data-workflow-id="<?php echo esc_attr( $workflow['id'] ); ?>">
							<?php esc_html_e( 'Delete', 'wpshadow' ); ?>
						</button>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<!-- Examples Section Below Workflows -->
		<div class="wpshadow-empty-state" style="margin-top: 40px;">
			<h3><?php esc_html_e( 'Quick Start Examples:', 'wpshadow' ); ?></h3>
			<p><?php esc_html_e( 'Create workflows based on these popular templates:', 'wpshadow' ); ?></p>
			<div id="example-list" class="example-list">
				<!-- Populated by JavaScript -->
			</div>
		</div>
	<?php endif; ?>
</div>

<style>
.wpshadow-workflow-list {
	max-width: 1200px;
}

/* Empty State */
.wpshadow-empty-state {
	text-align: center;
	padding: 60px 20px;
	background: #fff;
	border: 1px solid #ddd;
	border-radius: 8px;
	margin-top: 20px;
}

.empty-state-icon {
	font-size: 64px;
	color: #ccc;
	margin-bottom: 20px;
}

.empty-state-icon .dashicons {
	width: 64px;
	height: 64px;
	font-size: 64px;
}

.wpshadow-empty-state h2 {
	font-size: 24px;
	margin-bottom: 10px;
}

.wpshadow-empty-state > p {
	font-size: 16px;
	color: #666;
	margin-bottom: 30px;
}

/* Create Workflow CTA */
.workflow-cta-section {
	text-align: center;
	margin: 30px 0;
}

.workflow-cta-section .button-hero {
	padding: 20px 40px;
	font-size: 16px;
	height: auto;
	line-height: 1.5;
}

.empty-state-examples {
	max-width: 100%;
	margin: 30px 0 0 0;
	text-align: left;
}

.empty-state-examples h3 {
	margin-top: 0;
	font-size: 14px;
	text-transform: uppercase;
	color: #666;
	margin-bottom: 20px;
}

/* Example List */
.example-list {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
	gap: 15px;
}

.example-item {
	background: #f9f9f9;
	border: 2px solid #ddd;
	border-radius: 6px;
	padding: 16px;
	cursor: pointer;
	transition: all 0.2s ease;
	display: flex;
	flex-direction: column;
	gap: 10px;
}

.example-item:hover {
	border-color: #2271b1;
	background: #fff;
	box-shadow: 0 2px 6px rgba(34, 113, 177, 0.15);
}

.example-item-header {
	display: flex;
	align-items: flex-start;
	gap: 10px;
}

.example-item-icon {
	display: flex;
	align-items: center;
	justify-content: center;
	width: 32px;
	height: 32px;
	background: #e7f3ff;
	border-radius: 4px;
	flex-shrink: 0;
	color: #2271b1;
	font-size: 18px;
}

.example-item-icon .dashicons {
	width: 18px;
	height: 18px;
	font-size: 18px;
}

.example-item-title {
	font-weight: 600;
	margin: 0;
	font-size: 14px;
	color: #333;
}

.example-item-description {
	font-size: 12px;
	color: #666;
	margin: 0;
	line-height: 1.4;
}

.example-item-button {
	background: #2271b1;
	color: white;
	border: none;
	border-radius: 4px;
	padding: 6px 12px;
	font-size: 12px;
	font-weight: 600;
	cursor: pointer;
	transition: background 0.2s ease;
	margin-top: auto;
	width: 100%;
}

.example-item-button:hover {
	background: #135e96;
}

.example-item-button:active {
	opacity: 0.8;
}

.example-item-loading .example-item-button {
	opacity: 0.6;
	cursor: not-allowed;
}

/* Workflow Cards */
.wpshadow-workflows {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
	gap: 20px;
	margin-top: 20px;
}

.workflow-card {
	background: #fff;
	border: 1px solid #ddd;
	border-radius: 8px;
	padding: 20px;
	transition: all 0.2s ease;
}

.workflow-card:hover {
	box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.workflow-card.disabled {
	opacity: 0.6;
}

.workflow-header {
	display: flex;
	gap: 15px;
	margin-bottom: 15px;
}

.workflow-status {
	flex-shrink: 0;
}

.workflow-toggle {
	position: relative;
	display: inline-block;
	width: 44px;
	height: 24px;
	cursor: pointer;
}

.workflow-toggle input {
	opacity: 0;
	width: 0;
	height: 0;
}

.toggle-slider {
	position: absolute;
	cursor: pointer;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background-color: #ccc;
	transition: .3s;
	border-radius: 24px;
}

.toggle-slider:before {
	position: absolute;
	content: "";
	height: 18px;
	width: 18px;
	left: 3px;
	bottom: 3px;
	background-color: white;
	transition: .3s;
	border-radius: 50%;
}

.workflow-toggle input:checked + .toggle-slider {
	background-color: #2271b1;
}

.workflow-toggle input:checked + .toggle-slider:before {
	transform: translateX(20px);
}

.workflow-info {
	flex: 1;
}

.workflow-name {
	margin: 0 0 8px 0;
	font-size: 18px;
	font-weight: 600;
}

.workflow-summary {
	margin: 0;
	font-size: 13px;
	color: #666;
	display: flex;
	gap: 20px;
	flex-wrap: wrap;
}

.workflow-summary span {
	display: inline-flex;
	align-items: center;
	gap: 5px;
}

.workflow-summary .dashicons {
	width: 16px;
	height: 16px;
	font-size: 16px;
}

.workflow-buttons {
	display: flex;
	gap: 8px;
	padding-top: 15px;
	border-top: 1px solid #f0f0f0;
	justify-content: center;
}

.workflow-buttons .button {
	min-width: 70px;
}

.workflow-buttons .button-primary {
	order: -1;
}
</style>

<?php
/**
 * Get human-readable trigger summary with trigger name or schedule
 *
 * @param array $workflow Workflow data
 * @return string Trigger summary
 */
function get_trigger_summary( $workflow ) {
	// Try to get from blocks format (new format)
	$trigger_block = null;
	if ( ! empty( $workflow['blocks'] ) && is_array( $workflow['blocks'] ) ) {
		foreach ( $workflow['blocks'] as $block ) {
			if ( 'trigger' === $block['type'] ) {
				$trigger_block = $block;
				break;
			}
		}
	}
	
	// Fallback to direct trigger key (legacy format)
	if ( ! $trigger_block && ! empty( $workflow['trigger'] ) ) {
		$trigger_block = $workflow['trigger'];
	}
	
	if ( ! $trigger_block ) {
		return __( 'No trigger configured', 'wpshadow' );
	}

	$trigger_id = isset( $trigger_block['id'] ) ? $trigger_block['id'] : '';
	$config = isset( $trigger_block['config'] ) ? $trigger_block['config'] : array();
	
	// For time triggers, show the schedule
	if ( 'time_daily' === $trigger_id || ( isset( $trigger_block['type'] ) && 'time_trigger' === $trigger_block['type'] ) ) {
		$frequency = isset( $config['frequency'] ) ? $config['frequency'] : 'daily';
		$time = isset( $config['time'] ) ? $config['time'] : '02:00';
		
		// Convert time format (24-hour to 12-hour with AM/PM)
		$time_parts = explode( ':', $time );
		$hour = intval( $time_parts[0] );
		$minute = isset( $time_parts[1] ) ? $time_parts[1] : '00';
		$ampm = $hour >= 12 ? 'PM' : 'AM';
		$display_hour = $hour % 12;
		if ( 0 === $display_hour ) {
			$display_hour = 12;
		}
		
		$time_display = sprintf( '%d:%s %s', $display_hour, $minute, $ampm );
		
		if ( 'daily' === $frequency ) {
			return sprintf( __( 'Daily at %s', 'wpshadow' ), $time_display );
		} elseif ( 'weekly' === $frequency ) {
			$day = isset( $config['day'] ) ? ucfirst( $config['day'] ) : 'Sunday';
			return sprintf( __( 'Weekly on %s at %s', 'wpshadow' ), $day, $time_display );
		} elseif ( 'monthly' === $frequency ) {
			$day = isset( $config['day'] ) ? $config['day'] : '1';
			return sprintf( __( 'Monthly on day %s at %s', 'wpshadow' ), $day, $time_display );
		}
	}
	
	// For other triggers, get label from registry
	$all_triggers = \WPShadow\Workflow\Block_Registry::get_triggers();
	
	if ( ! empty( $trigger_id ) && isset( $all_triggers[ $trigger_id ] ) ) {
		$trigger_block_data = $all_triggers[ $trigger_id ];
		return $trigger_block_data['label'];
	}
	
	// Fallback to type-based summary
	if ( isset( $trigger_block['type'] ) ) {
		$type = $trigger_block['type'];
		$summaries = array(
			'time_trigger'      => __( 'On schedule', 'wpshadow' ),
			'page_load_trigger' => __( 'On page load', 'wpshadow' ),
			'event_trigger'     => __( 'On event', 'wpshadow' ),
			'condition_trigger' => __( 'When condition met', 'wpshadow' ),
		);
		return isset( $summaries[ $type ] ) ? $summaries[ $type ] : $type;
	}
	
	return __( 'Unknown trigger', 'wpshadow' );
}

/**
 * Get human-readable action summary with first (and only) action name
 * Note: This version of the plugin supports only one action per trigger
 *
 * @param array $workflow Workflow data
 * @return string Action summary
 */
function get_action_summary( $workflow ) {
	$action_blocks = array();
	
	// Try to get from blocks format (new format)
	if ( ! empty( $workflow['blocks'] ) && is_array( $workflow['blocks'] ) ) {
		foreach ( $workflow['blocks'] as $block ) {
			if ( 'action' === $block['type'] ) {
				$action_blocks[] = $block;
			}
		}
	}
	
	// Fallback to direct actions key (legacy format)
	if ( empty( $action_blocks ) && ! empty( $workflow['actions'] ) && is_array( $workflow['actions'] ) ) {
		$action_blocks = $workflow['actions'];
	}
	
	if ( empty( $action_blocks ) ) {
		return __( 'No actions configured', 'wpshadow' );
	}

	$first_action = $action_blocks[0];
	$action_id = isset( $first_action['id'] ) ? $first_action['id'] : '';
	
	// Get action from registry to get the label
	$all_actions = \WPShadow\Workflow\Block_Registry::get_actions();
	
	if ( ! empty( $action_id ) && isset( $all_actions[ $action_id ] ) ) {
		$action_block_data = $all_actions[ $action_id ];
		return $action_block_data['label'];
	}

	// Fallback - just count (shouldn't happen with one-action-per-trigger rule)
	return __( '1 action', 'wpshadow' );
}
?>

<script>
jQuery(document).ready(function($) {
	const $exampleList = $('#example-list');
	
	if ($exampleList.length === 0) {
		return;
	}

	/**
	 * Load and render examples
	 */
	function loadExamples() {
		$.post(ajaxurl, {
			action: 'wpshadow_get_examples',
			nonce: wpshadowWorkflow.nonce
		}, function(response) {
			if (response.success) {
				renderExamples(response.data.examples);
			}
		});
	}

	/**
	 * Render examples in the list
	 */
	function renderExamples(examples) {
		$exampleList.empty();

		if (!examples || Object.keys(examples).length === 0) {
			$exampleList.html('<p><?php esc_html_e( 'No more examples available.', 'wpshadow' ); ?></p>');
			return;
		}

		Object.entries(examples).forEach(function([exampleKey, example]) {
			const $item = $('<div class="example-item" data-example-key="' + exampleKey + '">');
			
			// Icon mapping
			const iconMap = {
				'heart': 'heart',
				'admin-appearance': 'admin-appearance',
				'shield': 'shield',
				'admin-users': 'admin-users',
				'lock': 'lock',
				'download': 'download',
				'admin-tools': 'admin-tools',
				'image-rotate': 'image-rotate',
				'database': 'database'
			};
			
			const icon = iconMap[example.icon] || 'admin-tools';

			const html = `
				<div class="example-item-header">
					<div class="example-item-icon">
						<span class="dashicons dashicons-${icon}"></span>
					</div>
					<h4 class="example-item-title">${$('<div>').text(example.name).html()}</h4>
				</div>
				<p class="example-item-description">${$('<div>').text(example.description).html()}</p>
				<button type="button" class="example-item-button">
					<?php esc_html_e( 'Use Example', 'wpshadow' ); ?>
				</button>
			`;

			$item.html(html);
			$exampleList.append($item);
		});

		// Bind click handlers
		attachExampleHandlers();
	}

	/**
	 * Attach event handlers to example items
	 */
	function attachExampleHandlers() {
		$exampleList.on('click', '.example-item-button', function(e) {
			e.preventDefault();
			const $button = $(this);
			const $item = $button.closest('.example-item');
			const exampleKey = $item.data('example-key');

			createFromExample(exampleKey, $button, $item);
		});

		// Also allow clicking the whole item to trigger the button
		$exampleList.on('click', '.example-item', function(e) {
			if (e.target.classList.contains('example-item-button')) {
				return;
			}
			$(this).find('.example-item-button').click();
		});
	}

	/**
	 * Create a workflow from the selected example
	 */
	function createFromExample(exampleKey, $button, $item) {
		if ($button.prop('disabled')) {
			return;
		}

		$item.addClass('example-item-loading');
		$button.prop('disabled', true).text('<?php esc_html_e( 'Creating...', 'wpshadow' ); ?>');

		$.post(ajaxurl, {
			action: 'wpshadow_create_from_example',
			nonce: wpshadowWorkflow.nonce,
			example_key: exampleKey
		}, function(response) {
			if (response.success) {
				// Reload the examples to show updated list
				loadExamples();
				
				// Show success message
				showNotice('<?php esc_html_e( 'Workflow created successfully! Reload the page to see it.', 'wpshadow' ); ?>', 'success');
				
				// Reload page after 1 second
				setTimeout(function() {
					location.reload();
				}, 1000);
			} else {
				$item.removeClass('example-item-loading');
				$button.prop('disabled', false).text('<?php esc_html_e( 'Use Example', 'wpshadow' ); ?>');
				showNotice(response.data.message || '<?php esc_html_e( 'Error creating workflow', 'wpshadow' ); ?>', 'error');
			}
		});
	}

	/**
	 * Show a notice message
	 */
	function showNotice(message, type) {
		const className = 'notice notice-' + (type === 'success' ? 'success' : 'error') + ' is-dismissible';
		const $notice = $('<div class="' + className + '"><p>' + message + '</p></div>');
		$('.wrap').prepend($notice);
		
		// Auto-dismiss after 5 seconds
		setTimeout(function() {
			$notice.fadeOut(function() {
				$(this).remove();
			});
		}, 5000);
	}

	// Initial load
	loadExamples();
});
</script>