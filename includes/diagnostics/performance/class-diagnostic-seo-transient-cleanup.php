<?php
declare(strict_types=1);
/**
 * Transient Cleanup Diagnostic
 *
 * Philosophy: Expired transients bloat database
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Transient_Cleanup extends Diagnostic_Base {
    public static function check(): ?array {
        global $wpdb;
        $expired = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(1) FROM {$wpdb->options} WHERE option_name LIKE %s AND option_value < %d", '_transient_timeout_%', time()));
        if ($expired > 100) {
            return [
                'id' => 'seo-transient-cleanup',
                'title' => 'Expired Transients Need Cleanup',
                'description' => sprintf('%d expired transients detected. Clean up to reduce database size and improve query performance.', $expired),
                'severity' => 'low',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/transient-cleanup/',
                'training_link' => 'https://wpshadow.com/training/database-optimization/',
                'auto_fixable' => false,
                'threat_level' => 20,
            ];
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
