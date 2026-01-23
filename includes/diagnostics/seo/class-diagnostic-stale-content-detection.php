<?php
declare(strict_types=1);
/**
 * Content Expiration/Stale Content Detection Diagnostic
 *
 * Philosophy: SEO & security - identify outdated/stale content
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for stale content that should be updated or removed.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Stale_Content_Detection extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		
		// Find posts not updated in 2+ years
		$stale_posts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, post_title, post_modified FROM {$wpdb->posts} 
				WHERE post_status = 'publish' 
				AND post_modified < DATE_SUB(NOW(), INTERVAL 2 YEAR)
				AND post_type IN ('post', 'page') LIMIT 10"
			)
		);
		
		if ( ! empty( $stale_posts ) ) {
			return array(
				'id'          => 'stale-content-detection',
				'title'       => 'Stale Content Not Updated in 2+ Years',
				'description' => sprintf(
					'Found %d old posts/pages not updated since 2020. Stale content harms SEO and user experience. Either update or add "last updated" dates.',
					count( $stale_posts )
				),
				'severity'    => 'low',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/manage-content-freshness/',
				'training_link' => 'https://wpshadow.com/training/content-strategy/',
				'auto_fixable' => false,
				'threat_level' => 45,
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
