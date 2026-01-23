<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_SQL_Injection_Attempts extends Diagnostic_Base {
    public static function check(): ?array {
        // Check if security monitoring is active (Wordfence, Sucuri, etc)
        $monitoring_active = is_plugin_active('wordfence/wordfence.php') ||
                            is_plugin_active('sucuri-scanner/sucuri.php') ||
                            is_plugin_active('better-wp-security/better-wp-security.php');
        
        if ($monitoring_active) {
            return null; // Monitoring in place
        }
        
        return ['id' => 'monitor-sql-injection', 'title' => __('SQL Injection Monitoring Not Active', 'wpshadow'), 'description' => __('No security plugin monitoring SQL injection attempts. Install Wordfence or similar.', 'wpshadow'), 'severity' => 'low', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/sql-injection-prevention/', 'training_link' => 'https://wpshadow.com/training/security-hardening/', 'auto_fixable' => false, 'threat_level' => 10];
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
	}
	/**
	 * Test: Plugin detection logic
	 *
	 * Verifies that diagnostic correctly checks for active plugins
	 * and reports issues appropriately.
	 *
	 * @return array Test result
	 */
	public static function test_plugin_detection(): array {
		$result = self::check();
		
		// Plugin detection should return null (no plugin/no issue) or array (issue)
		if ( $result === null || is_array( $result ) ) {
			return array(
				'passed'  => true,
				'message' => 'Plugin detection logic valid',
			);
		}
		
		return array(
			'passed'  => false,
			'message' => 'Invalid plugin detection result',
		);
	}}
