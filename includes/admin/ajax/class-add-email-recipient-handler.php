<?php

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Workflow\Email_Recipient_Manager;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * AJAX Handler: Add Email Recipient
 *
 * Action: wp_ajax_wpshadow_add_email_recipient
 * Nonce: wpshadow_email_recipient
 * Capability: manage_options
 */
class Add_Email_Recipient_Handler extends AJAX_Handler_Base
{

	/**
	 * Register AJAX hook
	 */
	public static function register(): void
	{
		add_action('wp_ajax_wpshadow_add_email_recipient', [__CLASS__, 'handle']);
	}

	/**
	 * Handle AJAX request
	 */
	public static function handle(): void
	{
		// Centralized security check (nonce + capability)
		self::verify_request('wpshadow_email_recipient', 'manage_options');

		// Get and sanitize parameters
		$email             = self::get_post_param('email', 'email', '', true);
		$send_verification = self::get_post_param('send_verification', 'bool', false, false);

		// Request recipient using Email_Recipient_Manager
		$result = Email_Recipient_Manager::request_recipient($email, $send_verification);

		// Consistent response
		if ($result['success']) {
			self::send_success($result);
		} else {
			self::send_error($result['message']);
		}
	}
}
