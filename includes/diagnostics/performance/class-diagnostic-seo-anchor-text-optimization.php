<?php
declare(strict_types=1);
/**
 * Anchor Text Optimization Diagnostic
 *
 * Philosophy: SEO relevance - descriptive anchors help rankings
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for generic anchor text overuse.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_Anchor_Text_Optimization extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		
		$posts = $wpdb->get_results(
			"SELECT post_content FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
			AND post_type IN ('post', 'page') 
			LIMIT 10"
		);
		
		$generic_count = 0;
		foreach ( $posts as $post ) {
			if ( preg_match_all( '/<a[^>]*>(click here|read more|here|this|link)<\/a>/i', $post->post_content ) ) {
				$generic_count++;
			}
		}
		
		if ( $generic_count > 3 ) {
			return array(
				'id'          => 'seo-anchor-text-optimization',
				'title'       => 'Generic Anchor Text Usage',
				'description' => sprintf( '%d pages use generic anchor text ("click here", "read more"). Use descriptive anchors that indicate link destination: "learn about keyword research" instead of "click here".', $generic_count ),
				'severity'    => 'low',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/optimize-anchor-text/',
				'training_link' => 'https://wpshadow.com/training/anchor-text-seo/',
				'auto_fixable' => false,
				'threat_level' => 45,
			);
		}
		
		return null;
	}
}
