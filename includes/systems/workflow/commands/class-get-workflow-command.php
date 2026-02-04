<?php
/**
 * Get Workflow Command
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
 * Get Workflow Command
 */
class Get_Workflow_Command extends Command {
	/**
	 * Get command name.
	 *
	 * @return string
	 */
	public static function get_name() {
		return 'get_workflow';
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

		$this->success( array( 'workflow' => $workflow ) );
	}
}
