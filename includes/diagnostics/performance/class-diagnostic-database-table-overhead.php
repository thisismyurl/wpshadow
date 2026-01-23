<?php
/**
 * Diagnostic: Database Table Overhead
 *
 * Detects fragmented tables that need optimization.
 *
 * Philosophy: Show Value (#9) - Measure wasted space
 * KB Link: https://wpshadow.com/kb/database-table-overhead
 * Training: https://wpshadow.com/training/database-table-overhead
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Table Overhead diagnostic
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Database_Table_Overhead extends Diagnostic_Base {

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Diagnostic result or null if no issue
	 */
	public static function check(): ?array {
		global $wpdb;

		// Get table overhead (MyISAM and InnoDB)
		$tables = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT 
					table_name as name,
					ROUND(data_length / 1024 / 1024, 2) as data_mb,
					ROUND(index_length / 1024 / 1024, 2) as index_mb,
					ROUND(data_free / 1024 / 1024, 2) as overhead_mb,
					engine
				FROM information_schema.TABLES
				WHERE table_schema = %s
				AND data_free > 0
				ORDER BY data_free DESC",
				DB_NAME
			),
			ARRAY_A
		);

		if ( empty( $tables ) ) {
			return null;
		}

		// Filter to significant overhead (> 1MB per table or > 5MB total)
		$significant_tables = array_filter( $tables, function( $table ) {
			return $table['overhead_mb'] > 1;
		} );

		$total_overhead = array_sum( array_column( $tables, 'overhead_mb' ) );

		if ( empty( $significant_tables ) && $total_overhead < 5 ) {
			return null;
		}

		$severity = $total_overhead > 50 ? 'medium' : 'low';

		$description = sprintf(
			__( 'Your database has %s MB of overhead across %d tables. Table overhead is wasted space from deleted rows and fragmentation. Running OPTIMIZE TABLE can reclaim this space and speed up queries.', 'wpshadow' ),
			number_format( $total_overhead, 2 ),
			count( $significant_tables )
		);

		if ( ! empty( $significant_tables ) ) {
			$top_table = $significant_tables[0];
			$description .= sprintf(
				' ' . __( 'Largest: %s has %s MB overhead.', 'wpshadow' ),
				$top_table['name'],
				number_format( $top_table['overhead_mb'], 2 )
			);
		}

		return [
			'id'                => 'database-table-overhead',
			'title'             => __( 'Database Table Overhead', 'wpshadow' ),
			'description'       => $description,
			'severity'          => $severity,
			'category'          => 'performance',
			'impact'            => 'medium',
			'effort'            => 'low',
			'kb_link'           => 'https://wpshadow.com/kb/database-table-overhead',
			'training_link'     => 'https://wpshadow.com/training/database-table-overhead',
			'affected_resource' => sprintf( '%d tables, %s MB overhead', count( $significant_tables ), number_format( $total_overhead, 2 ) ),
			'metadata'          => [
				'total_overhead_mb' => $total_overhead,
				'table_count'       => count( $significant_tables ),
				'tables'            => array_slice( $significant_tables, 0, 10 ),
			],
		];
	}

}