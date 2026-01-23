<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Google Analytics 4 Installed?
 * 
 * Target Persona: Digital Marketing Agency
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_GA4_Installed extends Diagnostic_Base {
    protected static $slug = 'ga4-installed';
    protected static $title = 'Google Analytics 4 Installed?';
    protected static $description = 'Checks if GA4 tracking is configured.';

    public static function check(): ?array {
        // Check for GA4 plugins
        $ga4_plugins = array(
            'google-analytics-for-wordpress/googleanalytics.php',
            'google-site-kit/google-site-kit.php',
            'ga-google-analytics/ga-google-analytics.php',
        );
        
        foreach ($ga4_plugins as $plugin) {
            if (is_plugin_active($plugin)) {
                return null; // Pass - GA4 plugin active
            }
        }
        
        // Check for GA4 code in header/footer (G-XXXXXXXX format)
        ob_start();
        wp_head();
        $header_content = ob_get_clean();
        
        if (preg_match('/G-[A-Z0-9]{10}/', $header_content)) {
            return null; // Pass - GA4 code detected
        }
        
        return array(
            'id'            => static::$slug,
            'title'         => static::$title,
            'description'   => 'Google Analytics 4 tracking not detected.',
            'color'         => '#ff9800',
            'bg_color'      => '#fff3e0',
            'kb_link'       => 'https://wpshadow.com/kb/ga4-installed/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=ga4-installed',
            'training_link' => 'https://wpshadow.com/training/ga4-installed/',
            'auto_fixable'  => false,
            'threat_level'  => 60,
            'module'        => 'Marketing',
            'priority'      => 1,
        );
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Google Analytics 4 Installed?
	 * Slug: ga4-installed
	 * 
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Checks if GA4 tracking is configured.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_ga4_installed(): array {
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
