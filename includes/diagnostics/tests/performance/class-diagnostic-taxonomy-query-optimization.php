<?php
/**
 * Diagnostic: Taxonomy Query Optimization
 *
 * Ensures taxonomy queries use proper joins and indexes.
 * Taxonomy queries join 3 tables (posts, term_relationships, term_taxonomy).
 * Without optimization, they slow dramatically with many terms.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since      1.26028.1846
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Taxonomy_Query_Optimization
 *
 * Tests taxonomy query performance and optimization.
 *
 * @since 1.26028.1846
 */
class Diagnostic_Taxonomy_Query_Optimization extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'taxonomy-query-optimization';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Taxonomy Query Optimization';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Ensures taxonomy queries use proper joins and indexes';

	/**
	 * Diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Check taxonomy query optimization.
	 *
	 * @since  1.26028.1846
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Check if site uses taxonomies extensively.
		$term_count = self::get_total_term_count();
		$post_count = wp_count_posts()->publish ?? 0;

		// Sites with few terms don't need optimization.
		if ( $term_count < 500 || $post_count < 1000 ) {
			return null;
		}

		// Check for term_taxonomy_id indexes.
		$has_proper_indexes = self::check_taxonomy_indexes();

		// Test multi-taxonomy query performance.
		$query_time = self::measure_taxonomy_query_performance();

		// If indexes are missing on large sites, flag as critical.
		if ( ! $has_proper_indexes && $term_count > 5000 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Term count */
					__( 'Site has %s taxonomy terms but missing critical indexes. Taxonomy queries join 3 tables and will be very slow without proper indexes. Add indexes to term_relationships and term_taxonomy tables.', 'wpshadow' ),
					number_format( $term_count )
				),
				'severity'     => 'critical',
				'threat_level' => 75,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/taxonomy-query-optimization',
				'meta'         => array(
					'term_count'          => $term_count,
					'post_count'          => $post_count,
					'has_proper_indexes'  => $has_proper_indexes,
					'query_time'          => $query_time,
					'recommendation'      => 'Add database indexes for taxonomy queries',
				),
			);
		}

		// If query time is slow, flag as high priority.
		if ( $query_time > 1.0 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: Query time in seconds, 2: Term count */
					__( 'Taxonomy queries are slow (%1$ss with %2$s terms). Consider optimizing database indexes and query structure.', 'wpshadow' ),
					number_format( $query_time, 2 ),
					number_format( $term_count )
				),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/taxonomy-query-optimization',
				'meta'         => array(
					'term_count'          => $term_count,
					'post_count'          => $post_count,
					'has_proper_indexes'  => $has_proper_indexes,
					'query_time'          => $query_time,
					'recommendation'      => 'Optimize taxonomy query indexes',
				),
			);
		}

		// Check if using multiple taxonomies without proper query optimization.
		if ( $term_count > 2000 && $query_time > 0.5 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: Term count, 2: Query time */
					__( 'Site has %1$s taxonomy terms and multi-taxonomy queries take %2$ss. Consider caching taxonomy queries or using persistent object cache.', 'wpshadow' ),
					number_format( $term_count ),
					number_format( $query_time, 2 )
				),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/taxonomy-query-optimization',
				'meta'         => array(
					'term_count'          => $term_count,
					'post_count'          => $post_count,
					'has_proper_indexes'  => $has_proper_indexes,
					'query_time'          => $query_time,
					'recommendation'      => 'Implement query caching or object cache',
				),
			);
		}

		// Taxonomy queries are adequately optimized.
		return null;
	}

	/**
	 * Get total term count across all taxonomies.
	 *
	 * @since  1.26028.1846
	 * @return int Total term count.
	 */
	private static function get_total_term_count() {
		global $wpdb;

		$count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->terms}" );

		return $count;
	}

	/**
	 * Check if proper taxonomy indexes exist.
	 *
	 * @since  1.26028.1846
	 * @return bool True if indexes are optimal, false otherwise.
	 */
	private static function check_taxonomy_indexes() {
		global $wpdb;

		// Check term_relationships indexes.
		$tr_indexes = $wpdb->get_results(
			$wpdb->prepare( 'SHOW INDEX FROM %i', $wpdb->term_relationships )
		);

		$has_object_id_index = false;
		$has_term_tax_id_index = false;

		foreach ( $tr_indexes as $index ) {
			if ( 'object_id' === $index->Key_name ) {
				$has_object_id_index = true;
			}
			if ( 'term_taxonomy_id' === $index->Key_name ) {
				$has_term_tax_id_index = true;
			}
		}

		// Check term_taxonomy indexes.
		$tt_indexes = $wpdb->get_results(
			$wpdb->prepare( 'SHOW INDEX FROM %i', $wpdb->term_taxonomy )
		);

		$has_taxonomy_index = false;

		foreach ( $tt_indexes as $index ) {
			if ( 'taxonomy' === $index->Key_name ) {
				$has_taxonomy_index = true;
			}
		}

		// All critical indexes should exist.
		return $has_object_id_index && $has_term_tax_id_index && $has_taxonomy_index;
	}

	/**
	 * Measure taxonomy query performance.
	 *
	 * @since  1.26028.1846
	 * @return float Query time in seconds.
	 */
	private static function measure_taxonomy_query_performance() {
		// Get a taxonomy with terms to test.
		$taxonomies = get_taxonomies( array( 'public' => true ) );

		if ( empty( $taxonomies ) ) {
			return 0.0;
		}

		// Get first taxonomy's first term.
		$first_taxonomy = reset( $taxonomies );
		$terms = get_terms(
			array(
				'taxonomy'   => $first_taxonomy,
				'number'     => 1,
				'hide_empty' => true,
			)
		);

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return 0.0;
		}

		$term = reset( $terms );

		// Measure multi-taxonomy query time.
		$start_time = microtime( true );

		// Perform a taxonomy query.
		$tax_query = new \WP_Query(
			array(
				'tax_query' => array(
					array(
						'taxonomy' => $first_taxonomy,
						'field'    => 'term_id',
						'terms'    => $term->term_id,
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
