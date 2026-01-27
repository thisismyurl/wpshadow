<?php
/**
 * Review & Save Step
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
		wp_safe_redirect( admin_url( 'admin.php?page=wpshadow-workflows&action=edit&workflow=' . $workflow_id . '&step=action' ) );
	} else {
		wp_safe_redirect( admin_url( 'admin.php?page=wpshadow-workflows&action=create' ) );
	}
	exit;
}
?>

<div class="wps-page-container">
	<div class="wps-page-header">
		<h1 class="wps-page-title"><?php esc_html_e( 'Review & Save', 'wpshadow' ); ?></h1>
		<p class="wps-page-description">
			<?php esc_html_e( 'Review your workflow and give it a name (or we\'ll generate a silly one for you!)', 'wpshadow' ); ?>
		</p>
	</div>

	<div id="workflow-summary" class="wps-card" style="margin-top: var(--wps-space-6);"></div>

	<div class="wps-card" style="margin-top: var(--wps-space-6);">
		<div class="wps-card-body">
			<form id="save-workflow-form" class="wps-form">
				<div class="wps-form-group">
					<label for="workflow_name" class="wps-form-label">
						<?php esc_html_e( 'Workflow Name', 'wpshadow' ); ?>
					</label>
					<input 
						type="text" 
						id="workflow_name" 
						name="workflow_name" 
						placeholder="<?php esc_attr_e( 'Leave blank for a randomly generated name', 'wpshadow' ); ?>"
						class="wps-input"
					>
					<p class="wps-form-help wps-text-sm">
						<?php esc_html_e( 'If left blank, we\'ll generate a silly name like "Brave Balloon" or "Dancing Dolphin"!', 'wpshadow' ); ?>
					</p>
				</div>

				<div class="wps-form-actions" style="margin-top: var(--wps-space-6); padding-top: var(--wps-space-4); border-top: 1px solid var(--wps-border-color); display: flex; gap: var(--wps-space-3);">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-workflows' . ( ! empty( $workflow_id ) ? '&action=edit&workflow=' . $workflow_id : '&action=create' ) . '&step=action&trigger=' . $trigger_id ) ); ?>" class="wps-btn wps-btn--secondary">
						<span class="dashicons dashicons-arrow-left-alt2" style="font-size: 16px; margin-right: var(--wps-space-1);"></span>
						<?php esc_html_e( 'Back to Actions', 'wpshadow' ); ?>
					</a>
					<button type="submit" class="wps-btn wps-btn--primary">
						<span class="dashicons dashicons-saved" style="font-size: 16px; margin-right: var(--wps-space-1);"></span>
						<?php esc_html_e( 'Save Workflow', 'wpshadow' ); ?>
					</button>
				</div>

				<div id="save-result" class="save-result" style="display: none;"></div>
			</form>
		</div>
	</div>
</div>

<style>
/* Summary Sections */
.summary-section {
	margin-bottom: var(--wps-space-6);
	padding: var(--wps-space-5);
	border-bottom: 1px solid var(--wps-border-color);
}

.summary-section:last-child {
	margin-bottom: 0;
	border-bottom: none;
}

.summary-section h3 {
	margin: 0 0 var(--wps-space-3) 0;
	font-size: var(--wps-text-sm);
	text-transform: uppercase;
	color: var(--wps-gray-600);
	font-weight: 600;
	letter-spacing: 0.05em;
}

.summary-content {
	display: flex;
	align-items: flex-start;
	gap: var(--wps-space-4);
	padding: var(--wps-space-4);
	background: var(--wps-gray-50);
	border-radius: var(--wps-radius-md);
}

.summary-icon {
	flex-shrink: 0;
	width: 40px;
	height: 40px;
	display: flex;
	align-items: center;
	justify-content: center;
	background: var(--wps-primary);
	border-radius: var(--wps-radius-md);
	color: #fff;
}

.summary-icon .dashicons {
	width: 24px;
	height: 24px;
	font-size: 24px;
}

.summary-text {
	flex: 1;
}

.summary-title {
	font-weight: 600;
	font-size: var(--wps-text-base);
	margin-bottom: var(--wps-space-1);
	color: var(--wps-gray-900);
}

.summary-description {
	font-size: var(--wps-text-sm);
	color: var(--wps-gray-600);
}

.summary-config {
	margin-top: var(--wps-space-2);
	padding: var(--wps-space-2);
	background: #fff;
	border-radius: var(--wps-radius-sm);
	font-size: var(--wps-text-xs);
	font-family: monospace;
	color: var(--wps-gray-700);
}

.action-list {
	display: flex;
	flex-direction: column;
	gap: var(--wps-space-3);
}

.action-item {
	display: flex;
	align-items: flex-start;
	gap: var(--wps-space-3);
	padding: var(--wps-space-4);
	background: var(--wps-gray-50);
	border-radius: var(--wps-radius-md);
	border-left: 3px solid var(--wps-success-dark);
}

.action-number {
	flex-shrink: 0;
	width: 28px;
	height: 28px;
	display: flex;
	align-items: center;
	justify-content: center;
	background: var(--wps-success-dark);
	color: #fff;
	border-radius: 50%;
	font-weight: 600;
	font-size: var(--wps-text-sm);
}

/* Save Result Messages */
.save-result.success {
	display: block !important;
	padding: var(--wps-space-4);
	background: var(--wps-success-lightest);
	border: 1px solid var(--wps-success-dark);
	border-radius: var(--wps-radius-md);
	margin-top: var(--wps-space-4);
	color: var(--wps-success-dark);
	font-weight: 600;
}

