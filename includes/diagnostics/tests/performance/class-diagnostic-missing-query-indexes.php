<?php
/**
 * Missing Query Indexes Diagnostic
 *
 * Identifies columns frequently used in queries that lack proper indexes.
 * This diagnostic focuses on practical query patterns and flags missing indexes
 * that cause slow admin screens, delayed searches, and high database load.
 *
 * **What This Check Does:**
 * - Scans critical tables for missing indexes
 * - Focuses on columns used in WHERE clauses
 * - Provides a short list of the most impactful missing indexes
 *
 * **Why This Matters:**
 * Missing indexes are a top cause of slow WordPress sites. As content grows,
 * queries that were fast at 1,000 posts become slow at 100,000 posts.
 *
 * **Philosophy Alignment:**
 * - #9 Show Value: Delivers clear, measurable performance wins
 * - #8 Inspire Confidence: Prevents slowdowns as sites scale
 *
 * **Learn More:**
 * See https://wpshadow.com/kb/missing-query-indexes
 * or https://wpshadow.com/training/database-indexing-101
 *
 * @since   1.4031.1939
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Missing_Query_Indexes Class
 *
 * Uses `SHOW INDEX` data to detect missing indexes on common columns.
 *
 * **Implementation Pattern:**
 * 1. Define critical tables and columns
 * 2. Fetch existing index metadata
 * 3. Identify columns lacking indexes
 * 4. Return findings with top-impact items
 *
 * **Related Diagnostics:**
 * - Database Index Efficiency
 * - Database Storage Engine Consistency
 */
class Diagnostic_Missing_Query_Indexes extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'missing-query-indexes';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Missing Query Indexes';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects frequently accessed columns that lack database indexes';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.4031.1939
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$missing_indexes = array();

		// Check common WordPress columns that should be indexed
		$critical_columns = array(
			"{$wpdb->posts}" => array( 'post_parent', 'post_type', 'post_status' ),
			"{$wpdb->postmeta}" => array( 'post_id', 'meta_key', 'meta_value' ),
			"{$wpdb->comments}" => array( 'comment_post_ID', 'comment_approved', 'user_id' ),
			"{$wpdb->commentmeta}" => array( 'comment_id', 'meta_key' ),
		);

		foreach ( $critical_columns as $table => $columns ) {
			$indexes = $wpdb->get_results(
				$wpdb->prepare(
					"SHOW INDEX FROM {$table}",
					array()
				)
			);

			$indexed_columns = array();
			foreach ( (array) $indexes as $index ) {
				$indexed_columns[] = $index->Column_name;
			}

			foreach ( $columns as $column ) {
				if ( ! in_array( $column, $indexed_columns, true ) ) {
					$missing_indexes[] = "{$table}.{$column}";
				}
			}
		}

		if ( ! empty( $missing_indexes ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: list of unindexed columns */
					__( 'Missing indexes on frequently queried columns: %s', 'wpshadow' ),
					implode( ', ', $missing_indexes )
				),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'details'      => array(
					'missing_indexes' => $missing_indexes,
				),
				'kb_link'      => 'https://wpshadow.com/kb/missing-query-indexes',
			);
		}

		return null;
	}
}
