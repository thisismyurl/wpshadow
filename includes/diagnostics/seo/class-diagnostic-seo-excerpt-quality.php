<?php
declare(strict_types=1);
/**
 * Excerpt Quality Diagnostic
 *
 * Philosophy: Hand-written excerpts improve CTR
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Excerpt_Quality extends Diagnostic_Base {
    public static function check(): ?array {
        global $wpdb;
        $total = (int) $wpdb->get_var("SELECT COUNT(1) FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type = 'post'");
        $with_excerpt = (int) $wpdb->get_var("SELECT COUNT(1) FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type = 'post' AND post_excerpt != ''");
        $missing = $total - $with_excerpt;
        if ($missing > 10 && $missing > ($total * 0.3)) {
            return [
                'id' => 'seo-excerpt-quality',
                'title' => 'Hand-Written Excerpts Missing',
                'description' => sprintf('%d posts relying on auto-generated excerpts. Write custom excerpts for better meta descriptions.', $missing),
                'severity' => 'low',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/excerpt-best-practices/',
                'training_link' => 'https://wpshadow.com/training/content-optimization/',
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
