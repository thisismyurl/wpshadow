<?php
declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Workflow\Workflow_Examples;

/**
 * AJAX Handler: Get Examples
 *
 * Retrieves available example templates for workflow creation.
 * Action: wp_ajax_wpshadow_get_examples
 * Nonce: wpshadow_workflow
 * Capability: manage_options
 */
class Get_Examples_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX hook
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_get_examples', [ __CLASS__, 'handle' ] );
	}

	/**
	 * Handle AJAX request
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_workflow', 'manage_options' );

		$examples = Workflow_Examples::get_display_examples();

		self::send_success( [
			'examples' => $examples,
			'count'    => count( $examples ),
		] );
	}
}
