<?php

declare(strict_types=1);
/**
 * Weak Password Hashing Diagnostic
 *
 * Philosophy: Cryptography - use strong password hashing
 * @package WPShadow
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for weak password hashing.
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Weak_Password_Hashing extends Diagnostic_Base
{
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array
	{
		global $wpdb;

		// Check for old MD5/SHA1 password hashes in custom tables
		$results = $wpdb->get_results(
			"SELECT COUNT(*) as count FROM {$wpdb->usermeta} WHERE meta_key = 'legacy_password_hash' AND meta_value REGEXP '^[a-f0-9]{32}$|^[a-f0-9]{40}$'"
		);

		if (! empty($results[0]->count) && $results[0]->count > 0) {
			return array(
				'id'          => 'weak-password-hashing',
				'title'       => 'Weak Password Hashing Algorithm Detected',
				'description' => sprintf(
					'Found %d users with weak password hashes (MD5 or SHA1). Rehash using bcrypt/Argon2. Old hashes are vulnerable to rainbow tables.',
					$results[0]->count
				),
				'severity'    => 'high',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/upgrade-password-hashing/',
				'training_link' => 'https://wpshadow.com/training/password-hashing/',
				'auto_fixable' => false,
				'threat_level' => 75,
			);
		}

		return null;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Weak Password Hashing
	 * Slug: -weak-password-hashing
	 * File: class-diagnostic-weak-password-hashing.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Weak Password Hashing
	 * Slug: -weak-password-hashing
	 *
	 * TODO: Review the check() method to understand what constitutes a passing test.
	 * The test should verify that:
	 * - check() returns NULL when the diagnostic condition is NOT met (site is healthy)
	 * - check() returns an array when the diagnostic condition IS met (issue found)
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live__weak_password_hashing(): array
	{
		global $wpdb;

		$weak_count = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key = 'legacy_password_hash' AND meta_value REGEXP '^[a-f0-9]{32}$|^[a-f0-9]{40}$'"
		);

		$diagnostic_result    = self::check();
		$should_find_issue    = ($weak_count > 0);
		$diagnostic_has_issue = (null !== $diagnostic_result);
		$test_passes          = ($should_find_issue === $diagnostic_has_issue);

		$message = sprintf(
			'Weak hashes: %d. Expected diagnostic to %s issue. Diagnostic %s issue. Test: %s',
			$weak_count,
			$should_find_issue ? 'FIND' : 'NOT find',
			$diagnostic_has_issue ? 'FOUND' : 'DID NOT find',
			$test_passes ? 'PASS' : 'FAIL'
		);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}
}
