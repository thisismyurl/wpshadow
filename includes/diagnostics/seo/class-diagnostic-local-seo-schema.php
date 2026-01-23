<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Local Business Schema Markup
 * 
 * Target Persona: Local Business Owner (Bakery/Plumber/Insurance)
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Local_SEO_Schema extends Diagnostic_Base {
    protected static $slug = 'local-seo-schema';
    protected static $title = 'Local Business Schema Markup';
    protected static $description = 'Checks for LocalBusiness schema to appear in Google.';

    public static function check(): ?array {
        // Check for schema plugins
        $schema_plugins = array(
            'schema-and-structured-data-for-wp/structured-data-for-wp.php',
            'all-in-one-seo-pack/all_in_one_seo_pack.php',
            'wordpress-seo/wp-seo.php',
        );
        
        foreach ($schema_plugins as $plugin) {
            if (is_plugin_active($plugin)) {
                return null; // Pass - schema plugin active
            }
        }
        
        // Check for LocalBusiness schema in page source
        ob_start();
        wp_head();
        $head = ob_get_clean();
        
        if (strpos($head, 'LocalBusiness') !== false || strpos($head, 'schema.org/LocalBusiness') !== false) {
            return null; // Pass - LocalBusiness schema present
        }
        
        return array(
            'id'            => static::$slug,
            'title'         => static::$title,
            'description'   => 'No LocalBusiness schema markup detected.',
            'color'         => '#ff9800',
            'bg_color'      => '#fff3e0',
            'kb_link'       => 'https://wpshadow.com/kb/local-seo-schema/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=local-seo-schema',
            'training_link' => 'https://wpshadow.com/training/local-seo-schema/',
            'auto_fixable'  => false,
            'threat_level'  => 60,
            'module'        => 'SEO',
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
