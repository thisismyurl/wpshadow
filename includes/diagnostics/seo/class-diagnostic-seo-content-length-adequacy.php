<?php
declare(strict_types=1);
/**
 * Content Length Adequacy Diagnostic
 *
 * Philosophy: Depth signals comprehensive coverage
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Content_Length_Adequacy extends Diagnostic_Base {
    public static function check(): ?array {
        global $wpdb;
        $thin_content = (int) $wpdb->get_var("SELECT COUNT(1) FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type = 'post' AND CHAR_LENGTH(post_content) < 300");
        if ($thin_content > 10) {
            return [
                'id' => 'seo-content-length-adequacy',
                'title' => 'Content Depth and Length',
                'description' => sprintf('%d posts under 300 characters. Expand thin content for better coverage and value.', $thin_content),
                'severity' => 'medium',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/content-depth/',
                'training_link' => 'https://wpshadow.com/training/comprehensive-content/',
                'auto_fixable' => false,
                'threat_level' => 45,
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
