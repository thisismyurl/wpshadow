<?php
/**
 * Diagnostic: Table-by-Table Size Analysis
 *
 * Checks database table sizes to identify bloated tables.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Database
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Table_Size_Analysis
 *
 * Tests database table sizes and identifies problematic tables.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Table_Size_Analysis extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'table-size-analysis';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Table-by-Table Size Analysis';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes database table sizes to identify bloated or problematic tables';

	/**
	 * Check table sizes.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$db_name = DB_NAME;
		$query   = $wpdb->prepare(
			"SELECT TABLE_NAME, ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) AS 'Size (MB)'
			FROM INFORMATION_SCHEMA.TABLES
			WHERE TABLE_SCHEMA = %s
			ORDER BY (DATA_LENGTH + INDEX_LENGTH) DESC",
			$db_name
		);

		$results = $wpdb->get_results( $query );

		if ( empty( $results ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Could not retrieve database table size information.', 'wpshadow' ),
				'severity'    => 'info',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/table_size_analysis',
				'meta'        => array(
					'db_name' => $db_name,
				),
			);
		}

		// Check for oversized tables (>100 MB).
		$oversized = array_filter(
			$results,
			function( $table ) {
				return (float) $table->{'Size (MB)'} > 100;
			}
		);

		if ( ! empty( $oversized ) ) {
			$table_list = array_column( $oversized, 'TABLE_NAME' );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: Comma-separated list of table names */
					__( 'Oversized database tables detected: %s. Consider cleanup or optimization (revisions, transients, logs).', 'wpshadow' ),
					implode( ', ', $table_list )
				),
				'severity'    => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/table_size_analysis',
				'meta'        => array(
					'oversized_tables' => $table_list,
					'total_tables'     => count( $results ),
				),
			);
		}

		return null;
	}
}
