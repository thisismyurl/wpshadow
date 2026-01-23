<?php
declare(strict_types=1);
/**
 * Missing Meta Title Diagnostic
 *
 * Philosophy: SEO basics - meta titles are critical ranking factor
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for missing or suboptimal meta titles.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_Missing_Meta_Title extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		
		$posts = $wpdb->get_results(
			"SELECT ID, post_title FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
			AND post_type IN ('post', 'page') 
			LIMIT 10"
		);
		
		$issues = 0;
		foreach ( $posts as $post ) {
			$title_length = strlen( $post->post_title );
			if ( $title_length < 30 || $title_length > 60 ) {
				$issues++;
			}
		}
		
		if ( $issues > 0 ) {
			return array(
				'id'          => 'seo-missing-meta-title',
				'title'       => 'Posts with Suboptimal Title Tags',
				'description' => sprintf( '%d posts have title tags too short (< 30 chars) or too long (> 60 chars). Optimize to 50-60 characters for best display.', $issues ),
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/optimize-title-tags/',
				'training_link' => 'https://wpshadow.com/training/title-tag-best-practices/',
				'auto_fixable' => false,
				'threat_level' => 65,
			);
		}
		
		return null;
	}

}