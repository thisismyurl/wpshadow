<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Large Database
 *
 * Detects when WordPress database grows too large, indicating need for optimization.
 * Large databases slow down performance and backup times.
 *
 * @since 1.2.0
 */
class Test_Large_Database extends Diagnostic_Base {


	private const SIZE_WARNING_MB  = 500;   // 500MB warning threshold
	private const SIZE_CRITICAL_MB = 1000; // 1GB critical threshold

	/**
	 * Check for oversized database
	 *
	 * @return array|null Diagnostic array if issues found, null if all good
	 */
	public static function check(): ?array {
		global $wpdb;

		$database_size = self::get_database_size();
		$database_mb   = $database_size / ( 1024 * 1024 );

		// Check thresholds
		if ( $database_mb < self::SIZE_WARNING_MB ) {
			return null;
		}

		$threat = $database_mb > self::SIZE_CRITICAL_MB ? 80 : 50;

		// Get table breakdown
		$tables_info = self::get_tables_info();

		return array(
			'threat_level'  => $threat,
			'threat_color'  => $threat >= 80 ? 'red' : 'yellow',
			'passed'        => false,
			'issue'         => sprintf(
				'Database is %s (consider optimization)',
				self::format_bytes( $database_size )
			),
			'metadata'      => array(
				'total_size'     => $database_size,
				'formatted_size' => self::format_bytes( $database_size ),
				'table_count'    => count( $tables_info ),
				'largest_tables' => array_slice( $tables_info, 0, 5 ),
			),
			'kb_link'       => 'https://wpshadow.com/kb/database-optimization/',
			'training_link' => 'https://wpshadow.com/training/wordpress-database-maintenance/',
		);
	}

	/**
	 * Guardian Sub-Test: Total database size
	 *
	 * @return array Test result
	 */
	public static function test_database_size(): array {
		$size    = self::get_database_size();
		$size_mb = $size / ( 1024 * 1024 );

		$status = $size_mb > self::SIZE_CRITICAL_MB ? 'critical' : ( $size_mb > self::SIZE_WARNING_MB ? 'large' : 'normal' );

		return array(
			'test_name'      => 'Database Size',
			'total_bytes'    => $size,
			'formatted_size' => self::format_bytes( $size ),
			'size_mb'        => round( $size_mb, 2 ),
			'status'         => $status,
			'passed'         => $size_mb < self::SIZE_WARNING_MB,
			'description'    => sprintf( 'Database size: %s (%s)', self::format_bytes( $size ), $status ),
		);
	}

	/**
	 * Guardian Sub-Test: Largest tables breakdown
	 *
	 * @return array Test result
	 */
	public static function test_largest_tables(): array {
		$tables = self::get_tables_info();

		return array(
			'test_name'      => 'Largest Database Tables',
			'table_count'    => count( $tables ),
			'largest_tables' => array_slice( $tables, 0, 10 ),
			'description'    => sprintf( 'Found %d tables, largest: %s', count( $tables ), $tables[0]['name'] ?? 'N/A' ),
		);
	}

	/**
	 * Guardian Sub-Test: Post revisions analysis
	 *
	 * @return array Test result
	 */
	public static function test_post_revisions(): array {
		global $wpdb;

		$revision_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'revision'" );
		$revision_size  = self::get_revisions_size();

		return array(
			'test_name'         => 'Post Revisions',
			'revision_count'    => (int) $revision_count,
			'revision_size'     => self::format_bytes( $revision_size ),
			'cleanup_potential' => $revision_count > 100 ? 'High' : 'Low',
			'description'       => sprintf( '%d revisions using %s space', $revision_count, self::format_bytes( $revision_size ) ),
		);
	}

	/**
	 * Guardian Sub-Test: Transient data analysis
	 *
	 * @return array Test result
	 */
	public static function test_transients(): array {
		global $wpdb;

		$transient_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '_transient_%'" );

		return array(
			'test_name'         => 'Transient Data',
			'transient_count'   => (int) $transient_count,
			'cleanup_potential' => $transient_count > 1000 ? 'High' : 'Low',
			'description'       => sprintf( 'Found %d transients (cache entries)', $transient_count ),
		);
	}

	/**
	 * Get total database size in bytes
	 *
	 * @return int Database size in bytes
	 */
	private static function get_database_size(): int {
		global $wpdb;

		$result = $wpdb->get_results( "SELECT SUM(data_length + index_length) as size FROM information_schema.tables WHERE table_schema = '" . DB_NAME . "'" );

		if ( $result && isset( $result[0]->size ) ) {
			return (int) $result[0]->size;
		}

		return 0;
	}

	/**
	 * Get database tables sorted by size
	 *
	 * @return array Table information
	 */
	private static function get_tables_info(): array {
		global $wpdb;

		$results = $wpdb->get_results( "SELECT table_name, data_length + index_length as size FROM information_schema.tables WHERE table_schema = '" . DB_NAME . "' ORDER BY size DESC" );

		$tables = array();
		if ( $results ) {
			foreach ( $results as $row ) {
				$tables[] = array(
					'name'      => $row->table_name,
					'size'      => $row->size,
					'formatted' => self::format_bytes( $row->size ),
				);
			}
		}

		return $tables;
	}

	/**
	 * Get total size of post revisions
	 *
	 * @return int Size in bytes
	 */
	private static function get_revisions_size(): int {
		global $wpdb;

		$result = $wpdb->get_var( "SELECT SUM(post_content_filtered) FROM {$wpdb->posts} WHERE post_type = 'revision'" );

		return (int) ( $result ?: 0 );
	}

	/**
	 * Format bytes as human-readable
	 *
	 * @param int $bytes Byte count
	 * @return string Formatted size
	 */
	private static function format_bytes( int $bytes ): string {
		$units  = array( 'B', 'KB', 'MB', 'GB' );
		$bytes  = max( $bytes, 0 );
		$pow    = floor( ( $bytes ? log( $bytes ) : 0 ) / log( 1024 ) );
		$pow    = min( $pow, count( $units ) - 1 );
		$bytes /= ( 1 << ( 10 * $pow ) );

		return round( $bytes, 2 ) . ' ' . $units[ $pow ];
	}

	/**
	 * Get diagnostic name
	 *
	 * @return string
	 */
	public static function get_name(): string {
		return 'Large Database';
	}

	/**
	 * Get diagnostic description
	 *
	 * @return string
	 */
	public static function get_description(): string {
		return 'Detects oversized databases that need optimization';
	}

	/**
	 * Get diagnostic category
	 *
	 * @return string
	 */
	public static function get_category(): string {
		return 'Performance';
	}
}
