<?php
declare(strict_types=1);
/**
 * Missing Canonical Tags Diagnostic
 *
 * Philosophy: SEO canonicalization - specify preferred URLs
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for missing canonical tags.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_Missing_Canonical_Tags extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		
		$posts = $wpdb->get_results(
			"SELECT ID FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
			AND post_type IN ('post', 'page') 
			LIMIT 10"
		);
		
		$missing = 0;
		foreach ( $posts as $post ) {
			$canonical = get_post_meta( $post->ID, '_yoast_wpseo_canonical', true );
			if ( empty( $canonical ) && ! has_action( 'wp_head', 'rel_canonical' ) ) {
				$missing++;
			}
		}
		
		if ( $missing > 0 ) {
			return array(
				'id'          => 'seo-missing-canonical-tags',
				'title'       => 'Missing Canonical Tags',
				'description' => sprintf( '%d pages missing canonical tags. Canonical tags prevent duplicate content issues. Add self-referential canonicals or use SEO plugin.', $missing ),
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/add-canonical-tags/',
				'training_link' => 'https://wpshadow.com/training/canonical-urls/',
				'auto_fixable' => false,
				'threat_level' => 60,
			);
		}
		
		return null;
	}
}
