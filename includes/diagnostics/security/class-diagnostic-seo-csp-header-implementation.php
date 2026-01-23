<?php
declare(strict_types=1);
/**
 * CSP Header Implementation Diagnostic
 *
 * Philosophy: CSP prevents XSS attacks
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_CSP_Header_Implementation extends Diagnostic_Base {
    public static function check(): ?array {
        // Check for CSP header
        $response = wp_remote_head(home_url(), array('timeout' => 10));
        
        if (is_wp_error($response)) {
            return null;
        }
        
        $headers = wp_remote_retrieve_headers($response);
        
        if (isset($headers['Content-Security-Policy']) || 
            isset($headers['content-security-policy']) ||
            isset($headers['Content-Security-Policy-Report-Only']) ||
            isset($headers['content-security-policy-report-only'])) {
            return null; // CSP is configured
        }
        
        return [
            'id' => 'seo-csp-header-implementation',
            'title' => 'Content Security Policy Not Configured',
            'description' => 'Content-Security-Policy (CSP) header missing. Implement to prevent XSS attacks.',
            'severity' => 'medium',
            'category' => 'security',
            'kb_link' => 'https://wpshadow.com/kb/csp-header/',
            'training_link' => 'https://wpshadow.com/training/security-headers/',
            'auto_fixable' => false,
            'threat_level' => 70,
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
