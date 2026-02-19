<?php
/**
 * Workflow Module - Workflow Builder Page
 *
 * Provides the render function for the workflow builder interface.
 *
 * @package WPShadow
 * @subpackage Workflow
 * @since      1.6030.2148
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load all workflow-related classes
require_once WPSHADOW_PATH . 'includes/systems/workflow/class-workflow-manager.php';
require_once WPSHADOW_PATH . 'includes/systems/workflow/class-workflow-wizard.php';
require_once WPSHADOW_PATH . 'includes/systems/workflow/class-workflow-suggestions.php';
require_once WPSHADOW_PATH . 'includes/systems/workflow/class-kanban-workflow-helper.php';
require_once WPSHADOW_PATH . 'includes/systems/workflow/class-block-registry.php';
require_once WPSHADOW_PATH . 'includes/systems/workflow/class-workflow-executor.php';

// Load AJAX handlers
require_once WPSHADOW_PATH . 'includes/admin/ajax/save-workflow-handler.php';

// Use statement for cleaner code.
use WPShadow\Workflow\Block_Registry;

/**
 * Render the Workflow Builder page
 *
 * Displays either the simplified automations dashboard or the advanced workflow builder
 * based on the current action.
 *
 * @since  1.6030.2148
 * @return void
 */
function wpshadow_render_workflow_builder() {
	// Verify user capabilities.
	if ( ! current_user_can( 'read' ) ) {
		wp_die( esc_html__( 'Insufficient permissions.', 'wpshadow' ) );
	}

	// Get the current action.
	$action = isset( $_GET['action'] ) ? sanitize_key( $_GET['action'] ) : 'list'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	// Route to the wizard for create/edit actions (new simplified interface).
	if ( in_array( $action, array( 'create', 'edit' ), true ) ) {
		// Load the automations wizard
		require_once WPSHADOW_PATH . 'includes/ui/templates/automations-wizard.php';
		return;
	} else {
		// Enqueue shared modal system.
		\WPShadow\Core\Admin_Asset_Registry::enqueue_modal_assets();

		// Enqueue dashboard assets.
		wp_enqueue_script(
			'wpshadow-automations-dashboard',
			WPSHADOW_URL . 'assets/js/automations-dashboard.js',
			array( 'jquery', 'wpshadow-modal' ),
			WPSHADOW_VERSION,
			true
		);

		// Localize script with data.
		\WPShadow\Core\Admin_Asset_Registry::localize_with_ajax_nonce(
			'wpshadow-automations-dashboard',
			'wpshadowAutomationsDashboard',
			'wpshadow_automations',
			array(
				'strings' => array(
					'confirmDelete'        => __( 'Are you sure you want to delete this automation? This action cannot be undone.', 'wpshadow' ),
					'deleteSuccess'        => __( 'Automation deleted successfully', 'wpshadow' ),
					'deleteError'          => __( 'Failed to delete automation', 'wpshadow' ),
					'runSuccess'           => __( 'Automation executed successfully', 'wpshadow' ),
					'runError'             => __( 'Failed to run automation', 'wpshadow' ),
					'toggleSuccess'        => __( 'Automation status updated', 'wpshadow' ),
					'toggleError'          => __( 'Failed to update automation status', 'wpshadow' ),
					'noActivity'           => __( 'No activity recorded', 'wpshadow' ),
					'errorLoadingActivity' => __( 'Error loading activity', 'wpshadow' ),
					'loadingActivity'      => __( 'Loading activity...', 'wpshadow' ),
					'createAutomation'     => __( 'Create Automation', 'wpshadow' ),
					'createdSuccess'       => __( 'Automation created! Next suggestion loaded.', 'wpshadow' ),
				),
			)
		);

		// Load the dashboard view.
		require_once WPSHADOW_PATH . 'includes/ui/templates/automations-dashboard.php';
	}
}
