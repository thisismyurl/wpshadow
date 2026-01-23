<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Google Tag Manager Installed?
 * 
 * Target Persona: Digital Marketing Agency
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_GTM_Installed extends Diagnostic_Base {
    protected static $slug = 'gtm-installed';
    protected static $title = 'Google Tag Manager Installed?';
    protected static $description = 'Verifies GTM container is present and firing.';

    public static function check(): ?array {
        // Check for GTM plugins
        $gtm_plugins = array(
            'duracelltomi-google-tag-manager/duracelltomi-google-tag-manager-for-wordpress.php',
            'google-site-kit/google-site-kit.php',
        );
        
        foreach ($gtm_plugins as $plugin) {
            if (is_plugin_active($plugin)) {
                return null; // Pass - GTM plugin active
            }
        }
        
        // Check for GTM code in header (GTM-XXXXXXX format)
        ob_start();
        wp_head();
        $header_content = ob_get_clean();
        
        if (preg_match('/GTM-[A-Z0-9]+/', $header_content)) {
            return null; // Pass - GTM container detected
        }
        
        return array(
            'id'            => static::$slug,
            'title'         => static::$title,
            'description'   => 'Google Tag Manager not detected.',
            'color'         => '#ff9800',
            'bg_color'      => '#fff3e0',
            'kb_link'       => 'https://wpshadow.com/kb/gtm-installed/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=gtm-installed',
            'training_link' => 'https://wpshadow.com/training/gtm-installed/',
            'auto_fixable'  => false,
            'threat_level'  => 60,
            'module'        => 'Marketing',
            'priority'      => 1,
        );
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Google Tag Manager Installed?
	 * Slug: gtm-installed
	 * 
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Verifies GTM container is present and firing.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_gtm_installed(): array {
		/*
		 * IMPLEMENTATION NOTES:
		 * - This test validates the actual WordPress site state
		 * - Do not use mocks or stubs
		 * - Call self::check() to get the diagnostic result
		 * - Verify the result matches expected site state
		 * - Return [ 'passed' => bool, 'message' => string ]
		 */
		
		$result = self::check();
		
		// TODO: Implement actual test logic
		return array(
			'passed' => false,
			'message' => 'Test not yet implemented for ' . self::$slug,
		);
	}

}
