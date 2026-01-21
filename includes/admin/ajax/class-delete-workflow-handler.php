<?php
declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Workflow\Workflow_Manager;
use WPShadow\Core\Activity_Logger;

/**
 * AJAX Handler: Delete Workflow
 *
 * Deletes a workflow by ID.
 * Action: wp_ajax_wpshadow_delete_workflow
 * Nonce: wpshadow_workflow
 * Capability: manage_options
 */
class Delete_Workflow_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX hook
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_delete_workflow', [ __CLASS__, 'handle' ] );
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

		$result = Workflow_Manager::delete_workflow( $workflow_id );

		if ( ! $result ) {
			self::send_error( 'Could not delete workflow.' );
			return;
		}

		// Log activity (#565: Activity Logging Expansion)
		Activity_Logger::log(
			'workflow_deleted',
			sprintf( __( 'Workflow deleted: %s', 'wpshadow' ), $workflow_id ),
			'workflows',
			array( 'workflow_id' => $workflow_id )
		);

		self::send_success( [ 'message' => 'Workflow deleted successfully.' ] );
	}
}
