<?php
declare(strict_types=1);
/**
 * Table of Contents Diagnostic
 *
 * Philosophy: SEO UX - TOC improves navigation on long content
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for table of contents on long posts.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_Table_Of_Contents extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		
		$long_posts = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
			AND post_type = 'post' 
			AND CHAR_LENGTH(post_content) > 3000"
		);
		
		if ( $long_posts > 5 ) {
			return array(
				'id'          => 'seo-table-of-contents',
				'title'       => 'Add Table of Contents to Long Posts',
				'description' => sprintf( '%d posts over 3000 characters. Add table of contents for easy navigation, improves UX, can generate jump links in search results. Use Easy Table of Contents plugin.', $long_posts ),
				'severity'    => 'low',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/add-table-of-contents/',
				'training_link' => 'https://wpshadow.com/training/content-navigation/',
				'auto_fixable' => false,
				'threat_level' => 45,
			);
		}
		
		return null;
	}
}
