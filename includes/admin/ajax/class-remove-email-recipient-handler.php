<?php

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Workflow\Email_Recipient_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AJAX Handler: Remove Email Recipient
 *
 * Action: wp_ajax_wpshadow_remove_recipient
 * Nonce: wpshadow_email_recipient
 * Capability: manage_options
 */
class Remove_Email_Recipient_Handler extends AJAX_Handler_Base {


	/**
	 * Register AJAX hook
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_remove_recipient', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle AJAX request
	 */
	public static function handle(): void {
		// Centralized security check (nonce + capability)
		self::verify_request( 'wpshadow_email_recipient', 'manage_options' );

		// Get and sanitize parameters
		$email = self::get_post_param( 'email', 'email', '', true );

		// Get recipients using Email_Recipient_Manager
		$recipients = Email_Recipient_Manager::get_approved_recipients();

		if ( ! isset( $recipients[ $email ] ) ) {
			self::send_error( __( 'Email not found.', 'wpshadow' ) );
		}

		// Remove the recipient
		unset( $recipients[ $email ] );
		update_option( Email_Recipient_Manager::OPTION_KEY, $recipients );

		self::send_success(
			array(
				'message' => __( 'Email recipient removed.', 'wpshadow' ),
			)
		);
	}
}
