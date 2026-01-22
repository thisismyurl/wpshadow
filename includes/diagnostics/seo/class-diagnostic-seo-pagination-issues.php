<?php
declare(strict_types=1);
/**
 * Pagination SEO Issues Diagnostic
 *
 * Philosophy: SEO crawlability - proper pagination prevents indexation issues
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for pagination SEO issues.
 */
class Diagnostic_SEO_Pagination_Issues extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Check if blog/shop has pagination
		$posts_per_page = get_option( 'posts_per_page' );
		$total_posts = wp_count_posts( 'post' )->publish;
		
		if ( $total_posts > $posts_per_page ) {
			return array(
				'id'          => 'seo-pagination-issues',
				'title'       => 'Review Pagination SEO',
				'description' => 'Site has pagination. Ensure paginated pages use rel="next"/rel="prev" or canonical to page 1. Avoid noindexing paginated pages. Consider "view all" option.',
				'severity'    => 'low',
				'category'    => 'seo',
				'kb_link'     => 'https://wpshadow.com/kb/optimize-pagination/',
				'training_link' => 'https://wpshadow.com/training/pagination-seo/',
				'auto_fixable' => false,
				'threat_level' => 45,
			);
		}
		
		return null;
	}
}
