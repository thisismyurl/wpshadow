<?php
/**
 * Action Selection Step
 *
 * @package WPShadow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$trigger_id  = isset( $_GET['trigger'] ) ? sanitize_key( $_GET['trigger'] ) : '';
$workflow_id = isset( $_GET['workflow'] ) ? sanitize_key( $_GET['workflow'] ) : '';
if ( empty( $trigger_id ) ) {
	if ( ! empty( $workflow_id ) ) {
		wp_safe_redirect( admin_url( 'admin.php?page=wpshadow-workflows&action=edit&workflow=' . $workflow_id . '&step=action' ) );
	} else {
		wp_safe_redirect( admin_url( 'admin.php?page=wpshadow-workflows&action=create' ) );
	}
	exit;
}

$action_categories = \WPShadow\Workflow\Workflow_Wizard::get_available_actions( $trigger_id );

// Get trigger details to display context
$trigger_label       = '';
$trigger_description = '';
$trigger_categories  = \WPShadow\Workflow\Workflow_Wizard::get_trigger_categories();
foreach ( $trigger_categories as $category ) {
	if ( isset( $category['triggers'][ $trigger_id ] ) ) {
		$trigger_label       = $category['triggers'][ $trigger_id ]['label'];
		$trigger_description = $category['triggers'][ $trigger_id ]['description'];
		break;
	}
}
?>

<div class="wizard-step action-selection">
	<div class="step-header">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-workflows' . ( ! empty( $workflow_id ) ? '&action=edit&workflow=' . $workflow_id : '&action=create' ) . '&step=trigger-config&trigger=' . $trigger_id ) ); ?>" class="back-button">
			<span class="dashicons dashicons-arrow-left-alt2"></span>
			<?php esc_html_e( 'Back', 'wpshadow' ); ?>
		</a>
		<h2><?php esc_html_e( 'Choose Actions', 'wpshadow' ); ?></h2>
	</div>

	<!-- Trigger Context Display -->
	<?php if ( ! empty( $trigger_label ) ) : ?>
		<div class="trigger-context">
			<div class="trigger-context-content">
				<strong><?php esc_html_e( 'Trigger:', 'wpshadow' ); ?></strong> 
				<span><?php echo esc_html( $trigger_label ); ?></span>
				<?php if ( ! empty( $trigger_description ) ) : ?>
					<p class="trigger-description"><?php echo esc_html( $trigger_description ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>

	<p class="description">
		<?php esc_html_e( 'Select one action to run when this trigger fires. WPShadow Pro unlocks multiple actions per trigger.', 'wpshadow' ); ?>
	</p>

	<!-- Selected Actions -->
	<div id="selected-actions" class="selected-actions" style="display: none;">
		<h3><?php esc_html_e( 'Selected Action', 'wpshadow' ); ?></h3>
		<div class="selected-actions-note"><?php esc_html_e( 'One action per workflow in this version. Upgrade to add more.', 'wpshadow' ); ?></div>
		<div id="selected-actions-list" class="selected-actions-list"></div>
		<button type="button" id="continue-to-review" class="button button-primary button-large">
			<?php esc_html_e( 'Continue to Review', 'wpshadow' ); ?>
			<span class="dashicons dashicons-arrow-right-alt2"></span>
		</button>
	</div>

	<!-- Available Actions -->
	<div class="action-categories">
		<?php foreach ( $action_categories as $category_id => $category ) : ?>
			<div class="action-category">
				<h3>
					<span class="dashicons dashicons-<?php echo esc_attr( $category['icon'] ); ?>"></span>
					<?php echo esc_html( $category['label'] ); ?>
				</h3>
				<div class="action-options">
					<?php foreach ( $category['actions'] as $action_id => $action ) : ?>
						<?php
						$action_config = \WPShadow\Workflow\Workflow_Wizard::get_action_config( $action_id );
						$has_config    = ! empty( $action_config );
						?>
						<button class="action-option" data-action-id="<?php echo esc_attr( $action_id ); ?>" data-has-config="<?php echo esc_attr( $has_config ? 'true' : 'false' ); ?>">
							<span class="action-icon">
								<span class="dashicons dashicons-<?php echo esc_attr( $action['icon'] ); ?>"></span>
							</span>
							<span class="action-content">
								<strong class="action-label"><?php echo esc_html( $action['label'] ); ?></strong>
								<span class="action-description"><?php echo esc_html( $action['description'] ); ?></span>
							</span>
							<span class="action-arrow">
								<span class="dashicons dashicons-plus-alt"></span>
							</span>
						</button>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>

<style>
.action-categories {
	display: flex;
	flex-direction: column;
	gap: 30px;
	margin-top: 30px;
}

.action-category h3 {
	font-size: 16px;
	font-weight: 600;
	margin: 0 0 15px 0;
	display: flex;
	align-items: center;
	gap: 8px;
	color: #2271b1;
}

.action-category h3 .dashicons {
	width: 20px;
	height: 20px;
	font-size: 20px;
}

.action-options {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
	gap: 12px;
}

.action-option {
	display: flex;
	align-items: center;
	gap: 15px;
	padding: 16px;
	background: #f9f9f9;
	border: 2px solid #ddd;
	border-radius: 6px;
	cursor: pointer;
	transition: all 0.2s ease;
	text-align: left;
	width: 100%;
}

.action-option:hover {
	background: #fff;
	border-color: #00a32a;
	transform: translateY(-2px);
	box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.action-option.selected {
	background: #e7f7e7;
	border-color: #00a32a;
}

.action-icon {
	flex-shrink: 0;
	width: 40px;
	height: 40px;
	display: flex;
	align-items: center;
	justify-content: center;
	background: #00a32a;
	border-radius: 8px;
	color: #fff;
}

.action-icon .dashicons {
	width: 24px;
	height: 24px;
	font-size: 24px;
}

.action-content {
	flex: 1;
	display: flex;
	flex-direction: column;
	gap: 4px;
}

.action-label {
	font-size: 14px;
	font-weight: 600;
	color: #000;
	display: block;
}

.action-description {
	font-size: 12px;
	color: #666;
	display: block;
}

.action-arrow {
	flex-shrink: 0;
	color: #00a32a;
	opacity: 0;
	transition: opacity 0.2s ease;
}

.action-option:hover .action-arrow {
	opacity: 1;
}

.action-arrow .dashicons {
	width: 20px;
	height: 20px;
	font-size: 20px;
}

/* Selected Actions */
.selected-actions {
	background: #f0f6ff;
	border: 2px solid #2271b1;
	border-radius: 8px;
	padding: 20px;
	margin-bottom: 30px;
}

