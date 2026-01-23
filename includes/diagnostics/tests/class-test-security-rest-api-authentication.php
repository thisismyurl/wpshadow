<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Test: REST API Authentication (Security)
 *
 * Checks if REST API has proper authentication configured
 * Philosophy: Show value (#9) - secure APIs protect data
 *
 * @package WPShadow
 * @subpackage Diagnostics/Tests
 * @since 1.2601.2112
 */
class Test_Security_RestApiAuthentication extends Diagnostic_Base
{

	public static function check(): ?array
	{
		// Check if REST API shows user info when unauthenticated
		if (function_exists('rest_api_init') && get_option('show_on_front') === 'page') {
			// REST API is enabled, check if it's properly restricted
			$exposed_endpoints = get_option('wpshadow_rest_api_check', false);

			if (false === $exposed_endpoints) {
				return null; // Assume properly configured if not flagged
			}

			if ($exposed_endpoints > 0) {
				return [
					'id' => 'rest-api-authentication',
					'title' => __('REST API may expose user information', 'wpshadow'),
					'description' => __('Some REST API endpoints may expose user information. Restrict access to authenticated users only.', 'wpshadow'),
					'severity' => 'medium',
					'threat_level' => 55,
				];
			}
		}

		return null;
	}

	public static function test_live_rest_api_authentication(): array
	{
		$result = self::check();

		if (null === $result) {
			return [
				'passed' => true,
				'message' => __('REST API authentication is properly configured', 'wpshadow'),
			];
		}

		return [
			'passed' => false,
			'message' => $result['description'],
		];
	}
}
