<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: wp_comments Foreign Key Missing (DB-020)
 * 
 * Checks if wp_comments lacks foreign key to wp_posts.
 * Philosophy: Show value (#9) with data integrity improvements.
 * 
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Comments_Foreign_Key_Missing extends Diagnostic_Base {
	
	/**
	 * Run the diagnostic check
	 * 
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array {
		global $wpdb;

		// Check for foreign key from wp_comments.comment_post_ID to wp_posts.ID
		$constraint_count = $wpdb->get_var($wpdb->prepare(
			"SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = 'comment_post_ID' AND REFERENCED_TABLE_NAME = %s AND REFERENCED_COLUMN_NAME = %s",
			DB_NAME,
			$wpdb->comments,
			$wpdb->posts,
			'ID'
		));

		if ((int) $constraint_count === 0) {
			return array(
				'id' => 'comments-foreign-key-missing',
				'title' => __('Comments table lacks post foreign key', 'wpshadow'),
				'description' => __('wp_comments is missing a foreign key to wp_posts. Add a foreign key to improve referential integrity and query performance.', 'wpshadow'),
				'severity' => 'medium',
				'category' => 'system',
				'kb_link' => 'https://wpshadow.com/kb/comments-foreign-key/',
				'training_link' => 'https://wpshadow.com/training/database-integrity/',
				'auto_fixable' => false,
				'threat_level' => 55,
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
