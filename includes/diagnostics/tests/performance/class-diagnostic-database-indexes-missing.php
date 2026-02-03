<?php
/**
 * Database Indexes Missing Diagnostic
 *
 * Checks for missing or inefficient database indexes.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.2062
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Indexes Missing Diagnostic Class
 *
 * Detects missing indexes on frequently queried columns.
 * Missing indexes cause slow queries and poor performance at scale.
 *
 * @since 1.26033.2062
 */
class Diagnostic_Database_Indexes_Missing extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-indexes-missing';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Indexes Missing';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for missing database indexes on key columns';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * Verifies critical indexes exist on WordPress tables.
	 * Missing indexes severely impact query performance as data grows.
	 *
	 * @since  1.26033.2062
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;
		
		$missing_indexes = array();
		
		// Define expected indexes for WordPress core tables
		$expected_indexes = array(
			$wpdb->posts => array(
				'post_name'           => 'post_name',
				'type_status_date'    => 'type_status_date',
				'post_parent'         => 'post_parent',
				'post_author'         => 'post_author',
			),
			$wpdb->postmeta => array(
				'post_id'    => 'post_id',
				'meta_key'   => 'meta_key',
			),
			$wpdb->comments => array(
				'comment_post_ID'      => 'comment_post_ID',
				'comment_approved_date_gmt' => 'comment_approved_date_gmt',
				'comment_parent'       => 'comment_parent',
			),
			$wpdb->term_relationships => array(
				'term_taxonomy_id' => 'term_taxonomy_id',
			),
			$wpdb->usermeta => array(
				'user_id'   => 'user_id',
				'meta_key'  => 'meta_key',
			),
		);
		
		// Check each table for expected indexes
		foreach ( $expected_indexes as $table => $indexes ) {
			// Get existing indexes
			$existing_indexes = $wpdb->get_results(
				$wpdb->prepare( 'SHOW INDEX FROM %i', $table ),
				ARRAY_A
			);
			
			if ( empty( $existing_indexes ) ) {
				$missing_indexes[] = sprintf(
					/* translators: %s: table name */
					__( 'Unable to check indexes on table %s', 'wpshadow' ),
					$table
				);
				continue;
			}
			
			// Build array of existing index names
			$existing_names = array();
			foreach ( $existing_indexes as $index ) {
				$existing_names[] = $index['Key_name'];
			}
			
			// Check for missing indexes
			foreach ( $indexes as $index_name => $column ) {
				if ( ! in_array( $index_name, $existing_names, true ) ) {
					$missing_indexes[] = sprintf(
						/* translators: 1: index name, 2: table name */
						__( 'Missing %1$s index on %2$s', 'wpshadow' ),
						$index_name,
						$table
					);
				}
			}
		}
		
		// Check for large tables without indexes
		$large_tables = array();
		$table_list   = array( $wpdb->posts, $wpdb->postmeta, $wpdb->comments, $wpdb->usermeta );
		
		foreach ( $table_list as $table ) {
			$row_count = $wpdb->get_var(
				$wpdb->prepare( 'SELECT COUNT(*) FROM %i', $table )
			);
			
			if ( $row_count > 100000 ) { // 100k+ rows
				$large_tables[ $table ] = $row_count;
			}
		}
		
		// If large tables exist, missing indexes are critical
		$severity     = 'medium';
		$threat_level = 50;
		
		if ( ! empty( $large_tables ) && ! empty( $missing_indexes ) ) {
			$severity     = 'high';
			$threat_level = 80;
		}
		
		// If missing indexes found
		if ( ! empty( $missing_indexes ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: list of missing indexes */
					__( 'Missing database indexes detected: %s. Missing indexes cause slow queries, especially on large tables. Database performance degrades significantly as data grows.', 'wpshadow' ),
					implode( '; ', array_slice( $missing_indexes, 0, 5 ) )
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-indexes',
				'meta'         => array(
					'missing_indexes'  => $missing_indexes,
					'total_missing'    => count( $missing_indexes ),
					'large_tables'     => $large_tables,
					'has_large_tables' => ! empty( $large_tables ),
					'recommendation'   => 'Run WordPress database repair or use index optimization plugin',
				),
			);
		}
		
		// Check for missing indexes on custom tables (common with plugins)
		$custom_tables = $wpdb->get_results(
			$wpdb->prepare(
				"SHOW TABLES LIKE %s",
				$wpdb->esc_like( $wpdb->prefix ) . '%'
			),
			ARRAY_N
		);
		
		$unindexed_custom_tables = 0;
		
		if ( $custom_tables ) {
			foreach ( $custom_tables as $table_row ) {
				$table = $table_row[0];
				
				// Skip core tables
				if ( in_array( $table, array( $wpdb->posts, $wpdb->postmeta, $wpdb->comments, $wpdb->usermeta, $wpdb->users, $wpdb->terms, $wpdb->term_taxonomy, $wpdb->term_relationships, $wpdb->options, $wpdb->links, $wpdb->commentmeta, $wpdb->termmeta ), true ) ) {
					continue;
				}
				
				// Check if table has any indexes
				$indexes = $wpdb->get_results(
					$wpdb->prepare( 'SHOW INDEX FROM %i', $table ),
					ARRAY_A
				);
				
				// Count non-primary indexes
				$index_count = 0;
				if ( $indexes ) {
					foreach ( $indexes as $index ) {
						if ( 'PRIMARY' !== $index['Key_name'] ) {
							$index_count++;
						}
					}
				}
				
				if ( $index_count === 0 ) {
					$unindexed_custom_tables++;
				}
			}
		}
		
		if ( $unindexed_custom_tables > 5 ) {
			return array(
				'id'           => 'custom-tables-unindexed',
				'title'        => __( 'Custom Tables Lack Indexes', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %d: number of unindexed tables */
					__( '%d custom plugin tables have no indexes. Plugins creating custom tables should add appropriate indexes for query performance.', 'wpshadow' ),
					$unindexed_custom_tables
				),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/custom-table-optimization',
				'meta'         => array(
					'unindexed_tables' => $unindexed_custom_tables,
					'recommendation'   => 'Contact plugin developers about index optimization',
				),
			);
		}
		
		// All expected indexes present
		return null;
	}
}
