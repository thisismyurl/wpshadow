<?php
declare(strict_types=1);
/**
 * Noindex Pages Audit Diagnostic
 *
 * Philosophy: SEO visibility - accidentally noindexed pages lose traffic
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for important pages set to noindex.
 */
class Diagnostic_SEO_Noindex_Pages_Audit extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		
		$noindex_posts = $wpdb->get_results(
			"SELECT p.ID, p.post_title 
			FROM {$wpdb->posts} p
			LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
			WHERE p.post_status = 'publish'
			AND p.post_type IN ('post', 'page')
			AND pm.meta_key = '_yoast_wpseo_meta-robots-noindex'
			AND pm.meta_value = '1'
			LIMIT 5"
		);
		
		if ( ! empty( $noindex_posts ) ) {
			return array(
				'id'          => 'seo-noindex-pages-audit',
				'title'       => 'Important Pages Set to Noindex',
				'description' => sprintf( '%d published pages/posts are noindexed. Accidentally noindexed pages won\'t rank. Review and remove noindex from important content.', count( $noindex_posts ) ),
				'severity'    => 'high',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/fix-noindex-issues/',
				'training_link' => 'https://wpshadow.com/training/indexation-control/',
				'auto_fixable' => false,
				'threat_level' => 75,
			);
		}
		
		return null;
	}
}
