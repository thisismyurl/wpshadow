<?php
/**
 * AJAX Handler: Get Automation Activity
 *
 * Retrieves activity history for a specific automation/workflow.
 *
 * @since   1.6030.2148
 * @package WPShadow\Admin\AJAX
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle AJAX request to get automation activity
 *
 * @since 1.6030.2148
 * @return void Dies with JSON response.
 */
function wpshadow_get_automation_activity_handler() {
	// Verify nonce and capability.
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wpshadow_automations' ) ) {
		wp_send_json_error( array( 'message' => __( 'Security check failed', 'wpshadow' ) ) );
	}

	if ( ! current_user_can( 'read' ) ) {
		wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
	}

	// Get workflow ID.
	$workflow_id = isset( $_POST['workflow_id'] ) ? sanitize_text_field( wp_unslash( $_POST['workflow_id'] ) ) : '';

	if ( empty( $workflow_id ) ) {
		wp_send_json_error( array( 'message' => __( 'Missing workflow ID', 'wpshadow' ) ) );
	}

	// Get activity history for this workflow using Activity_Logger.
	$activity_logger = \WPShadow\Core\Activity_Logger::class;

	// Get recent workflow activity.
	$activity = $activity_logger::get_activity(
		array(
			'type'     => 'workflow_executed',
			'meta_key' => 'workflow_id',
			'meta_value' => $workflow_id,
			'limit'    => 10,
			'orderby'  => 'timestamp',
			'order'    => 'DESC',
		)
	);

	if ( ! is_array( $activity ) ) {
		$activity = array();
	}

	// Format activity for display.
	$formatted_activity = array_map(
		function( $item ) {
			return array(
				'timestamp' => isset( $item['timestamp'] ) ? $item['timestamp'] : time(),
				'message'   => isset( $item['message'] ) ? $item['message'] : __( 'Workflow executed', 'wpshadow' ),
			);
		},
		$activity
	);

	wp_send_json_success( $formatted_activity );
}

add_action( 'wp_ajax_wpshadow_get_automation_activity', 'wpshadow_get_automation_activity_handler' );

/**
 * Handle AJAX request to run automation
 *
 * @since 1.6030.2148
 * @return void Dies with JSON response.
 */
function wpshadow_run_automation_handler() {
	// Verify nonce and capability.
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wpshadow_automations' ) ) {
		wp_send_json_error( array( 'message' => __( 'Security check failed', 'wpshadow' ) ) );
	}

	// SECURITY: Require admin capability for executing automations.
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
	}

	// Get workflow ID.
	$workflow_id = isset( $_POST['workflow_id'] ) ? sanitize_text_field( wp_unslash( $_POST['workflow_id'] ) ) : '';

	if ( empty( $workflow_id ) ) {
		wp_send_json_error( array( 'message' => __( 'Missing workflow ID', 'wpshadow' ) ) );
	}

	// Get the workflow.
	$workflow_manager = \WPShadow\Workflow\Workflow_Manager::class;
	$workflow = $workflow_manager::get_workflow( $workflow_id );

	if ( ! $workflow ) {
		wp_send_json_error( array( 'message' => __( 'Workflow not found', 'wpshadow' ) ) );
	}

	// Execute the workflow.
	try {
		$result = $workflow_manager::execute_workflow( $workflow_id );

		// Log the execution.
		\WPShadow\Core\Activity_Logger::log(
			'workflow_executed',
			array(
				'workflow_id'   => $workflow_id,
				'workflow_name' => $workflow['name'] ?? 'Unknown',
				'result'        => $result,
			)
		);

		wp_send_json_success(
			array(
				'message' => __( 'Automation executed successfully', 'wpshadow' ),
				'result'  => $result,
			)
		);
	} catch ( \Exception $e ) {
		wp_send_json_error(
			array(
				'message' => __( 'Failed to execute automation', 'wpshadow' ),
				'error'   => $e->getMessage(),
			)
		);
	}
}

add_action( 'wp_ajax_wpshadow_run_automation', 'wpshadow_run_automation_handler' );

