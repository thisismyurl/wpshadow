<?php declare(strict_types=1);
/**
 * Missing Topic Clusters Diagnostic
 *
 * Philosophy: SEO content strategy - pillar pages build authority
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check for topic cluster/pillar page strategy.
 */
class Diagnostic_SEO_Missing_Topic_Clusters {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		global $wpdb;
		
		$total_posts = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
			AND post_type = 'post'"
		);
		
		// Check for pillar page indicators
		$pillar_pages = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
			AND (post_title LIKE '%complete guide%' OR post_title LIKE '%ultimate guide%' OR post_title LIKE '%everything you need%')"
		);
		
		if ( $total_posts > 20 && $pillar_pages < 2 ) {
			return array(
				'id'          => 'seo-missing-topic-clusters',
				'title'       => 'No Topic Cluster Strategy',
				'description' => sprintf( '%d posts without clear pillar pages. Create comprehensive pillar pages (2000+ words) linked to related cluster content. Builds topical authority.', $total_posts ),
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/create-topic-clusters/',
				'training_link' => 'https://wpshadow.com/training/pillar-page-strategy/',
				'auto_fixable' => false,
				'threat_level' => 55,
			);
		}
		
		return null;
	}
}
