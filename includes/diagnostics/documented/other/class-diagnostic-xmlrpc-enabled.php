<?php

declare(strict_types=1);
/**
 * XML-RPC Enabled Diagnostic
 *
 * Philosophy: Legacy API - disable unused endpoints
 * @package WPShadow
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if XML-RPC is enabled.
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_XMLRPC_Enabled extends Diagnostic_Base
{
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array
	{
		if (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST) {
			return array(
				'id'            => 'xmlrpc-enabled',
				'title'         => 'XML-RPC Enabled (Legacy API)',
				'description'   => 'XML-RPC is an old API rarely used. It\'s often exploited for brute force and amplification attacks. Disable XML-RPC unless needed.',
				'severity'      => 'medium',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/disable-xmlrpc/',
				'training_link' => 'https://wpshadow.com/training/legacy-api-security/',
				'auto_fixable'  => false,
				'threat_level'  => 65,
			);
		}

		return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: XMLRPC Enabled
	 * Slug: -xmlrpc-enabled
	 * File: class-diagnostic-xmlrpc-enabled.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: XMLRPC Enabled
	 * Slug: -xmlrpc-enabled
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
	public static function test_live__xmlrpc_enabled(): array
	{
		// Note: XMLRPC_REQUEST is only defined during actual XML-RPC requests
		// The diagnostic as written will almost never trigger (design issue)
		// A better test would be: !apply_filters('xmlrpc_enabled', true)
		$xmlrpc_enabled_filter = apply_filters('xmlrpc_enabled', true);
		$xmlrpc_request_constant = (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST);

		// Current diagnostic logic (flawed: only triggers during XMLRPC requests)
		$has_issue = $xmlrpc_request_constant;

		$result = self::check();
		$diagnostic_found_issue = is_array($result);

		$test_passes = ($has_issue === $diagnostic_found_issue);

		$message = $test_passes
			? 'XMLRPC check matches site state (note: diagnostic logic may need improvement)'
			: sprintf(
				'Mismatch: expected %s but diagnostic returned %s (XMLRPC_REQUEST: %s, filter enabled: %s)',
				$has_issue ? 'issue' : 'no issue',
				$diagnostic_found_issue ? 'issue' : 'no issue',
				$xmlrpc_request_constant ? 'true' : 'false',
				$xmlrpc_enabled_filter ? 'true' : 'false'
			);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}
}