/**
 * Handle AJAX request to delete automation
 *
 * @since 1.6030.2148
 * @return void Dies with JSON response.
 */
function wpshadow_delete_automation_handler() {
	// Verify nonce and capability.
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wpshadow_automations' ) ) {
		wp_send_json_error( array( 'message' => __( 'Security check failed', 'wpshadow' ) ) );
	}

	// SECURITY: Require admin capability for deleting automations.
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
	}

	// Get workflow ID.
	$workflow_id = isset( $_POST['workflow_id'] ) ? sanitize_text_field( wp_unslash( $_POST['workflow_id'] ) ) : '';

	if ( empty( $workflow_id ) ) {
		wp_send_json_error( array( 'message' => __( 'Missing workflow ID', 'wpshadow' ) ) );
	}

	// Delete the workflow.
	$workflow_manager = \WPShadow\Workflow\Workflow_Manager::class;

	try {
		$result = $workflow_manager::delete_workflow( $workflow_id );

		if ( $result ) {
			// Log the deletion.
			\WPShadow\Core\Activity_Logger::log(
				'workflow_deleted',
				array(
					'workflow_id' => $workflow_id,
				)
			);

			wp_send_json_success( array( 'message' => __( 'Automation deleted successfully', 'wpshadow' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to delete automation', 'wpshadow' ) ) );
		}
	} catch ( \Exception $e ) {
		wp_send_json_error(
			array(
				'message' => __( 'Failed to delete automation', 'wpshadow' ),
				'error'   => $e->getMessage(),
			)
		);
	}
}

add_action( 'wp_ajax_wpshadow_delete_automation', 'wpshadow_delete_automation_handler' );

/**
 * Handle AJAX request to toggle automation enabled/disabled
 *
 * @since 1.6030.2148
 * @return void Dies with JSON response.
 */
function wpshadow_toggle_automation_handler() {
	// Verify nonce and capability.
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wpshadow_automations' ) ) {
		wp_send_json_error( array( 'message' => __( 'Security check failed', 'wpshadow' ) ) );
	}

	// SECURITY: Require admin capability for toggling automations.
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
	}

	// Get workflow ID and enabled status.
	$workflow_id = isset( $_POST['workflow_id'] ) ? sanitize_text_field( wp_unslash( $_POST['workflow_id'] ) ) : '';
	$enabled = isset( $_POST['enabled'] ) ? rest_sanitize_boolean( $_POST['enabled'] ) : false;

	if ( empty( $workflow_id ) ) {
		wp_send_json_error( array( 'message' => __( 'Missing workflow ID', 'wpshadow' ) ) );
	}

	// Get the workflow.
	$workflow_manager = \WPShadow\Workflow\Workflow_Manager::class;
	$workflow = $workflow_manager::get_workflow( $workflow_id );

	if ( ! $workflow ) {
		wp_send_json_error( array( 'message' => __( 'Workflow not found', 'wpshadow' ) ) );
	}

	// Update the workflow's enabled status.
	$workflow['enabled'] = $enabled;

	try {
		$result = $workflow_manager::save_workflow( $workflow_id, $workflow );

		if ( $result ) {
			// Log the status change.
			$action = $enabled ? 'workflow_enabled' : 'workflow_disabled';
			\WPShadow\Core\Activity_Logger::log(
				$action,
				array(
					'workflow_id'   => $workflow_id,
					'workflow_name' => $workflow['name'] ?? 'Unknown',
				)
			);

			wp_send_json_success(
				array(
					'message' => $enabled ? __( 'Automation enabled', 'wpshadow' ) : __( 'Automation disabled', 'wpshadow' ),
				)
			);
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to update automation status', 'wpshadow' ) ) );
		}
	} catch ( \Exception $e ) {
		wp_send_json_error(
			array(
				'message' => __( 'Failed to update automation status', 'wpshadow' ),
				'error'   => $e->getMessage(),
			)
		);
	}
}

add_action( 'wp_ajax_wpshadow_toggle_automation', 'wpshadow_toggle_automation_handler' );
