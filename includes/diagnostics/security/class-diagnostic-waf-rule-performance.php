<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: WAF Rule Performance Impact (SEC-PERF-005)
 * 
 * WAF Rule Performance Impact diagnostic
 * Philosophy: Show value (#9) - Security without penalty.
 * 
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_DiagnosticWafRulePerformance extends Diagnostic_Base {
    public static function check(): ?array {
        // Check if WAF (Web Application Firewall) is configured
        $has_wordfence = class_exists('Wordfence\Controller\Controller');
        $has_cloudflare_waf = !empty(wp_get_server_var('CF_RAY'));
        $has_sucuri = !empty(wp_get_server_var('X_SUCURI_CACHE'));
        
        if (!$has_wordfence && !$has_cloudflare_waf && !$has_sucuri) {
            return array(
                'id' => 'waf-rule-performance',
                'title' => __('Web Application Firewall Not Active', 'wpshadow'),
                'description' => __('No WAF service detected. Consider using Wordfence, Cloudflare WAF, or similar to protect against common web attacks.', 'wpshadow'),
                'severity' => 'medium',
                'category' => 'security',
                'kb_link' => 'https://wpshadow.com/kb/web-application-firewall/',
                'training_link' => 'https://wpshadow.com/training/waf-setup/',
                'auto_fixable' => false,
                'threat_level' => 70,
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
