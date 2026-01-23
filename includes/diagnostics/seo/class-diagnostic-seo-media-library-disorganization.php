<?php
declare(strict_types=1);
/**
 * Media Library Disorganization Diagnostic
 *
 * Philosophy: Organized media improves management
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Media_Library_Disorganization extends Diagnostic_Base {
    public static function check(): ?array {
        global $wpdb;
        $unattached = (int) $wpdb->get_var("SELECT COUNT(1) FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_parent = 0");
        if ($unattached > 500) {
            return [
                'id' => 'seo-media-library-disorganization',
                'title' => 'Unattached Media Files',
                'description' => sprintf('%d unattached media files. Consider organizing media library for better management.', $unattached),
                'severity' => 'low',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/media-organization/',
                'training_link' => 'https://wpshadow.com/training/media-management/',
                'auto_fixable' => false,
                'threat_level' => 10,
            ];
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
