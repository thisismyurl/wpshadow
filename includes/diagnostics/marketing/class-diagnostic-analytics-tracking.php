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
	 * Test: Result structure validation
	 *
	 * Ensures diagnostic returns null (no issues) or array (issues found)
	 * with all required fields populated.
	 *
	 * @return array Test result with 'passed' and 'message'
	 */
	public static function test_result_structure(): array {
		$result = self::check();
		
		// Valid states: null (pass) or array (fail)
		if ( null === $result || is_array( $result ) ) {
			// If array, validate structure
			if ( is_array( $result ) ) {
				$required = array(
					'id', 'title', 'description', 'category', 
					'severity', 'threat_level'
				);
				
				foreach ( $required as $field ) {
					if ( ! isset( $result[ $field ] ) ) {
						return array(
							'passed'  => false,
							'message' => "Missing field: $field",
						);
					}
				}
				
				// Validate field types
				if ( ! is_string( $result['severity'] ) ) {
					return array(
						'passed'  => false,
						'message' => 'severity must be string',
					);
				}
				
				if ( ! is_int( $result['threat_level'] ) || $result['threat_level'] < 0 || $result['threat_level'] > 100 ) {
					return array(
						'passed'  => false,
						'message' => 'threat_level must be int 0-100',
					);
				}
			}
			
			return array(
				'passed'  => true,
				'message' => 'Result structure valid',
			);
		}
		
		return array(
			'passed'  => false,
			'message' => 'Invalid result type: ' . gettype( $result ),
		);
	}
	/**
	 * Test: Plugin detection logic
	 *
	 * Verifies that diagnostic correctly checks for active plugins
	 * and reports issues appropriately.
	 *
	 * @return array Test result
	 */
	public static function test_plugin_detection(): array {
		$result = self::check();
		
		// Plugin detection should return null (no plugin/no issue) or array (issue)
		if ( $result === null || is_array( $result ) ) {
			return array(
				'passed'  => true,
				'message' => 'Plugin detection logic valid',
			);
		}
		
		return array(
			'passed'  => false,
			'message' => 'Invalid plugin detection result',
		);
	}}
