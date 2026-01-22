<?php
/**
 * Treatment: Optimize Database Tables
 *
 * Runs OPTIMIZE TABLE on fragmented tables.
 *
 * Philosophy: Ridiculously Good (#7) - Free database optimization
 * KB Link: https://wpshadow.com/kb/database-table-overhead
 * Training: https://wpshadow.com/training/database-table-overhead
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\KPI_Tracker;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Optimize Database Tables treatment
 */
class Treatment_Optimize_Database_Tables extends Treatment_Base {

	/**
	 * Apply the treatment
	 *
	 * @param array $options Treatment options
	 * @return bool Success status
	 */
	public static function apply( array $options = [] ): bool {
		global $wpdb;

		// Get tables with overhead
		$tables = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT 
					table_name as name,
					ROUND(data_free / 1024 / 1024, 2) as overhead_mb
				FROM information_schema.TABLES
				WHERE table_schema = %s
				AND data_free > 1048576
				ORDER BY data_free DESC",
				DB_NAME
			),
			ARRAY_A
		);

		if ( empty( $tables ) ) {
			return false;
		}

		$optimized = [];
		$total_reclaimed = 0;

		foreach ( $tables as $table ) {
			$table_name = $table['name'];
			$before_overhead = $table['overhead_mb'];

			// Run OPTIMIZE TABLE
			$result = $wpdb->query( "OPTIMIZE TABLE `{$table_name}`" );

			if ( $result ) {
				$optimized[] = [
					'table'             => $table_name,
					'overhead_reclaimed' => $before_overhead,
				];
				$total_reclaimed += $before_overhead;
			}
		}

		// Create backup with optimization results
		self::create_backup( [
			'optimized'       => $optimized,
			'total_reclaimed' => $total_reclaimed,
			'timestamp'       => time(),
		] );

		// Track KPI
		if ( ! empty( $optimized ) ) {
			KPI_Tracker::record_treatment_applied( __CLASS__, 3 );
		}

		return ! empty( $optimized );
	}

	/**
	 * Undo the treatment
	 *
	 * @return bool Success status
	 */
	public static function undo(): bool {
		// Cannot "un-optimize" tables (optimization is beneficial anyway)
		// This is a safe operation with no undo needed
		return true;
	}

	/**
	 * Get display name
	 *
	 * @return string
	 */
	public static function get_name(): string {
		return __( 'Optimize Database Tables', 'wpshadow' );
	}

	/**
	 * Get description
	 *
	 * @return string
	 */
	public static function get_description(): string {
		return sprintf(
			__( 'Runs OPTIMIZE TABLE on fragmented database tables to reclaim wasted space and improve query performance. Safe to run on live sites. <a href="%s" target="_blank">Learn about table optimization</a>', 'wpshadow' ),
			'https://wpshadow.com/kb/database-table-overhead'
		);
	}
}
