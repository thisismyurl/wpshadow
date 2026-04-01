<?php
/**
 * Database Size Monitoring Diagnostic
 *
 * Checks if database size is approaching hosting limits.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Size Monitoring Diagnostic Class
 *
 * Monitors database growth and warns about size limits.
 * Like checking how full your filing cabinet is getting.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Database_Size extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-size';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Size Monitoring';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if database size is approaching hosting limits';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'database';

	/**
	 * Run the database size monitoring diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if size issues detected, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Get total database size.
		$size_query = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT SUM(DATA_LENGTH + INDEX_LENGTH) as size
				FROM information_schema.TABLES
				WHERE TABLE_SCHEMA = %s',
				DB_NAME
			),
			ARRAY_A
		);

		if ( ! $size_query ) {
			return null; // Can't determine size.
		}

		$total_size = (int) ( $size_query['size'] ?? 0 );
		$size_mb = $total_size / 1024 / 1024;
		$size_gb = $size_mb / 1024;

		// Get individual table sizes.
		$table_sizes = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT TABLE_NAME, (DATA_LENGTH + INDEX_LENGTH) as size
				FROM information_schema.TABLES
				WHERE TABLE_SCHEMA = %s
				ORDER BY size DESC
				LIMIT 10',
				DB_NAME
			),
			ARRAY_A
		);

		// Check for unusually large tables.
		$large_tables = array();
		foreach ( $table_sizes as $table ) {
			$table_mb = (int) ( $table['size'] ?? 0 ) / 1024 / 1024;
			if ( $table_mb > 100 ) {
				$large_tables[] = array(
					'table' => $table['TABLE_NAME'],
					'size'  => $table_mb,
				);
			}
		}

		// Check growth rate if we have historical data.
		$last_check = get_transient( 'wpshadow_db_size_last_check' );
		$growth_rate = null;

		if ( $last_check ) {
			$days_since = ( time() - $last_check['timestamp'] ) / DAY_IN_SECONDS;
			if ( $days_since > 7 ) {
				$size_change = $size_mb - $last_check['size_mb'];
				$growth_rate = $size_change / $days_since; // MB per day.
			}
		}

		// Store current size for future comparison.
		set_transient(
			'wpshadow_db_size_last_check',
			array(
				'timestamp' => time(),
				'size_mb'   => $size_mb,
			),
			30 * DAY_IN_SECONDS
		);

		// Estimate hosting limits (common shared hosting limits).
		$common_limits = array(
			'small'  => 250,   // 250 MB.
			'medium' => 1024,  // 1 GB.
			'large'  => 2048,  // 2 GB.
		);

		// Warn if approaching limits.
		if ( $size_mb > 2048 ) {
			return array(
				'id'           => self::$slug . '-very-large',
				'title'        => __( 'Database Size Very Large', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %s: database size */
					__( 'Your database is %s (like a filing cabinet that\'s completely full). This may exceed hosting limits and slow down your site significantly. Consider: archiving old content, removing spam comments, clearing post revisions, or upgrading to a hosting plan with higher limits.', 'wpshadow' ),
					size_format( $total_size )
				),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-size?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'size_mb'      => $size_mb,
					'size_gb'      => $size_gb,
					'large_tables' => $large_tables,
					'growth_rate'  => $growth_rate,
				),
			);
		}

		if ( $size_mb > 1024 ) {
			return array(
				'id'           => self::$slug . '-large',
				'title'        => __( 'Database Size Approaching Limits', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %s: database size */
					__( 'Your database is %s (like a filing cabinet that\'s getting full). This might be approaching your hosting plan limits. Monitor your database size and consider cleanup if it continues growing. Check your hosting dashboard for database limits.', 'wpshadow' ),
					size_format( $total_size )
				),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-size?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'size_mb'      => $size_mb,
					'size_gb'      => $size_gb,
					'large_tables' => $large_tables,
					'growth_rate'  => $growth_rate,
				),
			);
		}

		if ( $growth_rate && $growth_rate > 10 ) {
			return array(
				'id'           => self::$slug . '-rapid-growth',
				'title'        => __( 'Database Growing Rapidly', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %s: growth rate in MB per day */
					__( 'Your database is growing by %s MB per day (like adding many folders to your filing cabinet daily). This rapid growth could indicate a logging issue, spam problem, or data accumulation. Review what\'s causing the growth to prevent future space issues.', 'wpshadow' ),
					number_format_i18n( $growth_rate, 2 )
				),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-size?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'size_mb'      => $size_mb,
					'growth_rate'  => $growth_rate,
					'large_tables' => $large_tables,
				),
			);
		}

		if ( ! empty( $large_tables ) ) {
			return array(
				'id'           => self::$slug . '-large-tables',
				'title'        => __( 'Large Database Tables Detected', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: 1: largest table name, 2: table size */
					__( 'Some database tables are unusually large. The biggest is %1$s at %2$s (like one drawer in your filing cabinet being completely full). Large tables can slow queries. Consider cleaning up old data or archiving if appropriate.', 'wpshadow' ),
					$large_tables[0]['table'],
					size_format( $large_tables[0]['size'] * 1024 * 1024 )
				),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-size?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'size_mb'      => $size_mb,
					'large_tables' => $large_tables,
				),
			);
		}

		return null; // Database size is healthy.
	}
}
