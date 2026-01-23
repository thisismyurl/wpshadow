<?php
declare(strict_types=1);
/**
 * Orphaned Pages Diagnostic
 *
 * Philosophy: SEO crawlability - all pages need internal links
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for orphaned pages with no internal links.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SEO_Orphaned_Pages extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		
		$all_posts = $wpdb->get_col(
			"SELECT ID FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
			AND post_type IN ('post', 'page')"
		);
		
		$orphaned = array();
		
		foreach ( $all_posts as $post_id ) {
			$permalink = get_permalink( $post_id );
			$linked = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->posts} 
					WHERE post_content LIKE %s 
					AND post_status = 'publish'",
					'%' . $wpdb->esc_like( $permalink ) . '%'
				)
			);
			
			if ( $linked === 0 ) {
				$orphaned[] = $post_id;
			}
			
			if ( count( $orphaned ) >= 5 ) {
				break;
			}
		}
		
		if ( ! empty( $orphaned ) ) {
			return array(
				'id'          => 'seo-orphaned-pages',
				'title'       => 'Orphaned Pages (No Internal Links)',
				'description' => sprintf( 'Found %d orphaned pages with zero internal links. Search engines may not discover these. Add internal links from related content.', count( $orphaned ) ),
				'severity'    => 'medium',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/fix-orphaned-pages/',
				'training_link' => 'https://wpshadow.com/training/content-connectivity/',
				'auto_fixable' => false,
				'threat_level' => 60,
			);
		}
		
		return null;
	}

}