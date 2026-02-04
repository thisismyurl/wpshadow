<?php
/**
 * Wizard Step: Review & Save
 *
 * Allows user to review and save the automation.
 *
 * @package WPShadow
 * @subpackage Views
 * @since   1.6030.2148
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get all data from session/workflow
$trigger_id = isset( $_GET['trigger_id'] ) ? sanitize_key( $_GET['trigger_id'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$action_id = isset( $_GET['action_id'] ) ? sanitize_key( $_GET['action_id'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

// Get registries
$triggers = \WPShadow\Workflow\Block_Registry::get_triggers();
$actions = \WPShadow\Workflow\Block_Registry::get_actions();

$trigger_data = $triggers[ $trigger_id ] ?? array();
$action_data = $actions[ $action_id ] ?? array();

// Get trigger config from POST
$frequency = isset( $_GET['frequency'] ) ? sanitize_key( $_GET['frequency'] ) : 'daily'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$time = isset( $_GET['time'] ) ? sanitize_text_field( wp_unslash( $_GET['time'] ) ) : '02:00'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$day = isset( $_GET['day'] ) ? sanitize_key( $_GET['day'] ) : 'monday'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
?>

<h2><?php esc_html_e( 'Review Your Automation', 'wpshadow' ); ?></h2>
<p><?php esc_html_e( 'Here\'s what your automation will do. Click "Save Automation" to activate it.', 'wpshadow' ); ?></p>

<!-- Flow Diagram -->
<div class="wpshadow-review-flow">
	<div class="wpshadow-flow-section">
		<h3><?php esc_html_e( 'When', 'wpshadow' ); ?></h3>
		<p><?php echo esc_html( $trigger_data['label'] ?? 'Unknown Trigger' ); ?></p>
	</div>

	<div class="wpshadow-flow-arrow">
		<span class="dashicons dashicons-arrow-right-alt"></span>
	</div>

	<div class="wpshadow-flow-section">
		<h3><?php esc_html_e( 'Then', 'wpshadow' ); ?></h3>
		<p><?php echo esc_html( $action_data['label'] ?? 'Unknown Action' ); ?></p>
	</div>
</div>

<!-- Details -->
<div class="wpshadow-review-section">
	<div class="wpshadow-review-item">
		<div class="wpshadow-review-item-label"><?php esc_html_e( 'Trigger Details', 'wpshadow' ); ?></div>
		<p class="wpshadow-review-item-value">
			<?php
			if ( $trigger_id === 'time_trigger' ) {
				printf(
					/* translators: %1$s: frequency, %2$s: time */
					esc_html__( '%1$s at %2$s', 'wpshadow' ),
					esc_html( ucfirst( $frequency ) ),
					esc_html( $time )
				);
				if ( $frequency === 'weekly' ) {
					printf( ' %s', esc_html( ucfirst( $day ) ) );
				}
			} else {
				echo esc_html( $trigger_data['description'] ?? 'Configured trigger' );
			}
			?>
		</p>
	</div>

	<div class="wpshadow-review-item">
		<div class="wpshadow-review-item-label"><?php esc_html_e( 'Action Details', 'wpshadow' ); ?></div>
		<p class="wpshadow-review-item-value">
			<?php echo esc_html( $action_data['description'] ?? 'Configured action' ); ?>
		</p>
	</div>
</div>

<!-- Automation Name -->
<div class="wpshadow-form-group">
	<label><?php esc_html_e( 'Automation Name', 'wpshadow' ); ?></label>
	<input 
		type="text" 
		id="automation_name"
		name="automation_name" 
		value="<?php echo isset( $workflow['name'] ) ? esc_attr( $workflow['name'] ) : ''; ?>"
		placeholder="<?php esc_attr_e( 'e.g., Daily backup at 2 AM', 'wpshadow' ); ?>"
		required
	>
	<small><?php esc_html_e( 'Give your automation a descriptive name', 'wpshadow' ); ?></small>
</div>

<!-- Hidden inputs for form submission -->
<input type="hidden" name="trigger_id" value="<?php echo esc_attr( $trigger_id ); ?>">
<input type="hidden" name="action_id" value="<?php echo esc_attr( $action_id ); ?>">
<input type="hidden" name="frequency" value="<?php echo esc_attr( $frequency ); ?>">
<input type="hidden" name="time" value="<?php echo esc_attr( $time ); ?>">
<input type="hidden" name="day" value="<?php echo esc_attr( $day ); ?>">
