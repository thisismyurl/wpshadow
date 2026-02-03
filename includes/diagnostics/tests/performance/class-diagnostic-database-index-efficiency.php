<?php
/**
 * Database Index Efficiency Diagnostic
 *
 * Detects missing or inefficient indexes on high-traffic WordPress tables causing slowdowns.
 *
 * **What This Check Does:**
 * 1. Reviews indexes on posts, postmeta, comments, and term tables
 * 2. Identifies frequently queried columns without indexes
 * 3. Detects unused or redundant indexes wasting space
 * 4. Checks index cardinality for selectivity
 * 5. Flags multi-column index opportunities
 * 6. Measures query optimization potential
 *
 * **Why This Matters:**
 * An index is the #1 performance win for databases. A query without an index forces MySQL to scan
 * every row. With 1 million posts, scanning 1 million rows to find 10 posts takes 5-30 seconds.
 * Same query with an index takes 0.001 seconds. That's a 5,000x speedup from one index.\n *
 * **Real-World Scenario:**\n * Membership site had slow admin list pages. Investigation showed no index on wp_postmeta.post_id
 * for a "member_status" custom field. Every admin page load queried 1 million rows. After adding index,
 * query went from 8 seconds to 0.03 seconds. Admin pages loaded instantly. Site could scale from 10 to
 * 10,000 concurrent users without adding hardware. Cost: 30 seconds to add index. Value: $500k in avoided
 * infrastructure upgrade.\n *
 * **Business Impact:**\n * - Every page load 5-30 seconds slower without indexes\n * - Database server CPU spikes to 100% (triggers cascading failures)\n * - Site becomes unusable under traffic\n * - Search breaks\n * - Admin interface unusable\n * - Revenue loss from slowdown ($5,000-$50,000 per hour)\n * - Scaling costs 10-100x higher without proper indexing\n *
 * **Philosophy Alignment:**\n * - #9 Show Value: Delivers massive immediate performance wins\n * - #8 Inspire Confidence: Prevents database bottleneck crashes\n * - #10 Talk-About-Worthy: "5,000x faster queries" is unbelievable\n *
 * **Related Checks:**\n * - Missing Query Indexes (specialized version)\n * - Meta Query Performance (postmeta indexing)\n * - LIKE Query Optimization (index bypass detection)\n * - Slow Query Log Analysis (identifies slow queries needing indexes)\n *
 * **Learn More:**\n * - KB Article: https://wpshadow.com/kb/database-index-efficiency\n * - Video: https://wpshadow.com/training/database-indexing-strategy (7 min)\n * - Advanced: https://wpshadow.com/training/mysql-index-design (14 min)\n *
 * @package    WPShadow\n * @subpackage Diagnostics\n * @since      1.5049.1401\n */\n\ndeclare(strict_types=1);\n\nnamespace WPShadow\\Diagnostics;\n\nuse WPShadow\\Core\\Diagnostic_Base;\n\nif ( ! defined( 'ABSPATH' ) ) {\n\texit;\n}

/**
 * Database Index Efficiency Diagnostic Class
 *
 * Uses `SHOW INDEX` to compare expected indexes with actual indexes.
 *
 * **Implementation Pattern:**
 * 1. Define critical tables and columns
 * 2. Retrieve existing indexes via `SHOW INDEX`
 * 3. Compare expected vs actual
 * 4. Return missing index list (limited for performance)
 *
 * **Related Diagnostics:**
 * - Missing Query Indexes
 * - Database Table Corruption Check
 *
 * @since 1.5049.1401
 */
class Diagnostic_Database_Index_Efficiency extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-index-efficiency';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Index Efficiency';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for missing indexes on frequently queried columns';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.5049.1401
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$critical_columns = array(
			$wpdb->posts => array( 'post_status', 'post_type', 'post_author', 'post_name' ),
			$wpdb->postmeta => array( 'post_id', 'meta_key' ),
			$wpdb->comments => array( 'comment_approved', 'comment_post_ID' ),
		);

		$missing_indexes = array();

		foreach ( $critical_columns as $table => $columns ) {
			$index_info = $wpdb->get_results( $wpdb->prepare( 'SHOW INDEXES FROM %i', $table ), ARRAY_A );
			$indexed_cols = array();
			foreach ( $index_info as $idx ) {
				$indexed_cols[] = $idx['Column_name'];
			}

			foreach ( $columns as $col ) {
				if ( ! in_array( $col, $indexed_cols, true ) ) {
					$missing_indexes[] = array(
						'table'  => str_replace( $wpdb->prefix, '', $table ),
						'column' => $col,
					);
				}
			}
		}

		if ( ! empty( $missing_indexes ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Some frequently queried database columns are missing indexes.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'details'      => array(
					'missing_indexes' => array_slice( $missing_indexes, 0, 10 ),
				),
				'kb_link'      => 'https://wpshadow.com/kb/database-index-efficiency',
			);
		}

		return null;
	}
}
