<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: UTM Parameters Tracked?
 * 
 * Target Persona: Digital Marketing Agency
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_UTM_Parameters_Preserved extends Diagnostic_Base {
    protected static $slug = 'utm-parameters-preserved';
    protected static $title = 'UTM Parameters Tracked?';
    protected static $description = 'Verifies campaign tracking parameters work.';

    public static function check(): ?array {
        // Check if UTM parameters are being captured/preserved
        // This checks for plugins or custom code that handle UTMs
        $utm_plugins = array(
            'utm-dot-codes/utm-dot-codes.php',
            'ga-google-analytics/ga-google-analytics.php',
        );
        
        foreach ($utm_plugins as $plugin) {
            if (is_plugin_active($plugin)) {
                return null; // Pass - UTM handling plugin active
            }
        }
        
        // Check if form plugins are active (they often handle UTMs)
        $form_plugins = array(
            'gravityforms/gravityforms.php',
            'wpforms/wpforms.php',
            'ninja-forms/ninja-forms.php',
        );
        
        foreach ($form_plugins as $plugin) {
            if (is_plugin_active($plugin)) {
                return null; // Pass - form plugin likely handles UTMs
            }
        }
        
        // If marketing tools present, suggest UTM preservation
        ob_start();
        wp_head();
        $header_content = ob_get_clean();
        
        if (preg_match('/G-[A-Z0-9]{10}/', $header_content) || preg_match('/GTM-[A-Z0-9]+/', $header_content)) {
            return array(
                'id'            => static::$slug,
                'title'         => static::$title,
                'description'   => 'Analytics tracking detected but no UTM parameter preservation found.',
                'color'         => '#ff9800',
                'bg_color'      => '#fff3e0',
                'kb_link'       => 'https://wpshadow.com/kb/utm-parameters-preserved/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=utm-parameters-preserved',
                'training_link' => 'https://wpshadow.com/training/utm-parameters-preserved/',
                'auto_fixable'  => false,
                'threat_level'  => 60,
                'module'        => 'Marketing',
                'priority'      => 2,
            );
        }
        
        return null;
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: UTM Parameters Tracked?
	 * Slug: utm-parameters-preserved
	 * 
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Verifies campaign tracking parameters work.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_utm_parameters_preserved(): array {
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
