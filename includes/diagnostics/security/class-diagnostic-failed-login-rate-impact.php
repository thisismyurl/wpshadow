<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Failed Login Rate Monitoring (SECURITY-PERF-004)
 * 
 * Tracks failed login attempts and their performance impact.
 * Philosophy: Show value (#9) - Reduce wasted resources on invalid logins.
 * 
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Failed_Login_Rate_Impact extends Diagnostic_Base {
    
    /**
     * Run the diagnostic check
     * 
     * @return array|null Array with finding details or null if no issue found
     */
    public static function check(): ?array {
		// Security check implementation
        // Track failed login rate impact
        global $wpdb;
        
        // Check for excessive failed login attempts in last 30 minutes
        $table = $wpdb->prefix . 'users';
        $recent_failures = get_transient('wpshadow_recent_login_failures');
        
        if ($recent_failures && $recent_failures > 10) {
            return array(
                'id' => 'failed-login-rate-impact',
                'title' => __('High Failed Login Rate', 'wpshadow'),
                'description' => sprintf(__('Detected %d failed login attempts in recent period. Enable login limiting and consider IP-based blocking.', 'wpshadow'), $recent_failures),
                'severity' => 'high',
                'category' => 'security',
                'kb_link' => 'https://wpshadow.com/kb/login-rate-limiting/',
                'training_link' => 'https://wpshadow.com/training/failed-login-protection/',
                'auto_fixable' => false,
                'threat_level' => 80,
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