.selected-actions h3 {
	margin: 0 0 10px 0;
	font-size: 16px;
	color: #2271b1;
}

.selected-actions-note {
	margin: 0 0 12px 0;
	font-size: 12px;
	color: #555;
}

.selected-actions-list {
	display: flex;
	flex-direction: column;
	gap: 10px;
	margin-bottom: 20px;
}

.selected-action-item {
	display: flex;
	align-items: center;
	gap: 12px;
	padding: 12px;
	background: #fff;
	border: 1px solid #2271b1;
	border-radius: 4px;
}

.selected-action-item .action-number {
	flex-shrink: 0;
	width: 28px;
	height: 28px;
	display: flex;
	align-items: center;
	justify-content: center;
	background: #2271b1;
	color: #fff;
	border-radius: 50%;
	font-weight: 600;
	font-size: 13px;
}

.selected-action-item .action-label {
	flex: 1;
	font-weight: 600;
}

.selected-action-item .remove-action {
	flex-shrink: 0;
	cursor: pointer;
	color: #d63638;
	padding: 4px;
	border: none;
	background: none;
}

.selected-action-item .remove-action .dashicons {
	width: 20px;
	height: 20px;
	font-size: 20px;
}

/* Trigger Context Display */
.trigger-context {
	background: #f3f7ff;
	border-left: 4px solid #2271b1;
	border-radius: 4px;
	padding: 16px;
	margin-bottom: 25px;
}

