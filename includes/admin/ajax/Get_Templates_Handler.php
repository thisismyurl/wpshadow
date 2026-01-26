<?php
declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Workflow\Workflow_Templates;

/**
 * AJAX Handler: Get Templates
 *
 * Retrieves categorized workflow templates.
 * Action: wp_ajax_wpshadow_get_templates
 * Nonce: wpshadow_workflow
 * Capability: manage_options
 */
class Get_Templates_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX hook
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_get_templates', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle AJAX request
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_workflow', 'manage_options' );

		$category = self::get_post_param( 'category', 'key', 'all', false );

		if ( 'all' === $category ) {
			$templates = Workflow_Templates::get_all_templates();
		} else {
			$templates = array(
				$category => array(
					'templates' => Workflow_Templates::get_by_category( $category ),
				),
			);
		}

		self::send_success(
			array(
				'templates' => $templates,
				'stats'     => Workflow_Templates::get_usage_stats(),
			)
		);
	}
}

Get_Templates_Handler::register();
