<?php

declare(strict_types=1);
/**
 * Biometric Authentication Support Diagnostic
 *
 * Philosophy: Modern authentication - fingerprint/face recognition
 * @package WPShadow
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if biometric authentication is available.
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Biometric_Authentication extends Diagnostic_Base
{
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array
	{
		$biometric_plugins = array(
			'biometric-login/biometric-login.php',
			'webauthn/webauthn.php',
		);

		$active = get_option('active_plugins', array());
		foreach ($biometric_plugins as $plugin) {
			if (in_array($plugin, $active, true)) {
				return null;
			}
		}

		return array(
			'id'          => 'biometric-authentication',
			'title'       => 'No Biometric Authentication Support',
			'description' => 'Biometric/WebAuthn authentication not available. Offer passwordless login options (fingerprint, face recognition) for improved security and UX.',
			'severity'    => 'low',
			'category'    => 'security',
			'kb_link'     => 'https://wpshadow.com/kb/add-biometric-auth/',
			'training_link' => 'https://wpshadow.com/training/webauthn/',
			'auto_fixable' => false,
			'threat_level' => 45,
		);
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Biometric Authentication
	 * Slug: -biometric-authentication
	 * File: class-diagnostic-biometric-authentication.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Biometric Authentication
	 * Slug: -biometric-authentication
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
	public static function test_live__biometric_authentication(): array
	{
		$biometric_plugins = array(
			'biometric-login/biometric-login.php',
			'webauthn/webauthn.php',
		);

		$active = get_option('active_plugins', array());
		$has_biometric = false;
		foreach ($biometric_plugins as $plugin) {
			if (in_array($plugin, $active, true)) {
				$has_biometric = true;
				break;
			}
		}

		$expected_no_issue = $has_biometric;

		$result = self::check();
		$has_finding = ! is_null($result);

		if ($expected_no_issue !== $has_finding) {
			$message = $expected_no_issue ? 'Finding returned when biometric plugin is active.' : 'No finding when biometric plugin is inactive.';
			return array(
				'passed'  => true,
				'message' => $message,
			);
		}

		$message = $expected_no_issue
			? 'Expected no finding when biometric plugin active but got one.'
			: 'Expected a finding when biometric plugin inactive but got none.';

		return array(
			'passed'  => false,
			'message' => $message,
		);
	}
}
