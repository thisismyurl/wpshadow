<?php
/**
 * Import Resource Exhaustion Diagnostic
 *
 * Tests for resource issues during large imports.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Import Resource Exhaustion Diagnostic Class
 *
 * Tests for CPU, memory, and database resource issues during large imports.
 *
 * @since 1.6033.0000
 */
class Diagnostic_Import_Resource_Exhaustion extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'import-resource-exhaustion';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Import Resource Exhaustion';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for resource issues during large imports';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Check for database table optimization.
		$unoptimized_tables = 0;

		$tables = $wpdb->get_col( "SHOW TABLES FROM " . DB_NAME );
		foreach ( $tables as $table ) {
			$table_status = $wpdb->get_results( "ANALYZE TABLE {$table}" );
			if ( ! empty( $table_status ) && strpos( $table_status[0]->Msg_text, 'OK' ) === false ) {
				$unoptimized_tables++;
			}
		}

		if ( $unoptimized_tables > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of unoptimized tables */
				__( '%d database tables could use optimization', 'wpshadow' ),
				$unoptimized_tables
			);
		}

		// Check for database query performance.
		$slow_query_log = ini_get( 'slow_query_log' );
		if ( $slow_query_log ) {
			$issues[] = __( 'Slow query log enabled - check for problematic import queries', 'wpshadow' );
		}

		// Check for available disk space.
		if ( function_exists( 'disk_free_space' ) ) {
			$free_space = disk_free_space( WP_CONTENT_DIR );
			if ( $free_space < 104857600 ) { // < 100MB
				$issues[] = sprintf(
					/* translators: %s: free disk space */
					__( 'Low disk space available (%s) - imports may fail', 'wpshadow' ),
					size_format( $free_space )
				);
			}
		}

		// Check for file descriptor limits (if available).
		if ( function_exists( 'posix_getrlimit' ) ) {
			$limits = posix_getrlimit();
			if ( $limits['soft openfiles'] < 1024 ) {
				$issues[] = sprintf(
					/* translators: %d: file descriptor limit */
					__( 'Low file descriptor limit (%d) - may restrict concurrent requests', 'wpshadow' ),
					$limits['soft openfiles']
				);
			}
		}

		// Check WordPress.com VIP Go safe mode (restrictive).
		if ( defined( 'WPCOM_VIP_GO' ) && WPCOM_VIP_GO ) {
			$issues[] = __( 'WordPress.com VIP Go mode detected - imports may be restricted', 'wpshadow' );
		}

		// Check for managed hosting restrictions.
		if ( defined( 'WP_MEMORY_LIMIT' ) ) {
			$wp_memory = wp_convert_hr_to_bytes( WP_MEMORY_LIMIT );
			if ( $wp_memory < 67108864 ) { // 64MB
				$issues[] = sprintf(
					/* translators: %s: WordPress memory limit */
					__( 'WordPress memory limit is constrained (%s)', 'wpshadow' ),
					WP_MEMORY_LIMIT
				);
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/import-resource-exhaustion',
			);
		}

		return null;
	}
}
