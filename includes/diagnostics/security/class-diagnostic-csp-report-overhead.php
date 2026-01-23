<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: CSP Report Overhead (SEC-PERF-008)
 * 
 * CSP Report Overhead diagnostic
 * Philosophy: Educate (#5) - Monitor without slowdown.
 * 
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_DiagnosticCspReportOverhead extends Diagnostic_Base {
    public static function check(): ?array {
        // Check if Content-Security-Policy header is set
        $headers = wp_get_server_var('HTTP_CONTENT_SECURITY_POLICY_REPORT_ONLY');
        if (!$headers) {
            $headers = wp_get_server_var('HTTP_CONTENT_SECURITY_POLICY');
        }
        
        // If CSP enabled, monitor for excessive reports
        if ($headers) {
            $csp_reports = get_transient('wpshadow_csp_violation_count');
            if ($csp_reports && $csp_reports > 100) {
                return array(
                    'id' => 'csp-report-overhead',
                    'title' => __('High CSP Violation Report Rate', 'wpshadow'),
                    'description' => __('CSP violations are being reported frequently. Review CSP policy to reduce legitimate violations and improve performance.', 'wpshadow'),
                    'severity' => 'medium',
                    'category' => 'security',
                    'kb_link' => 'https://wpshadow.com/kb/csp-policy-optimization/',
                    'training_link' => 'https://wpshadow.com/training/csp-headers/',
                    'auto_fixable' => false,
                    'threat_level' => 40,
                );
            }
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
