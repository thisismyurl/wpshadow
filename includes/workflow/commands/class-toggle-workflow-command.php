<?php
/**
 * Toggle Workflow Command
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
 * Toggle Workflow Command
 */
class Toggle_Workflow_Command extends Command {
	/**
	 * Get command name.
	 *
	 * @return string
	 */
	public static function get_name() {
		return 'toggle_workflow';
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
		$enabled     = isset( $_POST['enabled'] ) ? (bool) $_POST['enabled'] : null;

		if ( empty( $workflow_id ) ) {
			$this->error( __( 'Invalid workflow ID.', 'wpshadow' ) );
			return;
		}

		$workflow = Workflow_Manager::toggle_workflow( $workflow_id, $enabled );

		if ( ! $workflow ) {
			$this->error( __( 'Could not toggle workflow.', 'wpshadow' ) );
			return;
		}

		$this->success(
			array(
				'message'  => __( 'Workflow updated.', 'wpshadow' ),
				'workflow' => $workflow,
			)
		);
	}
}
