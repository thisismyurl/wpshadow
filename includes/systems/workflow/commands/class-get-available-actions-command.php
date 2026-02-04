<?php
/**
 * Get Available Actions Command
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
 * Get Available Actions Command
 */
class Get_Available_Actions_Command extends Command {
	/**
	 * Get command name.
	 *
	 * @return string
	 */
	public static function get_name() {
		return 'get_available_actions';
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

		$diagnostics = Workflow_Manager::get_available_diagnostics();
		$treatments  = Workflow_Manager::get_available_treatments();

		$this->success(
			array(
				'diagnostics' => $diagnostics,
				'treatments'  => $treatments,
			)
		);
	}
}
