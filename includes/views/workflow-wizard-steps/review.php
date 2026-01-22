<?php
/**
 * Review & Save Step
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
?>

<div class="wizard-step review-step">
	<h2><?php esc_html_e( 'Review & Save', 'wpshadow' ); ?></h2>
	<p class="description">
		<?php esc_html_e( 'Review your workflow and give it a name (or we\'ll generate a silly one for you!)', 'wpshadow' ); ?>
	</p>

	<div id="workflow-summary" class="workflow-summary"></div>

	<form id="save-workflow-form" class="save-form">
		<div class="form-field">
			<label for="workflow_name">
				<?php esc_html_e( 'Workflow Name', 'wpshadow' ); ?>
			</label>
			<input 
				type="text" 
				id="workflow_name" 
				name="workflow_name" 
				placeholder="<?php esc_attr_e( 'Leave blank for a randomly generated name', 'wpshadow' ); ?>"
				class="regular-text"
			>
			<p class="description">
				<?php esc_html_e( 'If left blank, we\'ll generate a silly name like "Brave Balloon" or "Dancing Dolphin"!', 'wpshadow' ); ?>
			</p>
		</div>

		<div class="form-actions">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-workflows' . ( ! empty( $workflow_id ) ? '&action=edit&workflow=' . $workflow_id : '&action=create' ) . '&step=action&trigger=' . $trigger_id ) ); ?>" class="button button-large">
				<span class="dashicons dashicons-arrow-left-alt2"></span>
				<?php esc_html_e( 'Back to Actions', 'wpshadow' ); ?>
			</a>
			<button type="submit" class="button button-primary button-large button-hero">
				<span class="dashicons dashicons-saved"></span>
				<?php esc_html_e( 'Save Workflow', 'wpshadow' ); ?>
			</button>
		</div>

		<div id="save-result" class="save-result" style="display: none;"></div>
	</form>
</div>

<style>
.workflow-summary {
	background: #fff;
	border: 2px solid #2271b1;
	border-radius: 8px;
	padding: 25px;
	margin: 30px 0;
}

.summary-section {
	margin-bottom: 25px;
}

.summary-section:last-child {
	margin-bottom: 0;
}

.summary-section h3 {
	margin: 0 0 10px 0;
	font-size: 14px;
	text-transform: uppercase;
	color: #666;
	font-weight: 600;
}

.summary-content {
	display: flex;
	align-items: flex-start;
	gap: 15px;
	padding: 15px;
	background: #f9f9f9;
	border-radius: 6px;
}

.summary-icon {
	flex-shrink: 0;
	width: 40px;
	height: 40px;
	display: flex;
	align-items: center;
	justify-content: center;
	background: #2271b1;
	border-radius: 8px;
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
	font-size: 15px;
	margin-bottom: 5px;
}

.summary-description {
	font-size: 13px;
	color: #666;
}

.summary-config {
	margin-top: 10px;
	padding: 10px;
	background: #fff;
	border-radius: 4px;
	font-size: 12px;
	font-family: monospace;
}

.action-list {
	display: flex;
	flex-direction: column;
	gap: 12px;
}

.action-item {
	display: flex;
	align-items: flex-start;
	gap: 12px;
	padding: 15px;
	background: #f9f9f9;
	border-radius: 6px;
	border-left: 3px solid #00a32a;
}

.action-number {
	flex-shrink: 0;
	width: 28px;
	height: 28px;
	display: flex;
	align-items: center;
	justify-content: center;
	background: #00a32a;
	color: #fff;
	border-radius: 50%;
	font-weight: 600;
	font-size: 13px;
}

.save-form {
	max-width: 600px;
	margin-top: 40px;
}

.save-result.success {
	display: block !important;
	padding: 15px;
	background: #d5f4d8;
	border: 1px solid #00a32a;
	border-radius: 4px;
	margin-top: 20px;
	color: #00a32a;
	font-weight: 600;
}

.save-result.error {
	display: block !important;
	padding: 15px;
	background: #ffd8d8;
	border: 1px solid #d63638;
	border-radius: 4px;
	margin-top: 20px;
	color: #d63638;
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
