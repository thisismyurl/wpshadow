<?php
/**
 * Table Optimization Status Diagnostic
 *
 * Checks database table fragmentation and optimization status.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1512
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Table Optimization Status Diagnostic Class
 *
 * Checks for fragmented tables and optimization opportunities.
 *
 * @since 1.6035.1512
 */
class Diagnostic_Table_Optimization_Status extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'table-optimization-status';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Table Optimization Status';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for fragmented tables needing optimization';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'database-health';

	/**
	 * Run the table optimization diagnostic check.
	 *
	 * @since  1.6035.1512
	 * @return array|null Finding array if optimization needed, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_table_optimization';
		$cached = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		global $wpdb;

		$tables = $wpdb->get_results( 'SHOW TABLES', ARRAY_N );

		if ( empty( $tables ) ) {
			set_transient( $cache_key, null, DAY_IN_SECONDS );
			return null;
		}

		$fragmented_tables = array();
		$total_wasted_space = 0;

		foreach ( $tables as $table ) {
			$table_name = $table[0];

			$table_status = $wpdb->get_row(
				$wpdb->prepare(
					"SHOW TABLE STATUS WHERE Name = %s",
					$table_name
				),
				ARRAY_A
			);

			if ( ! $table_status || empty( $table_status['Data_free'] ) ) {
				continue;
			}

			$wasted_space = (int) $table_status['Data_free'];

			if ( $wasted_space > 0 ) {
				$fragmented_tables[] = array(
					'table'        => $table_name,
					'wasted_space' => $wasted_space,
				);
				$total_wasted_space += $wasted_space;
			}
		}

		$result = null;

		if ( ! empty( $fragmented_tables ) && $total_wasted_space > 1024 * 1024 ) { // > 1MB
			$result = array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: 1: count of tables, 2: wasted space */
					__( '%1$d tables are fragmented with %2$s of wasted space. Database optimization recommended.', 'wpshadow' ),
					count( $fragmented_tables ),
					self::format_bytes( $total_wasted_space )
				),
				'severity'    => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/optimize-database-tables',
				'meta'        => array(
					'fragmented_table_count' => count( $fragmented_tables ),
					'total_wasted_space'     => self::format_bytes( $total_wasted_space ),
				),
			);
		}

		set_transient( $cache_key, $result, DAY_IN_SECONDS );

		return $result;
	}

	/**
	 * Format bytes to human readable.
	 *
	 * @since  1.6035.1512
	 * @param  int $bytes Bytes to format.
	 * @return string Formatted bytes.
	 */
	private static function format_bytes( int $bytes ): string {
		$units = array( 'B', 'KB', 'MB', 'GB' );
		$bytes = max( $bytes, 0 );
		$pow = floor( ( $bytes ? log( $bytes ) : 0 ) / log( 1024 ) );
		$pow = min( $pow, count( $units ) - 1 );
		$bytes /= ( 1 << ( 10 * $pow ) );

		return round( $bytes, 2 ) . ' ' . $units[ $pow ];
	}
}
