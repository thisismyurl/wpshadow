<?php
declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Workflow\Workflow_Wizard;

/**
 * AJAX Handler: Get Action Config
 *
 * Retrieves configuration fields for workflow actions (for wizard).
 * Action: wp_ajax_wpshadow_get_action_config
 * Nonce: wpshadow_workflow
 * Capability: manage_options
 */
class Get_Action_Config_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX hook
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_get_action_config', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle AJAX request
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_workflow', 'manage_options' );

		$action_id = self::get_post_param( 'action_id', 'key', '', true );

		if ( empty( $action_id ) ) {
			self::send_error( 'Invalid action ID.' );
			return;
		}

		$fields = Workflow_Wizard::get_action_config( $action_id );

		self::send_success( array( 'fields' => $fields ) );
	}
}
