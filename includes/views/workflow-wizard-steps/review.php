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
		wp_safe_redirect( admin_url( 'admin.php?page=wpshadow-automations&action=edit&workflow=' . $workflow_id . '&step=action' ) );
	} else {
		wp_safe_redirect( admin_url( 'admin.php?page=wpshadow-automations&action=create' ) );
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

	<div id="workflow-summary" class="wps-card wpshadow-workflow-summary"></div>

	<div class="wps-card wpshadow-workflow-form-card">
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

				<div class="wps-form-actions">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-automations' . ( ! empty( $workflow_id ) ? '&action=edit&workflow=' . $workflow_id : '&action=create' ) . '&step=action&trigger=' . $trigger_id ) ); ?>" class="wps-btn wps-btn--secondary">
						<span class="dashicons dashicons-arrow-left-alt2 wpshadow-icon-compact"></span>
						<?php esc_html_e( 'Back to Actions', 'wpshadow' ); ?>
					</a>
					<button type="submit" class="wps-btn wps-btn--primary">
						<span class="dashicons dashicons-saved wpshadow-icon-compact"></span>
						<?php esc_html_e( 'Save Workflow', 'wpshadow' ); ?>
					</button>
				</div>

				<div id="save-result" class="save-result"></div>
			</form>
		</div>
	</div>
</div>

<div id="workflow-review" class="wpshadow-workflow-review" data-trigger-id="<?php echo esc_attr( $trigger_id ); ?>" data-workflow-id="<?php echo esc_attr( $workflow_id ); ?>"></div>



<div id="workflow-review" class="wpshadow-workflow-review" data-trigger-id="<?php echo esc_attr( $trigger_id ); ?>" data-workflow-id="<?php echo esc_attr( $workflow_id ); ?>"></div>
