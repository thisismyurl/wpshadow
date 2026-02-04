<?php
/**
 * Load Workflows Command
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
 * Load Workflows Command
 */
class Load_Workflows_Command extends Command {
	/**
	 * Get command name.
	 *
	 * @return string
	 */
	public static function get_name() {
		return 'load_workflows';
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

		$workflows = Workflow_Manager::get_workflows();

		$this->success(
			array(
				'workflows' => $workflows,
				'count'     => count( $workflows ),
			)
		);
	}
}
