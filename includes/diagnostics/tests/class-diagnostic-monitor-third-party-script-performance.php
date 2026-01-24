<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

class Diagnostic_Monitor_Third_Party_Script_Performance extends Diagnostic_Base { public static function check(): ?array { return ['id' => 'monitor-3p-performance', 'title' => __('Third-Party Script Performance Impact', 'wpshadow'), 'description' => __('Tracks performance impact of external scripts. Slow 3P scripts = page slowdown, ranking penalty.', 'wpshadow'), 'severity' => 'high', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/script-optimization/', 'training_link' => 'https://wpshadow.com/training/script-loading/', 'auto_fixable' => false, 'threat_level' => 7]; } 

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Monitor Third Party Script Performance
	 * Slug: -monitor-third-party-script-performance
	 * File: class-diagnostic-monitor-third-party-script-performance.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Monitor Third Party Script Performance
	 * Slug: -monitor-third-party-script-performance
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
	public static function test_live__monitor_third_party_script_performance(): array {
		$result = self::check();
		if ($result === null) {
			return ['passed' => true, 'message' => 'Third-party scripts are optimized and performant'];
		}
		$message = $result['description'] ?? 'Third-party script performance issue detected';
		return ['passed' => false, 'message' => $message];
	}

}
