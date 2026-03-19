<?php
/**
 * Slow Query Detection Diagnostic
 *
 * Checks for slow database queries affecting performance.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Slow Query Detection Diagnostic Class
 *
 * Identifies slow database queries that hurt performance.
 * Like finding which tasks take too long to complete.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Slow_Queries extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'slow-queries';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Slow Database Queries';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for slow database queries affecting performance';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'database';

	/**
	 * Run the slow query diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if slow queries detected, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Check if query monitoring is available.
		$query_monitor_active = defined( 'QM_VERSION' ) || class_exists( 'QueryMonitor' );
		$savequeries_enabled  = defined( 'SAVEQUERIES' ) && SAVEQUERIES;

		// Check wpdb query log (requires SAVEQUERIES).
		$slow_queries = array();
		if ( $savequeries_enabled && ! empty( $wpdb->queries ) ) {
			foreach ( $wpdb->queries as $query ) {
				$time = $query[1] ?? 0;
				if ( $time > 0.05 ) { // 50ms threshold.
					$slow_queries[] = array(
						'query' => substr( $query[0], 0, 100 ) . '...',
						'time'  => $time,
					);
				}
			}
		}

		// Check slow query log if accessible.
		$slow_query_log_enabled = $wpdb->get_var( "SHOW VARIABLES LIKE 'slow_query_log'" );
		$long_query_time = $wpdb->get_var( "SHOW VARIABLES LIKE 'long_query_time'" );

		$recommendations = array();

		// If slow query logging is disabled.
		if ( $slow_query_log_enabled && 'OFF' === strtoupper( $slow_query_log_enabled ) ) {
			$recommendations[] = __( 'Enable MySQL slow query logging to track performance issues', 'wpshadow' );
		}

		// If long_query_time is too high.
		if ( $long_query_time && (float) $long_query_time > 2.0 ) {
			$recommendations[] = sprintf(
				/* translators: %s: current long_query_time value */
				__( 'Lower long_query_time from %s seconds to 1-2 seconds', 'wpshadow' ),
				$long_query_time
			);
		}

		// Check for missing indexes on common queries.
		$tables_needing_indexes = array();

		// Check postmeta for common queries without indexes.
		$postmeta_no_index = $wpdb->get_results(
			"EXPLAIN SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'test' LIMIT 1",
			ARRAY_A
		);

		if ( ! empty( $postmeta_no_index ) ) {
			foreach ( $postmeta_no_index as $explain ) {
				if ( 'ALL' === $explain['type'] ) {
					$tables_needing_indexes[] = $wpdb->postmeta;
					break;
				}
			}
		}

		// Check for table scans on options table.
		$options_scan = $wpdb->get_results(
			"EXPLAIN SELECT option_value FROM {$wpdb->options} WHERE option_name = 'test' LIMIT 1",
			ARRAY_A
		);

		if ( ! empty( $options_scan ) ) {
			foreach ( $options_scan as $explain ) {
				if ( 'ALL' === $explain['type'] && 'PRIMARY' !== $explain['key'] ) {
					$tables_needing_indexes[] = $wpdb->options;
					break;
				}
			}
		}

		// Report findings.
		if ( count( $slow_queries ) > 5 ) {
			return array(
				'id'           => self::$slug . '-detected',
				'title'        => __( 'Slow Database Queries Detected', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %d: number of slow queries */
					__( 'We found %d slow database queries (like tasks that take too long to complete). These slow down your site for visitors. Common causes: missing indexes, poorly written plugin code, or large tables. Use a database optimization plugin or Query Monitor to identify and fix these queries.', 'wpshadow' ),
					count( $slow_queries )
				),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/slow-queries',
				'context'      => array(
					'slow_query_count' => count( $slow_queries ),
					'sample_queries'   => array_slice( $slow_queries, 0, 5 ),
				),
			);
		}

		if ( ! empty( $tables_needing_indexes ) ) {
			return array(
				'id'           => self::$slug . '-missing-indexes',
				'title'        => __( 'Database Tables Need Indexes', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %s: list of tables */
					__( 'Some database tables are missing indexes (like a filing cabinet without alphabetical dividers—everything takes longer to find). Tables affected: %s. Adding indexes speeds up queries significantly. Contact your hosting provider or use a database optimization plugin.', 'wpshadow' ),
					implode( ', ', array_unique( $tables_needing_indexes ) )
				),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/slow-queries',
				'context'      => array(
					'tables' => $tables_needing_indexes,
				),
			);
		}

		if ( ! $savequeries_enabled && ! $query_monitor_active ) {
			return array(
				'id'           => self::$slug . '-monitoring-disabled',
				'title'        => __( 'Database Query Monitoring Not Enabled', 'wpshadow' ),
				'description'  => __( 'Adding query monitoring helps you spot slow database operations before they become problems (like having a performance dashboard for your database). Consider installing Query Monitor plugin or enabling SAVEQUERIES during development to track query performance.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/slow-queries',
				'context'      => array(
					'savequeries' => $savequeries_enabled,
					'query_monitor' => $query_monitor_active,
				),
			);
		}

		return null; // No slow query issues detected.
	}
}
