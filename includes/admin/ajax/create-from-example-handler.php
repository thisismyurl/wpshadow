<?php
declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Workflow\Workflow_Examples;

/**
 * AJAX Handler: Create From Example
 *
 * Creates a workflow from an example template.
 * Action: wp_ajax_wpshadow_create_from_example
 * Nonce: wpshadow_workflow
 * Capability: manage_options
 */
class Create_From_Example_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX hook
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_create_from_example', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle AJAX request
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_workflow', 'manage_options' );

		$example_key = self::get_post_param( 'example_key', 'key', '', true );

		if ( empty( $example_key ) ) {
			self::send_error( 'Invalid example key.' );
			return;
		}

		$result = Workflow_Examples::create_from_example( $example_key );

		if ( isset( $result['error'] ) ) {
			self::send_error( $result['error'] );
			return;
		}

		self::send_success(
			array(
				'message'  => 'Workflow created from example successfully.',
				'workflow' => $result,
			)
		);
	}
}
