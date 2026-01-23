<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Security_X_Powered_By extends Diagnostic_Base
{

	protected static $slug = 'test-security-x-powered-by';
	protected static $title = 'X-Powered-By Exposure Test';
	protected static $description = 'Tests for X-Powered-By header disclosing stack details.';

	public static function check(?string $url = null, ?string $html = null): ?array
	{
		$check_url = $url ?? home_url('/');
		$response = wp_remote_get($check_url, ['timeout' => 10, 'sslverify' => false]);

		if (is_wp_error($response)) {
			return null;
		}

		$headers = wp_remote_retrieve_headers($response);

		if (isset($headers['x-powered-by'])) {
			$powered_by = $headers['x-powered-by'];

			return [
				'id' => 'security-x-powered-by-exposed',
				'title' => 'X-Powered-By Header Exposed',
				'description' => sprintf('X-Powered-By header reveals: "%s". This discloses technology stack to potential attackers.', $powered_by),
				'color' => '#ff9800',
				'bg_color' => '#fff3e0',
				'kb_link' => 'https://wpshadow.com/kb/x-powered-by/',
				'training_link' => 'https://wpshadow.com/training/information-disclosure/',
				'auto_fixable' => false,
				'threat_level' => 30,
				'module' => 'Security',
				'priority' => 3,
				'meta' => ['powered_by' => $powered_by],
			];
		}

		return null;
	}

	public static function get_name(): string
	{
		return __('X-Powered-By Exposure', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks if X-Powered-By header is exposed.', 'wpshadow');
	}
}
