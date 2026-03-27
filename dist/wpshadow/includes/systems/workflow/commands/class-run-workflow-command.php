<?php
/**
 * Run Workflow Command
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Workflow\Commands;

use WPShadow\Workflow\Command;
use WPShadow\Workflow\Workflow_Manager;
use WPShadow\Workflow\Workflow_Executor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Run Workflow Command
 */
class Run_Workflow_Command extends Command {
	/**
	 * Get command name.
	 *
	 * @return string
	 */
	public static function get_name() {
		return 'run_workflow';
	}

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function execute() {
		if ( ! $this->verify_request() ) {
			return;
		}

		$workflow_id = $this->get_post_var( 'workflow_id', '' );

		if ( empty( $workflow_id ) ) {
			$this->error( __( 'Invalid workflow ID.', 'wpshadow' ) );
			return;
		}

		$workflow = Workflow_Manager::get_workflow( $workflow_id );

		if ( ! $workflow ) {
			$this->error( __( 'Workflow not found.', 'wpshadow' ) );
			return;
		}

		// Create execution context
		$context = array(
			'trigger_type' => 'manual',
			'user_id'      => get_current_user_id(),
			'timestamp'    => time(),
		);

		// Execute the workflow
		$result = Workflow_Executor::execute_workflow( $workflow, $context );

		$this->success(
			array(
				'message' => __( 'Workflow executed successfully.', 'wpshadow' ),
				'result'  => $result,
			)
		);
	}
}
