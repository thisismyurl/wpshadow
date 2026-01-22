<?php declare(strict_types=1);
/**
 * Cornerstone Content Identification Diagnostic
 *
 * Philosophy: SEO strategy - identify and promote best content
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check for cornerstone content strategy.
 */
class Diagnostic_SEO_Cornerstone_Content {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		global $wpdb;
		
		$cornerstone = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} 
			WHERE meta_key = '_yoast_wpseo_is_cornerstone' 
			AND meta_value = '1'"
		);
		
		if ( $cornerstone === 0 ) {
			return array(
				'id'          => 'seo-cornerstone-content',
				'title'       => 'Identify Cornerstone Content',
				'description' => 'No cornerstone content marked. Identify 3-5 best, most comprehensive articles. Mark as cornerstone, keep updated, link to from other posts, promote heavily.',
				'severity'    => 'low',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/cornerstone-content/',
				'training_link' => 'https://wpshadow.com/training/content-hierarchy/',
				'auto_fixable' => false,
				'threat_level' => 45,
			);
		}
		
		return null;
	}
}
