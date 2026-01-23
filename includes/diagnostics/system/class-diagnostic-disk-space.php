<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Disk Space Monitoring
 * 
 * Target Persona: Web Hosting Provider
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Disk_Space extends Diagnostic_Base {
    protected static $slug = 'disk-space';
    protected static $title = 'Disk Space Monitoring';
    protected static $description = 'Alerts when disk usage exceeds thresholds.';

    public static function check(): ?array {
        $upload_dir = wp_upload_dir();
        $path = $upload_dir['basedir'];
        
        $free_space = @disk_free_space($path);
        $total_space = @disk_total_space($path);
        
        if ($free_space === false || $total_space === false) {
            return null;
        }
        
        $used_space = $total_space - $free_space;
        $usage_percent = ($used_space / $total_space) * 100;
        
        if ($usage_percent < 80) {
            return null;
        }
        
        $severity = 'medium';
        $threat = 60;
        if ($usage_percent >= 95) {
            $severity = 'critical';
            $threat = 100;
        } elseif ($usage_percent >= 90) {
            $severity = 'high';
            $threat = 80;
        }
        
        return array(
            'id'            => static::$slug,
            'title'         => sprintf(__('Disk usage at %.1f%%', 'wpshadow'), $usage_percent),
            'description'   => sprintf(
                __('Only %s of %s remaining. Consider cleaning up old backups, uploads, or upgrading storage.', 'wpshadow'),
                size_format($free_space),
                size_format($total_space)
            ),
            'severity'      => $severity,
            'category'      => 'system',
            'kb_link'       => 'https://wpshadow.com/kb/disk-space/',
            'training_link' => 'https://wpshadow.com/training/disk-space/',
            'auto_fixable'  => false,
            'threat_level'  => $threat,
            'disk_stats'    => array(
                'free' => $free_space,
                'total' => $total_space,
                'used' => $used_space,
                'usage_percent' => $usage_percent,
            ),
        );
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Disk Space Monitoring
	 * Slug: disk-space
	 * 
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Alerts when disk usage exceeds thresholds.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_disk_space(): array {
		/*
		 * IMPLEMENTATION NOTES:
		 * - This test validates the actual WordPress site state
		 * - Do not use mocks or stubs
		 * - Call self::check() to get the diagnostic result
		 * - Verify the result matches expected site state
		 * - Return [ 'passed' => bool, 'message' => string ]
		 */
		
		$result = self::check();
		
		// Pattern: check() returns NULL when disk usage < 80% (healthy)
		// Pattern: check() returns array when disk usage >= 80% (issue found)
		
		if ($result === null) {
			return [
				'passed' => true,
				'message' => 'Disk space usage is healthy (less than 80%)',
			];
		}
		
		$usage = $result['disk_stats']['usage_percent'] ?? 0;
		return [
			'passed' => false,
			'message' => "Disk space usage is high at {$usage}%",
		];
	}

}
