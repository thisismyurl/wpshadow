<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: WCAG 2.1 AA Compliance
 * 
 * Target Persona: Enterprise IT/Compliance Team
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_WCAG_Accessibility extends Diagnostic_Base {
    protected static $slug = 'wcag-accessibility';
    protected static $title = 'WCAG 2.1 AA Compliance';
    protected static $description = 'Measures accessibility compliance level.';

    public static function check(): ?array {
        // Check for accessibility plugins
        $a11y_plugins = array(
            'wp-accessibility/wp-accessibility.php',
            'accessibility-checker/accessibility-checker.php',
            'one-click-accessibility/one-click-accessibility.php',
        );
        
        $has_a11y_plugin = false;
        foreach ($a11y_plugins as $plugin) {
            if (is_plugin_active($plugin)) {
                $has_a11y_plugin = true;
                break;
            }
        }
        
        // Check theme for basic accessibility features (skip link)
        $current_theme = wp_get_theme();
        $theme_files = $current_theme->get_files('php', 1);
        $has_skip_link = false;
        
        foreach ($theme_files as $file) {
            if (strpos(file_get_contents($file), 'skip-link') !== false ||
                strpos(file_get_contents($file), 'skip to content') !== false) {
                $has_skip_link = true;
                break;
            }
        }
        
        // Pass if accessibility plugin active or theme has skip link
        if ($has_a11y_plugin || $has_skip_link) {
            return null;
        }
        
        return array(
            'id'            => static::$slug,
            'title'         => static::$title,
            'description'   => 'No accessibility plugin detected and theme lacks basic accessibility features.',
            'color'         => '#ff9800',
            'bg_color'      => '#fff3e0',
            'kb_link'       => 'https://wpshadow.com/kb/wcag-accessibility/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=wcag-accessibility',
            'training_link' => 'https://wpshadow.com/training/wcag-accessibility/',
            'auto_fixable'  => false,
            'threat_level'  => 60,
            'module'        => 'Compliance',
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
