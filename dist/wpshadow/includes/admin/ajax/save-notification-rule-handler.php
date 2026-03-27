<?php
/**
 * Save Notification Rule AJAX Handler
 *
 * @package WPShadow
 * @subpackage Workflow
 */

namespace WPShadow\Admin\Ajax;

use WPShadow\Workflow\Notification_Builder;
use WPShadow\Core\AJAX_Handler_Base;
use function add_action;
use function sanitize_text_field;
use function sanitize_key;
use function wp_kses_post;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle saving notification/email rules
 */
class Save_Notification_Rule_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX hook
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_save_notification_rule', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle AJAX request
	 */
	public static function handle(): void {
		// Security check
		self::verify_request( 'wpshadow_notification_nonce', 'manage_options' );

		// Get parameters
		$mode           = self::get_post_param( 'mode', 'key', 'notification', true );
		$rule_id        = self::get_post_param( 'rule_id', 'text', '', false );
		$name           = self::get_post_param( 'name', 'text', '', true );
		$trigger_type   = self::get_post_param( 'trigger_type', 'key', '', true );
		$action_message = self::get_post_param( 'action_message', 'text', '', true );
		$action_subject = self::get_post_param( 'action_subject', 'text', '', false );
		$action_style   = self::get_post_param( 'action_style', 'key', 'info', false );

		// Validate inputs
		if ( empty( $name ) || empty( $trigger_type ) || empty( $action_message ) ) {
			self::send_error( __( 'All required fields must be filled.', 'wpshadow' ) );
		}

		// Set builder mode
		Notification_Builder::set_mode( $mode );

		// Build rule
		$rule = array(
			'id'      => $rule_id,
			'name'    => sanitize_text_field( $name ),
			'trigger' => array(
				'type'  => $trigger_type,
				'label' => $trigger_type, // Will be populated by builder
			),
			'action'  => array(
				'type'  => $mode === 'email' ? 'send_email' : 'send_notification',
				'label' => $mode === 'email' ? __( 'Send Email', 'wpshadow' ) : __( 'Send Notification', 'wpshadow' ),
			),
			'config'  => array(
				'message' => wp_kses_post( $action_message ),
				'subject' => $mode === 'email' ? sanitize_text_field( $action_subject ) : '',
				'style'   => $mode === 'email' ? '' : sanitize_key( $action_style ),
			),
		);

		// Save rule
		$saved_id = Notification_Builder::save_rule( $rule );

		if ( ! $saved_id ) {
			self::send_error( __( 'Failed to save rule.', 'wpshadow' ) );
		}

		self::send_success(
			array(
				'rule_id' => $saved_id,
				'message' => __( 'Rule saved successfully!', 'wpshadow' ),
			)
		);
	}
}
