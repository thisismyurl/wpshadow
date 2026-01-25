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
 * @guardian-integrated Yes - Loaded via Diagnostic_Registry
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
 * @guardian-integrated Yes - Loaded via Diagnostic_Registry
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
				'SELECT
					table_name as name,
					ROUND(data_length / 1024 / 1024, 2) as data_mb,
					ROUND(index_length / 1024 / 1024, 2) as index_mb,
					ROUND(data_free / 1024 / 1024, 2) as overhead_mb,
					engine
				FROM information_schema.TABLES
				WHERE table_schema = %s
				AND data_free > 0
				ORDER BY data_free DESC',
				DB_NAME
			),
			ARRAY_A
		);

		if ( empty( $tables ) ) {
			return null;
		}

		// Filter to significant overhead (> 1MB per table or > 5MB total)
		$significant_tables = array_filter(
			$tables,
			function ( $table ) {
				return $table['overhead_mb'] > 1;
			}
		);

		$total_overhead = array_sum( array_column( $tables, 'overhead_mb' ) );

		if ( empty( $significant_tables ) && $total_overhead < 5 ) {
			return null;
		}

		$severity = $total_overhead > 50 ? 'medium' : 'low';

		$description = sprintf(
			__( 'Your database has %1$s MB of overhead across %2$d tables. Table overhead is wasted space from deleted rows and fragmentation. Running OPTIMIZE TABLE can reclaim this space and speed up queries.', 'wpshadow' ),
			number_format( $total_overhead, 2 ),
			count( $significant_tables )
		);

		if ( ! empty( $significant_tables ) ) {
			$top_table    = $significant_tables[0];
			$description .= sprintf(
				' ' . __( 'Largest: %1$s has %2$s MB overhead.', 'wpshadow' ),
				$top_table['name'],
				number_format( $top_table['overhead_mb'], 2 )
			);
		}

		return array(
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
			'metadata'          => array(
				'total_overhead_mb' => $total_overhead,
				'table_count'       => count( $significant_tables ),
				'tables'            => array_slice( $significant_tables, 0, 10 ),
			),
		);
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Database Table Overhead
	 * Slug: -database-table-overhead
	 * File: class-diagnostic-database-table-overhead.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Database Table Overhead
	 * Slug: -database-table-overhead
	 *
	 * TODO: Review the check() method to understand what constitutes a passing test.
	 * The test should verify that:
	 * - check() returns NULL when the diagnostic condition is NOT met (site is healthy)
	 * - check() returns an array when the diagnostic condition IS met (issue found)
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live__database_table_overhead(): array {
		global $wpdb;

		// Recompute actual table overhead
		$tables = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT
					ROUND(data_free / 1024 / 1024, 2) as overhead_mb
				FROM information_schema.TABLES
				WHERE table_schema = %s
				AND data_free > 0',
				DB_NAME
			),
			ARRAY_A
		);

		$total_overhead    = 0;
		$significant_count = 0;
		if ( ! empty( $tables ) ) {
			$total_overhead    = array_sum( array_column( $tables, 'overhead_mb' ) );
			$significant_count = count(
				array_filter(
					$tables,
					function ( $t ) {
						return $t['overhead_mb'] > 1;
					}
				)
			);
		}

		// Call diagnostic check
		$diagnostic_result = self::check();

		// Determine expected state (matches check() logic)
		$should_find_issue      = ( $significant_count > 0 || $total_overhead >= 5 );
		$diagnostic_found_issue = ( $diagnostic_result !== null );

		// Compare expected vs actual diagnostic result
		$test_passes = ( $should_find_issue === $diagnostic_found_issue );

		$message = sprintf(
			'Total overhead: %.2f MB, significant tables: %d. Expected diagnostic to %s issue. Diagnostic %s issue. Test: %s',
			$total_overhead,
			$significant_count,
			$should_find_issue ? 'FIND' : 'NOT find',
			$diagnostic_found_issue ? 'FOUND' : 'DID NOT find',
			$test_passes ? 'PASS' : 'FAIL'
		);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}
}
