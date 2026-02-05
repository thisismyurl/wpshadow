<?php
/**
 * Post Meta Query Performance Treatment
 *
 * Measures performance of meta_query operations. Detects slow meta queries that
 * impact site performance and suggests optimization strategies.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post Meta Query Performance Treatment Class
 *
 * Checks for performance issues in meta query operations.
 *
 * @since 1.6030.2148
 */
class Treatment_Post_Meta_Query_Performance extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-meta-query-performance';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Post Meta Query Performance';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Measures meta_query performance and detects slow operations';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'posts';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Check postmeta table size (large tables = slower queries).
		$row_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->postmeta}" );

		if ( $row_count > 100000 ) {
			$issues[] = sprintf(
				/* translators: %s: number of rows */
				__( 'Postmeta table has %s rows (large table impacts query performance)', 'wpshadow' ),
				number_format_i18n( $row_count )
			);
		}

		// Check for missing indexes on meta_key and meta_value.
		$indexes = $wpdb->get_results(
			$wpdb->prepare(
				"SHOW INDEX FROM {$wpdb->postmeta} WHERE Key_name != 'PRIMARY'"
			),
			ARRAY_A
		);

		$has_meta_key_index = false;
		$has_meta_value_index = false;

		foreach ( $indexes as $index ) {
			if ( 'meta_key' === $index['Column_name'] ) {
				$has_meta_key_index = true;
			}
			if ( 'meta_value' === $index['Column_name'] ) {
				$has_meta_value_index = true;
			}
		}

		if ( ! $has_meta_key_index && $row_count > 10000 ) {
			$issues[] = __( 'Missing index on meta_key column (queries will be extremely slow)', 'wpshadow' );
		}

		// Check for frequently queried meta keys without composite indexes.
		$frequently_queried = $wpdb->get_results(
			"SELECT meta_key, COUNT(*) as count
			FROM {$wpdb->postmeta}
			GROUP BY meta_key
			HAVING count > 5000
			ORDER BY count DESC
			LIMIT 10",
			ARRAY_A
		);

		if ( ! empty( $frequently_queried ) && count( $frequently_queried ) > 3 ) {
			$key_names = wp_list_pluck( $frequently_queried, 'meta_key' );
			$issues[] = sprintf(
				/* translators: %d: number of keys */
				__( '%d meta keys used heavily (consider adding specific indexes for performance)', 'wpshadow' ),
				count( $frequently_queried )
			);
		}

		// Check for meta queries with LIKE operations (very slow).
		$like_queries = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->postmeta}
			WHERE meta_value LIKE '%%search%%'
			OR meta_key LIKE '%%search%%'
			LIMIT 1"
		);

		// Test a sample meta query for performance.
		$start_time = microtime( true );
		$wpdb->get_results(
			$wpdb->prepare(
				"SELECT post_id
				FROM {$wpdb->postmeta}
				WHERE meta_key = %s
				LIMIT 100",
				'_thumbnail_id'
			),
			ARRAY_A
		);
		$query_time = microtime( true ) - $start_time;

		if ( $query_time > 0.5 ) {
			$issues[] = sprintf(
				/* translators: %s: query time in seconds */
				__( 'Simple meta query took %s seconds (database needs optimization)', 'wpshadow' ),
				number_format( $query_time, 3 )
			);
		}

		// Check for meta queries on very long text values (slow comparisons).
		$long_values = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->postmeta}
			WHERE LENGTH(meta_value) > 10000"
		);

		if ( $long_values > 100 ) {
			$issues[] = sprintf(
				/* translators: %d: number of long values */
				__( '%d meta values exceed 10KB (querying these is slow)', 'wpshadow' ),
				$long_values
			);
		}

		// Check for complex meta queries (multiple meta_key comparisons).
		$distinct_keys_per_post = $wpdb->get_var(
			"SELECT AVG(key_count) FROM (
				SELECT post_id, COUNT(DISTINCT meta_key) as key_count
				FROM {$wpdb->postmeta}
				GROUP BY post_id
			) as counts"
		);

		if ( $distinct_keys_per_post > 30 ) {
			$issues[] = sprintf(
				/* translators: %d: average number of keys */
				__( 'Posts average %d distinct meta keys (complex queries will be slow)', 'wpshadow' ),
				round( $distinct_keys_per_post )
			);
		}

		// Check for table fragmentation.
		$table_status = $wpdb->get_row(
			$wpdb->prepare(
				"SHOW TABLE STATUS LIKE %s",
				$wpdb->postmeta
			),
			ARRAY_A
		);

		if ( isset( $table_status['Data_free'] ) && $table_status['Data_free'] > 10485760 ) {
			$data_free_mb = round( $table_status['Data_free'] / 1024 / 1024 );
			$issues[] = sprintf(
				/* translators: %d: fragmentation size in MB */
				__( 'Postmeta table has %d MB fragmentation (run OPTIMIZE TABLE)', 'wpshadow' ),
				$data_free_mb
			);
		}

		// Check for meta_query usage in recent queries.
		if ( defined( 'SAVEQUERIES' ) && SAVEQUERIES ) {
			global $wpdb;
			if ( isset( $wpdb->queries ) && is_array( $wpdb->queries ) ) {
				$meta_queries = 0;
				$slow_meta_queries = 0;

				foreach ( $wpdb->queries as $query_data ) {
					$query = $query_data[0];
					$time = $query_data[1];

					if ( strpos( $query, $wpdb->postmeta ) !== false ) {
						++$meta_queries;
						if ( $time > 0.1 ) {
							++$slow_meta_queries;
						}
					}
				}

				if ( $slow_meta_queries > 5 ) {
					$issues[] = sprintf(
						/* translators: %d: number of slow queries */
						__( '%d slow meta queries detected (>0.1s each)', 'wpshadow' ),
						$slow_meta_queries
					);
				}
			}
		}

		// Check for posts with excessive meta (causes slow individual queries).
		$posts_with_excessive_meta = $wpdb->get_var(
			"SELECT COUNT(*) FROM (
				SELECT post_id, COUNT(*) as meta_count
				FROM {$wpdb->postmeta}
				GROUP BY post_id
				HAVING meta_count > 100
			) as excessive"
		);

		if ( $posts_with_excessive_meta > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d posts have 100+ meta fields (querying these posts is slow)', 'wpshadow' ),
				$posts_with_excessive_meta
			);
		}

		// Check if meta queries use comparison operators (slower than exact matches).
		$comparison_usage = $wpdb->get_var(
			"SELECT COUNT(DISTINCT meta_key)
			FROM {$wpdb->postmeta}
			WHERE meta_value REGEXP '^[0-9]+$'
			AND meta_key NOT LIKE '\\_%%'"
		);

		if ( $comparison_usage > 20 ) {
			$issues[] = sprintf(
				/* translators: %d: number of numeric keys */
				__( '%d numeric meta keys (comparison queries on these are slower than exact matches)', 'wpshadow' ),
				$comparison_usage
			);
		}

		// Check for serialized data in meta values (requires unserialization overhead).
		$serialized_count = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->postmeta}
			WHERE meta_value LIKE 'a:%'
			OR meta_value LIKE 'O:%'"
		);

		if ( $serialized_count > 5000 ) {
			$issues[] = sprintf(
				/* translators: %s: number of serialized values */
				__( '%s serialized meta values (querying requires unserialization overhead)', 'wpshadow' ),
				number_format_i18n( $serialized_count )
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/post-meta-query-performance',
			);
		}

		return null;
	}
}
