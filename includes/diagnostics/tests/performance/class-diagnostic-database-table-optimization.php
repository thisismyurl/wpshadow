<?php
/**
 * Database Table Optimization Diagnostic
 *
 * Checks if database tables need optimization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.2067
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Table Optimization Diagnostic Class
 *
 * Checks for fragmented tables and overhead space.
 * Regular optimization improves query performance.
 *
 * @since 1.26033.2067
 */
class Diagnostic_Database_Table_Optimization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-table-optimization';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Table Optimization';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if database tables need optimization';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * Analyzes table status for fragmentation and overhead.
	 * Threshold: >10MB total overhead
	 *
	 * @since  1.26033.2067
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;
		
		// Get table status
		$tables = $wpdb->get_results(
			$wpdb->prepare(
				'SHOW TABLE STATUS FROM %i',
				DB_NAME
			),
			ARRAY_A
		);
		
		if ( empty( $tables ) ) {
			return null;
		}
		
		$total_overhead = 0;
		$fragmented_tables = array();
		$total_size = 0;
		
		foreach ( $tables as $table ) {
			// Only check WordPress tables
			if ( strpos( $table['Name'], $wpdb->prefix ) !== 0 ) {
				continue;
			}
			
			$data_free = isset( $table['Data_free'] ) ? (int) $table['Data_free'] : 0;
			$data_length = isset( $table['Data_length'] ) ? (int) $table['Data_length'] : 0;
			$index_length = isset( $table['Index_length'] ) ? (int) $table['Index_length'] : 0;
			
			$table_size = $data_length + $index_length;
			$total_size += $table_size;
			$total_overhead += $data_free;
			
			// Track tables with >5MB overhead or >20% fragmentation
			if ( $data_free > 5242880 ) { // 5MB
				$fragmented_tables[] = array(
					'name'     => $table['Name'],
					'overhead' => $data_free,
					'size'     => $table_size,
					'percent'  => $table_size > 0 ? round( ( $data_free / $table_size ) * 100, 1 ) : 0,
				);
			} elseif ( $table_size > 0 && ( $data_free / $table_size ) > 0.2 ) {
				$fragmented_tables[] = array(
					'name'     => $table['Name'],
					'overhead' => $data_free,
					'size'     => $table_size,
					'percent'  => round( ( $data_free / $table_size ) * 100, 1 ),
				);
			}
		}
		
		// Check if optimization needed
		if ( $total_overhead < 1048576 ) { // <1MB
			return null; // Minimal overhead
		}
		
		$severity = 'low';
		$threat_level = 25;
		
		if ( $total_overhead > 104857600 ) { // >100MB
			$severity = 'high';
			$threat_level = 70;
		} elseif ( $total_overhead > 10485760 ) { // >10MB
			$severity = 'medium';
			$threat_level = 50;
		}
		
		$overhead_percent = $total_size > 0 ? round( ( $total_overhead / $total_size ) * 100, 1 ) : 0;
		
		$description = sprintf(
			/* translators: 1: overhead size, 2: overhead percentage, 3: number of fragmented tables */
			__( 'Database has %1$s overhead (%2$s%% of total size) across %3$d tables. Optimizing tables reduces wasted space and improves query performance.', 'wpshadow' ),
			size_format( $total_overhead ),
			$overhead_percent,
			count( $fragmented_tables )
		);
		
		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => $description,
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => true,
			'kb_link'      => 'https://wpshadow.com/kb/optimize-database-tables',
			'meta'         => array(
				'total_overhead'       => $total_overhead,
				'total_overhead_formatted' => size_format( $total_overhead ),
				'total_size'           => $total_size,
				'total_size_formatted' => size_format( $total_size ),
				'overhead_percent'     => $overhead_percent,
				'fragmented_tables'    => array_slice( $fragmented_tables, 0, 10 ),
				'tables_count'         => count( $fragmented_tables ),
				'recommendation'       => 'Run OPTIMIZE TABLE on fragmented tables',
			),
		);
	}
}
