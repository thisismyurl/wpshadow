<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Security_Permissions_Policy extends Diagnostic_Base
{

	protected static $slug = 'test-security-permissions-policy';
	protected static $title = 'Permissions Policy Test';
	protected static $description = 'Tests for Permissions-Policy (Feature-Policy) header';

	public static function check(?string $url = null, ?string $html = null): ?array
	{
		$check_url = $url ?? home_url('/');

		$response = wp_remote_get($check_url, ['timeout' => 10, 'sslverify' => false]);

		if (is_wp_error($response)) {
			return null;
		}

		$headers = wp_remote_retrieve_headers($response);

		$has_permissions = isset($headers['permissions-policy']);
		$has_feature = isset($headers['feature-policy']); // Old name

		if (!$has_permissions && !$has_feature) {
			return [
				'id' => 'security-no-permissions-policy',
				'title' => 'No Permissions Policy',
				'description' => 'No Permissions-Policy header found. This header controls browser features like geolocation, camera, microphone access.',
				'color' => '#2196f3',
				'bg_color' => '#e3f2fd',
				'kb_link' => 'https://wpshadow.com/kb/permissions-policy/',
				'training_link' => 'https://wpshadow.com/training/security-headers/',
				'auto_fixable' => false,
				'threat_level' => 25,
				'module' => 'Security',
				'priority' => 3,
				'meta' => ['has_policy' => false],
			];
		}

		return null;
	}

	public static function get_name(): string
	{
		return __('Permissions Policy', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for Permissions-Policy (Feature-Policy) header.', 'wpshadow');
	}
}
