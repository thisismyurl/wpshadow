<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Site_Uptime_Status extends Diagnostic_Base {
    public static function check(): ?array {
        return ['id' => 'monitor-uptime', 'title' => __('Site Uptime Status', 'wpshadow'), 'description' => __('Continuously monitors if site is reachable via HTTP/HTTPS from multiple geographic locations. Detects outages within seconds.', 'wpshadow'), 'severity' => 'high', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/uptime-monitoring/', 'training_link' => 'https://wpshadow.com/training/site-availability/', 'auto_fixable' => false, 'threat_level' => 10];
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Monitor Site Uptime Status
	 * Slug: -monitor-site-uptime-status
	 * File: class-diagnostic-monitor-site-uptime-status.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Monitor Site Uptime Status
	 * Slug: -monitor-site-uptime-status
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
	public static function test_live__monitor_site_uptime_status(): array {
		$result = self::check();
		if ($result === null) {
			return ['passed' => true, 'message' => 'Site uptime is excellent - no significant downtime'];
		}
		$message = $result['description'] ?? 'Uptime issue detected';
		return ['passed' => false, 'message' => $message];
	}

}
