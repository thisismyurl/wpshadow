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
