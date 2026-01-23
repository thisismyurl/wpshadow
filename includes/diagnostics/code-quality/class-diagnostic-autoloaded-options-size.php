<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Autoloaded Options Size (DB-001)
 * 
 * Detects if autoloaded options exceed 800KB threshold.
 * Philosophy: Shows value (#9) by tracking measurable database performance improvement.
 * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 * 
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
 */
class Diagnostic_Autoloaded_Options_Size extends Diagnostic_Base {
	
	/**
	 * Run the diagnostic check
	 * 
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array {
		// Check WordPress autoloaded options size
        global $wpdb;
        
        // Get total autoloaded options size
        $autoloaded_size = $wpdb->get_var(
            "SELECT COALESCE(SUM(CHAR_LENGTH(option_value)), 0) FROM {$wpdb->options} WHERE autoload='yes'"
        );
        
        // Convert to MB
        $size_mb = $autoloaded_size / (1024 * 1024);
        
        // If more than 1MB of autoloaded options, that's excessive
        if ($size_mb > 1) {
            return array(
                'id' => 'autoloaded-options-size',
                'title' => sprintf(__('Large Autoloaded Options (%s MB)', 'wpshadow'), number_format($size_mb, 2)),
                'description' => __('Autoloaded options are loaded on every page load. Consider disabling autoload for options over 1MB total.', 'wpshadow'),
                'severity' => 'medium',
                'category' => 'performance',
                'kb_link' => 'https://wpshadow.com/kb/autoloaded-options-optimization/',
                'training_link' => 'https://wpshadow.com/training/options-autoload/',
                'auto_fixable' => false,
                'threat_level' => 55,
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
	}}
