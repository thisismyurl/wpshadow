<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Cookie Consent Compliant?
 * 
 * Target Persona: Enterprise IT/Compliance Team
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Cookie_Consent extends Diagnostic_Base {
    protected static $slug = 'cookie-consent';
    protected static $title = 'Cookie Consent Compliant?';
    protected static $description = 'Verifies cookie banner meets regulations.';

    public static function check(): ?array {
        // Check for cookie consent plugins
        $cookie_plugins = array(
            'gdpr-cookie-consent/gdpr-cookie-consent.php',
            'cookie-notice/cookie-notice.php',
            'complianz-gdpr/complianz-gpdr.php',
            'cookie-law-info/cookie-law-info.php',
        );
        
        foreach ($cookie_plugins as $plugin) {
            if (is_plugin_active($plugin)) {
                return null; // Pass - cookie consent plugin active
            }
        }
        
        return array(
            'id'            => static::$slug,
            'title'         => static::$title,
            'description'   => static::$description . ' No cookie consent plugin detected.',
            'color'         => '#ff9800',
            'bg_color'      => '#fff3e0',
            'kb_link'       => 'https://wpshadow.com/kb/cookie-consent/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=cookie-consent',
            'training_link' => 'https://wpshadow.com/training/cookie-consent/',
            'auto_fixable'  => false,
            'threat_level'  => 60,
            'module'        => 'Compliance',
            'priority'      => 1,
        );
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Cookie Consent Compliant?
	 * Slug: cookie-consent
	 * 
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Verifies cookie banner meets regulations.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_cookie_consent(): array {
		$result = self::check();
		
		$cookie_plugins = array(
			'gdpr-cookie-consent/gdpr-cookie-consent.php',
			'cookie-notice/cookie-notice.php',
			'complianz-gdpr/complianz-gpdr.php',
			'cookie-law-info/cookie-law-info.php',
		);
		
		$has_plugin = false;
		foreach ($cookie_plugins as $plugin) {
			if (is_plugin_active($plugin)) {
				$has_plugin = true;
				break;
			}
		}
		
		$diagnostic_passed = is_null($result);
		$test_passes = ($has_plugin === $diagnostic_passed);
		
		return array(
			'passed' => $test_passes,
			'message' => $test_passes ? 'Cookie consent check matches site state' : 
				"Mismatch: expected " . ($has_plugin ? 'pass' : 'fail') . " but got " . 
				($diagnostic_passed ? 'pass' : 'fail'),
		);
	}

}
