<?php
/**
 * Workflow Wizard View (Workflow Builder step-by-step)
 *
 * @package WPShadow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$action      = isset( $_GET['action'] ) ? sanitize_key( $_GET['action'] ) : 'create';
$workflow_id = isset( $_GET['workflow'] ) ? sanitize_text_field( $_GET['workflow'] ) : '';
$step        = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : 'trigger';

// If editing, load workflow
$workflow = null;
if ( $action === 'edit' && $workflow_id ) {
	$workflows = \WPShadow\Workflow\Workflow_Manager::get_workflows();
	foreach ( $workflows as $wf ) {
		if ( $wf['id'] === $workflow_id ) {
			$workflow = $wf;
			break;
		}
	}
}
?>

<div class="wrap wpshadow-workflow-wizard">
	<h1>
		<?php
		if ( $action === 'edit' ) {
			esc_html_e( 'Edit Workflow', 'wpshadow' );
		} else {
			esc_html_e( 'Create Workflow', 'wpshadow' );
		}
		?>
	</h1>

	<!-- Progress Steps -->
	<div class="wizard-progress">
		<div class="progress-step <?php echo $step === 'trigger' ? 'active' : ( $step === 'action' || $step === 'review' ? 'completed' : '' ); ?>">
			<span class="step-number">1</span>
			<span class="step-label"><?php esc_html_e( 'Choose Trigger', 'wpshadow' ); ?></span>
		</div>
		<div class="progress-step <?php echo $step === 'action' ? 'active' : ( $step === 'review' ? 'completed' : '' ); ?>">
			<span class="step-number">2</span>
			<span class="step-label"><?php esc_html_e( 'Choose Action', 'wpshadow' ); ?></span>
		</div>
		<div class="progress-step <?php echo $step === 'review' ? 'active' : ''; ?>">
			<span class="step-number">3</span>
			<span class="step-label"><?php esc_html_e( 'Review & Save', 'wpshadow' ); ?></span>
		</div>
	</div>

	<!-- Wizard Content -->
	<div class="wizard-content">
		<?php
		switch ( $step ) {
			case 'trigger':
				include __DIR__ . '/workflow-wizard-steps/trigger-selection.php';
				break;
			case 'trigger-config':
				include __DIR__ . '/workflow-wizard-steps/trigger-config.php';
				break;
			case 'action':
				include __DIR__ . '/workflow-wizard-steps/action-selection.php';
				break;
			case 'action-config':
				include __DIR__ . '/workflow-wizard-steps/action-config.php';
				break;
			case 'review':
				include __DIR__ . '/workflow-wizard-steps/review.php';
				break;
			default:
				include __DIR__ . '/workflow-wizard-steps/trigger-selection.php';
		}
		?>
	</div>
</div>

<style>
.wpshadow-workflow-wizard {
	max-width: 1200px;
}

/* Progress Steps */
.wizard-progress {
	display: flex;
	justify-content: space-between;
	margin: 30px 0 40px 0;
	position: relative;
}

.wizard-progress::before {
	content: '';
	position: absolute;
	top: 20px;
	left: 60px;
	right: 60px;
	height: 2px;
	background: #ddd;
	z-index: 0;
}

.progress-step {
	flex: 1;
	text-align: center;
	position: relative;
	z-index: 1;
}

.step-number {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 40px;
	height: 40px;
	border-radius: 50%;
	background: #fff;
	border: 2px solid #ddd;
	font-weight: 600;
	margin-bottom: 8px;
	transition: all 0.3s ease;
}

.progress-step.active .step-number {
	background: #2271b1;
	border-color: #2271b1;
	color: #fff;
}

.progress-step.completed .step-number {
	background: #00a32a;
	border-color: #00a32a;
	color: #fff;
}

.progress-step.completed .step-number::before {
	content: '✓';
}

.step-label {
	display: block;
	font-size: 13px;
	color: #666;
	font-weight: 500;
}

.progress-step.active .step-label {
	color: #2271b1;
	font-weight: 600;
}

/* Wizard Content */
.wizard-content {
	background: #fff;
	border: 1px solid #ddd;
	border-radius: 8px;
	padding: 30px;
	min-height: 400px;
}
</style>
