<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Suspicious_User_Agents extends Diagnostic_Base {
    public static function check(): ?array {
        return ['id' => 'monitor-user-agents', 'title' => __('Suspicious User Agent Detection', 'wpshadow'), 'description' => __('Detects scanning tools, exploit frameworks, malicious bots. Distinguishes legitimate crawlers from reconnaissance tools.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/bot-management/', 'training_link' => 'https://wpshadow.com/training/traffic-filtering/', 'auto_fixable' => false, 'threat_level' => 6];
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Monitor Suspicious User Agents
	 * Slug: -monitor-suspicious-user-agents
	 * File: class-diagnostic-monitor-suspicious-user-agents.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Monitor Suspicious User Agents
	 * Slug: -monitor-suspicious-user-agents
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
	public static function test_live__monitor_suspicious_user_agents(): array {
		$result = self::check();
		if ($result === null) {
			return ['passed' => true, 'message' => 'No suspicious user agents detected'];
		}
		$message = $result['description'] ?? 'Suspicious user agent activity detected';
		return ['passed' => false, 'message' => $message];
	}

}
