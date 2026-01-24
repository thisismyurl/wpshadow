<?php

declare(strict_types=1);
/**
 * Admin HTTPS Enforcement Diagnostic
 *
 * Philosophy: Security hardening - protect admin sessions
 * @package WPShadow
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Loaded via Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if admin is forced over HTTPS.
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Loaded via Diagnostic_Registry
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
	 * Diagnostic: Admin HTTPS Enforcement
	 * Slug: admin-https
	 * File: class-diagnostic-admin-https.php
	 *
	 * Test Purpose:
	 * Verify that admin is forced over HTTPS when SSL is available
	 * - PASS: check() returns NULL when FORCE_SSL_ADMIN is enabled (site is secure)
	 * - FAIL: check() returns array when FORCE_SSL_ADMIN is disabled (security issue)
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_admin_https(): array
	{
		$result = self::check();

		// Test passes if check result matches site configuration
		if (!is_ssl()) {
			// No SSL = diagnostic should pass (return null)
			return array(
				'passed' => is_null($result),
				'message' => 'Site has no SSL, diagnostic correctly passes'
			);
		}

		// Has SSL - check FORCE_SSL_ADMIN
		$has_force_ssl = (defined('FORCE_SSL_ADMIN') && FORCE_SSL_ADMIN);
		if ($has_force_ssl) {
			// SSL + FORCE_SSL_ADMIN enabled = pass (return null)
			return array(
				'passed' => is_null($result),
				'message' => 'Admin HTTPS enforced with FORCE_SSL_ADMIN'
			);
		} else {
			// SSL but no FORCE_SSL_ADMIN = issue found (return array)
			return array(
				'passed' => !is_null($result) && isset($result['id']) && $result['id'] === 'admin-https',
				'message' => 'Admin HTTPS not enforced, issue correctly identified'
			);
		}
	}
}
