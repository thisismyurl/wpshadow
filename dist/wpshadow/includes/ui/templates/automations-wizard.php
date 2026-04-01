<?php
/**
 * Automation Wizard View
 *
 * Simple step-by-step wizard for creating automations.
 * Steps:
 * 1. Select Trigger
 * 2. Configure Trigger
 * 3. Select Action
 * 4. Configure Action
 * 5. Review & Save
 *
 * @package WPShadow
 * @subpackage Views
 * @since 0.6093.1200
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get the current step from URL parameter
$current_step = isset( $_GET['step'] ) ? sanitize_key( wp_unslash( $_GET['step'] ) ) : 'trigger-selection'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

// Allowed steps
$allowed_steps = array(
	'trigger-selection',
	'trigger-config',
	'action-selection',
	'action-config',
	'review',
);

if ( ! in_array( $current_step, $allowed_steps, true ) ) {
	$current_step = 'trigger-selection';
}

// Get workflow data if editing
$workflow = array();
$workflow_id = isset( $_GET['workflow'] ) ? sanitize_key( wp_unslash( $_GET['workflow'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

if ( ! empty( $workflow_id ) ) {
	$workflow = \WPShadow\Workflow\Workflow_Manager::get_workflow( $workflow_id );
}

// Get triggers and actions from registry
$triggers = \WPShadow\Workflow\Block_Registry::get_triggers();
$actions = \WPShadow\Workflow\Block_Registry::get_actions();

// Enqueue wizard assets
wp_enqueue_style( 'wpshadow-wizard-styles', WPSHADOW_URL . 'includes/ui/wizard-steps/wizard-styles.css', array(), WPSHADOW_VERSION );
?>

<div class="wpshadow-wizard-container">
	<!-- Page Header -->
	<div class="wpshadow-wizard-header">
		<h1><?php echo esc_html( $workflow_id ? __( 'Edit Automation', 'wpshadow' ) : __( 'Create New Automation', 'wpshadow' ) ); ?></h1>
		<?php do_action( 'wpshadow_after_page_header' ); ?>
		<p><?php echo esc_html( $workflow_id ? sprintf( __( 'Modify "%s" automation', 'wpshadow' ), esc_html( $workflow['name'] ?? 'Unknown' ) ) : __( 'Build your automation step by step.', 'wpshadow' ) ); ?></p>
	</div>

	<!-- Wizard Progress Bar -->
	<div class="wpshadow-progress-bar">
		<div class="wpshadow-progress-step <?php echo ( $current_step === 'trigger-selection' ) ? 'active' : ''; ?> <?php echo in_array( $current_step, array( 'trigger-config', 'action-selection', 'action-config', 'review' ), true ) ? 'completed' : ''; ?>" data-step="trigger-selection">
			<div class="wpshadow-progress-step-number">1</div>
			<div class="wpshadow-progress-step-label"><?php esc_html_e( 'Select Trigger', 'wpshadow' ); ?></div>
		</div>

		<div class="wpshadow-progress-step <?php echo ( $current_step === 'trigger-config' ) ? 'active' : ''; ?> <?php echo in_array( $current_step, array( 'action-selection', 'action-config', 'review' ), true ) ? 'completed' : ''; ?>" data-step="trigger-config">
			<div class="wpshadow-progress-step-number">2</div>
			<div class="wpshadow-progress-step-label"><?php esc_html_e( 'Configure Trigger', 'wpshadow' ); ?></div>
		</div>

		<div class="wpshadow-progress-step <?php echo ( $current_step === 'action-selection' ) ? 'active' : ''; ?> <?php echo in_array( $current_step, array( 'action-config', 'review' ), true ) ? 'completed' : ''; ?>" data-step="action-selection">
			<div class="wpshadow-progress-step-number">3</div>
			<div class="wpshadow-progress-step-label"><?php esc_html_e( 'Select Action', 'wpshadow' ); ?></div>
		</div>

		<div class="wpshadow-progress-step <?php echo ( $current_step === 'review' ) ? 'active' : ''; ?>" data-step="review">
			<div class="wpshadow-progress-step-number">4</div>
			<div class="wpshadow-progress-step-label"><?php esc_html_e( 'Review & Save', 'wpshadow' ); ?></div>
		</div>
	</div>

	<!-- Wizard Content -->
	<div class="wpshadow-wizard-content">
		<div class="wpshadow-wizard-step active" id="wizard-step">
			<?php
			// Load the appropriate step view
			$step_file = WPSHADOW_PATH . 'includes/ui/workflow-wizard-steps/' . $current_step . '.php';

			if ( file_exists( $step_file ) ) {
				require_once $step_file;
			} else {
				echo '<p>' . esc_html__( 'Step not found.', 'wpshadow' ) . '</p>';
			}
			?>
		</div>
	</div>
</div>

<?php
if ( function_exists( 'wpshadow_render_page_activities' ) ) {
	wpshadow_render_page_activities( 'workflows', 10 );
}
?>
