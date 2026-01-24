<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Security_Sensitive_Files extends Diagnostic_Base
{

	protected static $slug = 'test-security-sensitive-files';
	protected static $title = 'Sensitive Files Exposed Test';
	protected static $description = 'Tests for publicly accessible sensitive files';

	public static function check(?string $url = null, ?string $html = null): ?array
	{
		$site_url = home_url('/');

		// Test common sensitive files
		$sensitive_files = [
			'readme.html' => 'WordPress version disclosure',
			'license.txt' => 'WordPress installation',
			'.env' => 'environment variables',
			'wp-config.php.bak' => 'backup config file',
			'wp-config.php~' => 'backup config file',
			'.git/config' => 'git repository',
			'.htaccess.bak' => 'backup htaccess',
			'phpinfo.php' => 'PHP info page',
			'info.php' => 'PHP info page'
		];

		$exposed_files = [];

		foreach ($sensitive_files as $file => $description) {
			$test_url = rtrim($site_url, '/') . '/' . $file;
			$response = wp_remote_get($test_url, ['timeout' => 3, 'sslverify' => false]);

			if (is_wp_error($response)) {
				continue;
			}

			$status_code = wp_remote_retrieve_response_code($response);

			// If accessible (200)
			if ($status_code === 200) {
				$body = wp_remote_retrieve_body($response);

				// Verify it's not a 404 page disguised as 200
				if (!preg_match('/404|not found|page.*not.*exist/i', $body)) {
					$exposed_files[$file] = $description;
				}
			}
		}

		if (!empty($exposed_files)) {
			return [
				'id' => 'security-sensitive-files-exposed',
				'title' => 'Sensitive Files Accessible',
				'description' => sprintf(
					'%d sensitive file(s) publicly accessible: %s. These files can reveal security information to attackers.',
					count($exposed_files),
					implode(', ', array_keys($exposed_files))
				)
				'kb_link' => 'https://wpshadow.com/kb/sensitive-files/',
				'training_link' => 'https://wpshadow.com/training/file-security/',
				'auto_fixable' => false,
				'threat_level' => 60,
				'module' => 'Security',
				'priority' => 1,
				'meta' => ['exposed_files' => array_keys($exposed_files), 'descriptions' => $exposed_files],
			];
		}

		return null;
	}

	public static function get_name(): string
	{
		return __('Sensitive Files Exposed', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for publicly accessible sensitive files.', 'wpshadow');
	}
}
