<?php
/**
 * Delete Notification Rule AJAX Handler
 *
 * @package WPShadow
 * @subpackage Workflow
 */

namespace WPShadow\Admin\Ajax;

use WPShadow\Workflow\Notification_Builder;
use WPShadow\Core\AJAX_Handler_Base;
use function add_action;
use function sanitize_key;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle deleting notification/email rules
 */
class Delete_Notification_Rule_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX hook
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_delete_notification_rule', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle AJAX request
	 */
	public static function handle(): void {
		// Security check
		self::verify_request( 'wpshadow_notification_builder', 'manage_options' );

		// Get parameters
		$mode    = self::get_post_param( 'mode', 'key', 'notification', true );
		$rule_id = self::get_post_param( 'rule_id', 'text', '', true );

		if ( empty( $rule_id ) ) {
			self::send_error( __( 'Rule ID is required.', 'wpshadow' ) );
		}

		// Set builder mode
		Notification_Builder::set_mode( $mode );

		// Delete rule
		$deleted = Notification_Builder::delete_rule( $rule_id );

		if ( ! $deleted ) {
			self::send_error( __( 'Failed to delete rule.', 'wpshadow' ) );
		}

		self::send_success(
			array(
				'message' => __( 'Rule deleted successfully!', 'wpshadow' ),
			)
		);
	}
}
