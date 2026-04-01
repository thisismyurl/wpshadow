<?php
/**
 * Action Selection Step
 *
 * @package WPShadow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPShadow\Core\Form_Param_Helper;

$trigger_id  = Form_Param_Helper::get( 'trigger', 'key', '' );
$workflow_id = Form_Param_Helper::get( 'workflow', 'key', '' );
if ( empty( $trigger_id ) ) {
	if ( ! empty( $workflow_id ) ) {
		wp_safe_redirect( admin_url( 'admin.php?page=wpshadow-automations&action=edit&workflow=' . $workflow_id . '&step=action-selection' ) );
	} else {
		wp_safe_redirect( admin_url( 'admin.php?page=wpshadow-automations&action=create' ) );
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

<div class="wps-page-container">
	<?php
	wpshadow_render_wizard_back_button(
		admin_url( 'admin.php?page=wpshadow-automations' . ( ! empty( $workflow_id ) ? '&action=edit&workflow=' . $workflow_id : '&action=create' ) . '&step=trigger-config&trigger=' . $trigger_id )
	);
	?>
	<?php
	wpshadow_render_page_header(
		__( 'Choose Actions', 'wpshadow' ),
		__( 'Select one action to run when this trigger fires. WPShadow Pro unlocks multiple actions per trigger.', 'wpshadow' ),
		'dashicons-admin-tools'
	);
	?>

	<!-- Trigger Context Display -->
	<?php if ( ! empty( $trigger_label ) ) : ?>
		<div class="wps-alert wps-alert--info wps-action-trigger-info">
			<strong class="wps-action-trigger-label"><?php esc_html_e( 'Trigger:', 'wpshadow' ); ?></strong>
			<span class="wps-action-trigger-value"><?php echo esc_html( $trigger_label ); ?></span>
			<?php if ( ! empty( $trigger_description ) ) : ?>
				<p class="wps-text-sm wps-text-muted wps-action-description">
					<?php echo esc_html( $trigger_description ); ?>
				</p>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<!-- Selected Actions -->
	<div id="selected-actions" class="wps-card wps-card-primary wps-action-selected-container">
		<div class="wps-card-header">
			<h3 class="wps-card-title"><?php esc_html_e( 'Selected Action', 'wpshadow' ); ?></h3>
		</div>
		<div class="wps-card-body">
			<p class="wps-text-sm wps-text-muted wps-action-selected-note">
				<?php esc_html_e( 'One action per workflow in this version. Upgrade to add more.', 'wpshadow' ); ?>
			</p>
			<div id="selected-actions-list" class="wps-layout-stack wps-layout-stack-sm"></div>
			<button type="button" id="continue-to-review" class="wps-btn wps-btn--primary wps-action-continue-button">
				<?php esc_html_e( 'Continue to Review', 'wpshadow' ); ?>
				<span class="dashicons dashicons-arrow-right-alt2 wps-action-next-icon"></span>
			</button>
		</div>
	</div>

	<!-- Available Actions -->
	<div class="wps-layout-stack wps-layout-stack-lg">
		<?php foreach ( $action_categories as $category_id => $category ) : ?>
			<div class="wps-card">
				<div class="wps-card-header">
					<h3 class="wps-card-title">
						<span class="dashicons dashicons-<?php echo esc_attr( $category['icon'] ); ?>" class="wps-action-category-icon"></span>
						<?php echo esc_html( $category['label'] ); ?>
					</h3>
				</div>
				<div class="wps-card-body">
					<div class="wps-grid wps-grid-cols-2">
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
			</div>
		<?php endforeach; ?>
	</div>
</div>

<style>
/* Action Option Buttons */
.action-option {
	display: flex;
	align-items: center;
	gap: var(--wps-space-4);
	padding: var(--wps-space-4);
	background: var(--wps-gray-50);
	border: 2px solid var(--wps-border-color);
	border-radius: var(--wps-radius-md);
	cursor: pointer;
	transition: all 0.2s ease;
	text-align: left;
	width: 100%;
}

.action-option:hover {
	background: #fff;
	border-color: var(--wps-success-dark);
	transform: translateY(-2px);
	box-shadow: var(--wps-shadow-md);
}

.action-option:focus-visible {
	outline: 3px solid var(--wps-focus-ring);
	outline-offset: 2px;
}

.action-option.selected {
	background: var(--wps-success-lightest);
	border-color: var(--wps-success-dark);
}

.action-icon {
	flex-shrink: 0;
	width: 40px;
	height: 40px;
	display: flex;
	align-items: center;
	justify-content: center;
	background: var(--wps-success-dark);
	border-radius: var(--wps-radius-md);
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
	gap: var(--wps-space-1);
}

.action-label {
	font-size: var(--wps-text-sm);
	font-weight: 600;
	color: var(--wps-gray-900);
	display: block;
}

.action-description {
	font-size: var(--wps-text-xs);
	color: var(--wps-gray-600);
	display: block;
}

.action-arrow {
	flex-shrink: 0;
	color: var(--wps-success-dark);
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

/* Selected Action Item */
.selected-action-item {
	display: flex;
	align-items: center;
	gap: var(--wps-space-3);
	padding: var(--wps-space-3);
	background: #fff;
	border: 1px solid var(--wps-primary);
	border-radius: var(--wps-radius-md);
}

.selected-action-item .action-number {
	flex-shrink: 0;
	width: 28px;
	height: 28px;
	display: flex;
	align-items: center;
	justify-content: center;
	background: var(--wps-primary);
	color: #fff;
	border-radius: 50%;
	font-weight: 600;
	font-size: var(--wps-text-sm);
}

.selected-action-item .action-label {
	flex: 1;
	font-weight: 600;
}

.selected-action-item .remove-action {
	flex-shrink: 0;
	cursor: pointer;
	color: var(--wps-danger-dark);
	padding: var(--wps-space-1);
	border: none;
	background: none;
}

.selected-action-item .remove-action .dashicons {
	width: 20px;
	height: 20px;
	font-size: 20px;
}

/* Page Header Flex Layout */
.wps-page-header {
	display: flex;
	align-items: flex-start;
	gap: var(--wps-space-3);
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
				window.location.href = '<?php echo admin_url( 'admin.php?page=wpshadow-automations' . ( ! empty( $workflow_id ) ? '&action=edit&workflow=' . $workflow_id : '&action=create' ) ); ?>&step=action-config&trigger=' + triggerId + '&action_index=0';
			} else {
				// No configuration needed - go straight to review
				window.location.href = '<?php echo admin_url( 'admin.php?page=wpshadow-automations' . ( ! empty( $workflow_id ) ? '&action=edit&workflow=' . $workflow_id : '&action=create' ) ); ?>&step=review&trigger=' + triggerId;
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
			WPShadowModal.alert({
				title: '<?php echo esc_js( __( 'Validation', 'wpshadow' ) ); ?>',
				message: '<?php echo esc_js( __( 'Please select an action.', 'wpshadow' ) ); ?>',
				type: 'warning'
			});
			return;
		}

		// Store selected actions
		sessionStorage.setItem('workflow_actions', JSON.stringify(selectedActions));

		// Navigate to action config (single action in free tier) - preserve workflow ID if editing
		window.location.href = '<?php echo admin_url( 'admin.php?page=wpshadow-automations' . ( ! empty( $workflow_id ) ? '&action=edit&workflow=' . $workflow_id : '&action=create' ) ); ?>&step=action-config&trigger=' + triggerId + '&action_index=0';
	});
});
</script>
