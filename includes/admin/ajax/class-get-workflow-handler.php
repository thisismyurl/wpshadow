<?php
declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Workflow\Workflow_Manager;

/**
 * AJAX Handler: Get Workflow
 *
 * Retrieves a single workflow by ID.
 * Action: wp_ajax_wpshadow_get_workflow
 * Nonce: wpshadow_workflow
 * Capability: manage_options
 */
class Get_Workflow_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX hook
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_get_workflow', [ __CLASS__, 'handle' ] );
	}

	/**
	 * Handle AJAX request
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_workflow', 'manage_options' );

		$workflow_id = self::get_post_param( 'workflow_id', 'key', '', true );

		if ( empty( $workflow_id ) ) {
			self::send_error( 'Invalid workflow ID.' );
			return;
		}

		$workflow = Workflow_Manager::get_workflow( $workflow_id );

		if ( ! $workflow ) {
			self::send_error( 'Workflow not found.' );
			return;
		}

		self::send_success( [ 'workflow' => $workflow ] );
	}
}
