<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_WordPress_Core_Update extends Diagnostic_Base {
    public static function check(): ?array {
        return ['id' => 'monitor-wp-updates', 'title' => __('WordPress Core Update Available', 'wpshadow'), 'description' => __('Alerts when WordPress major/minor/patch updates available. Delays increase security risk and incompatibility.', 'wpshadow'), 'severity' => 'high', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/wordpress-updates/', 'training_link' => 'https://wpshadow.com/training/core-upgrades/', 'auto_fixable' => false, 'threat_level' => 8];
    }


	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Monitor WordPress Core Update
	 * Slug: -monitor-wordpress-core-update
	 * File: class-diagnostic-monitor-wordpress-core-update.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Monitor WordPress Core Update
	 * Slug: -monitor-wordpress-core-update
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
	public static function test_live__monitor_wordpress_core_update(): array {
		$result = self::check();
		if ($result === null) {
			return ['passed' => true, 'message' => 'WordPress core is up-to-date'];
		}
		$message = $result['description'] ?? 'WordPress update available';
		return ['passed' => false, 'message' => $message];
	}

}
