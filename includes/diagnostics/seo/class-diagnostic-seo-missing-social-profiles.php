<?php
declare(strict_types=1);
/**
 * Missing Social Profiles Diagnostic
 *
 * Philosophy: SEO entity - social signals build brand authority
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for social profile links.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_Missing_Social_Profiles extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		
		$social_patterns = array(
			'facebook.com',
			'twitter.com',
			'linkedin.com',
			'instagram.com',
			'youtube.com',
		);
		
		$found_profiles = 0;
		foreach ( $social_patterns as $pattern ) {
			$count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->posts} 
					WHERE post_content LIKE %s",
					'%' . $wpdb->esc_like( $pattern ) . '%'
				)
			);
			if ( $count > 0 ) {
				$found_profiles++;
			}
		}
		
		if ( $found_profiles < 2 ) {
			return array(
				'id'          => 'seo-missing-social-profiles',
				'title'       => 'Limited Social Media Presence',
				'description' => sprintf( 'Found %d social profile links. Add social profiles to footer/header. Include in Organization schema. Strengthens entity signals.', $found_profiles ),
				'severity'    => 'low',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/add-social-profiles/',
				'training_link' => 'https://wpshadow.com/training/social-seo/',
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
