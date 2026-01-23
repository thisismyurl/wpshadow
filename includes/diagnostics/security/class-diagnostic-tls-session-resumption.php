<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: SSL/TLS Session Resumption Rate (SEC-PERF-007)
 * 
 * SSL/TLS Session Resumption Rate diagnostic
 * Philosophy: Show value (#9) - Reduce handshake.
 * 
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_DiagnosticTlsSessionResumption extends Diagnostic_Base {
    public static function check(): ?array {
        // Check if SSL is configured
        if (!is_ssl()) {
            return null;
        }
        
        // Check for TLS session ID support
        $session_id = wp_get_server_var('SSL_SESSION_ID');
        
        // If no session ID, session resumption may not be enabled
        if (empty($session_id)) {
            return array(
                'id' => 'tls-session-resumption',
                'title' => __('TLS Session Resumption Not Detected', 'wpshadow'),
                'description' => __('Enable TLS session resumption (session IDs or tickets) in your web server to reduce handshake overhead on repeat connections.', 'wpshadow'),
                'severity' => 'info',
                'category' => 'performance',
                'kb_link' => 'https://wpshadow.com/kb/tls-session-resumption/',
                'training_link' => 'https://wpshadow.com/training/session-resumption/',
                'auto_fixable' => false,
                'threat_level' => 25,
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
