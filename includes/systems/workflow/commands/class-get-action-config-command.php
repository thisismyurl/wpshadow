<?php
/**
 * Get Action Config Command
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Workflow\Commands;

use WPShadow\Workflow\Command;
use WPShadow\Workflow\Workflow_Wizard;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get Action Config Command
 */
class Get_Action_Config_Command extends Command {
	/**
	 * Get command name.
	 *
	 * @return string
	 */
	public static function get_name() {
		return 'get_action_config';
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

		$action_id = $this->get_post_var( 'action_id', '' );

		if ( empty( $action_id ) ) {
			$this->error( __( 'Invalid action ID.', 'wpshadow' ) );
			return;
		}

		$fields = Workflow_Wizard::get_action_config( $action_id );

		$this->success( array( 'fields' => $fields ) );
	}
}
