<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Does Site Work on Phones?
 * 
 * Target Persona: Non-technical Site Owner (Mom/Dad)
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Mobile_Friendly extends Diagnostic_Base {
    protected static $slug = 'mobile-friendly';
    protected static $title = 'Does Site Work on Phones?';
    protected static $description = 'Checks mobile-friendliness and viewport settings.';

    public static function check(): ?array {
        // Check for viewport meta tag
        ob_start();
        wp_head();
        $head = ob_get_clean();
        
        if (strpos($head, 'viewport') !== false) {
            return null; // Pass - viewport meta tag present
        }
        
        // Check if theme is responsive
        $current_theme = wp_get_theme();
        $tags = $current_theme->get('Tags');
        if ($tags && in_array('responsive', array_map('strtolower', $tags))) {
            return null; // Pass - responsive theme
        }
        
        return array(
            'id'            => static::$slug,
            'title'         => static::$title,
            'description'   => 'No viewport meta tag or responsive theme detected.',
            'color'         => '#ff9800',
            'bg_color'      => '#fff3e0',
            'kb_link'       => 'https://wpshadow.com/kb/mobile-friendly/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=mobile-friendly',
            'training_link' => 'https://wpshadow.com/training/mobile-friendly/',
            'auto_fixable'  => false,
            'threat_level'  => 60,
            'module'        => 'Design',
            'priority'      => 1,
        );
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Does Site Work on Phones?
	 * Slug: mobile-friendly
	 * 
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Checks mobile-friendliness and viewport settings.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_mobile_friendly(): array {
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
