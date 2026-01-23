<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Utm extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-utm_parameter_consistency', 'title' => __('UTM Parameter Consistency', 'wpshadow'), 'description' => __('Monitors UTM naming conventions. Inconsistency = analytics mess.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Monitor Utm
	 * Slug: -monitor-utm-parameter-consistency
	 * File: class-diagnostic-monitor-utm-parameter-consistency.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Monitor Utm
	 * Slug: -monitor-utm-parameter-consistency
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
	public static function test_live__monitor_utm_parameter_consistency(): array {
		$result = self::check();
		if ($result === null) {
			return ['passed' => true, 'message' => 'UTM parameters are consistent and traceable'];
		}
		$message = $result['description'] ?? 'UTM parameter inconsistency detected';
		return ['passed' => false, 'message' => $message];
	}

}
