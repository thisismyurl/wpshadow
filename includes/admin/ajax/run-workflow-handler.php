<?php
/**
 * AJAX Handler: Execute Workflow Immediately
 *
 * Manually triggers a workflow on-demand without waiting for scheduled time.
 * User has full control - can run anytime, see real-time progress.
 *
 * **Workflow Execution:**
 * - Load workflow configuration by ID
 * - Execute action sequence in order (scan, fix, notify, etc.)
 * - Stream progress updates via AJAX
 * - Return results with metrics and impact
 *
 * **User Scenarios:**
 * - Test workflow before scheduling
 * - Run immediately when urgent issue arises
 * - Manual override for scheduled automation
 * - Check if workflow still works correctly
 *
 * **Philosophy Alignment:**
 * - #1 (Helpful Neighbor): Give user full control over automation
 * - #8 (Inspire Confidence): Show progress in real-time
 * - #9 (Show Value): Report impact metrics after execution
 *
 * @package WPShadow
 * @since 1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Workflow\Workflow_Manager;
use WPShadow\Workflow\Workflow_Executor;
 * Capability: manage_options
 */
class Run_Workflow_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX hook
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_run_workflow', array( __CLASS__, 'handle' ) );
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
		$context = array(
			'trigger_type' => 'manual',
			'user_id'      => get_current_user_id(),
			'timestamp'    => time(),
		);

		$result = Workflow_Executor::execute_workflow( $workflow, $context );

		self::send_success(
			array(
				'message' => 'Workflow executed successfully.',
				'result'  => $result,
			)
		);
	}
}
