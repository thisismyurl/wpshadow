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
}
