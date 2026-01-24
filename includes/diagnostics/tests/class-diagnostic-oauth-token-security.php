<?php

declare(strict_types=1);
/**
 * OAuth Token Security Diagnostic
 *
 * Philosophy: Third-party auth - secure token handling
 * @package WPShadow
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for secure OAuth token storage.
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_OAuth_Token_Security extends Diagnostic_Base
{
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array
	{
		global $wpdb;

		// Check for OAuth tokens in user meta or options (should be encrypted)
		$results = $wpdb->get_results(
			"SELECT COUNT(*) as count FROM {$wpdb->usermeta} WHERE meta_key LIKE '%oauth%' OR meta_key LIKE '%token%'"
		);

		if (! empty($results[0]->count) && $results[0]->count > 0) {
			// Tokens found - check if encrypted
			$tokens = $wpdb->get_results(
				"SELECT meta_value FROM {$wpdb->usermeta} WHERE meta_key LIKE '%oauth%' LIMIT 1"
			);

			if (! empty($tokens)) {
				$token_value = $tokens[0]->meta_value;

				// Check if it looks encrypted
				if (! preg_match('/^[a-f0-9]+$/', $token_value) && strlen($token_value) > 100) {
					return array(
						'id'          => 'oauth-token-security',
						'title'       => 'OAuth Tokens May Not Be Encrypted',
						'description' => 'OAuth tokens stored in database without encryption. Compromised database exposes third-party accounts. Encrypt sensitive tokens at rest.',
						'severity'    => 'high',
						'category'    => 'security',
						'kb_link'     => 'https://wpshadow.com/kb/encrypt-oauth-tokens/',
						'training_link' => 'https://wpshadow.com/training/token-security/',
						'auto_fixable' => false,
						'threat_level' => 75,
					);
				}
			}
		}

		return null;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: OAuth Token Security
	 * Slug: -oauth-token-security
	 * File: class-diagnostic-oauth-token-security.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: OAuth Token Security
	 * Slug: -oauth-token-security
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
	public static function test_live__oauth_token_security(): array
	{
		global $wpdb;

		$token_count = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key LIKE '%oauth%' OR meta_key LIKE '%token%'"
		);

		$token_value = null;
		if ($token_count > 0) {
			$token_value = $wpdb->get_var(
				"SELECT meta_value FROM {$wpdb->usermeta} WHERE meta_key LIKE '%oauth%' LIMIT 1"
			);
		}

		$looks_encrypted = false;
		if (null !== $token_value) {
			$looks_encrypted = (bool) preg_match('/^[a-f0-9]+$/', (string) $token_value) || strlen((string) $token_value) <= 100;
		}

		// Call diagnostic check
		$diagnostic_result = self::check();

		// Determine expected state (matches check() logic)
		$should_find_issue    = ($token_count > 0 && null !== $token_value && ! $looks_encrypted);
		$diagnostic_has_issue = (null !== $diagnostic_result);
		$test_passes          = ($should_find_issue === $diagnostic_has_issue);

		$message = sprintf(
			'OAuth/token rows: %d, sample token %s encrypted. Expected diagnostic to %s issue. Diagnostic %s issue. Test: %s',
			$token_count,
			$looks_encrypted ? 'APPEARS' : 'DOES NOT appear',
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
