<?php
declare(strict_types=1);
/**
 * Missing Author Schema Diagnostic
 *
 * Philosophy: SEO credibility - author markup builds trust
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for missing author schema markup.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_Missing_Author_Schema extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Check a recent post for author schema
		$recent_posts = get_posts(array('numberposts' => 1));
		
		if (empty($recent_posts)) {
			return null;
		}
		
		$post_content = apply_filters('the_content', $recent_posts[0]->post_content);
		$post_html = get_post_field('post_content', $recent_posts[0]->ID);
		
		// Check for author schema markup patterns
		if (stripos($post_html, 'schema.org/Person') !== false ||
		    stripos($post_html, '"@type":"Person"') !== false ||
		    stripos($post_html, 'itemtype="http://schema.org/Person"') !== false) {
			return null; // Author schema exists
		}
		
		return array(
			'id'          => 'seo-missing-author-schema',
			'title'       => 'Missing Author Schema Markup',
			'description' => 'Posts lack Author schema. Add Person schema to strengthen E-E-A-T signals.',
			'severity'    => 'low',
			'category'    => 'seo',
			'kb_link'     => 'https://wpshadow.com/kb/add-author-schema/',
			'training_link' => 'https://wpshadow.com/training/author-markup/',
			'auto_fixable' => false,
			'threat_level' => 50,
		);
	}
}
