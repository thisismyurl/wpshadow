<?php declare(strict_types=1);
/**
 * Missing Meta Description Diagnostic
 *
 * Philosophy: SEO basics - meta descriptions drive CTR
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check for missing or suboptimal meta descriptions.
 */
class Diagnostic_SEO_Missing_Meta_Description {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		global $wpdb;
		
		$posts = $wpdb->get_results(
			"SELECT ID, post_title FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
			AND post_type IN ('post', 'page') 
			LIMIT 10"
		);
		
		$missing = 0;
		foreach ( $posts as $post ) {
			$meta_desc = get_post_meta( $post->ID, '_yoast_wpseo_metadesc', true );
			if ( empty( $meta_desc ) ) {
				$missing++;
			}
		}
		
		if ( $missing > 0 ) {
			return array(
				'id'          => 'seo-missing-meta-description',
				'title'       => 'Posts Missing Meta Descriptions',
				'description' => sprintf( '%d posts missing meta descriptions. Meta descriptions improve click-through rate from search results. Add 120-160 character descriptions.', $missing ),
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/add-meta-descriptions/',
				'training_link' => 'https://wpshadow.com/training/meta-tag-optimization/',
				'auto_fixable' => false,
				'threat_level' => 60,
			);
		}
		
		return null;
	}
}