.trigger-context-content {
	display: flex;
	flex-direction: column;
	gap: 8px;
}

.trigger-context-content strong {
	color: #2271b1;
	font-weight: 600;
}

.trigger-context-content span {
	color: #2c3338;
	font-size: 15px;
	font-weight: 500;
}

.trigger-description {
	font-size: 13px;
	color: #555;
	margin: 8px 0 0 0;
	font-style: italic;
}
</style>

<script>
jQuery(document).ready(function($) {
	let selectedActions = [];
	const triggerId = '<?php echo esc_js( $trigger_id ); ?>';

	// Add action (single-selection for free tier)
	$('.action-option').on('click', function() {
		const actionId = $(this).data('action-id');
		const actionLabel = $(this).find('.action-label').text();
		const hasConfig = $(this).data('has-config');

		// If already selected, replace the previous choice
		if (selectedActions.length === 1) {
			const previousId = selectedActions[0].id;
			if (previousId === actionId) {
				return; // no change
			}
			$('.action-option[data-action-id="' + previousId + '"]').removeClass('selected');
			selectedActions = [];
		}

		selectedActions = [{
			id: actionId,
			label: actionLabel,
			config: {}
		}];

		// Update UI
		$('.action-option').removeClass('selected');
		$(this).addClass('selected');
		updateSelectedActionsList();

		// Auto-advance based on whether action has configuration
		setTimeout(function() {
			// Store selected actions
			sessionStorage.setItem('workflow_actions', JSON.stringify(selectedActions));
			
			if (hasConfig === true || hasConfig === 'true') {
				// Action has configuration - go to action config screen
				window.location.href = '<?php echo admin_url( 'admin.php?page=wpshadow-workflows' . ( ! empty( $workflow_id ) ? '&action=edit&workflow=' . $workflow_id : '&action=create' ) ); ?>&step=action-config&trigger=' + triggerId + '&action_index=0';
			} else {
				// No configuration needed - go straight to review
				window.location.href = '<?php echo admin_url( 'admin.php?page=wpshadow-workflows' . ( ! empty( $workflow_id ) ? '&action=edit&workflow=' . $workflow_id : '&action=create' ) ); ?>&step=review&trigger=' + triggerId;
			}
		}, 200); // Small delay for UI feedback
	});

	// Update selected actions display
	function updateSelectedActionsList() {
		const $list = $('#selected-actions-list');
		$list.empty();
		
		if (selectedActions.length === 0) {
			$('#selected-actions').hide();
			return;
		}
		
		$('#selected-actions').show();
		
		selectedActions.forEach((action, index) => {
			const $item = $('<div class="selected-action-item">');
			$item.append('<span class="action-number">' + (index + 1) + '</span>');
			$item.append('<span class="action-label">' + action.label + '</span>');
			
			const $removeBtn = $('<button type="button" class="remove-action">');
			$removeBtn.append('<span class="dashicons dashicons-no-alt"></span>');
			$removeBtn.on('click', function() {
				removeAction(index);
			});
			
			$item.append($removeBtn);
			$list.append($item);
		});
	}

	// Remove action
	function removeAction(index) {
		const actionId = selectedActions[index].id;
		selectedActions.splice(index, 1);
		$('.action-option[data-action-id="' + actionId + '"]').removeClass('selected');
		updateSelectedActionsList();
	}

	// Continue to review/config
	$('#continue-to-review').on('click', function() {
		if (selectedActions.length === 0) {
			alert('<?php echo esc_js( __( 'Please select an action.', 'wpshadow' ) ); ?>');
			return;
		}
		
		// Store selected actions
		sessionStorage.setItem('workflow_actions', JSON.stringify(selectedActions));
		
		// Navigate to action config (single action in free tier) - preserve workflow ID if editing
		window.location.href = '<?php echo admin_url( 'admin.php?page=wpshadow-workflows' . ( ! empty( $workflow_id ) ? '&action=edit&workflow=' . $workflow_id : '&action=create' ) ); ?>&step=action-config&trigger=' + triggerId + '&action_index=0';
	});
});
</script>
