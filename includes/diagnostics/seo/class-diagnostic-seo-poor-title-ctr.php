<?php
declare(strict_types=1);
/**
 * Poor Title CTR Optimization Diagnostic
 *
 * Philosophy: SEO SERP - optimize titles for clicks
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if titles use CTR optimization techniques.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_Poor_Title_CTR_Optimization extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		
		$posts = $wpdb->get_results(
			"SELECT post_title FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
			AND post_type IN ('post', 'page') 
			LIMIT 20"
		);
		
		$unoptimized = 0;
		$ctr_patterns = array(
			'/\d+/',           // Numbers
			'/\b(how|why|what|best|guide|ultimate|complete)\b/i', // Power words
			'/\b(2024|2025|2026)\b/', // Year
		);
		
		foreach ( $posts as $post ) {
			$has_pattern = false;
			foreach ( $ctr_patterns as $pattern ) {
				if ( preg_match( $pattern, $post->post_title ) ) {
					$has_pattern = true;
					break;
				}
			}
			if ( ! $has_pattern ) {
				$unoptimized++;
			}
		}
		
		if ( $unoptimized > 10 ) {
			return array(
				'id'          => 'seo-poor-title-ctr',
				'title'       => 'Titles Not Optimized for CTR',
				'description' => sprintf( '%d titles lack CTR optimization (numbers, power words, year). Improve click-through rate with: "7 Ways...", "Ultimate Guide", "Best [Topic] 2026".', $unoptimized ),
				'severity'    => 'low',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/optimize-title-ctr/',
				'training_link' => 'https://wpshadow.com/training/clickable-titles/',
				'auto_fixable' => false,
				'threat_level' => 50,
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
