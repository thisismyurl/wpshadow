<?php
declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Workflow\Workflow_Manager;

/**
 * AJAX Handler: Load Workflows
 *
 * Retrieves all saved workflows.
 * Action: wp_ajax_wpshadow_load_workflows
 * Nonce: wpshadow_workflow
 * Capability: manage_options
 */
class Load_Workflows_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX hook
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_load_workflows', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle AJAX request
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_workflow', 'manage_options' );

		$workflows = Workflow_Manager::get_workflows();

		self::send_success(
			array(
				'workflows' => $workflows,
				'count'     => count( $workflows ),
			)
		);
	}
}
