<?php

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Workflow\Workflow_Manager;

/**
 * AJAX Handler: Generate Workflow Name
 *
 * Generates a silly default workflow name.
 * Action: wp_ajax_wpshadow_generate_workflow_name
 * Nonce: wpshadow_workflow
 * Capability: (none required for name generation)
 */
class Generate_Workflow_Name_Handler extends AJAX_Handler_Base
{

	/**
	 * Register AJAX hook
	 */
	public static function register(): void
	{
		add_action('wp_ajax_wpshadow_generate_workflow_name', [__CLASS__, 'handle']);
	}

	/**
	 * Handle AJAX request
	 */
	public static function handle(): void
	{
		// Verify nonce only, no capability required for name generation
		self::verify_request('wpshadow_workflow', 'read');

		$name = Workflow_Manager::generate_silly_name();

		self::send_success(['name' => $name]);
	}
}
