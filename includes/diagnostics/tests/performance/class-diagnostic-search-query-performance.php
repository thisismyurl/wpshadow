<?php
/**
 * Diagnostic: Search Query Performance
 *
 * Validates WordPress search queries are optimized.
 * Default WordPress search uses slow LIKE queries with wildcards.
 * On sites with 10,000+ posts, search can take 2-5 seconds.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since      1.26028.1843
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Search_Query_Performance
 *
 * Tests WordPress search query performance and optimization.
 *
 * @since 1.26028.1843
 */
class Diagnostic_Search_Query_Performance extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'search-query-performance';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Search Query Performance';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Validates WordPress search queries are optimized';

	/**
	 * Diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Check search query performance.
	 *
	 * @since  1.26028.1843
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Get post count to determine if optimization is needed.
		$post_count = self::get_searchable_post_count();

		// Sites with fewer than 1000 posts don't typically have search performance issues.
		if ( $post_count < 1000 ) {
			return null;
		}

		// Check if a search performance plugin is active.
		$has_search_plugin = self::has_search_optimization_plugin();

		// Check if full-text search indexes exist.
		$has_fulltext_indexes = self::check_fulltext_indexes();

		// Test actual search performance.
		$search_time = self::measure_search_performance();

		// If search takes >2 seconds on large sites, flag as critical.
		if ( $post_count > 10000 && $search_time > 2.0 && ! $has_search_plugin ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: Search time in seconds, 2: Post count */
					__( 'WordPress search is slow (%1$ss on %2$s posts). Default WordPress search uses inefficient LIKE queries. Consider installing a search optimization plugin like Relevanssi or SearchWP.', 'wpshadow' ),
					number_format( $search_time, 2 ),
					number_format( $post_count )
				),
				'severity'     => 'critical',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/search-query-performance',
				'meta'         => array(
					'post_count'           => $post_count,
					'search_time'          => $search_time,
					'has_search_plugin'    => $has_search_plugin,
					'has_fulltext_indexes' => $has_fulltext_indexes,
					'recommendation'       => 'Install Relevanssi or SearchWP',
				),
			);
		}

		// If search takes >1 second on medium sites, flag as high priority.
		if ( $post_count > 5000 && $search_time > 1.0 && ! $has_search_plugin ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: Search time in seconds, 2: Post count */
					__( 'WordPress search is taking %1$ss on %2$s posts. Consider optimizing search with full-text indexes or a search plugin.', 'wpshadow' ),
					number_format( $search_time, 2 ),
					number_format( $post_count )
				),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/search-query-performance',
				'meta'         => array(
					'post_count'           => $post_count,
					'search_time'          => $search_time,
					'has_search_plugin'    => $has_search_plugin,
					'has_fulltext_indexes' => $has_fulltext_indexes,
					'recommendation'       => 'Optimize search queries or add search plugin',
				),
			);
		}

		// Check if using default search without optimization on medium-large sites.
		if ( $post_count > 5000 && ! $has_search_plugin && ! $has_fulltext_indexes ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Post count */
					__( 'Site has %s posts but uses default WordPress search without optimization. As traffic grows, search performance will degrade. Consider adding full-text indexes or a search plugin.', 'wpshadow' ),
					number_format( $post_count )
				),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/search-query-performance',
				'meta'         => array(
					'post_count'           => $post_count,
					'search_time'          => $search_time,
					'has_search_plugin'    => $has_search_plugin,
					'has_fulltext_indexes' => $has_fulltext_indexes,
					'recommendation'       => 'Prepare for scale with search optimization',
				),
			);
		}

		// Search is adequately optimized.
		return null;
	}

	/**
	 * Get count of searchable posts.
	 *
	 * @since  1.26028.1843
	 * @return int Number of published posts.
	 */
	private static function get_searchable_post_count() {
		global $wpdb;

		// Get published posts count across all searchable post types.
		$post_types = get_post_types( array( 'exclude_from_search' => false ) );
		if ( empty( $post_types ) ) {
			return 0;
		}

		$post_types_string = "'" . implode( "','", array_map( 'esc_sql', $post_types ) ) . "'";

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type IN ({$post_types_string}) AND post_status = 'publish'" );

		return $count;
	}

	/**
	 * Check if a search optimization plugin is active.
	 *
	 * @since  1.26028.1843
	 * @return bool True if search plugin detected, false otherwise.
	 */
	private static function has_search_optimization_plugin() {
		// Check for common search plugins.
		$search_plugins = array(
			'relevanssi/relevanssi.php',
			'relevanssi-premium/relevanssi-premium.php',
			'searchwp/index.php',
			'elasticpress/elasticpress.php',
			'wp-elasticsearch/wp-elasticsearch.php',
			'algolia/algolia.php',
			'ivory-search/ivory-search.php',
			'better-search/better-search.php',
			'wpsolr-search-engine/wpsolr_search_engine.php',
		);

		foreach ( $search_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if full-text search indexes exist.
	 *
	 * @since  1.26028.1843
	 * @return bool True if full-text indexes found, false otherwise.
	 */
	private static function check_fulltext_indexes() {
		global $wpdb;

		// Check if posts table has full-text indexes.
		$indexes = $wpdb->get_results(
			$wpdb->prepare(
				'SHOW INDEX FROM %i WHERE Index_type = %s',
				$wpdb->posts,
				'FULLTEXT'
			)
		);

		return ! empty( $indexes );
	}

	/**
	 * Measure actual search performance.
	 *
	 * @since  1.26028.1843
	 * @return float Search time in seconds.
	 */
	private static function measure_search_performance() {
		// Use a common search term to test.
		$search_term = 'test';

		// Measure search query time.
		$start_time = microtime( true );

		// Perform a search query (limited to 10 results).
		$search_query = new \WP_Query(
			array(
				's'              => $search_term,
				'posts_per_page' => 10,
				'no_found_rows'  => false,
				'fields'         => 'ids',
			)
		);

		$end_time = microtime( true );

		// Clean up.
		wp_reset_postdata();

		return $end_time - $start_time;
	}
}
