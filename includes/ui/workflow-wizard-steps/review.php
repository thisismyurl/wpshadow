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
_url( 'admin.php?page=wpshadow-automations&action=edit&workflow=' . $workflow_id . '&step=action' ) );
} else {
_url( 'admin.php?page=wpshadow-automations&action=create' ) );
}
exit;
}
?>

<div class="wps-page-container">
<div class="wps-page-header">
esc_html_e( 'Review & Save', 'wpshadow' ); ?></h1>
">
'Review your workflow and give it a name (or we\'ll generate a silly one for you!)', 'wpshadow' ); ?>
id="workflow-summary" class="wps-card wpshadow-workflow-summary"></div>

<div class="wps-card wpshadow-workflow-form-card">
">
class="wps-form">
for="workflow_name" class="wps-form-label">
'Workflow Name', 'wpshadow' ); ?>
put 

ame" 
ame="workflow_name" 
'Leave blank for a randomly generated name', 'wpshadow' ); ?>"
put"
wps-text-sm">
'If left blank, we\'ll generate a silly name like "Brave Balloon" or "Dancing Dolphin"!', 'wpshadow' ); ?>
class="wps-form-actions">
echo esc_url( admin_url( 'admin.php?page=wpshadow-automations' . ( ! empty( $workflow_id ) ? '&action=edit&workflow=' . $workflow_id : '&action=create' ) . '&step=action&trigger=' . $trigger_id ) ); ?>" class="wps-btn wps-btn--secondary">
 class="dashicons dashicons-arrow-left-alt2 wpshadow-icon-compact"></span>
'Back to Actions', 'wpshadow' ); ?>
 type="submit" class="wps-btn wps-btn--primary">
 class="dashicons dashicons-saved wpshadow-icon-compact"></span>
'Save Workflow', 'wpshadow' ); ?>
>
class="save-result"></div>
id="workflow-review" class="wpshadow-workflow-review" data-trigger-id="<?php echo esc_attr( $trigger_id ); ?>" data-workflow-id="<?php echo esc_attr( $workflow_id ); ?>"></div>
