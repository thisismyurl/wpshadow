<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Data Retention Policy Set?
 * 
 * Target Persona: Enterprise IT/Compliance Team
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Data_Retention_Policy extends Diagnostic_Base {
    protected static $slug = 'data-retention-policy';
    protected static $title = 'Data Retention Policy Set?';
    protected static $description = 'Checks if data retention schedule is configured.';

    public static function check(): ?array {
        // Check if data retention settings are configured
        // WordPress has built-in comment/user cleanup settings
        $comment_days = (int) get_option('wp_delete_comment_older_than', 0);
        $user_cleanup = get_option('wpshadow_data_retention_days', 0);
        
        // Check for privacy/GDPR plugins that manage retention
        $retention_plugins = array(
            'wp-gdpr-compliance/wp-gdpr-compliance.php',
            'gdpr-cookie-consent/gdpr-cookie-consent.php',
        );
        
        $has_retention_plugin = false;
        foreach ($retention_plugins as $plugin) {
            if (is_plugin_active($plugin)) {
                $has_retention_plugin = true;
                break;
            }
        }
        
        // Pass if retention configured via plugin or settings
        if ($has_retention_plugin || $comment_days > 0 || $user_cleanup > 0) {
            return null;
        }
        
        return array(
            'id'            => static::$slug,
            'title'         => static::$title,
            'description'   => 'No data retention policy configured. Consider setting automatic cleanup schedules.',
            'color'         => '#ff9800',
            'bg_color'      => '#fff3e0',
            'kb_link'       => 'https://wpshadow.com/kb/data-retention-policy/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=data-retention-policy',
            'training_link' => 'https://wpshadow.com/training/data-retention-policy/',
            'auto_fixable'  => false,
            'threat_level'  => 60,
            'module'        => 'Compliance',
            'priority'      => 2,
        );
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Data Retention Policy Set?
	 * Slug: data-retention-policy
	 * 
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Checks if data retention schedule is configured.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_data_retention_policy(): array {
		$result = self::check();
		
		$comment_days = (int) get_option('wp_delete_comment_older_than', 0);
		$user_cleanup = get_option('wpshadow_data_retention_days', 0);
		
		$retention_plugins = array(
			'wp-gdpr-compliance/wp-gdpr-compliance.php',
			'gdpr-cookie-consent/gdpr-cookie-consent.php',
		);
		
		$has_retention_plugin = false;
		foreach ($retention_plugins as $plugin) {
			if (is_plugin_active($plugin)) {
				$has_retention_plugin = true;
				break;
			}
		}
		
		$should_pass = ($has_retention_plugin || $comment_days > 0 || $user_cleanup > 0);
		$diagnostic_passed = is_null($result);
		$test_passes = ($should_pass === $diagnostic_passed);
		
		return array(
			'passed' => $test_passes,
			'message' => $test_passes ? 'Data retention check matches site state' : 
				"Mismatch: expected " . ($should_pass ? 'pass' : 'fail') . " but got " . 
				($diagnostic_passed ? 'pass' : 'fail'),
		);
	}

}
