<?php
declare(strict_types=1);
/**
 * Mobile Redirect Strategy Diagnostic
 *
 * Philosophy: Separate mobile URLs need proper configuration
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Mobile_Redirect_Strategy extends Diagnostic_Base {
    public static function check(): ?array {
        // Check if site uses separate mobile URLs
        $site_url = get_site_url();
        $parsed = parse_url($site_url);
        
        if (!isset($parsed['host'])) {
            return null;
        }
        
        // Check if this is a mobile subdomain (m.example.com)
        if (strpos($parsed['host'], 'm.') === 0 || strpos($parsed['host'], 'mobile.') === 0) {
            // This IS a mobile URL, check for alternate link
            return [
                'id' => 'seo-mobile-redirect-strategy',
                'title' => 'Mobile URL Configuration Review',
                'description' => 'Using separate mobile subdomain. Ensure proper rel=alternate and rel=canonical tags.',
                'severity' => 'medium',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/mobile-redirects/',
                'training_link' => 'https://wpshadow.com/training/mobile-url-structure/',
                'auto_fixable' => false,
                'threat_level' => 50,
            ];
        }
        
        // Most sites use responsive design now, not separate mobile URLs
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
