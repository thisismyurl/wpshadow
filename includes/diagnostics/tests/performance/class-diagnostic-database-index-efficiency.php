<?php
/**
 * Database Index Efficiency Diagnostic
 *
 * Detects missing or inefficient indexes on high‑traffic WordPress tables.
 * Without indexes, common queries require full table scans, which can turn
 * millisecond queries into multi‑second delays under load.
 *
 * **What This Check Does:**
 * - Reviews indexes on posts, postmeta, comments, and term tables
 * - Identifies frequently queried columns without indexes
 * - Highlights potential performance bottlenecks
 * - Provides actionable index recommendations
 *
 * **Why This Matters:**
 * Indexes are the fastest performance win for large WordPress sites. Missing
 * indexes on `postmeta.meta_key` or `posts.post_type` can slow every page load.
 *
 * **Philosophy Alignment:**
 * - #9 Show Value: Performance gains are measurable and immediate
 * - #8 Inspire Confidence: Prevents slow queries under traffic spikes
 *
 * **Learn More:**
 * See https://wpshadow.com/kb/database-index-efficiency
 * or https://wpshadow.com/training/wordpress-database-performance
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5049.1401
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
