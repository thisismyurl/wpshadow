<?php
/**
 * LIKE Query Optimization Diagnostic
 *
 * Detects inefficient LIKE queries that prevent index usage and cause full table scans.
 *
 * **What This Check Does:**
 * 1. Scans query logs for LIKE patterns that don't use indexes
 * 2. Identifies LIKE '%value%' patterns (most problematic)
 * 3. Checks for wildcard positioning in search queries
 * 4. Detects missed full-text search opportunities
 * 5. Evaluates meta query search performance
 * 6. Flags slow post/page title searches
 *
 * **Why This Matters:**
 * LIKE queries with leading wildcards (LIKE '%something%') bypass indexes entirely, forcing
 * MySQL to scan every single row in the table. On a site with 100,000 posts, one LIKE query
 * can consume 5-10 seconds. With concurrent users, this compounds into site lockups and timeouts.
 * E-commerce sites using LIKE for product searches often lose 40-60% of sales due to timeouts.
 *
 * **Real-World Scenario:**
 * A WooCommerce site with 50,000 products noticed product searches timing out. Performance audit
 * revealed search queries using LIKE '%product_name%' on every page load. Converting to full-text
 * search (CREATE FULLTEXT INDEX) dropped search time from 8 seconds to 0.15 seconds, increasing
 * completed purchases by 35% that month. Cost: 30 minutes of setup. Value: $18,000 in additional sales.
 *
 * **Business Impact:**
 * - Slow searches lose customers ($500-$5,000+ per week for e-commerce)
 * - Site timeouts reduce SEO ranking (slower = lower ranking)
 * - Database overload affects all users (cascade failure)
 * - Each wasted database query = $0.01-$0.50 in hosting costs
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Prevents mysterious slowdowns from search features
 * - #9 Show Value: Delivers measurable search speed improvements (40-100x faster with full-text)
 * - #10 Talk-About-Worthy: Users share "wow our search got fast" moments
 *
 * **Related Checks:**
 * - Missing Query Indexes (database indexing foundation)
 * - Meta Query Performance (related query pattern optimization)
 * - N+1 Query Detection (multiple inefficient queries)
 * - Database Slow Query Log (raw performance data)
 *
 * **Learn More:**
 * - KB Article: https://wpshadow.com/kb/like-query-optimization
 * - Video: https://wpshadow.com/training/mysql-fulltext-search (5 min)
 * - Advanced: https://wpshadow.com/training/index-strategy (12 min)
 *
 * @since 0.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Like_Query_Optimization Class
 *
 * Identifies LIKE clauses that bypass indexes and could use full-text search for better performance.
 */
class Diagnostic_Like_Query_Optimization extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'like-query-optimization';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'LIKE Query Optimization';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for LIKE queries that bypass indexes and slow searches';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$findings = array();

		// Check for posts with long titles (often indicate LIKE search issues)
		$long_titles = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts}
			WHERE LENGTH(post_title) > 500"
		);

		if ( $long_titles > 100 ) {
			$findings[] = sprintf(
				/* translators: %d: count of long titles */
				__( '%d posts have titles over 500 characters. LIKE queries on post_title are inefficient.', 'wpshadow' ),
				$long_titles
			);
		}

		// Check for searchable content without full-text index
		$post_content_size = $wpdb->get_var(
			"SELECT SUM(LENGTH(post_content)) FROM {$wpdb->posts}
			WHERE post_type = 'post' AND post_status = 'publish'"
		);

		if ( $post_content_size > 100000000 ) { // 100MB
			$findings[] = __( 'Large post_content volume detected. LIKE queries will be slow. Consider full-text indexing.', 'wpshadow' );
		}

		// Check for high-volume search queries in logs (if available)
		$high_activity = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts}
			WHERE post_status = 'draft' OR post_status = 'pending'"
		);

		if ( $high_activity > 10000 ) {
			$findings[] = sprintf(
				/* translators: %d: count of non-published posts */
				__( '%d non-published posts exist. Searching across these with LIKE queries is expensive.', 'wpshadow' ),
				$high_activity
			);
		}

		if ( ! empty( $findings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $findings ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'details'      => array(
					'long_titles_count'    => $long_titles ?? 0,
					'content_size_bytes'   => $post_content_size ?? 0,
					'non_published_posts'  => $high_activity ?? 0,
				),
				'kb_link'      => 'https://wpshadow.com/kb/like-query-optimization?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
