<?php
declare(strict_types=1);
/**
 * Redirect Integrity Diagnostic
 *
 * Philosophy: Clean canonicalization to HTTPS and primary host
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Redirect_Integrity extends Diagnostic_Base {
    /**
     * Quick check: HTTP home should redirect to HTTPS (single hop).
     *
     * @return array|null
     */
    public static function check(): ?array {
        $home = home_url('/', 'http');
        $response = wp_remote_head($home, ['timeout' => 5, 'redirection' => 3]);
        if (!is_wp_error($response)) {
            $finalUrl = wp_remote_retrieve_header($response, 'location');
            $code = wp_remote_retrieve_response_code($response);
            if ($code >= 300 && $code < 400) {
                // We saw a redirect; advisory only
                return null;
            }
        }
        return [
            'id' => 'seo-redirect-integrity',
            'title' => 'HTTP to HTTPS Redirect Integrity',
            'description' => 'Verify that HTTP requests redirect to HTTPS on the canonical host with a single hop (301/308 preferred).',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/https-canonicalization/',
            'training_link' => 'https://wpshadow.com/training/redirects-seo/',
            'auto_fixable' => false,
            'threat_level' => 20,
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
