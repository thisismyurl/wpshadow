<?php declare(strict_types=1);
/**
 * Duplicate Content Diagnostic
 *
 * Philosophy: SEO uniqueness - duplicate content confuses search engines
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check for duplicate content.
 */
class Diagnostic_SEO_Duplicate_Content {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		global $wpdb;
		
		$duplicates = $wpdb->get_results(
			"SELECT post_content, COUNT(*) as count 
			FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
			AND post_type IN ('post', 'page')
			GROUP BY post_content 
			HAVING count > 1"
		);
		
		if ( ! empty( $duplicates ) ) {
			return array(
				'id'          => 'seo-duplicate-content',
				'title'       => 'Duplicate Content Detected',
				'description' => sprintf( 'Found %d instances of duplicate content. Search engines penalize duplicate content. Make each page unique or use canonical tags.', count( $duplicates ) ),
				'severity'    => 'high',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/fix-duplicate-content/',
				'training_link' => 'https://wpshadow.com/training/content-uniqueness/',
				'auto_fixable' => false,
				'threat_level' => 70,
			);
		}
		
		return null;
	}
}
