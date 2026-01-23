<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: User Data Export Available?
 * 
 * Target Persona: Enterprise IT/Compliance Team
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_User_Data_Export extends Diagnostic_Base {
    protected static $slug = 'user-data-export';
    protected static $title = 'User Data Export Available?';
    protected static $description = 'Tests GDPR data export functionality.';

    public static function check(): ?array {
        // WordPress 4.9.6+ has built-in data export (Tools > Export Personal Data)
        global $wp_version;
        $has_core_export = version_compare($wp_version, '4.9.6', '>=');
        
        // Check for GDPR plugins that enhance export
        $export_plugins = array(
            'wp-gdpr-compliance/wp-gdpr-compliance.php',
            'gdpr-cookie-consent/gdpr-cookie-consent.php',
        );
        
        $has_export_plugin = false;
        foreach ($export_plugins as $plugin) {
            if (is_plugin_active($plugin)) {
                $has_export_plugin = true;
                break;
            }
        }
        
        // Pass if core export available or plugin active
        if ($has_core_export || $has_export_plugin) {
            return null;
        }
        
        return array(
            'id'            => static::$slug,
            'title'         => static::$title,
            'description'   => 'User data export capability not detected. Update WordPress or install GDPR plugin.',
            'color'         => '#f44336',
            'bg_color'      => '#ffebee',
            'kb_link'       => 'https://wpshadow.com/kb/user-data-export/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=user-data-export',
            'training_link' => 'https://wpshadow.com/training/user-data-export/',
            'auto_fixable'  => false,
            'threat_level'  => 60,
            'module'        => 'Compliance',
            'priority'      => 1,
        );
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: User Data Export Available?
	 * Slug: user-data-export
	 * 
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Tests GDPR data export functionality.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_user_data_export(): array {
		$result = self::check();
		
		global $wp_version;
		$has_core_export = version_compare($wp_version, '4.9.6', '>=');
		
		$export_plugins = array(
			'wp-gdpr-compliance/wp-gdpr-compliance.php',
			'gdpr-cookie-consent/gdpr-cookie-consent.php',
		);
		
		$has_export_plugin = false;
		foreach ($export_plugins as $plugin) {
			if (is_plugin_active($plugin)) {
				$has_export_plugin = true;
				break;
			}
		}
		
		$should_pass = ($has_core_export || $has_export_plugin);
		$diagnostic_passed = is_null($result);
		$test_passes = ($should_pass === $diagnostic_passed);
		
		return array(
			'passed' => $test_passes,
			'message' => $test_passes ? 'User data export check matches site state' : 
				"Mismatch: expected " . ($should_pass ? 'pass' : 'fail') . " but got " . 
				($diagnostic_passed ? 'pass' : 'fail'),
		);
	}

}
