<?php
/**
 * Diagnostic: Meta Query Performance
 *
 * Validates meta queries are optimized and not causing performance issues.
 * Meta queries are inherently slow. Multiple meta queries can create O(n²) complexity.
 * Sites with 10,000+ posts and multiple meta_query clauses need careful optimization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since      1.26028.1848
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Meta_Query_Performance
 *
 * Tests meta query performance and optimization.
 *
 * @since 1.26028.1848
 */
class Diagnostic_Meta_Query_Performance extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'meta-query-performance';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Meta Query Performance';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Validates meta queries are optimized and not causing performance issues';

	/**
	 * Diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Check meta query performance.
	 *
	 * @since  1.26028.1848
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Get post count to determine optimization needs.
		$post_count = wp_count_posts()->publish ?? 0;

		// Sites with few posts don't need meta query optimization.
		if ( $post_count < 1000 ) {
			return null;
		}

		// Check for meta_key indexes.
		$has_meta_indexes = self::check_meta_indexes();

		// Check postmeta table size.
		$postmeta_count = self::get_postmeta_count();

		// Test meta query performance.
		$query_time = self::measure_meta_query_performance();

		// If missing indexes on large postmeta tables, flag as critical.
		if ( ! $has_meta_indexes && $postmeta_count > 50000 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Postmeta row count */
					__( 'Postmeta table has %s rows but missing critical meta_key indexes. Meta queries will be extremely slow. Add composite index on (meta_key, meta_value) for better performance.', 'wpshadow' ),
					number_format( $postmeta_count )
				),
				'severity'     => 'critical',
				'threat_level' => 75,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/meta-query-performance',
				'meta'         => array(
					'post_count'        => $post_count,
					'postmeta_count'    => $postmeta_count,
					'has_meta_indexes'  => $has_meta_indexes,
					'query_time'        => $query_time,
					'recommendation'    => 'Add database indexes for meta queries',
				),
			);
		}

		// If query time is very slow on large sites, flag as high priority.
		if ( $post_count > 10000 && $query_time > 1.5 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: Query time in seconds, 2: Post count */
					__( 'Meta queries are slow (%1$ss with %2$s posts). Multiple meta_query clauses create complex joins. Consider denormalizing frequently-queried meta into taxonomy terms or custom tables.', 'wpshadow' ),
					number_format( $query_time, 2 ),
					number_format( $post_count )
				),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/meta-query-performance',
				'meta'         => array(
					'post_count'        => $post_count,
					'postmeta_count'    => $postmeta_count,
					'has_meta_indexes'  => $has_meta_indexes,
					'query_time'        => $query_time,
					'recommendation'    => 'Denormalize meta or use custom tables',
				),
			);
		}

		// If using meta queries extensively on medium sites.
		if ( $post_count > 5000 && $postmeta_count > 25000 && $query_time > 0.8 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: Postmeta count, 2: Query time */
					__( 'Site has %1$s postmeta rows and meta queries take %2$ss. Consider caching meta query results or optimizing meta_key indexes.', 'wpshadow' ),
					number_format( $postmeta_count ),
					number_format( $query_time, 2 )
				),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/meta-query-performance',
				'meta'         => array(
					'post_count'        => $post_count,
					'postmeta_count'    => $postmeta_count,
					'has_meta_indexes'  => $has_meta_indexes,
					'query_time'        => $query_time,
					'recommendation'    => 'Cache meta query results',
				),
			);
		}

		// Meta queries are adequately optimized.
		return null;
	}

	/**
	 * Get postmeta table row count.
	 *
	 * @since  1.26028.1848
	 * @return int Number of postmeta rows.
	 */
	private static function get_postmeta_count() {
		global $wpdb;

		$count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->postmeta}" );

		return $count;
	}

	/**
	 * Check if meta_key indexes exist.
	 *
	 * @since  1.26028.1848
	 * @return bool True if indexes are optimal, false otherwise.
	 */
	private static function check_meta_indexes() {
		global $wpdb;

		// Check postmeta indexes.
		$indexes = $wpdb->get_results(
			$wpdb->prepare( 'SHOW INDEX FROM %i', $wpdb->postmeta )
		);

		$has_meta_key_index = false;
		$has_composite_index = false;

		foreach ( $indexes as $index ) {
			// Check for meta_key index.
			if ( 'meta_key' === $index->Key_name || 'meta_key' === $index->Column_name ) {
				$has_meta_key_index = true;

				// Check if it's a composite index (better performance).
				if ( $index->Seq_in_index > 1 ) {
					$has_composite_index = true;
				}
			}
		}

		// At minimum, need meta_key index.
		return $has_meta_key_index;
	}

	/**
	 * Measure meta query performance.
	 *
	 * @since  1.26028.1848
	 * @return float Query time in seconds.
	 */
	private static function measure_meta_query_performance() {
		global $wpdb;

		// Get a common meta_key to test with.
		$common_key = $wpdb->get_var(
			"SELECT meta_key FROM {$wpdb->postmeta} GROUP BY meta_key ORDER BY COUNT(*) DESC LIMIT 1"
		);

		if ( ! $common_key ) {
			return 0.0;
		}

		// Measure meta query time.
		$start_time = microtime( true );

		// Perform a meta query.
		$meta_query = new \WP_Query(
			array(
				'meta_query' => array(
					array(
						'key'     => $common_key,
						'compare' => 'EXISTS',
					),
				),
				'posts_per_page' => 10,
				'fields'         => 'ids',
				'no_found_rows'  => true,
			)
		);

		$end_time = microtime( true );

		// Clean up.
		wp_reset_postdata();

		return $end_time - $start_time;
	}
}
