<?php

declare(strict_types=1);
/**
 * Auth Cookie Domain Scope Diagnostic
 *
 * Philosophy: Cookie security - minimize cookie scope
 * @package WPShadow
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check authentication cookie domain configuration.
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Auth_Cookie_Domain extends Diagnostic_Base
{
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array
	{
		if (! defined('COOKIE_DOMAIN')) {
			return null; // Using default (exact domain)
		}

		$cookie_domain = COOKIE_DOMAIN;

		// Check if wildcard subdomain is used
		if (strpos($cookie_domain, '.') === 0) {
			// Wildcard like .example.com
			return array(
				'id'          => 'auth-cookie-domain',
				'title'       => 'Overly Broad Cookie Domain',
				'description' => sprintf(
					'COOKIE_DOMAIN is set to "%s" (wildcard subdomain). Authentication cookies will be sent to ALL subdomains, potentially exposing sessions to compromised subdomains. Use exact domain instead.',
					$cookie_domain
				),
				'severity'    => 'high',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/restrict-cookie-domain/',
				'training_link' => 'https://wpshadow.com/training/cookie-security/',
				'auto_fixable' => false,
				'threat_level' => 70,
			);
		}

		return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live__auth_cookie_domain(): array
	{
		// Check current COOKIE_DOMAIN setting
		$has_issue = false;
		if (defined('COOKIE_DOMAIN')) {
			$cookie_domain = COOKIE_DOMAIN;
			// Wildcard like .example.com indicates overly broad scope
			if (strpos($cookie_domain, '.') === 0) {
				$has_issue = true;
			}
		}

		$result = self::check();
		$diagnostic_found_issue = !is_null($result);
		$test_passes = ($has_issue === $diagnostic_found_issue);

		return array(
			'passed' => $test_passes,
			'message' => $test_passes ? 'Auth cookie domain check matches site state' :
				"Mismatch: expected " . ($has_issue ? 'issue' : 'no issue') . " but got " .
				($diagnostic_found_issue ? 'issue' : 'pass'),
		);
	}
}
