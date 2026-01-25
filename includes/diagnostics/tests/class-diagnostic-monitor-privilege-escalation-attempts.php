<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_Monitor_Privilege_Escalation_Attempts extends Diagnostic_Base {

	public static function check(): ?array {
		// Check if monitoring plugins are active
		$has_monitoring = is_plugin_active( 'wordfence/wordfence.php' ) ||
			is_plugin_active( 'sucuri-scanner/sucuri.php' );
		if ( $has_monitoring ) {
			return null; // Monitoring in place
		}

		return array(
			'id'            => 'monitor-priv-escalation',
			'title'         => __( 'Privilege Escalation Attempts', 'wpshadow' ),
			'description'   => __( 'Detects when users try actions above their permission level. Subscriber accessing admin pages, user modifying others\' content.', 'wpshadow' ),
			'severity'      => 'high',
			'category'      => 'monitoring',
			'kb_link'       => 'https://wpshadow.com/kb/permission-control/',
			'training_link' => 'https://wpshadow.com/training/role-management/',
			'auto_fixable'  => false,
			'threat_level'  => 9,
		);
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Monitor Privilege Escalation Attempts
	 * Slug: -monitor-privilege-escalation-attempts
	 * File: class-diagnostic-monitor-privilege-escalation-attempts.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Monitor Privilege Escalation Attempts
	 * Slug: -monitor-privilege-escalation-attempts
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
	public static function test_live__monitor_privilege_escalation_attempts(): array {
		$has_monitoring = is_plugin_active( 'wordfence/wordfence.php' ) || is_plugin_active( 'sucuri-scanner/sucuri.php' );

		$diagnostic_result    = self::check();
		$should_find_issue    = ( ! $has_monitoring );
		$diagnostic_has_issue = ( null !== $diagnostic_result );
		$test_passes          = ( $should_find_issue === $diagnostic_has_issue );

		$message = sprintf(
			'Privilege-escalation monitoring active: %s. Expected diagnostic to %s issue. Diagnostic %s issue. Test: %s',
			$has_monitoring ? 'YES' : 'NO',
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
