<?php
declare(strict_types=1);
/**
 * Comment Spam Ratio Diagnostic
 *
 * Philosophy: High spam ratio wastes crawl budget
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Comment_Spam_Ratio extends Diagnostic_Base {
    public static function check(): ?array {
        global $wpdb;
        $approved = (int) $wpdb->get_var("SELECT COUNT(1) FROM {$wpdb->comments} WHERE comment_approved = '1'");
        $spam = (int) $wpdb->get_var("SELECT COUNT(1) FROM {$wpdb->comments} WHERE comment_approved = 'spam'");
        if ($spam > 100 && $spam > ($approved * 2)) {
            return [
                'id' => 'seo-comment-spam-ratio',
                'title' => 'High Comment Spam Ratio',
                'description' => sprintf('%d spam comments vs %d approved. Clean up spam to reduce database bloat.', $spam, $approved),
                'severity' => 'low',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/comment-spam/',
                'training_link' => 'https://wpshadow.com/training/comment-management/',
                'auto_fixable' => false,
                'threat_level' => 25,
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
