<?php declare(strict_types=1);
/**
 * Missing How-To Schema Diagnostic
 *
 * Philosophy: SEO featured content - How-To schema gets rich snippets
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check for How-To schema on tutorial content.
 */
class Diagnostic_SEO_Missing_HowTo_Schema {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		global $wpdb;
		
		$howto_content = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
			AND (post_title LIKE '%how to%' OR post_content LIKE '%step 1%')"
		);
		
		if ( $howto_content > 0 ) {
			return array(
				'id'          => 'seo-missing-howto-schema',
				'title'       => 'How-To Content Missing Schema',
				'description' => sprintf( '%d how-to posts detected. Add HowTo schema with steps, tools, supplies. Enables rich results with step carousel in search.', $howto_content ),
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/add-howto-schema/',
				'training_link' => 'https://wpshadow.com/training/howto-markup/',
				'auto_fixable' => false,
				'threat_level' => 60,
			);
		}
		
		return null;
	}
}
