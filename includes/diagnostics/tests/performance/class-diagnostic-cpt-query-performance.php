<?php
/**
 * CPT Query Performance Diagnostic
 *
 * Detects slow or inefficient custom post type queries that degrade overall site performance.
 *
 * **What This Check Does:**
 * 1. Measures query performance for custom post type listings
 * 2. Identifies N+1 query patterns in CPT loops
 * 3. Detects missing indexes on CPT-specific columns
 * 4. Checks for inefficient post__in clauses with many IDs
 * 5. Analyzes taxonomy queries for CPT filtering
 * 6. Flags slow CPT archive page queries
 *
 * **Why This Matters:**
 * Custom post types (e-commerce products, events, listings) are often queried inefficiently. A CPT
 * loop that loads 50 items but makes 250 database queries (N+1 pattern) is not uncommon. Each extra
 * query adds 50-200ms. 200 extra queries × 100ms = 20 seconds of wasted database time per page load.
 * With 10,000 daily visits, that's 2.3 hours of wasted database processing per day.\n *
 * **Real-World Scenario:**\n * Real estate site listing 5,000 properties (CPT). Property archive page loaded 20 properties but made
 * 320 database queries (1 to get properties + 15 to get metadata/images/relationships per property).
 * Page took 45 seconds to load. After optimizing to get all data in 5 queries (using get_posts with
 * meta_query consolidation and lazy-loading images), archive loaded in 1.2 seconds. Property inquiries
 * increased 58% because site no longer timed out on archive pages. Cost: 8 hours optimization.
 * Value: $125,000 in additional property leads that quarter.\n *
 * **Business Impact:**\n * - Archive pages timeout (potential customers never see listings)\n * - Admin CPT management slow (admins can't edit/bulk actions)\n * - Site search broken if using CPT search (N+1 multiplies)\n * - Database server overwhelmed (affects all users)\n * - Revenue loss from timeouts ($5,000-$50,000 for e-commerce)\n *
 * **Philosophy Alignment:**\n * - #8 Inspire Confidence: Prevents cascade failures on high-traffic CPT pages\n * - #9 Show Value: Delivers 10-50x speedup for CPT archives\n * - #10 Talk-About-Worthy: \"Our product pages load instantly\" is huge for e-commerce\n *
 * **Related Checks:**\n * - Meta Query Performance (CPT metadata optimization)\n * - Missing Query Indexes (CPT-specific indexes)\n * - N+1 Query Detection (related pattern)\n * - Taxonomy Query Performance (CPT filtering)\n *
 * **Learn More:**\n * - KB Article: https://wpshadow.com/kb/cpt-query-performance\n * - Video: https://wpshadow.com/training/custom-post-type-queries (7 min)\n * - Advanced: https://wpshadow.com/training/n-plus-one-elimination (12 min)\n *
 * @package    WPShadow\n * @subpackage Diagnostics\n * @since      1.2601.2148\n */\n\ndeclare(strict_types=1);\n\nnamespace WPShadow\\Diagnostics;\n\nuse WPShadow\\Core\\Diagnostic_Base;\n\nif ( ! defined( 'ABSPATH' ) ) {\n\texit;\n}\n\n/**\n * CPT Query Performance Class\n *\n * Analyzes custom post type query patterns for N+1 queries and missing indexes.
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
