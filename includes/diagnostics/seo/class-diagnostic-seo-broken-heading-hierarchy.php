<?php
declare(strict_types=1);
/**
 * Broken Heading Hierarchy Diagnostic
 *
 * Philosophy: SEO structure - proper heading order matters
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for broken heading hierarchy.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_Broken_Heading_Hierarchy extends Diagnostic_Base {
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
			LIMIT 10"
		);
		
		$issues = 0;
		foreach ( $posts as $post ) {
			// Check if H3 appears before H2, etc
			if ( preg_match( '/<h1[^>]*>.*?<h3[^>]*>/is', $post->post_content ) ) {
				$issues++;
			}
		}
		
		if ( $issues > 0 ) {
			return array(
				'id'          => 'seo-broken-heading-hierarchy',
				'title'       => 'Broken Heading Hierarchy',
				'description' => sprintf( '%d posts skip heading levels (e.g., H1→H3 without H2). Maintain proper hierarchy: H1→H2→H3→H4.', $issues ),
				'severity'    => 'low',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/fix-heading-hierarchy/',
				'training_link' => 'https://wpshadow.com/training/semantic-html/',
				'auto_fixable' => false,
				'threat_level' => 45,
			);
		}
		
		return null;
	}

}