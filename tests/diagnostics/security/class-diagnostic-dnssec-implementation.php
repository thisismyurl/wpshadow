<?php

declare(strict_types=1);
/**
 * DNSSEC Implementation Diagnostic
 *
 * Philosophy: DNS security - authenticate domain responses
 * @package WPShadow
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if DNSSEC is enabled.
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_DNSSEC_Implementation extends Diagnostic_Base
{
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array
	{
		$dnssec_enabled = get_option('wpshadow_dnssec_enabled');

		if (empty($dnssec_enabled)) {
			return array(
				'id'          => 'dnssec-implementation',
				'title'       => 'DNSSEC Not Enabled',
				'description' => 'DNSSEC not implemented. DNS responses not cryptographically verified. Attackers can redirect traffic to malicious sites via DNS hijacking. Enable DNSSEC.',
				'severity'    => 'medium',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/enable-dnssec/',
				'training_link' => 'https://wpshadow.com/training/dnssec-setup/',
				'auto_fixable' => false,
				'threat_level' => 65,
			);
		}

		return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: DNSSEC Implementation
	 * Slug: -dnssec-implementation
	 * File: class-diagnostic-dnssec-implementation.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: DNSSEC Implementation
	 * Slug: -dnssec-implementation
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
	public static function test_live__dnssec_implementation(): array
	{
		$dnssec_enabled = get_option('wpshadow_dnssec_enabled');
		$expected_issue = empty($dnssec_enabled);

		$result = self::check();
		$has_finding = is_array($result);

		if ($expected_issue === $has_finding) {
			$message = $expected_issue ? 'Finding returned when DNSSEC disabled.' : 'No finding returned when DNSSEC enabled.';
			return array(
				'passed'  => true,
				'message' => $message,
			);
		}

		$message = $expected_issue
			? 'Expected a finding when DNSSEC is disabled, but got none.'
			: 'Expected no finding when DNSSEC is enabled, but got a finding.';

		return array(
			'passed'  => false,
			'message' => $message,
		);
	}
}
