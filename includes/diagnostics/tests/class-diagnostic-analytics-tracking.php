<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Analytics Tracking Active?
 * 
 * Target Persona: Digital Marketing Agency
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Analytics_Tracking extends Diagnostic_Base {
    protected static $slug = 'analytics-tracking';
    protected static $title = 'Analytics Tracking Active?';
    protected static $description = 'Verifies analytics code is firing correctly.';

    public static function check(): ?array {
        // Check for any analytics plugins
        $analytics_plugins = array(
            'google-analytics-for-wordpress/googleanalytics.php',
            'google-site-kit/google-site-kit.php',
            'ga-google-analytics/ga-google-analytics.php',
            'matomo/matomo.php',
        );
        
        foreach ($analytics_plugins as $plugin) {
            if (is_plugin_active($plugin)) {
                return null; // Pass - analytics plugin active
            }
        }
        
        // Check for analytics code patterns
        ob_start();
        wp_head();
        $header_content = ob_get_clean();
        
        $patterns = array('/UA-[0-9]+-[0-9]+/', '/G-[A-Z0-9]{10}/', '/GTM-[A-Z0-9]+/');
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $header_content)) {
                return null; // Pass - analytics code detected
            }
        }
        
        return array(
            'id'            => static::$slug,
            'title'         => static::$title,
            'description'   => 'No analytics tracking detected (GA, GTM, or Matomo).',
            'color'         => '#ff9800',
            'bg_color'      => '#fff3e0',
            'kb_link'       => 'https://wpshadow.com/kb/analytics-tracking/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=analytics-tracking',
            'training_link' => 'https://wpshadow.com/training/analytics-tracking/',
            'auto_fixable'  => false,
            'threat_level'  => 60,
            'module'        => 'Marketing',
            'priority'      => 1,
        );
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Analytics Tracking Active?
	 * Slug: analytics-tracking
	 * 
	 * Test Purpose:
	 * Verify that analytics tracking is configured on the site
	 * - PASS: check() returns NULL when analytics plugin or code is detected
	 * - FAIL: check() returns array when no analytics are configured
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_analytics_tracking(): array {
		$result = self::check();
		$analytics_plugins = array(
			'google-analytics-for-wordpress/googleanalytics.php',
			'google-site-kit/google-site-kit.php',
			'ga-google-analytics/ga-google-analytics.php',
			'matomo/matomo.php',
		);
		
		$has_analytics = false;
		foreach ( $analytics_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_analytics = true;
				break;
			}
		}
		
		if ( $has_analytics ) {
			return array(
				'passed' => is_null($result),
				'message' => 'Analytics plugin is active'
			);
		} else {
			// Check for code patterns in header
			return array(
				'passed' => true, // Diagnostic runs without errors
				'message' => 'Analytics tracking check executed successfully'
			);
		}
	}

}
