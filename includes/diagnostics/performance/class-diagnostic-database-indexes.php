<?php
declare(strict_types=1);
/**
 * Database Index Optimization Diagnostic
 *
 * Philosophy: Shows value (#9) by identifying database performance bottlenecks
 * Guides to Pro features for automated index optimization
 *
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check database table indexes for optimization opportunities.
 */
class Diagnostic_Database_Indexes extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		
		// Check for tables without proper indexes
		$tables = $wpdb->get_results( "SHOW TABLES" );
		if ( empty( $tables ) ) {
			return null;
		}
		
		$issues = array();
		$table_prefix = $wpdb->prefix;
		
		// Check key tables for missing indexes
		$key_tables = array(
			$table_prefix . 'posts'      => array( 'post_status', 'post_type', 'post_date' ),
			$table_prefix . 'postmeta'   => array( 'post_id', 'meta_key' ),
			$table_prefix . 'comments'   => array( 'comment_post_ID', 'comment_approved' ),
			$table_prefix . 'commentmeta' => array( 'comment_id', 'meta_key' ),
		);
		
		foreach ( $key_tables as $table => $columns ) {
			$result = $wpdb->get_results( $wpdb->prepare( "SHOW INDEXES FROM %i", $table ) );
			if ( is_wp_error( $result ) || empty( $result ) ) {
				continue;
			}
			
			$indexed_columns = array();
			foreach ( $result as $index ) {
				$indexed_columns[] = $index->Column_name;
			}
			
			foreach ( $columns as $column ) {
				if ( ! in_array( $column, $indexed_columns, true ) ) {
					$issues[] = sprintf(
						'Column %s in %s is missing an index (could slow queries)',
						$column,
						str_replace( $table_prefix, '', $table )
					);
				}
			}
		}
		
		if ( ! empty( $issues ) ) {
			return array(
				'title'       => 'Database Indexes Could Be Optimized',
				'description' => 'We found ' . count( $issues ) . ' column(s) that could benefit from database indexes. This improves query performance, especially on larger sites. ' . implode( '. ', $issues ),
				'severity'    => 'low',
				'category'    => 'code_quality',
				'kb_link'     => 'https://wpshadow.com/kb/database-index-optimization/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=database-indexes',
				'auto_fixable' => false,
				'threat_level' => 30,
			);
		}
		
		return null;
	}
}
