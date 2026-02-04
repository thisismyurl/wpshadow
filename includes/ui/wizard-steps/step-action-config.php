<?php
/**
 * Wizard Step: Action Configuration
 *
 * Allows user to configure the selected action.
 *
 * @package WPShadow
 * @subpackage Views
 * @since   1.6030.2148
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get selected action from session
$selected_action = isset( $_GET['action_id'] ) ? sanitize_key( $_GET['action_id'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

if ( empty( $selected_action ) ) {
	echo '<p>' . esc_html__( 'Please select an action first.', 'wpshadow' ) . '</p>';
	return;
}

// Get action config from workflow if editing
$action_config = array();
if ( ! empty( $workflow['blocks'] ) ) {
	foreach ( $workflow['blocks'] as $block ) {
		if ( $block['type'] === 'action' && $block['id'] === $selected_action ) {
			$action_config = $block['config'] ?? array();
			break;
		}
	}
}

// Get action from registry
$actions = \WPShadow\Workflow\Block_Registry::get_actions();
$action_data = $actions[ $selected_action ] ?? array();
?>

<h2><?php echo esc_html( $action_data['label'] ?? __( 'Configure Action', 'wpshadow' ) ); ?></h2>
<p><?php echo esc_html( $action_data['description'] ?? '' ); ?></p>

<div class="wpshadow-config-form">
	<?php
	// Show configuration options based on action type
	if ( $selected_action === 'email_notification' ) :
		$email_recipients = $action_config['recipients'] ?? '';
		?>

		<div class="wpshadow-form-group">
			<label><?php esc_html_e( 'Email Recipients', 'wpshadow' ); ?></label>
			<input 
				type="text" 
				name="email_recipients" 
				value="<?php echo esc_attr( $email_recipients ); ?>"
				placeholder="<?php esc_attr_e( 'Enter email address(es)', 'wpshadow' ); ?>"
			>
			<small><?php esc_html_e( 'Separate multiple emails with commas', 'wpshadow' ); ?></small>
		</div>

		<div class="wpshadow-form-group">
			<label><?php esc_html_e( 'Email Subject', 'wpshadow' ); ?></label>
			<input 
				type="text" 
				name="email_subject" 
				value="<?php echo esc_attr( $action_config['subject'] ?? '' ); ?>"
				placeholder="<?php esc_attr_e( 'e.g., Automation Report', 'wpshadow' ); ?>"
			>
		</div>

	<?php elseif ( in_array( $selected_action, array( 'backup_action', 'clear_cache_action' ), true ) ) : ?>
		<p><?php esc_html_e( 'This action requires no additional configuration.', 'wpshadow' ); ?></p>

	<?php else : ?>
		<p><?php esc_html_e( 'This action requires no additional configuration.', 'wpshadow' ); ?></p>
	<?php endif; ?>
</div>

<input type="hidden" name="action_id" value="<?php echo esc_attr( $selected_action ); ?>">
