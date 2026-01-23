<?php

/**
 * Email Service Utility
 *
 * Centralized email operations for consistent handling across the plugin.
 * Eliminates duplicate email validation, sending, and configuration logic.
 *
 * @package WPShadow
 * @subpackage Utils
 */

declare(strict_types=1);

namespace WPShadow\Utils;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Email Service - DRY utility for email operations
 */
class Email_Service
{

	/**
	 * Validate email address
	 *
	 * @param string $email Email address to validate.
	 * @return bool True if valid, false otherwise
	 */
	public static function is_valid(string $email): bool
	{
		return is_email($email) !== false;
	}

	/**
	 * Send email with consistent error handling
	 *
	 * @param string|array $to      Recipient email address(es).
	 * @param string       $subject Email subject.
	 * @param string       $message Email message body.
	 * @param array        $headers Optional. Email headers.
	 * @param array        $attachments Optional. File attachments.
	 * @return array Result array with 'success' and 'message' keys
	 */
	public static function send($to, string $subject, string $message, array $headers = array(), array $attachments = array()): array
	{
		// Validate recipients
		$recipients = is_array($to) ? $to : array($to);
		foreach ($recipients as $recipient) {
			if (! self::is_valid($recipient)) {
				return array(
					'success' => false,
					'message' => sprintf(__('Invalid email address: %s', 'wpshadow'), esc_html($recipient)),
				);
			}
		}

		// Send email
		$sent = wp_mail($to, $subject, $message, $headers, $attachments);

		if (! $sent) {
			return array(
				'success' => false,
				'message' => __('Email delivery failed. Check your mail server configuration.', 'wpshadow'),
			);
		}

		return array(
			'success' => true,
			'message' => __('Email sent successfully.', 'wpshadow'),
		);
	}

	/**
	 * Get configured FROM name
	 *
	 * @return string FROM name
	 */
	public static function get_from_name(): string
	{
		$from_name = get_option('wpshadow_email_from_name', '');

		if (empty($from_name)) {
			$from_name = get_bloginfo('name');
		}

		return apply_filters('wpshadow_email_from_name', $from_name);
	}

	/**
	 * Get configured FROM email address
	 *
	 * @return string FROM email address
	 */
	public static function get_from_email(): string
	{
		$from_email = get_option('wpshadow_email_from_email', '');

		if (empty($from_email) || ! self::is_valid($from_email)) {
			$from_email = get_bloginfo('admin_email');
		}

		return apply_filters('wpshadow_email_from_email', $from_email);
	}

	/**
	 * Get default email headers with FROM information
	 *
	 * @return array Email headers
	 */
	public static function get_default_headers(): array
	{
		$from_name  = self::get_from_name();
		$from_email = self::get_from_email();

		return array(
			'Content-Type: text/html; charset=UTF-8',
			sprintf('From: %s <%s>', $from_name, $from_email),
		);
	}

	/**
	 * Send test email
	 *
	 * @param string $to Recipient email address.
	 * @return array Result array with 'success', 'message', and 'details' keys
	 */
	public static function send_test(string $to): array
	{
		if (! self::is_valid($to)) {
			return array(
				'success' => false,
				'message' => __('Invalid email address provided.', 'wpshadow'),
			);
		}

		$subject = __('WPShadow Test Email', 'wpshadow');
		$message = sprintf(
			__('This is a test email from WPShadow sent at %s.', 'wpshadow'),
			current_time('mysql')
		);

		$result = self::send($to, $subject, $message, self::get_default_headers());

		$result['details'] = array(
			'from_name'  => self::get_from_name(),
			'from_email' => self::get_from_email(),
			'to'         => $to,
			'timestamp'  => current_time('mysql'),
		);

		return $result;
	}

	/**
	 * Sanitize email address
	 *
	 * @param string $email Email address to sanitize.
	 * @return string Sanitized email address
	 */
	public static function sanitize(string $email): string
	{
		return sanitize_email($email);
	}

	/**
	 * Format email address with name
	 *
	 * @param string $email Email address.
	 * @param string $name  Optional. Display name.
	 * @return string Formatted email address
	 */
	public static function format_address(string $email, string $name = ''): string
	{
		if (empty($name)) {
			return $email;
		}

		return sprintf('%s <%s>', $name, $email);
	}

	/**
	 * Parse email address from formatted string
	 *
	 * @param string $formatted Formatted email string (e.g., "Name <email@example.com>").
	 * @return string Email address
	 */
	public static function parse_address(string $formatted): string
	{
		if (preg_match('/<([^>]+)>/', $formatted, $matches)) {
			return trim($matches[1]);
		}

		return trim($formatted);
	}

	/**
	 * Check if email domain is valid
	 *
	 * @param string $email Email address to check.
	 * @return bool True if domain is valid
	 */
	public static function has_valid_domain(string $email): bool
	{
		if (! self::is_valid($email)) {
			return false;
		}

		$parts = explode('@', $email);
		if (count($parts) !== 2) {
			return false;
		}

		$domain = $parts[1];

		// Check DNS MX record
		return checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A');
	}

	/**
	 * Build HTML email template
	 *
	 * @param string $content Email content.
	 * @param string $title   Optional. Email title.
	 * @return string HTML email
	 */
	public static function build_html_template(string $content, string $title = ''): string
	{
		$site_name = get_bloginfo('name');

		ob_start();
?>
		<!DOCTYPE html>
		<html>

		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title><?php echo esc_html($title ? $title : $site_name); ?></title>
		</head>

		<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
			<?php if ($title) : ?>
				<h2 style="color: #0073aa; border-bottom: 2px solid #0073aa; padding-bottom: 10px;">
					<?php echo esc_html($title); ?>
				</h2>
			<?php endif; ?>

			<div style="margin: 20px 0;">
				<?php echo wp_kses_post($content); ?>
			</div>

			<hr style="border: none; border-top: 1px solid #ddd; margin: 30px 0;">

			<p style="font-size: 12px; color: #666;">
				<?php
				printf(
					/* translators: %s: site name */
					esc_html__('This email was sent from %s', 'wpshadow'),
					'<strong>' . esc_html($site_name) . '</strong>'
				);
				?>
			</p>
		</body>

		</html>
<?php
		return ob_get_clean();
	}
}
