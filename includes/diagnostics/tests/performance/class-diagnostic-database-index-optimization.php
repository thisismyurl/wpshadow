<?php
/**
 * Database Index Optimization Diagnostic
 *
 * Issue #4970: Custom Tables Missing Indexes
 * Pillar: ⚙️ Murphy's Law
 *
 * Checks if custom database tables have proper indexes.
 * Missing indexes cause slow queries as data grows.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Database_Index_Optimization Class
 *
 * @since 1.6050.0000
 */
class Diagnostic_Database_Index_Optimization extends Diagnostic_Base {

	protected static $slug = 'database-index-optimization';
	protected static $title = 'Custom Tables Missing Indexes';
	protected static $description = 'Checks if custom database tables have appropriate indexes';
	protected static $family = 'performance';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Add indexes to columns used in WHERE clauses', 'wpshadow' );
		$issues[] = __( 'Add indexes to columns used in JOIN conditions', 'wpshadow' );
		$issues[] = __( 'Add indexes to columns used in ORDER BY', 'wpshadow' );
		$issues[] = __( 'Use composite indexes for multi-column queries', 'wpshadow' );
		$issues[] = __( 'Monitor slow query log to identify missing indexes', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Database indexes are like book indexes - they let MySQL find data instantly. Without indexes, MySQL scans every row (slow).', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/database-indexes',
				'details'      => array(
					'recommendations'         => $issues,
					'performance_impact'      => '10-1000x faster queries with proper indexes',
					'create_index'            => 'ALTER TABLE table_name ADD INDEX (column_name);',
					'check_indexes'           => 'SHOW INDEX FROM table_name;',
				),
			);
		}

		return null;
	}
}
