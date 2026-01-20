<?php
/**
 * Workflow AJAX Handlers
 *
 * @package WPShadow
 * @subpackage Workflow
 */

namespace WPShadow\Workflow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Save a workflow
 */
add_action( 'wp_ajax_wpshadow_save_workflow', function() {
	check_ajax_referer( 'wpshadow_workflow', 'nonce' );
	
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Insufficient permissions.' ) );
	}
	
	$name        = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
	$blocks      = isset( $_POST['blocks'] ) ? json_decode( wp_unslash( $_POST['blocks'] ), true ) : array();
	$workflow_id = isset( $_POST['workflow_id'] ) ? sanitize_key( $_POST['workflow_id'] ) : null;
	
	if ( empty( $blocks ) ) {
		wp_send_json_error( array( 'message' => 'Workflow must contain at least one block.' ) );
	}
	
	// Validate blocks
	foreach ( $blocks as $block ) {
		$result = Block_Registry::validate_block( $block );
		if ( ! $result['valid'] ) {
			wp_send_json_error( array( 'message' => 'Invalid block: ' . $result['error'] ) );
		}
	}
	
	$workflow = Workflow_Manager::save_workflow( $name, $blocks, $workflow_id );
	
	wp_send_json_success( array(
		'message'  => 'Workflow saved successfully.',
		'workflow' => $workflow,
	) );
} );

/**
 * Load all workflows
 */
add_action( 'wp_ajax_wpshadow_load_workflows', function() {
	check_ajax_referer( 'wpshadow_workflow', 'nonce' );
	
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Insufficient permissions.' ) );
	}
	
	$workflows = Workflow_Manager::get_workflows();
	
	wp_send_json_success( array(
		'workflows' => $workflows,
		'count'     => count( $workflows ),
	) );
} );

/**
 * Load a single workflow
 */
add_action( 'wp_ajax_wpshadow_get_workflow', function() {
	check_ajax_referer( 'wpshadow_workflow', 'nonce' );
	
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Insufficient permissions.' ) );
	}
	
	$workflow_id = isset( $_POST['workflow_id'] ) ? sanitize_key( $_POST['workflow_id'] ) : '';
	
	if ( empty( $workflow_id ) ) {
		wp_send_json_error( array( 'message' => 'Invalid workflow ID.' ) );
	}
	
	$workflow = Workflow_Manager::get_workflow( $workflow_id );
	
	if ( ! $workflow ) {
		wp_send_json_error( array( 'message' => 'Workflow not found.' ) );
	}
	
	wp_send_json_success( array( 'workflow' => $workflow ) );
} );

/**
 * Delete a workflow
 */
add_action( 'wp_ajax_wpshadow_delete_workflow', function() {
	check_ajax_referer( 'wpshadow_workflow', 'nonce' );
	
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Insufficient permissions.' ) );
	}
	
	$workflow_id = isset( $_POST['workflow_id'] ) ? sanitize_key( $_POST['workflow_id'] ) : '';
	
	if ( empty( $workflow_id ) ) {
		wp_send_json_error( array( 'message' => 'Invalid workflow ID.' ) );
	}
	
	$result = Workflow_Manager::delete_workflow( $workflow_id );
	
	if ( ! $result ) {
		wp_send_json_error( array( 'message' => 'Could not delete workflow.' ) );
	}
	
	wp_send_json_success( array( 'message' => 'Workflow deleted successfully.' ) );
} );

/**
 * Toggle workflow enabled status
 */
add_action( 'wp_ajax_wpshadow_toggle_workflow', function() {
	check_ajax_referer( 'wpshadow_workflow', 'nonce' );
	
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Insufficient permissions.' ) );
	}
	
	$workflow_id = isset( $_POST['workflow_id'] ) ? sanitize_key( $_POST['workflow_id'] ) : '';
	$enabled     = isset( $_POST['enabled'] ) ? (bool) $_POST['enabled'] : null;
	
	if ( empty( $workflow_id ) ) {
		wp_send_json_error( array( 'message' => 'Invalid workflow ID.' ) );
	}
	
	$workflow = Workflow_Manager::toggle_workflow( $workflow_id, $enabled );
	
	if ( ! $workflow ) {
		wp_send_json_error( array( 'message' => 'Could not toggle workflow.' ) );
	}
	
	wp_send_json_success( array(
		'message'  => 'Workflow updated.',
		'workflow' => $workflow,
	) );
} );

