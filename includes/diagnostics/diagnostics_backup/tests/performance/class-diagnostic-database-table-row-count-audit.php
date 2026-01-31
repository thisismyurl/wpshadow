<?php
/**
 * Database Table Row Count Audit Diagnostic
 *
 * Identifies abnormally large tables requiring investigation.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26029.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Table Row Count Audit Class
 *
 * Tests table sizes.
 *
 * @since 1.26029.0000
 */
class Diagnostic_Database_Table_Row_Count_Audit extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-table-row-count-audit';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Table Row Count Audit';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies abnormally large tables requiring investigation';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26029.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$row_count_check = self::check_table_row_counts();
		
		if ( $row_count_check['has_issues'] ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $row_count_check['issues'] ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-table-row-count-audit',
				'meta'         => array(
					'oversized_tables' => $row_count_check['oversized_tables'],
					'total_rows'       => $row_count_check['total_rows'],
				),
			);
		}

		return null;
	}

	/**
	 * Check table row counts.
	 *
	 * @since  1.26029.0000
	 * @return array Check results.
	 */
	private static function check_table_row_counts() {
		global $wpdb;

		$check = array(
			'has_issues'       => false,
			'issues'           => array(),
			'oversized_tables' => array(),
			'total_rows'       => 0,
		);

		// Core tables to check.
		$tables_to_check = array(
			$wpdb->posts       => 100000,  // 100k posts is excessive for most sites.
			$wpdb->postmeta    => 500000,  // 500k postmeta rows indicates bloat.
			$wpdb->comments    => 50000,   // 50k comments (often spam).
			$wpdb->commentmeta => 100000,  // 100k commentmeta rows.
			$wpdb->options     => 10000,   // 10k options (includes transients).
			$wpdb->usermeta    => 100000,  // 100k usermeta rows.
		);

		foreach ( $tables_to_check as $table => $threshold ) {
			$row_count = (int) $wpdb->get_var(
				$wpdb->prepare( 'SELECT COUNT(*) FROM %i', $table )
			);

			$check['total_rows'] += $row_count;

			if ( $row_count > $threshold ) {
				$check['oversized_tables'][] = array(
					'table'     => basename( $table ),
					'row_count' => $row_count,
					'threshold' => $threshold,
				);
			}
		}

		// Detect issues.
		if ( ! empty( $check['oversized_tables'] ) ) {
			$check['has_issues'] = true;

			foreach ( $check['oversized_tables'] as $oversized ) {
				$check['issues'][] = sprintf(
					/* translators: 1: table name, 2: row count, 3: threshold */
					__( '%1$s has %2$s rows (threshold: %3$s)', 'wpshadow' ),
					$oversized['table'],
					number_format_i18n( $oversized['row_count'] ),
					number_format_i18n( $oversized['threshold'] )
				);
			}
		}

		return $check;
	}
}
