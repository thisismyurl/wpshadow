<?php
/**
 * Database Table Optimization Status Diagnostic
 *
 * Checks if database tables need optimization.
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
 * Database Table Optimization Status Diagnostic Class
 *
 * Flags tables with excessive fragmentation.
 *
 * @since 1.5049.1401
 */
class Diagnostic_Database_Table_Optimization_Status extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-table-optimization-status';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Table Optimization Status';

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
	 * @since  1.5049.1401
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$tables = $wpdb->get_col( "SHOW TABLES LIKE '{$wpdb->prefix}%'" );
		$tables_needing_optimization = array();

		foreach ( array_slice( $tables, 0, 20 ) as $table ) {
			$status = $wpdb->get_results( $wpdb->prepare( 'SHOW TABLE STATUS LIKE %s', basename( $table ) ), ARRAY_A );
			if ( empty( $status ) ) {
				continue;
			}

			$row = $status[0];
			if ( isset( $row['Data_free'] ) && $row['Data_free'] > 0 ) {
				$free_mb = round( $row['Data_free'] / 1024 / 1024, 2 );
				if ( $free_mb >= 1.0 ) {
					$tables_needing_optimization[] = array(
						'table' => $table,
						'free_mb' => $free_mb,
					);
				}
			}
		}

		if ( ! empty( $tables_needing_optimization ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Some database tables have unused space and can be optimized.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'details'      => array(
					'tables' => array_slice( $tables_needing_optimization, 0, 10 ),
				),
				'kb_link'      => 'https://wpshadow.com/kb/database-table-optimization-status',
			);
		}

		return null;
	}
}
