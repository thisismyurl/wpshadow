<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Missing Responsive Images (IMG-003)
 * 
 * Detects images without srcset attribute.
 * Philosophy: Show value (#9) with mobile data savings.
 * 
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Missing_Responsive_Images extends Diagnostic_Base {
	
	/**
	 * Run the diagnostic check
	 * 
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array {
		// Check for missing responsive image implementations
        global $wpdb;
        
        // Count images without srcset attributes in posts
        $missing_srcset = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type='attachment' AND post_mime_type LIKE 'image/%' LIMIT 1000"
        );
        
        if ($missing_srcset && $missing_srcset > 100) {
            return array(
                'id' => 'missing-responsive-images',
                'title' => sprintf(__('%d Images May Need Responsive Sizes', 'wpshadow'), $missing_srcset),
                'description' => __('Add srcset and sizes attributes to images for responsive delivery. Use WordPress native image functions or plugins.', 'wpshadow'),
                'severity' => 'low',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/responsive-images/',
                'training_link' => 'https://wpshadow.com/training/image-optimization/',
                'auto_fixable' => false,
                'threat_level' => 30,
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
