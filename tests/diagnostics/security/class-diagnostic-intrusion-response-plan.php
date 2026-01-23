<?php

declare(strict_types=1);
/**
 * Intrusion Response Plan Diagnostic
 *
 * Philosophy: Incident response - breach containment procedures
 * @package WPShadow
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if intrusion response procedures exist.
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Intrusion_Response_Plan extends Diagnostic_Base
{
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array
	{
		$response_plan = get_option('wpshadow_intrusion_response_plan');

		if (empty($response_plan)) {
			return array(
				'id'          => 'intrusion-response-plan',
				'title'       => 'No Intrusion Response Plan',
				'description' => 'No documented incident response procedures. If breached, you won\'t have a clear containment/recovery plan. Document incident response: quarantine, forensics, cleanup, notification.',
				'severity'    => 'medium',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/create-incident-response-plan/',
				'training_link' => 'https://wpshadow.com/training/breach-response/',
				'auto_fixable' => false,
				'threat_level' => 70,
			);
		}

		return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Intrusion Response Plan
	 * Slug: -intrusion-response-plan
	 * File: class-diagnostic-intrusion-response-plan.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Intrusion Response Plan
	 * Slug: -intrusion-response-plan
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
	public static function test_live__intrusion_response_plan(): array
	{
		$response_plan = get_option('wpshadow_intrusion_response_plan');
		$expected_issue = empty($response_plan);

		$result = self::check();
		$has_finding = is_array($result);

		if ($expected_issue === $has_finding) {
			$message = $expected_issue ? 'Finding returned when no intrusion response plan set.' : 'No finding returned when a response plan exists.';
			return array(
				'passed'  => true,
				'message' => $message,
			);
		}

		$message = $expected_issue
			? 'Expected a finding when no intrusion response plan is set, but got none.'
			: 'Expected no finding when intrusion response plan exists, but got a finding.';

		return array(
			'passed'  => false,
			'message' => $message,
		);
	}
}
