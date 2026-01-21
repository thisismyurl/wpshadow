<?php
declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Workflow\Workflow_Manager;

/**
 * AJAX Handler: Toggle Workflow
 *
 * Toggles workflow enabled status.
 * Action: wp_ajax_wpshadow_toggle_workflow
 * Nonce: wpshadow_workflow
 * Capability: manage_options
 */
class Toggle_Workflow_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX hook
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_toggle_workflow', [ __CLASS__, 'handle' ] );
	}

	/**
	 * Handle AJAX request
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_workflow', 'manage_options' );

		$workflow_id = self::get_post_param( 'workflow_id', 'key', '', true );
		$enabled_raw = self::get_post_param( 'enabled', 'text', null, true );

		if ( empty( $workflow_id ) ) {
			self::send_error( 'Invalid workflow ID.' );
			return;
		}

		$enabled = null !== $enabled_raw ? (bool) $enabled_raw : null;

		$workflow = Workflow_Manager::toggle_workflow( $workflow_id, $enabled );

		if ( ! $workflow ) {
			self::send_error( 'Could not toggle workflow.' );
			return;
		}

		self::send_success( [
			'message'  => 'Workflow updated.',
			'workflow' => $workflow,
		] );
	}
}
