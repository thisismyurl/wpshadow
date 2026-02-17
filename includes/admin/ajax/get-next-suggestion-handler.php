<?php
/**
 * AJAX Handler: Get Next Suggestion
 *
 * @package WPShadow
 * @subpackage Admin\Ajax
 */

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Workflow\Workflow_Suggestions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle request to get next smart suggestion
 */
class Get_Next_Suggestion_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX hook
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_get_next_suggestion', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle AJAX request to get next suggestion
	 */
	public static function handle(): void {
		// Security check
		$nonce_value = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';

		if ( ! $nonce_value || ! wp_verify_nonce( $nonce_value, 'wpshadow_automations' ) ) {
			self::send_error( __( 'Security check failed', 'wpshadow' ) );
			return;
		}

		// Require admin capability
		if ( ! current_user_can( 'manage_options' ) ) {
			self::send_error( __( 'Insufficient permissions', 'wpshadow' ) );
			return;
		}

		// Get the suggestion ID that was just created
		$created_id = self::get_post_param( 'suggestion_id', 'text', '', true );

		// Get the next suggestion
		$next_suggestion = Workflow_Suggestions::get_next_suggestion( $created_id );

		if ( ! $next_suggestion ) {
			self::send_success( array( 'suggestion' => null ) );
			return;
		}

		self::send_success(
			array(
				'suggestion' => $next_suggestion,
			)
		);
	}
}

// Register the handler
Get_Next_Suggestion_Handler::register();
