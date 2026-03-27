<?php
declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Workflow\Workflow_Manager;

/**
 * AJAX Handler: Get Available Actions
 *
 * Retrieves available diagnostics and treatments for workflows.
 * Action: wp_ajax_wpshadow_get_available_actions
 * Nonce: wpshadow_workflow
 * Capability: manage_options
 */
class Get_Available_Actions_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX hook
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_get_available_actions', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle AJAX request
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_workflow', 'manage_options' );

		$diagnostics = Workflow_Manager::get_available_diagnostics();
		$treatments  = Workflow_Manager::get_available_treatments();

		self::send_success(
			array(
				'diagnostics' => $diagnostics,
				'treatments'  => $treatments,
			)
		);
	}
}
