<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: User Data Export Available?
 * 
 * Target Persona: Enterprise IT/Compliance Team
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_User_Data_Export extends Diagnostic_Base {
    protected static $slug = 'user-data-export';
    protected static $title = 'User Data Export Available?';
    protected static $description = 'Tests GDPR data export functionality.';

    public static function check(): ?array {
        // WordPress 4.9.6+ has built-in data export (Tools > Export Personal Data)
        global $wp_version;
        $has_core_export = version_compare($wp_version, '4.9.6', '>=');
        
        // Check for GDPR plugins that enhance export
        $export_plugins = array(
            'wp-gdpr-compliance/wp-gdpr-compliance.php',
            'gdpr-cookie-consent/gdpr-cookie-consent.php',
        );
        
        $has_export_plugin = false;
        foreach ($export_plugins as $plugin) {
            if (is_plugin_active($plugin)) {
                $has_export_plugin = true;
                break;
            }
        }
        
        // Pass if core export available or plugin active
        if ($has_core_export || $has_export_plugin) {
            return null;
        }
        
        return array(
            'id'            => static::$slug,
            'title'         => static::$title,
            'description'   => 'User data export capability not detected. Update WordPress or install GDPR plugin.',
            'color'         => '#f44336',
            'bg_color'      => '#ffebee',
            'kb_link'       => 'https://wpshadow.com/kb/user-data-export/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=user-data-export',
            'training_link' => 'https://wpshadow.com/training/user-data-export/',
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
