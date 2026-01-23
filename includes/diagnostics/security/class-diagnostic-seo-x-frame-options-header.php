<?php
declare(strict_types=1);
/**
 * X-Frame-Options Header Diagnostic
 *
 * Philosophy: Prevent clickjacking attacks
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_X_Frame_Options_Header extends Diagnostic_Base {
    public static function check(): ?array {
        // Check for X-Frame-Options header
        $response = wp_remote_head(home_url(), array('timeout' => 10));
        
        if (is_wp_error($response)) {
            return null;
        }
        
        $headers = wp_remote_retrieve_headers($response);
        
        if (isset($headers['X-Frame-Options']) || isset($headers['x-frame-options'])) {
            return null;
        }
        
        return [
            'id' => 'seo-x-frame-options-header',
            'title' => 'X-Frame-Options Header Missing',
            'description' => 'X-Frame-Options header not set. Add to prevent clickjacking attacks.',
            'severity' => 'medium',
            'category' => 'security',
            'kb_link' => 'https://wpshadow.com/kb/x-frame-options/',
            'training_link' => 'https://wpshadow.com/training/clickjacking-prevention/',
            'auto_fixable' => true,
            'threat_level' => 50,
        ];
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
