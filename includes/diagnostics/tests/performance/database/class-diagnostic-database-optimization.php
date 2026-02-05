<?php
/**
 * Database Optimization Needed Diagnostic
 *
 * Checks if database tables need optimization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1530
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Optimization Diagnostic Class
 *
 * Checks for database table fragmentation and overhead.
 * Like checking if your filing cabinet needs reorganizing.
 *
 * @since 1.6035.1530
 */
class Diagnostic_Database_Optimization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-optimization';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Optimization Needed';

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
	protected static $family = 'database';

	/**
	 * Run the database optimization diagnostic check.
	 *
	 * @since  1.6035.1530
	 * @return array|null Finding array if optimization needed, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Get table status information.
		$tables = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT TABLE_NAME, ENGINE, TABLE_ROWS, DATA_LENGTH, INDEX_LENGTH, DATA_FREE
				FROM information_schema.TABLES
				WHERE TABLE_SCHEMA = %s',
				DB_NAME
			),
			ARRAY_A
		);

		if ( ! $tables ) {
			return null; // Can't check optimization.
		}

		$total_overhead = 0;
		$tables_with_overhead = array();
		$total_size = 0;

		foreach ( $tables as $table ) {
			$table_name = $table['TABLE_NAME'];
			$data_free = (int) ( $table['DATA_FREE'] ?? 0 );
			$table_size = (int) ( $table['DATA_LENGTH'] ?? 0 ) + (int) ( $table['INDEX_LENGTH'] ?? 0 );

			$total_size += $table_size;

			// Skip non-WordPress tables.
			if ( false === strpos( $table_name, $wpdb->prefix ) ) {
				continue;
			}

			// Check for overhead (fragmentation).
			if ( $data_free > 0 ) {
				$total_overhead += $data_free;
				$overhead_pct = ( $data_free / max( $table_size, 1 ) ) * 100;

				// Only report if overhead is significant (>10% or >1MB).
				if ( $overhead_pct > 10 || $data_free > 1048576 ) {
					$tables_with_overhead[] = array(
						'table'        => $table_name,
						'overhead'     => $data_free,
						'overhead_pct' => $overhead_pct,
					);
				}
			}
		}

		// Check for transient buildup.
		$transient_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->options}
			WHERE option_name LIKE '%_transient_%'"
		);

		$expired_transients = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->options}
				WHERE option_name LIKE %s
				AND option_value < %d",
				'%_transient_timeout_%',
				time()
			)
		);

		// Check for autoload burden.
		$autoload_size = $wpdb->get_var(
			"SELECT SUM(LENGTH(option_value)) FROM {$wpdb->options}
			WHERE autoload = 'yes'"
		);

		$autoload_mb = $autoload_size / 1024 / 1024;

		// Report findings.
		$issues = array();

		if ( ! empty( $tables_with_overhead ) ) {
			$issues[] = sprintf(
				/* translators: 1: overhead size in MB, 2: number of tables */
				__( '%1$s MB of table fragmentation across %2$d tables (like gaps in your filing cabinet from deleted files)', 'wpshadow' ),
				number_format_i18n( $total_overhead / 1024 / 1024, 2 ),
				count( $tables_with_overhead )
			);
		}

		if ( $expired_transients > 100 ) {
			$issues[] = sprintf(
				/* translators: %d: number of expired transients */
				__( '%d expired cached items (temporary data that wasn\'t cleaned up)', 'wpshadow' ),
				number_format_i18n( (int) $expired_transients )
			);
		}

		if ( $autoload_mb > 1 ) {
			$issues[] = sprintf(
				/* translators: %s: autoload size in MB */
				__( '%s MB of data loading on every page (like carrying too many folders at once)', 'wpshadow' ),
				number_format_i18n( $autoload_mb, 2 )
			);
		}

		if ( ! empty( $issues ) ) {
			$severity = ( $total_overhead > 10485760 || $autoload_mb > 3 ) ? 'medium' : 'low';
			$threat_level = ( $total_overhead > 10485760 || $autoload_mb > 3 ) ? 50 : 30;

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: list of optimization issues */
					__( 'Your database could benefit from optimization (like reorganizing a messy filing cabinet for faster access). Issues found: %s. Running database optimization improves query speed and reduces storage space. Use WP-Optimize, Advanced Database Cleaner, or similar plugins.', 'wpshadow' ),
					implode( '; ', $issues )
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-optimization',
				'context'      => array(
					'total_overhead_mb'   => $total_overhead / 1024 / 1024,
					'tables_with_overhead' => count( $tables_with_overhead ),
					'expired_transients'  => (int) $expired_transients,
					'autoload_mb'         => $autoload_mb,
					'issues'              => $issues,
				),
			);
		}

		return null; // Database is optimized.
	}
}
