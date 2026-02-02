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
 * @since   1.2601.2148
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get the current step from URL parameter
$current_step = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : 'trigger-selection'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

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
$workflow_id = isset( $_GET['workflow'] ) ? sanitize_key( $_GET['workflow'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

if ( ! empty( $workflow_id ) ) {
	$workflow = \WPShadow\Workflow\Workflow_Manager::get_workflow( $workflow_id );
}

// Get triggers and actions from registry
$triggers = \WPShadow\Workflow\Block_Registry::get_triggers();
$actions = \WPShadow\Workflow\Block_Registry::get_actions();
?>

<div class="wps-page-container wpshadow-automation-wizard">
	<!-- Page Header -->
	<?php wpshadow_render_page_header(
		$workflow_id ? __( 'Edit Automation', 'wpshadow' ) : __( 'Create New Automation', 'wpshadow' ),
		$workflow_id ? 
			sprintf( __( 'Modify "%s" automation', 'wpshadow' ), esc_html( $workflow['name'] ?? 'Unknown' ) ) :
			__( 'Build your automation step by step.', 'wpshadow' ),
		'dashicons-hammer'
	); ?>

	<!-- Wizard Progress Bar -->
	<div class="wpshadow-wizard-progress">
		<div class="wpshadow-wizard-steps">
			<div class="wpshadow-wizard-step <?php echo $current_step === 'trigger-selection' ? 'active' : ''; ?> <?php echo in_array( $current_step, array( 'trigger-config', 'action-selection', 'action-config', 'review' ), true ) ? 'completed' : ''; ?>">
				<div class="wpshadow-step-number">1</div>
				<div class="wpshadow-step-label"><?php esc_html_e( 'Trigger', 'wpshadow' ); ?></div>
			</div>

			<div class="wpshadow-wizard-connector"></div>

			<div class="wpshadow-wizard-step <?php echo $current_step === 'trigger-config' ? 'active' : ''; ?> <?php echo in_array( $current_step, array( 'action-selection', 'action-config', 'review' ), true ) ? 'completed' : ''; ?>">
				<div class="wpshadow-step-number">2</div>
				<div class="wpshadow-step-label"><?php esc_html_e( 'Configure', 'wpshadow' ); ?></div>
			</div>

			<div class="wpshadow-wizard-connector"></div>

			<div class="wpshadow-wizard-step <?php echo $current_step === 'action-selection' ? 'active' : ''; ?> <?php echo in_array( $current_step, array( 'action-config', 'review' ), true ) ? 'completed' : ''; ?>">
				<div class="wpshadow-step-number">3</div>
				<div class="wpshadow-step-label"><?php esc_html_e( 'Action', 'wpshadow' ); ?></div>
			</div>

			<div class="wpshadow-wizard-connector"></div>

			<div class="wpshadow-wizard-step <?php echo $current_step === 'review' ? 'active' : ''; ?>">
				<div class="wpshadow-step-number">4</div>
				<div class="wpshadow-step-label"><?php esc_html_e( 'Review', 'wpshadow' ); ?></div>
			</div>
		</div>
	</div>

	<!-- Wizard Content -->
	<div class="wpshadow-wizard-content wps-card">
		<?php
		// Load the appropriate step view
		$step_file = WPSHADOW_PATH . 'includes/views/wizard-steps/step-' . $current_step . '.php';

		if ( file_exists( $step_file ) ) {
			require_once $step_file;
		} else {
			echo '<p>' . esc_html__( 'Step not found.', 'wpshadow' ) . '</p>';
		}
		?>
	</div>

	<!-- Navigation Buttons -->
	<div class="wpshadow-wizard-nav">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-automations' ) ); ?>" class="wps-btn wps-btn-secondary">
			<?php esc_html_e( 'Cancel', 'wpshadow' ); ?>
		</a>

		<?php if ( $current_step !== 'trigger-selection' ) : ?>
			<button type="button" class="wps-btn wps-btn-secondary wpshadow-wizard-prev-btn" data-step="<?php echo esc_attr( $current_step ); ?>">
				<?php esc_html_e( 'Back', 'wpshadow' ); ?>
			</button>
		<?php endif; ?>

		<?php if ( $current_step !== 'review' ) : ?>
			<button type="button" class="wps-btn wps-btn-primary wpshadow-wizard-next-btn" data-step="<?php echo esc_attr( $current_step ); ?>">
				<?php esc_html_e( 'Next', 'wpshadow' ); ?>
			</button>
		<?php else : ?>
			<button type="button" class="wps-btn wps-btn-primary wpshadow-wizard-save-btn" data-workflow-id="<?php echo esc_attr( $workflow_id ); ?>">
				<?php esc_html_e( 'Save Automation', 'wpshadow' ); ?>
			</button>
		<?php endif; ?>
	</div>
</div>

<style>
/* Wizard Styles */
.wpshadow-automation-wizard {
	max-width: 900px;
	margin: 0 auto;
}

/* Progress Bar */
.wpshadow-wizard-progress {
	margin-bottom: 40px;
	padding: 20px;
	background: white;
	border-radius: 8px;
	border: 1px solid #eee;
}

.wpshadow-wizard-steps {
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 10px;
}

.wpshadow-wizard-step {
	display: flex;
	flex-direction: column;
	align-items: center;
	gap: 8px;
	flex: 1;
	text-align: center;
}

.wpshadow-step-number {
	width: 40px;
	height: 40px;
	display: flex;
	align-items: center;
	justify-content: center;
	background: #f0f0f0;
	border: 2px solid #ddd;
	border-radius: 50%;
	font-weight: 600;
	font-size: 16px;
	color: #666;
	transition: all 0.3s ease;
	margin: 0 auto;
}

.wpshadow-wizard-step.active .wpshadow-step-number {
	background: #2271b1;
	color: white;
	border-color: #2271b1;
}

.wpshadow-wizard-step.completed .wpshadow-step-number {
	background: #5cb85c;
	color: white;
	border-color: #5cb85c;
}

.wpshadow-step-label {
	font-size: 13px;
	font-weight: 500;
	color: #666;
	white-space: nowrap;
	max-width: 80px;
	overflow: hidden;
	text-overflow: ellipsis;
}

.wpshadow-wizard-step.active .wpshadow-step-label {
	color: #2271b1;
	font-weight: 600;
}

.wpshadow-wizard-connector {
	flex: 1;
	height: 2px;
	background: #ddd;
	margin: 0 -10px;
	min-width: 20px;
}

.wpshadow-wizard-step.completed + .wpshadow-wizard-connector {
	background: #5cb85c;
}

/* Wizard Content */
.wpshadow-wizard-content {
	padding: 40px;
	margin-bottom: 30px;
	background: white;
	border-radius: 8px;
}

.wpshadow-wizard-content h2 {
	margin-top: 0;
	margin-bottom: 20px;
	font-size: 20px;
}

.wpshadow-wizard-content p {
	color: #666;
	line-height: 1.6;
	margin-bottom: 20px;
}

/* Trigger/Action Selection Grid */
.wpshadow-trigger-grid,
.wpshadow-action-grid {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
	gap: 15px;
	margin-bottom: 30px;
}

.wpshadow-trigger-option,
.wpshadow-action-option {
	padding: 20px;
	border: 2px solid #eee;
	border-radius: 8px;
	cursor: pointer;
	transition: all 0.2s ease;
	display: flex;
	flex-direction: column;
	gap: 10px;
}

.wpshadow-trigger-option:hover,
.wpshadow-action-option:hover {
	border-color: #2271b1;
	background: #f8f9ff;
}

.wpshadow-trigger-option.selected,
.wpshadow-action-option.selected {
	border-color: #2271b1;
	background: #f0f7ff;
}

.wpshadow-trigger-option input[type="radio"],
.wpshadow-action-option input[type="radio"] {
	cursor: pointer;
}

.wpshadow-option-icon {
	font-size: 24px;
	color: #2271b1;
}

.wpshadow-option-title {
	font-weight: 600;
	font-size: 14px;
	color: #333;
}

.wpshadow-option-description {
	font-size: 12px;
	color: #666;
	line-height: 1.4;
}

/* Configuration Form */
.wpshadow-config-form {
	display: flex;
	flex-direction: column;
	gap: 20px;
}

.wpshadow-form-group {
	display: flex;
	flex-direction: column;
	gap: 8px;
}

.wpshadow-form-group label {
	font-weight: 600;
	font-size: 14px;
	color: #333;
}

.wpshadow-form-group input[type="text"],
.wpshadow-form-group input[type="time"],
.wpshadow-form-group input[type="email"],
.wpshadow-form-group select {
	padding: 10px 12px;
	border: 1px solid #ddd;
	border-radius: 4px;
	font-size: 14px;
	font-family: inherit;
}

.wpshadow-form-group input:focus,
.wpshadow-form-group select:focus {
	outline: none;
	border-color: #2271b1;
	box-shadow: 0 0 0 3px rgba(34, 113, 177, 0.1);
}

.wpshadow-checkbox-group {
	display: flex;
	align-items: center;
	gap: 10px;
}

.wpshadow-checkbox-group input[type="checkbox"] {
	width: 18px;
	height: 18px;
	cursor: pointer;
}

.wpshadow-checkbox-group label {
	margin: 0;
	cursor: pointer;
	font-weight: 400;
}

/* Review Section */
.wpshadow-review-section {
	display: flex;
	flex-direction: column;
	gap: 20px;
}

.wpshadow-review-item {
	padding: 20px;
	background: #f9f9f9;
	border-left: 4px solid #2271b1;
	border-radius: 4px;
}

.wpshadow-review-item-label {
	font-size: 12px;
	text-transform: uppercase;
	color: #999;
	margin-bottom: 8px;
	font-weight: 600;
	letter-spacing: 0.5px;
}

.wpshadow-review-item-value {
	font-size: 16px;
	color: #333;
	margin: 0;
}

.wpshadow-review-flow {
	display: flex;
	align-items: center;
	gap: 15px;
	margin: 20px 0;
	padding: 20px;
	background: #f0f7ff;
	border-radius: 8px;
}

.wpshadow-flow-arrow {
	font-size: 24px;
	color: #2271b1;
}

.wpshadow-flow-section {
	flex: 1;
}

.wpshadow-flow-section h3 {
	margin: 0 0 8px 0;
	font-size: 14px;
	color: #666;
	text-transform: uppercase;
	font-weight: 600;
}

.wpshadow-flow-section p {
	margin: 0;
	font-size: 16px;
	color: #333;
}

/* Navigation */
.wpshadow-wizard-nav {
	display: flex;
	gap: 10px;
	justify-content: flex-end;
	flex-wrap: wrap;
}

.wpshadow-wizard-nav .wps-btn {
	min-width: 120px;
}

/* Responsive */
@media (max-width: 768px) {
	.wpshadow-wizard-content {
		padding: 20px;
	}

	.wpshadow-trigger-grid,
	.wpshadow-action-grid {
		grid-template-columns: 1fr;
	}

	.wpshadow-wizard-steps {
		flex-wrap: wrap;
	}

	.wpshadow-step-label {
		max-width: 100%;
	}

	.wpshadow-wizard-connector {
		display: none;
	}

	.wpshadow-wizard-nav {
		flex-direction: column;
	}

	.wpshadow-wizard-nav .wps-btn {
		width: 100%;
	}
}
</style>
