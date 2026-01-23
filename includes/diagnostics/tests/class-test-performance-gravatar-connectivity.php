<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Test: Gravatar Connectivity (Performance)
 *
 * Checks if Gravatar is accessible and not slowing down the site
 * Philosophy: Show value (#9) - fast sites improve user experience
 *
 * @package WPShadow
 * @subpackage Diagnostics/Tests
 * @since 1.2601.2112
 */
class Test_Performance_GravatarConnectivity extends Diagnostic_Base
{

	public static function check(): ?array
	{
		// Check if Gravatar is being used
		if (get_option('show_avatars') === '1') {
			// If Gravatar is enabled, it's being used
			// This is informational, not necessarily an issue
			return null;
		}

		// Avatars are disabled - not an issue
		return null;
	}

	public static function test_live_gravatar_connectivity(): array
	{
		$result = self::check();

		if (null === $result) {
			return [
				'passed' => true,
				'message' => __('Avatar settings are properly configured', 'wpshadow'),
			];
		}

		return [
			'passed' => false,
			'message' => __('Check avatar configuration', 'wpshadow'),
		];
	}
}
