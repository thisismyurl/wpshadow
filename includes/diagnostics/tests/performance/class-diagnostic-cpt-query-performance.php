<?php
/**
 * CPT Query Performance Diagnostic
 *
 * Measures query performance for custom post type listings and detects
 * slow or inefficient queries that may degrade site performance.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CPT Query Performance Class
 *
 * Analyzes custom post type query performance and identifies slow queries,
 * missing indexes, and inefficient query patterns.
 *
 * @since 1.2601.2148
 */
class Diagnostic_CPT_Query_Performance extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cpt-query-performance';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'CPT Query Performance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Measures query performance for custom post type listings';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests query performance for CPTs and identifies slow queries
	 * or missing database indexes.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if performance issues found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();
		$slow_queries = array();

		// Get all custom post types.
		$post_types = get_post_types(
			array(
				'public'   => true,
				'_builtin' => false,
			),
			'objects'
		);

		if ( empty( $post_types ) ) {
			return null;
		}

		foreach ( $post_types as $post_type => $post_type_obj ) {
			// Get post count.
			$post_count = wp_count_posts( $post_type );
			$total = isset( $post_count->publish ) ? $post_count->publish : 0;

			if ( $total === 0 ) {
				continue;
			}

			// Test basic query performance.
			$start_time = microtime( true );

			$wpdb->get_results(
				$wpdb->prepare(
					"SELECT ID, post_title FROM {$wpdb->posts} 
					WHERE post_type = %s 
					AND post_status = 'publish' 
					LIMIT 20",
					$post_type
				)
			);

			$query_time = ( microtime( true ) - $start_time ) * 1000; // Convert to ms.

			// Flag queries slower than 100ms for 20 posts.
			if ( $query_time > 100 ) {
				$slow_queries[ $post_type ] = array(
					'type'  => $post_type_obj->label,
					'time'  => round( $query_time, 2 ),
					'posts' => $total,
				);

				$issues[] = sprintf(
					/* translators: 1: post type label, 2: query time in ms, 3: post count */
					__( '%1$s query took %2$sms for %3$s posts', 'wpshadow' ),
					$post_type_obj->label,
					number_format_i18n( round( $query_time, 2 ), 2 ),
					number_format_i18n( $total )
				);
			}

			// Check for meta query performance if CPT uses meta.
			$meta_count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->postmeta} pm
					INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
					WHERE p.post_type = %s",
					$post_type
				)
			);

			if ( $meta_count && (int) $meta_count > 1000 ) {
				// Test meta query performance.
				$start_time = microtime( true );

				$wpdb->get_results(
					$wpdb->prepare(
						"SELECT p.ID, pm.meta_key, pm.meta_value 
						FROM {$wpdb->posts} p
						INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
						WHERE p.post_type = %s
						AND p.post_status = 'publish'
						LIMIT 20",
						$post_type
					)
				);

				$meta_query_time = ( microtime( true ) - $start_time ) * 1000;

				if ( $meta_query_time > 200 ) {
					$issues[] = sprintf(
						/* translators: 1: post type label, 2: query time in ms */
						__( '%1$s meta query took %2$sms (slow)', 'wpshadow' ),
						$post_type_obj->label,
						number_format_i18n( round( $meta_query_time, 2 ), 2 )
					);
				}
			}

			// Check if taxonomies slow down queries.
			$taxonomies = get_object_taxonomies( $post_type );

			if ( ! empty( $taxonomies ) ) {
				$start_time = microtime( true );

				$wpdb->get_results(
					$wpdb->prepare(
						"SELECT p.ID, t.name 
						FROM {$wpdb->posts} p
						LEFT JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
						LEFT JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
						LEFT JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
						WHERE p.post_type = %s
						AND p.post_status = 'publish'
						LIMIT 20",
						$post_type
					)
				);

				$tax_query_time = ( microtime( true ) - $start_time ) * 1000;

				if ( $tax_query_time > 150 ) {
					$issues[] = sprintf(
						/* translators: 1: post type label, 2: query time in ms */
						__( '%1$s with taxonomy query took %2$sms (slow)', 'wpshadow' ),
						$post_type_obj->label,
						number_format_i18n( round( $tax_query_time, 2 ), 2 )
					);
				}
			}

			// Check for large result sets without pagination.
			if ( $total > 1000 ) {
				$issues[] = sprintf(
					/* translators: 1: post type label, 2: post count */
					__( '%1$s has %2$s posts - queries without LIMIT will be very slow', 'wpshadow' ),
					$post_type_obj->label,
					number_format_i18n( $total )
				);
			}
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => implode( '. ', $issues ),
			'severity'    => 'medium',
			'threat_level' => 55,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/cpt-query-performance',
			'details'     => array(
				'slow_queries' => $slow_queries,
			),
		);
	}
}
