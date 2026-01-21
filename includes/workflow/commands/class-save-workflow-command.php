<?php
/**
 * Save Workflow Command
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Workflow\Commands;

use WPShadow\Workflow\Command;
use WPShadow\Workflow\Block_Registry;
use WPShadow\Workflow\Workflow_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Save Workflow Command
 */
class Save_Workflow_Command extends Command {
	/**
	 * Get command name.
	 *
	 * @return string
	 */
	public static function get_name() {
		return 'save_workflow';
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

		$name        = $this->get_post_var( 'name', '' );
		$blocks      = isset( $_POST['blocks'] ) ? json_decode( wp_unslash( $_POST['blocks'] ), true ) : array();
		$workflow_id = $this->get_post_var( 'workflow_id', '' );
		$workflow_id = ( '' === $workflow_id ) ? null : $workflow_id;

		// Validate blocks not empty
		if ( empty( $blocks ) ) {
			$this->error( __( 'Workflow must contain at least one block.', 'wpshadow' ) );
			return;
		}

		// Validate each block
		foreach ( $blocks as $block ) {
			$result = Block_Registry::validate_block( $block );
			if ( ! $result['valid'] ) {
				$this->error(
					sprintf(
						__( 'Invalid block: %s', 'wpshadow' ),
						$result['error']
					)
				);
				return;
			}
		}

		// Save the workflow
		$workflow = Workflow_Manager::save_workflow( $name, $blocks, $workflow_id );

		$this->success(
			array(
				'message'  => __( 'Workflow saved successfully.', 'wpshadow' ),
				'workflow' => $workflow,
			)
		);
	}
}
