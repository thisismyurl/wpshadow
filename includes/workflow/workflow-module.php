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

// Ensure Block_Registry is loaded.
require_once WPSHADOW_PATH . 'includes/workflow/class-block-registry.php';

// Use statement for cleaner code.
use WPShadow\Workflow\Block_Registry;

/**
 * Render the Workflow Builder page
 *
 * @since  1.2601.2148
 * @return void
 */
function wpshadow_render_workflow_builder() {
	// Verify user capabilities.
	if ( ! current_user_can( 'read' ) ) {
		wp_die( esc_html__( 'Insufficient permissions.', 'wpshadow' ) );
	}

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

	// Load the view.
	require_once WPSHADOW_PATH . 'includes/views/workflow-builder.php';
}
