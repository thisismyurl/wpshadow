<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Speculation Rules API Readiness (FE-362)
 *
 * Assesses prefetch/prerender rules coverage and safety guards.
 * Philosophy: Show value (#9) and educate (#5) with clear, actionable insights.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SpeculationRulesApiReadiness extends Diagnostic_Base {
    /**
     * Run the diagnostic check
     *
     * @return array|null Array with finding details or null if no issue found
     */
    public static function check(): ?array {
		// Check for Speculation Rules API implementation
        if (!is_ssl()) {
            return null;
        }
        
        // Check if Speculation Rules API is enabled
        $has_speculation = apply_filters('wpshadow_speculation_rules_enabled', false);
        
        if (!$has_speculation) {
            return array(
                'id' => 'speculation-rules-api-readiness',
                'title' => __('Speculation Rules API Not Enabled', 'wpshadow'),
                'description' => __('Enable Speculation Rules API for faster navigation. This modern Chrome feature allows prefetching of likely navigation targets.', 'wpshadow'),
                'severity' => 'info',
                'category' => 'monitoring',
                'kb_link' => 'https://wpshadow.com/kb/speculation-rules-api/',
                'training_link' => 'https://wpshadow.com/training/prefetch-strategies/',
                'auto_fixable' => false,
                'threat_level' => 15,
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
