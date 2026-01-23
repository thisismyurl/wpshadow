<?php
declare(strict_types=1);
/**
 * Breadcrumb Click Analytics Diagnostic
 *
 * Philosophy: Breadcrumb usage shows navigation patterns
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Breadcrumb_Click_Analytics extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-breadcrumb-click-analytics',
            'title' => 'Breadcrumb Navigation Analytics',
            'description' => 'Track breadcrumb clicks to understand how users navigate site hierarchy.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/breadcrumb-analytics/',
            'training_link' => 'https://wpshadow.com/training/navigation-tracking/',
            'auto_fixable' => false,
            'threat_level' => 15,
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
