<?php
declare(strict_types=1);
/**
 * Auto-Draft Cleanup Diagnostic
 *
 * Philosophy: Orphaned auto-drafts clutter database
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_AutoDraft_Cleanup extends Diagnostic_Base {
    public static function check(): ?array {
        global $wpdb;
        $autodrafts = (int) $wpdb->get_var("SELECT COUNT(1) FROM {$wpdb->posts} WHERE post_status = 'auto-draft' AND post_modified < DATE_SUB(NOW(), INTERVAL 7 DAY)");
        if ($autodrafts > 50) {
            return [
                'id' => 'seo-autodraft-cleanup',
                'title' => 'Orphaned Auto-Drafts',
                'description' => sprintf('%d old auto-drafts detected. Clean up to reduce database bloat.', $autodrafts),
                'severity' => 'low',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/autodraft-cleanup/',
                'training_link' => 'https://wpshadow.com/training/database-optimization/',
                'auto_fixable' => false,
                'threat_level' => 15,
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
