<?php

declare(strict_types=1);
/**
 * Admin HTTPS Enforcement Diagnostic
 *
 * Philosophy: Security hardening - protect admin sessions
 * @package WPShadow
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if admin is forced over HTTPS.
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Admin_HTTPS extends Diagnostic_Base
{
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array
	{
		// Only check if site has SSL
		if (! is_ssl()) {
			return null;
		}

		// Check if FORCE_SSL_ADMIN is enabled
		if (! defined('FORCE_SSL_ADMIN') || ! FORCE_SSL_ADMIN) {
			return array(
				'id'          => 'admin-https',
				'title'       => 'Admin Not Forced Over HTTPS',
				'description' => 'Your site has SSL but admin area is not forced over HTTPS. Enable FORCE_SSL_ADMIN to prevent session hijacking.',
				'severity'    => 'high',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/force-admin-https/',
				'training_link' => 'https://wpshadow.com/training/admin-https/',
				'auto_fixable' => false,
				'threat_level' => 80,
			);
		}

		return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Admin HTTPS
	 * Slug: -admin-https
	 * File: class-diagnostic-admin-https.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Admin HTTPS
	 * Slug: -admin-https
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
	public static function test_live_admin_https(): array
	{
		$result = self::check();

		if (!is_ssl()) {
			// If no SSL at all, check passes (check() returns null)
			$diagnostic_passed = is_null($result);
			return array('passed' => $diagnostic_passed, 'message' => 'No SSL - passes');
		}

		// Has SSL - should check for FORCE_SSL_ADMIN
		$has_force_ssl = (defined('FORCE_SSL_ADMIN') && FORCE_SSL_ADMIN);
		$should_pass = $has_force_ssl;
		$diagnostic_passed = is_null($result);
		$test_passes = ($should_pass === $diagnostic_passed);

		return array(
			'passed' => $test_passes,
			'message' => $test_passes ? 'Admin HTTPS check matches site state' :
				"Mismatch: expected " . ($should_pass ? 'pass' : 'fail') . " but got " .
				($diagnostic_passed ? 'pass' : 'fail'),
		);
	}
}
