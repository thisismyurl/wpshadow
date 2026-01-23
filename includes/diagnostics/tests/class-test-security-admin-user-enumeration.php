<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Security_Admin_User_Enumeration extends Diagnostic_Base
{

	protected static $slug = 'test-security-admin-user-enumeration';
	protected static $title = 'User Enumeration Test';
	protected static $description = 'Tests for user enumeration vulnerability';

	public static function check(?string $url = null, ?string $html = null): ?array
	{
		// Test author archive enumeration
		$test_url = home_url('/?author=1');

		$response = wp_remote_get($test_url, [
			'timeout' => 5,
			'sslverify' => false,
			'redirection' => 0 // Don't follow redirects
		]);

		if (is_wp_error($response)) {
			return null;
		}

		$status_code = wp_remote_retrieve_response_code($response);

		// Check if redirects to author page (reveals username)
		if ($status_code === 301 || $status_code === 302) {
			$location = wp_remote_retrieve_header($response, 'location');

			// If redirects to /author/username/, enumeration is possible
			if ($location && preg_match('/\/author\/([^\/]+)/i', $location, $match)) {
				$username = $match[1];

				return [
					'id' => 'security-user-enumeration',
					'title' => 'User Enumeration Possible',
					'description' => sprintf('Author archive URLs reveal usernames (e.g., "%s"). Attackers can enumerate users for brute-force attacks.', $username),
					'color' => '#ff9800',
					'bg_color' => '#fff3e0',
					'kb_link' => 'https://wpshadow.com/kb/user-enumeration/',
					'training_link' => 'https://wpshadow.com/training/authentication-security/',
					'auto_fixable' => false,
					'threat_level' => 40,
					'module' => 'Security',
					'priority' => 2,
					'meta' => ['username_revealed' => $username, 'test_url' => $test_url],
				];
			}
		}

		// Also check if author page loads directly
		if ($status_code === 200) {
			$body = wp_remote_retrieve_body($response);

			// Check for author page indicators
			if (preg_match('/author-|by <[^>]+>([^<]+)</i', $body)) {
				return [
					'id' => 'security-user-enumeration-direct',
					'title' => 'User Enumeration via Author Pages',
					'description' => 'Author pages are publicly accessible via /?author=1 URLs. This allows attackers to enumerate usernames.',
					'color' => '#ff9800',
					'bg_color' => '#fff3e0',
					'kb_link' => 'https://wpshadow.com/kb/user-enumeration/',
					'training_link' => 'https://wpshadow.com/training/authentication-security/',
					'auto_fixable' => false,
					'threat_level' => 40,
					'module' => 'Security',
					'priority' => 2,
					'meta' => ['method' => 'direct_access', 'test_url' => $test_url],
				];
			}
		}

		return null;
	}

	public static function get_name(): string
	{
		return __('User Enumeration', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for user enumeration vulnerability.', 'wpshadow');
	}
}