/**
 * Generate a silly default workflow name
 */
add_action( 'wp_ajax_wpshadow_generate_workflow_name', function() {
	check_ajax_referer( 'wpshadow_workflow', 'nonce' );
	
	$name = Workflow_Manager::generate_silly_name();
	
	wp_send_json_success( array( 'name' => $name ) );
} );

/**
 * Get available diagnostics and treatments
 */
add_action( 'wp_ajax_wpshadow_get_available_actions', function() {
	check_ajax_referer( 'wpshadow_workflow', 'nonce' );
	
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Insufficient permissions.' ) );
	}
	
	$diagnostics = Workflow_Manager::get_available_diagnostics();
	$treatments  = Workflow_Manager::get_available_treatments();
	
	wp_send_json_success( array(
		'diagnostics' => $diagnostics,
		'treatments'  => $treatments,
	) );
} );

/**
 * Get action configuration fields (for wizard)
 */
add_action( 'wp_ajax_wpshadow_get_action_config', function() {
	check_ajax_referer( 'wpshadow_workflow', 'nonce' );
	
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Insufficient permissions.' ) );
	}
	
	$action_id = isset( $_POST['action_id'] ) ? sanitize_key( $_POST['action_id'] ) : '';
	
	if ( empty( $action_id ) ) {
		wp_send_json_error( array( 'message' => 'Invalid action ID.' ) );
	}
	
	$fields = Workflow_Wizard::get_action_config( $action_id );
	
	wp_send_json_success( array( 'fields' => $fields ) );
} );

/**
 * Save workflow from wizard (new format)
 */
add_action( 'wp_ajax_wpshadow_save_workflow', function() {
	check_ajax_referer( 'wpshadow_workflow', 'nonce' );
	
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Insufficient permissions.' ) );
	}
	
	$workflow_json = isset( $_POST['workflow'] ) ? wp_unslash( $_POST['workflow'] ) : '';
	
	if ( empty( $workflow_json ) ) {
		wp_send_json_error( array( 'message' => 'No workflow data provided.' ) );
	}
	
	$wizard_data = json_decode( $workflow_json, true );
	
	if ( ! $wizard_data ) {
		wp_send_json_error( array( 'message' => 'Invalid workflow data.' ) );
	}
	
	// Convert wizard format to executor format
	$workflow = Workflow_Wizard::convert_to_executor_format( $wizard_data );
	
	// Save workflow
	$workflows = get_option( 'wpshadow_workflows', array() );
	
	// Generate silly name if empty
	if ( empty( $workflow['name'] ) ) {
		$workflow['name'] = Workflow_Manager::generate_silly_name();
	}
	
	$workflows[ $workflow['id'] ] = $workflow;
	update_option( 'wpshadow_workflows', $workflows );
	
	wp_send_json_success( array(
		'message'  => 'Workflow saved successfully.',
		'workflow' => $workflow,
	) );
} );

/**
 * Run workflow manually
 */
add_action( 'wp_ajax_wpshadow_run_workflow', function() {
	check_ajax_referer( 'wpshadow_workflow', 'nonce' );
	
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Insufficient permissions.' ) );
	}
	
	$workflow_id = isset( $_POST['workflow_id'] ) ? sanitize_key( $_POST['workflow_id'] ) : '';
	
	if ( empty( $workflow_id ) ) {
		wp_send_json_error( array( 'message' => 'Invalid workflow ID.' ) );
	}
	
	$workflow = Workflow_Manager::get_workflow( $workflow_id );
	
	if ( ! $workflow ) {
		wp_send_json_error( array( 'message' => 'Workflow not found.' ) );
	}
	
	// Execute workflow with manual trigger context
	$context = array(
		'trigger_type' => 'manual',
		'user_id'      => get_current_user_id(),
		'timestamp'    => time(),
	);
	
	$result = Workflow_Executor::execute_workflow( $workflow, $context );
	
	wp_send_json_success( array(
		'message' => 'Workflow executed successfully.',
		'result'  => $result,
	) );
} );
