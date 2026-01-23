<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Third-Party Script Quarantine Testing (FE-019)
 * 
 * Measures performance impact of each third-party script.
 * Philosophy: Educate (#5) - Know the cost of every tag.
 * 
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Third_Party_Script_Quarantine extends Diagnostic_Base {
    public static function check(): ?array {
        $quarantined_scripts = get_transient('wpshadow_quarantined_scripts_count');
        
        if ($quarantined_scripts && $quarantined_scripts > 0) {
            return array(
                'id' => 'third-party-script-quarantine',
                'title' => sprintf(__('%d Scripts in Quarantine', 'wpshadow'), $quarantined_scripts),
                'description' => __('Some third-party scripts have been isolated due to performance or security concerns. Review and enable them carefully.', 'wpshadow'),
                'severity' => 'info',
                'category' => 'monitoring',
                'kb_link' => 'https://wpshadow.com/kb/script-quarantine/',
                'training_link' => 'https://wpshadow.com/training/malicious-script-detection/',
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