.save-result.error {
	display: block !important;
	padding: var(--wps-space-4);
	background: var(--wps-danger-lightest);
	border: 1px solid var(--wps-danger-dark);
	border-radius: var(--wps-radius-md);
	margin-top: var(--wps-space-4);
	color: var(--wps-danger-dark);
	font-weight: 600;
}
</style>

<script>
jQuery(document).ready(function($) {
	const triggerId = '<?php echo esc_js( $trigger_id ); ?>';
	const workflowId = '<?php echo esc_js( $workflow_id ); ?>';
	const baseUrl = workflowId ? '<?php echo esc_url_raw( admin_url( 'admin.php?page=wpshadow-workflows&action=edit' ) ); ?>&workflow=' + workflowId : '<?php echo esc_url_raw( admin_url( 'admin.php?page=wpshadow-workflows&action=create' ) ); ?>';
	
	// Load workflow data from sessionStorage
	const triggerConfig = JSON.parse(sessionStorage.getItem('workflow_trigger_config') || '{}');
	const actions = JSON.parse(sessionStorage.getItem('workflow_actions') || '[]');
	
	if (actions.length === 0) {
		window.location.href = baseUrl + '&step=action&trigger=' + triggerId;
		return;
	}
	
	// Build summary
	buildSummary();
	
	function buildSummary() {
		const $summary = $('#workflow-summary');
		
		// Trigger section
		const $triggerSection = $('<div class="summary-section">');
		$triggerSection.append('<h3><?php esc_html_e( 'Trigger', 'wpshadow' ); ?></h3>');
		
		const $triggerContent = $('<div class="summary-content">');
		$triggerContent.append('<div class="summary-icon"><span class="dashicons dashicons-clock"></span></div>');
		
		const $triggerText = $('<div class="summary-text">');
		$triggerText.append('<div class="summary-title">' + getTriggerLabel(triggerId) + '</div>');
		
		if (Object.keys(triggerConfig).length > 0) {
			const $configDiv = $('<div class="summary-config">');
			Object.entries(triggerConfig).forEach(([key, value]) => {
				if (key !== 'trigger_id') {
					$configDiv.append('<div><strong>' + key + ':</strong> ' + value + '</div>');
				}
			});
			$triggerText.append($configDiv);
		}
		
		$triggerContent.append($triggerText);
		$triggerSection.append($triggerContent);
		$summary.append($triggerSection);
		
		// Actions section
		const $actionsSection = $('<div class="summary-section">');
		$actionsSection.append('<h3><?php esc_html_e( 'Actions', 'wpshadow' ); ?></h3>');
		
		const $actionList = $('<div class="action-list">');
		
		actions.forEach(function(action, index) {
			const $actionItem = $('<div class="action-item">');
			$actionItem.append('<div class="action-number">' + (index + 1) + '</div>');
			
			const $actionText = $('<div class="summary-text">');
			$actionText.append('<div class="summary-title">' + action.label + '</div>');
			
			if (action.config && Object.keys(action.config).length > 0) {
				const $configDiv = $('<div class="summary-config">');
				Object.entries(action.config).forEach(([key, value]) => {
					$configDiv.append('<div><strong>' + key + ':</strong> ' + value + '</div>');
				});
				$actionText.append($configDiv);
			}
			
			$actionItem.append($actionText);
			$actionList.append($actionItem);
		});
		
		$actionsSection.append($actionList);
		$summary.append($actionsSection);
	}
	
	function getTriggerLabel(id) {
		const labels = {
			'time_daily': '<?php esc_html_e( 'Every Day', 'wpshadow' ); ?>',
			'time_weekly': '<?php esc_html_e( 'Every Week', 'wpshadow' ); ?>',
			'time_hourly': '<?php esc_html_e( 'Every Hour', 'wpshadow' ); ?>',
			'page_load_all': '<?php esc_html_e( 'Every Page Load', 'wpshadow' ); ?>',
			'page_load_frontend': '<?php esc_html_e( 'Frontend Page Load', 'wpshadow' ); ?>',
			'page_load_admin': '<?php esc_html_e( 'Admin Page Load', 'wpshadow' ); ?>',
			'user_login': '<?php esc_html_e( 'User Login', 'wpshadow' ); ?>',
			'plugin_activated': '<?php esc_html_e( 'Plugin Activated', 'wpshadow' ); ?>',
		};
		
		return labels[id] || id;
	}
	
	// Handle form submission
	$('#save-workflow-form').on('submit', function(e) {
		e.preventDefault();
		
		const workflowName = $('#workflow_name').val().trim();
		
		// Build workflow data
		const workflowData = {
			name: workflowName,
			trigger: {
				type: triggerId,
				config: triggerConfig
			},
			actions: actions
		};
		
		// Save via AJAX
		$.post(ajaxurl, {
			action: 'wpshadow_save_workflow',
			nonce: '<?php echo wp_create_nonce( 'wpshadow_workflow' ); ?>',
			workflow: JSON.stringify(workflowData)
		}, function(response) {
			if (response.success) {
				// Clear sessionStorage
				sessionStorage.removeItem('workflow_trigger_id');
				sessionStorage.removeItem('workflow_trigger_config');
				sessionStorage.removeItem('workflow_actions');
				
				// Show success message
				$('#save-result').addClass('success').text('<?php esc_html_e( 'Workflow saved successfully!', 'wpshadow' ); ?>');
				
			// Redirect after 1 second - go back to list
			setTimeout(function() {
				window.location.href = '<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-workflows' ) ); ?>';
				}, 1000);
			} else {
				$('#save-result').addClass('error').text(response.data.message || '<?php esc_html_e( 'Error saving workflow', 'wpshadow' ); ?>');
			}
		});
	});
});
</script>