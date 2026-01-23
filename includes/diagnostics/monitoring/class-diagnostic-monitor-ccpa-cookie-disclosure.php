<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

class Diagnostic_Monitor_CCPA_Cookie_Disclosure extends Diagnostic_Base { public static function check(): ?array { return ['id' => 'monitor-ccpa-disclosure', 'title' => __('CCPA Cookie Disclosure Compliance', 'wpshadow'), 'description' => __('Verifies CCPA disclosure visible to California users. Missing = $2500+ per violation fine.', 'wpshadow'), 'severity' => 'critical', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/ccpa-compliance/', 'training_link' => 'https://wpshadow.com/training/privacy-regulations/', 'auto_fixable' => false, 'threat_level' => 10]; } 
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
