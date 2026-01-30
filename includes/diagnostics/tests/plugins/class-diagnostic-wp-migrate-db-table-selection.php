<?php
/**
 * WP Migrate DB Table Selection Diagnostic
 *
 * WP Migrate DB migrating unnecessary tables.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.385.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP Migrate DB Table Selection Diagnostic Class
 *
 * @since 1.385.0000
 */
class Diagnostic_WpMigrateDbTableSelection extends Diagnostic_Base {

	protected static $slug = 'wp-migrate-db-table-selection';
	protected static $title = 'WP Migrate DB Table Selection';
	protected static $description = 'WP Migrate DB migrating unnecessary tables';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'WPMDB_VERSION' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Get all tables
		$all_tables = $wpdb->get_col( 'SHOW TABLES' );
		$core_tables = array(
			$wpdb->posts,
			$wpdb->postmeta,
			$wpdb->users,
			$wpdb->usermeta,
			$wpdb->options,
			$wpdb->comments,
			$wpdb->commentmeta,
			$wpdb->terms,
			$wpdb->term_taxonomy,
			$wpdb->term_relationships,
			$wpdb->termmeta,
		);
		
		// Check 2: Excluded tables
		$excluded = get_option( 'wpmdb_exclude_tables', array() );
		$excluded_count = is_array( $excluded ) ? count( $excluded ) : 0;
		
		if ( $excluded_count === 0 && count( $all_tables ) > 15 ) {
			$issues[] = __( 'No table exclusions (migrating everything)', 'wpshadow' );
		}
		
		// Check 3: Large tables
		$table_sizes = $wpdb->get_results(
			"SELECT table_name, 
			        ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
			 FROM information_schema.TABLES
			 WHERE table_schema = DATABASE()
			 ORDER BY size_mb DESC
			 LIMIT 5",
			ARRAY_A
		);
		
		$large_tables = array();
		foreach ( $table_sizes as $table ) {
			if ( $table['size_mb'] > 100 ) {
				$large_tables[] = $table['table_name'];
			}
		}
		
		if ( count( $large_tables ) > 0 && $excluded_count === 0 ) {
			$issues[] = sprintf(
				/* translators: %s: list of large tables */
				__( 'Large tables not excluded: %s (slow migrations)', 'wpshadow' ),
				implode( ', ', array_slice( $large_tables, 0, 3 ) )
			);
		}
		
		// Check 4: Transient tables
		$exclude_transients = get_option( 'wpmdb_exclude_transients', false );
		if ( ! $exclude_transients ) {
			$issues[] = __( 'Transients not excluded (unnecessary data)', 'wpshadow' );
		}
		
		// Check 5: Log tables
		$log_tables = array();
		foreach ( $all_tables as $table ) {
			if ( strpos( $table, '_log' ) !== false || strpos( $table, '_logs' ) !== false ) {
				$log_tables[] = $table;
			}
		}
		
		if ( count( $log_tables ) > 0 ) {
			$excluded_logs = array_intersect( $log_tables, $excluded );
			if ( count( $excluded_logs ) === 0 ) {
				$issues[] = sprintf( __( '%d log tables not excluded', 'wpshadow' ), count( $log_tables ) );
			}
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 40;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 52;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 46;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of table selection issues */
				__( 'WP Migrate DB table selection has %d optimization opportunities: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/wp-migrate-db-table-selection',
		);
	}
}
