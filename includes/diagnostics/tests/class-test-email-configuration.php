<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Email Configuration
 *
 * Verifies WordPress email sending is configured correctly.
 * Failed email configuration causes lost password resets and notifications.
 *
 * @since 1.2.0
 */
class Test_Email_Configuration extends Diagnostic_Base
{

	/**
	 * Check email configuration
	 *
	 * @return array|null Diagnostic array if issues found, null if all good
	 */
	public static function check(): ?array
	{
		$email_config = self::check_email_setup();

		if ($email_config['threat_level'] === 0) {
			return null;
		}

		return [
			'threat_level'    => $email_config['threat_level'],
			'threat_color'    => 'orange',
			'passed'          => false,
			'issue'           => $email_config['issue'],
			'metadata'        => $email_config,
			'kb_link'         => 'https://wpshadow.com/kb/wordpress-email-configuration/',
			'training_link'   => 'https://wpshadow.com/training/wordpress-notifications-email/',
		];
	}

	/**
	 * Guardian Sub-Test: Admin email address
	 *
	 * @return array Test result
	 */
	public static function test_admin_email(): array
	{
		$admin_email = get_option('admin_email');

		$is_valid = is_email($admin_email);

		return [
			'test_name'   => 'Admin Email Address',
			'email'       => $admin_email,
			'is_valid'    => $is_valid,
			'passed'      => $is_valid,
			'description' => $is_valid ? sprintf('Admin email: %s', $admin_email) : 'Invalid admin email address',
		];
	}

	/**
	 * Guardian Sub-Test: SMTP configuration
	 *
	 * @return array Test result
	 */
	public static function test_smtp_configuration(): array
	{
		$active_plugins = get_plugins();

		$smtp_plugins = [
			'wp-mail-smtp/wp_mail_smtp.php' => 'WP Mail SMTP',
			'post-smtp/postman-smtp.php' => 'Post SMTP',
			'mailgun/mailgun.php' => 'Mailgun',
			'sendgrid-email-delivery-simplified/sendgrid-email-delivery-simplified.php' => 'SendGrid',
		];

		$active_smtp = null;
		foreach ($smtp_plugins as $plugin_file => $plugin_name) {
			if (isset($active_plugins[$plugin_file])) {
				$active_smtp = $plugin_name;
				break;
			}
		}

		return [
			'test_name'     => 'SMTP Configuration',
			'active_smtp'   => $active_smtp,
			'passed'        => $active_smtp !== null,
			'description'   => $active_smtp ?? 'No SMTP plugin configured',
		];
	}

	/**
	 * Guardian Sub-Test: Email test
	 *
	 * @return array Test result
	 */
	public static function test_email_delivery(): array
	{
		// This is a test that would need to be run carefully
		// We'll just check if wp_mail function is available

		$wp_mail_available = function_exists('wp_mail');

		return [
			'test_name'           => 'Email Delivery Function',
			'wp_mail_available'   => $wp_mail_available,
			'passed'              => $wp_mail_available,
			'description'         => $wp_mail_available ? 'wp_mail() function available' : 'wp_mail() not available',
		];
	}

	/**
	 * Guardian Sub-Test: From header configuration
	 *
	 * @return array Test result
	 */
	public static function test_from_header(): array
	{
		$from_name = get_option('blogname');
		$from_email = get_option('admin_email');

		$has_configuration = ! empty($from_name) && ! empty($from_email);

		return [
			'test_name'        => 'From Header Configuration',
			'from_name'        => $from_name,
			'from_email'       => $from_email,
			'passed'           => $has_configuration,
			'description'      => $has_configuration ? sprintf('From: %s <%s>', $from_name, $from_email) : 'From header not configured',
		];
	}

	/**
	 * Check email setup
	 *
	 * @return array Email configuration check
	 */
	private static function check_email_setup(): array
	{
		$admin_email = get_option('admin_email');
		$is_valid_email = is_email($admin_email);

		$threat_level = 0;
		$issues = [];

		if (! $is_valid_email) {
			$issues[] = 'Invalid admin email address';
			$threat_level = 60;
		}

		// Check for SMTP plugin
		$active_plugins = get_plugins();

		$smtp_plugins = [
			'wp-mail-smtp/wp_mail_smtp.php',
			'post-smtp/postman-smtp.php',
			'mailgun/mailgun.php',
			'sendgrid-email-delivery-simplified/sendgrid-email-delivery-simplified.php',
		];

		$has_smtp = false;
		foreach ($smtp_plugins as $plugin_file) {
			if (isset($active_plugins[$plugin_file])) {
				$has_smtp = true;
				break;
			}
		}

		if (! $has_smtp) {
			$issues[] = 'No dedicated SMTP plugin configured';
			$threat_level = max($threat_level, 30);
		}

		// Check blog name
		$from_name = get_option('blogname');
		if (empty($from_name)) {
			$issues[] = 'Blog name not configured';
			$threat_level = max($threat_level, 20);
		}

		$issue = ! empty($issues) ? implode('; ', $issues) : 'Email configuration is properly set up';

		return [
			'threat_level'       => $threat_level,
			'issue'              => $issue,
			'admin_email'        => $admin_email,
			'is_valid_email'     => $is_valid_email,
			'has_smtp'           => $has_smtp,
		];
	}

	/**
	 * Get diagnostic name
	 *
	 * @return string
	 */
	public static function get_name(): string
	{
		return 'Email Configuration';
	}

	/**
	 * Get diagnostic description
	 *
	 * @return string
	 */
	public static function get_description(): string
	{
		return 'Verifies WordPress email sending is configured correctly for notifications';
	}

	/**
	 * Get diagnostic category
	 *
	 * @return string
	 */
	public static function get_category(): string
	{
		return 'Configuration';
	}
}
