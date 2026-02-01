<?php
/**
 * Workflow Module - Workflow Builder Page
 *
 * Provides the render function for the workflow builder interface.
 *
 * @package WPShadow
 * @subpackage Workflow
 * @since      1.2601.2148
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load all workflow-related classes
require_once WPSHADOW_PATH . 'includes/workflow/class-workflow-manager.php';
require_once WPSHADOW_PATH . 'includes/workflow/class-workflow-suggestions.php';
require_once WPSHADOW_PATH . 'includes/workflow/class-kanban-workflow-helper.php';
require_once WPSHADOW_PATH . 'includes/workflow/class-block-registry.php';
require_once WPSHADOW_PATH . 'includes/workflow/class-workflow-executor.php';

// Ensure Block_Registry is loaded.
require_once WPSHADOW_PATH . 'includes/workflow/class-block-registry.php';

// Use statement for cleaner code.
use WPShadow\Workflow\Block_Registry;

/**
 * Render the Workflow Builder page
 *
 * Displays either the simplified automations dashboard or the advanced workflow builder
 * based on the current action.
 *
 * @since  1.2601.2148
 * @return void
 */
function wpshadow_render_workflow_builder() {
	// Verify user capabilities.
	if ( ! current_user_can( 'read' ) ) {
		wp_die( esc_html__( 'Insufficient permissions.', 'wpshadow' ) );
	}

	// Get the current action.
	$action = isset( $_GET['action'] ) ? sanitize_key( $_GET['action'] ) : 'list'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	// Check if we should show the builder (create/edit actions) or the dashboard.
	if ( in_array( $action, array( 'create', 'edit' ), true ) ) {
		// Enqueue modal system first.
		wp_enqueue_script(
			'wpshadow-modal',
			WPSHADOW_URL . 'assets/js/wpshadow-modal.js',
			array( 'jquery' ),
			WPSHADOW_VERSION,
			true
		);

		// Enqueue workflow builder assets.
		wp_enqueue_style(
			'wpshadow-workflow-builder',
			WPSHADOW_URL . 'assets/css/workflow-builder.css',
			array( 'wpshadow-design-system' ),
			WPSHADOW_VERSION
		);

		wp_enqueue_script(
			'wpshadow-workflow-builder',
			WPSHADOW_URL . 'assets/js/workflow-builder.js',
			array( 'jquery', 'wpshadow-modal' ),
			WPSHADOW_VERSION,
			true
		);

		// Localize script with data.
		wp_localize_script(
			'wpshadow-workflow-builder',
			'wpshadowWorkflow',
			array(
				'nonce'    => wp_create_nonce( 'wpshadow_workflow_builder' ),
				'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
				'kbUrl'    => 'https://wpshadow.com/kb/workflows',
				'triggers' => Block_Registry::get_triggers(),
				'actions'  => Block_Registry::get_actions(),
				'strings'  => array(
					'saveSuccess'   => __( 'Workflow saved successfully', 'wpshadow' ),
					'saveError'     => __( 'Failed to save workflow', 'wpshadow' ),
					'testSuccess'   => __( 'Test completed successfully', 'wpshadow' ),
					'testError'     => __( 'Test failed', 'wpshadow' ),
					'clearConfirm'  => __( 'Are you sure you want to clear all blocks? This action cannot be undone.', 'wpshadow' ),
					'noBlocks'      => __( 'Add blocks to your workflow first', 'wpshadow' ),
					'dragBlock'     => __( 'Dragging block', 'wpshadow' ),
					'dropSuccess'   => __( 'Block added to workflow', 'wpshadow' ),
					'blockRemoved'  => __( 'Block removed from workflow', 'wpshadow' ),
					'configSaved'   => __( 'Block configuration saved', 'wpshadow' ),
					'buildWorkflow' => __( 'Build Your Workflow', 'wpshadow' ),
					'dragBlocks'    => __( 'Drag blocks from the left to get started', 'wpshadow' ),
					'step1'         => __( 'Add a TRIGGER block (IF condition)', 'wpshadow' ),
					'step2'         => __( 'Add ACTION blocks (THEN what to do)', 'wpshadow' ),
					'step3'         => __( 'Configure each block', 'wpshadow' ),
					'step4'         => __( 'Save and test your workflow', 'wpshadow' ),

					'singleTrigger' => __( 'Only one trigger is allowed per workflow in the free version. Need multiple triggers? Upgrade to WPShadow Pro for unlimited workflow complexity.', 'wpshadow' ),
					'singleAction'  => __( 'Only one action is allowed per workflow in the free version. Need multiple actions? Upgrade to WPShadow Pro for unlimited workflow complexity.', 'wpshadow' ),
				),
			)
		);

		// Load the builder view.
		require_once WPSHADOW_PATH . 'includes/views/workflow-builder.php';
	} else {
		// Enqueue modal system for detail modal.
		wp_enqueue_script(
			'wpshadow-modal',
			WPSHADOW_URL . 'assets/js/wpshadow-modal.js',
			array( 'jquery' ),
			WPSHADOW_VERSION,
			true
		);

		// Enqueue dashboard assets.
		wp_enqueue_script(
			'wpshadow-automations-dashboard',
			WPSHADOW_URL . 'assets/js/automations-dashboard.js',
			array( 'jquery', 'wpshadow-modal' ),
			WPSHADOW_VERSION,
			true
		);

		// Localize script with data.
		wp_localize_script(
			'wpshadow-automations-dashboard',
			'wpshadowAutomationsDashboard',
			array(
				'nonce'   => wp_create_nonce( 'wpshadow_automations' ),
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
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
				),
			)
		);

		// Load the dashboard view.
		require_once WPSHADOW_PATH . 'includes/views/automations-dashboard.php';
	}
}
