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
	}}
