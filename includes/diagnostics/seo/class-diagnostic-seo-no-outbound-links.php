<?php
declare(strict_types=1);
/**
 * No Outbound Links Diagnostic
 *
 * Philosophy: SEO trust - outbound links show research
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for posts with no outbound links.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_No_Outbound_Links extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		
		$posts = $wpdb->get_results(
			"SELECT ID, post_content FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
			AND post_type IN ('post', 'page') 
			LIMIT 20"
		);
		
		$no_outbound = 0;
		$home_domain = parse_url( home_url(), PHP_URL_HOST );
		
		foreach ( $posts as $post ) {
			preg_match_all( '/<a[^>]*href=["\']https?:\/\/([^"\'\/]+)/i', $post->post_content, $matches );
			
			$has_outbound = false;
			foreach ( $matches[1] as $domain ) {
				if ( $domain !== $home_domain ) {
					$has_outbound = true;
					break;
				}
			}
			
			if ( ! $has_outbound ) {
				$no_outbound++;
			}
		}
		
		if ( $no_outbound > 5 ) {
			return array(
				'id'          => 'seo-no-outbound-links',
				'title'       => 'Posts Lacking Outbound Links',
				'description' => sprintf( '%d posts have no outbound links. Link to authoritative sources (studies, data, expert sites). Shows research and builds credibility.', $no_outbound ),
				'severity'    => 'low',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/add-outbound-links/',
				'training_link' => 'https://wpshadow.com/training/link-strategy/',
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
