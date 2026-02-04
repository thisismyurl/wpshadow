<?php
/**
 * Delete Workflow Command
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Workflow\Commands;

use WPShadow\Workflow\Command;
use WPShadow\Workflow\Workflow_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Delete Workflow Command
 */
class Delete_Workflow_Command extends Command {
	/**
	 * Get command name.
	 *
	 * @return string
	 */
	public static function get_name() {
		return 'delete_workflow';
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

		$result = Workflow_Manager::delete_workflow( $workflow_id );

		if ( ! $result ) {
			$this->error( __( 'Could not delete workflow.', 'wpshadow' ) );
			return;
		}

		$this->success(
			array( 'message' => __( 'Workflow deleted successfully.', 'wpshadow' ) )
		);
	}
}
