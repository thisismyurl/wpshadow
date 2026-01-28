<?php
/**
 * Search Performance Index Diagnostic
 *
 * Measures site search functionality performance and identifies
 * slow or problematic search queries.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Search_Performance_Index Class
 *
 * Verifies site search performs efficiently.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Search_Performance_Index extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'search-performance-index';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Search Performance Index';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Measures site search functionality performance';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if search performance issues found, null otherwise.
	 */
	public static function check() {
		$search_perf = self::test_search_performance();

		if ( $search_perf['is_good'] ) {
			return null; // Search is performing well
		}

		$severity = $search_perf['avg_time'] > 3 ? 'high' : 'medium';
		$threat   = $search_perf['avg_time'] > 3 ? 70 : 55;

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: average search time in seconds */
				__( 'Site search is slow (average %d seconds). Users expect results in <1 second. Slow search reduces engagement and conversions.', 'wpshadow' ),
				(int) $search_perf['avg_time']
			),
			'severity'     => $severity,
			'threat_level' => $threat,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/search-performance',
			'family'       => self::$family,
			'meta'         => array(
				'avg_search_time_seconds' => round( $search_perf['avg_time'], 2 ),
				'target_performance'      => '< 1 second',
				'user_impact'             => __( 'Each 1 second delay reduces conversions by 7-10%' ),
				'bounce_rate_impact'      => __( 'Slow search increases bounce rate by 30-50%' ),
			),
			'details'      => array(
				'search_performance_impact' => array(
					__( 'Search is primary navigation on product/content sites' ),
					__( 'Users expect Google-like instant results' ),
					__( 'Each second delay = 3-5% conversion loss' ),
					__( 'Mobile users even more impatient (2 second max)' ),
				),
				'optimization_strategies'   => array(
					'Database Optimization' => array(
						'Add index: ALTER TABLE wp_posts ADD INDEX idx_search (post_title, post_content)',
						'Remove unused custom post types',
						'Archive old posts to separate table',
					),
					'Search Plugin Upgrades' => array(
						'Elasticsearch: Fast search for large sites (100K+ posts)',
						'Algolia: Cloud-based search, instant results',
						'Relevanssi: Better search ranking (free plugin)',
						'Default: WordPress native search (slowest)',
					),
					'Caching Search Results' => array(
						'Cache popular search queries',
						'Clear cache when new content published',
						'Redis cache layer reduces DB hits',
					),
				),
				'setup_for_fast_search'     => array(
					'Small Site (1-1000 posts)' => array(
						'Ensure posts indexed (check WordPress Search Consultant)',
						'Use Relevanssi plugin (free)',
						'Enable page caching (WP Super Cache)',
					),
					'Medium Site (1K-10K posts)' => array(
						'Upgrade to Elasticsearch',
						'Database optimization required',
						'Dedicated search server recommended',
					),
					'Large Site (10K+ posts)' => array(
						'Elasticsearch or Algolia required',
						'Separate search infrastructure',
						'Custom search filtering/facets',
					),
				),
				'quick_wins'                => array(
					__( 'Disable plugins that hook search' ),
					__( 'Remove post statuses from search (drafts, pending)' ),
					__( 'Limit search results per page to 10-20' ),
					__( 'Enable full-text search indexing' ),
					__( 'Use transient caching for results' ),
				),
			),
		);
	}

	/**
	 * Test search performance.
	 *
	 * @since  1.2601.2148
	 * @return array Search performance metrics.
	 */
	private static function test_search_performance() {
		// Test search with common query
		$test_queries = array( 'the', 'test', 'product', 'post', 'page' );
		$total_time   = 0;

		foreach ( $test_queries as $query ) {
			$start = microtime( true );

			$results = new \WP_Query(
				array(
					's'              => $query,
					'posts_per_page' => 10,
				)
			);

			$end = microtime( true );
			wp_reset_postdata();

			$query_time = $end - $start;
			$total_time += $query_time;

			// If any query exceeds 3 seconds, mark as slow
			if ( $query_time > 3 ) {
				return array(
					'is_good'  => false,
					'avg_time' => $query_time,
					'issue'    => "Query '$query' took " . round( $query_time, 2 ) . 's',
				);
			}
		}

		$avg_time = $total_time / count( $test_queries );

		return array(
			'is_good'  => $avg_time < 1,
			'avg_time' => $avg_time,
		);
	}
}
