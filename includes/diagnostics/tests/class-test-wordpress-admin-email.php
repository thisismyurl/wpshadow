<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_WordPress_Admin_Email extends Diagnostic_Base
{

	protected static $slug = 'test-wordpress-admin-email';
	protected static $title = 'Admin Email Test';
	protected static $description = 'Tests for generic admin email address';

	public static function check(?string $url = null, ?string $html = null): ?array
	{
		if ($html !== null) {
			return self::analyze_html($html, $url ?? 'provided-html');
		}

		$html = self::fetch_html($url ?? home_url('/'));
		if ($html === false) {
			return null;
		}

		return self::analyze_html($html, $url ?? home_url('/'));
	}

	protected static function analyze_html(string $html, string $checked_url): ?array
	{
		// Look for email addresses in HTML
		preg_match_all('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/i', $html, $emails);

		if (empty($emails[0])) {
			return null;
		}

		$generic_patterns = '/^(admin|webmaster|info|contact|noreply|no-reply)@/i';

		$has_generic = false;
		foreach ($emails[0] as $email) {
			if (preg_match($generic_patterns, $email)) {
				$has_generic = true;
				break;
			}
		}

		if ($has_generic) {
			return [
				'id' => 'wordpress-generic-email',
				'title' => 'Generic Email Address Used',
				'description' => 'Generic email addresses (admin@, webmaster@) detected. Use personalized email for better deliverability and trust.',
				'color' => '#2196f3',
				'bg_color' => '#e3f2fd',
				'kb_link' => 'https://wpshadow.com/kb/admin-email/',
				'training_link' => 'https://wpshadow.com/training/email-configuration/',
				'auto_fixable' => false,
				'threat_level' => 25,
				'module' => 'WordPress',
				'priority' => 3,
				'meta' => ['has_generic_email' => true],
			];
		}

		return null;
	}

	protected static function fetch_html(string $url)
	{
		$response = wp_remote_get($url, ['timeout' => 10, 'sslverify' => false]);
		return is_wp_error($response) ? false : wp_remote_retrieve_body($response);
	}

	public static function get_name(): string
	{
		return __('Admin Email', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for generic admin email address.', 'wpshadow');
	}
}
