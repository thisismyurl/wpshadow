<?php
declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Workflow\Workflow_Manager;
use WPShadow\Workflow\Workflow_Executor;

/**
 * AJAX Handler: Run Workflow
 *
 * Manually executes a workflow.
 * Action: wp_ajax_wpshadow_run_workflow
 * Nonce: wpshadow_workflow
 * Capability: manage_options
 */
class Run_Workflow_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX hook
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_run_workflow', [ __CLASS__, 'handle' ] );
	}

	/**
	 * Handle AJAX request
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_workflow', 'manage_options' );

		$workflow_id = self::get_post_param( 'workflow_id', 'key', '', true );

		if ( empty( $workflow_id ) ) {
			self::send_error( 'Invalid workflow ID.' );
			return;
		}

		$workflow = Workflow_Manager::get_workflow( $workflow_id );

		if ( ! $workflow ) {
			self::send_error( 'Workflow not found.' );
			return;
		}

		// Execute workflow with manual trigger context
		$context = [
			'trigger_type' => 'manual',
			'user_id'      => get_current_user_id(),
			'timestamp'    => time(),
		];

		$result = Workflow_Executor::execute_workflow( $workflow, $context );

		self::send_success( [
			'message' => 'Workflow executed successfully.',
			'result'  => $result,
		] );
	}
}
