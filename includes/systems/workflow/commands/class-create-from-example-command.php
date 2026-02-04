<?php
/**
 * Create From Example Command
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Workflow\Commands;

use WPShadow\Workflow\Command;
use WPShadow\Workflow\Workflow_Examples;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Create From Example Command
 */
class Create_From_Example_Command extends Command {
	/**
	 * Get command name.
	 *
	 * @return string
	 */
	public static function get_name() {
		return 'create_from_example';
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

		$example_key = $this->get_post_var( 'example_key', '' );

		if ( empty( $example_key ) ) {
			$this->error( __( 'Invalid example key.', 'wpshadow' ) );
			return;
		}

		$result = Workflow_Examples::create_from_example( $example_key );

		if ( isset( $result['error'] ) ) {
			$this->error( $result['error'] );
			return;
		}

		$this->success(
			array(
				'message'  => __( 'Workflow created from example successfully.', 'wpshadow' ),
				'workflow' => $result,
			)
		);
	}
}
