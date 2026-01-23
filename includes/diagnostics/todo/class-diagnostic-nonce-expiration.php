<?php

declare(strict_types=1);
/**
 * WordPress Nonce Expiration Diagnostic
 *
 * Philosophy: Session security - reasonable nonce lifetime
 * @package WPShadow
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check WordPress nonce expiration time.
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Nonce_Expiration extends Diagnostic_Base
{
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array
	{
		// Check nonce lifetime (default is 1 day)
		$nonce_life = apply_filters('nonce_life', DAY_IN_SECONDS);

		// If nonce lifetime is longer than 12 hours
		if ($nonce_life > (12 * HOUR_IN_SECONDS)) {
			return array(
				'id'          => 'nonce-expiration',
				'title'       => 'Long Nonce Expiration Time',
				'description' => sprintf(
					'WordPress security nonces remain valid for %s. Long-lived nonces increase CSRF attack window. Consider reducing nonce lifetime to 8-12 hours for sensitive operations.',
					human_time_diff(0, $nonce_life)
				),
				'severity'    => 'medium',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/configure-nonce-lifetime/',
				'training_link' => 'https://wpshadow.com/training/nonce-security/',
				'auto_fixable' => false,
				'threat_level' => 60,
			);
		}

		return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Nonce Expiration
	 * Slug: -nonce-expiration
	 * File: class-diagnostic-nonce-expiration.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Nonce Expiration
	 * Slug: -nonce-expiration
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
	public static function test_live__nonce_expiration(): array
	{
		$nonce_life = apply_filters('nonce_life', DAY_IN_SECONDS);
		$threshold  = 12 * HOUR_IN_SECONDS; // Must match check() logic

		$diagnostic_result    = self::check();
		$should_find_issue    = ($nonce_life > $threshold);
		$diagnostic_has_issue = (null !== $diagnostic_result);
		$test_passes          = ($should_find_issue === $diagnostic_has_issue);

		$message = sprintf(
			'Nonce lifetime: %s seconds. Threshold: %s. Expected diagnostic to %s issue. Diagnostic %s issue. Test: %s',
			$nonce_life,
			$threshold,
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
