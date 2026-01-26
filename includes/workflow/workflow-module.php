<?php
/**
 * Workflow Module - Visual Workflow Builder Page
 *
 * Provides the render function for the visual workflow builder interface.
 *
 * @package WPShadow
 * @subpackage Workflow
 * @since      1.2601.2148
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the Visual Workflow Builder page
 *
 * @since  1.2601.2148
 * @return void
 */
function wpshadow_render_workflow_builder() {
	// Verify user capabilities.
	if ( ! current_user_can( 'read' ) ) {
		wp_die( esc_html__( 'Insufficient permissions.', 'wpshadow' ) );
	}

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
		array( 'jquery' ),
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
				'saveSuccess'  => __( 'Workflow saved successfully', 'wpshadow' ),
				'saveError'    => __( 'Failed to save workflow', 'wpshadow' ),
				'testSuccess'  => __( 'Test completed successfully', 'wpshadow' ),
				'testError'    => __( 'Test failed', 'wpshadow' ),
				'clearConfirm' => __( 'Are you sure you want to clear all blocks? This action cannot be undone.', 'wpshadow' ),
				'noBlocks'     => __( 'Add blocks to your workflow first', 'wpshadow' ),
				'dragBlock'    => __( 'Dragging block', 'wpshadow' ),
				'dropSuccess'  => __( 'Block added to workflow', 'wpshadow' ),
				'blockRemoved' => __( 'Block removed from workflow', 'wpshadow' ),
				'configSaved'  => __( 'Block configuration saved', 'wpshadow' ),
			),
		)
	);

	// Load the view.
	require_once WPSHADOW_PATH . 'includes/views/workflow-builder.php';
}
