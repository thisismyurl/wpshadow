<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Test: Weak Password Hashing (Security)
 *
 * Checks if WordPress is using secure password hashing
 * Philosophy: Show value (#9) - strong security protects user data
 *
 * @package WPShadow
 * @subpackage Diagnostics/Tests
 * @since 1.2601.2112
 */
class Test_Security_WeakPasswordHashing extends Diagnostic_Base
{

	public static function check(): ?array
	{
		// Check if WordPress password hashing is properly configured
		global $wp_version;

		if (version_compare($wp_version, '5.3.0', '<')) {
			return [
				'id' => 'weak-password-hashing',
				'title' => __('Weak password hashing detected', 'wpshadow'),
				'description' => __('WordPress 5.3+ includes improved password hashing. Update to get better security.', 'wpshadow'),
				'severity' => 'medium',
				'threat_level' => 60,
			];
		}

		return null;
	}

	public static function test_live_weak_password_hashing(): array
	{
		$result = self::check();

		if (null === $result) {
			return [
				'passed' => true,
				'message' => __('Password hashing is secure', 'wpshadow'),
			];
		}

		return [
			'passed' => false,
			'message' => $result['description'],
		];
	}
}
