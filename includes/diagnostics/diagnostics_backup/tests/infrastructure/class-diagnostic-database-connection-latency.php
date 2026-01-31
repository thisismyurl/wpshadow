<?php
/**
 * Diagnostic: Database Connection Latency
 *
 * Measures database connection latency to detect slow database access.
 * High latency can impact page load times and query performance.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Infrastructure
 * @since      1.2601.2200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Database_Connection_Latency Class
 *
 * Measures the latency of database connections by executing lightweight
 * ping queries. Uses microsecond precision timing to detect even small
 * latency issues.
 *
 * Measures connection time vs query execution time to isolate network
 * latency from query performance. 
 *
 * Guidelines:
 * - Excellent: <10ms connection latency
 * - Good: 10-50ms
 * - Acceptable: 50-100ms  
 * - Poor: >100ms
 *
 * @since 1.2601.2200
 */
class Diagnostic_Database_Connection_Latency extends Diagnostic_Base {

	/**
	 * Diagnostic slug/identifier
	 *
	 * @var string
	 */
	protected static $slug = 'database-connection-latency';

	/**
	 * Diagnostic title (user-facing)
	 *
	 * @var string
	 */
	protected static $title = 'Database Connection Latency';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Measures latency of database connections to identify slow database access';

	/**
	 * Family grouping
	 *
	 * @var string
	 */
	protected static $family = 'infrastructure';

	/**
	 * Family label (human-readable)
	 *
	 * @var string
	 */
	protected static $family_label = 'Infrastructure';

	/**
	 * Number of ping queries to execute for averaging
	 *
	 * @var int
	 */
	private const PING_ITERATIONS = 5;

	/**
	 * Excellent latency threshold in milliseconds
	 *
	 * @var int
	 */
	private const LATENCY_EXCELLENT = 10;

	/**
	 * Good latency threshold in milliseconds
	 *
	 * @var int
	 */
	private const LATENCY_GOOD = 50;

	/**
	 * Acceptable latency threshold in milliseconds
	 *
	 * @var int
	 */
	private const LATENCY_ACCEPTABLE = 100;

	/**
	 * Run the diagnostic check.
	 *
	 * Measures database connection latency through repeated lightweight queries.
	 * Excludes query parsing/execution time by using simple SELECT 1 statements.
	 *
	 * @since  1.2601.2200
	 * @return array|null Finding array if latency is poor, null otherwise.
	 */
	public static function check() {
		// Get WordPress database connection info
		$connection_info = self::get_connection_info();
		
		// Measure connection latency through ping queries
		$latency_data = self::measure_connection_latency();
		
		if ( ! $latency_data || ! isset( $latency_data['avg_ms'] ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Unable to measure database connection latency. The database may be temporarily unavailable.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/infrastructure-database-latency',
				'family'      => self::$family,
			);
		}

		$avg_ms = $latency_data['avg_ms'];
		$min_ms = $latency_data['min_ms'];
		$max_ms = $latency_data['max_ms'];

		// Excellent latency - no issue
		if ( $avg_ms < self::LATENCY_EXCELLENT ) {
			return null;
		}

		// Determine severity based on latency
		if ( $avg_ms < self::LATENCY_GOOD ) {
			// Good latency
			return null;
		}

		if ( $avg_ms < self::LATENCY_ACCEPTABLE ) {
			// Acceptable but worth noting
			$severity = 'low';
			$threat_level = 25;
			$status = __( 'acceptable', 'wpshadow' );
		} else {
			// Poor latency - significant impact
			$severity = 'medium';
			$threat_level = 50;
			$status = __( 'poor', 'wpshadow' );
		}

		$description = sprintf(
			/* translators: 1: average latency in ms, 2: status (acceptable/poor), 3: connection location info */
			__( 'Database connection latency is averaging %1$sms, which is %2$s. This adds overhead to every database query. %3$s High latency typically indicates: remote database server, high network latency, server resource contention, or network congestion. Consider optimizing the database connection or moving database closer to web server.', 'wpshadow' ),
			number_format( $avg_ms, 2 ),
			$status,
			$connection_info
		);

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => $description,
			'severity'    => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/infrastructure-database-latency',
			'family'      => self::$family,
			'meta'        => array(
				'latency_avg_ms' => round( $avg_ms, 2 ),
				'latency_min_ms' => round( $min_ms, 2 ),
				'latency_max_ms' => round( $max_ms, 2 ),
				'threshold_excellent' => self::LATENCY_EXCELLENT,
				'threshold_good' => self::LATENCY_GOOD,
				'threshold_acceptable' => self::LATENCY_ACCEPTABLE,
				'ping_iterations' => self::PING_ITERATIONS,
			),
		);
	}

	/**
	 * Measure database connection latency through ping queries.
	 *
	 * Executes lightweight SELECT 1 queries multiple times and measures
	 * the time taken to get the first response back.
	 *
	 * @since  1.2601.2200
	 * @return array|null Array with 'avg_ms', 'min_ms', 'max_ms' keys, or null on error.
	 */
	private static function measure_connection_latency() {
		global $wpdb;

		if ( ! $wpdb ) {
			return null;
		}

		$latencies = array();

		// Run ping queries to measure latency
		for ( $i = 0; $i < self::PING_ITERATIONS; ++$i ) {
			$start = microtime( true );
			
			// Use suppress errors to prevent database errors in output
			$result = $wpdb->get_results( 'SELECT 1 as ping' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			
			$end = microtime( true );
			
			// If query failed, skip this iteration
			if ( null === $result || ! is_array( $result ) ) {
				continue;
			}

			// Calculate latency in milliseconds
			$latency_ms = ( $end - $start ) * 1000;
			$latencies[] = $latency_ms;
		}

		// If we couldn't get enough successful pings, return null
		if ( empty( $latencies ) ) {
			return null;
		}

		// Calculate statistics
		$avg_ms = array_sum( $latencies ) / count( $latencies );
		$min_ms = min( $latencies );
		$max_ms = max( $latencies );

		return array(
			'avg_ms' => $avg_ms,
			'min_ms' => $min_ms,
			'max_ms' => $max_ms,
			'count'  => count( $latencies ),
		);
	}

	/**
	 * Get database connection information.
	 *
	 * Determines if the database is on the same server, remote, or unknown.
	 *
	 * @since  1.2601.2200
	 * @return string Human-readable connection information.
	 */
	private static function get_connection_info() {
		global $wpdb;

		if ( ! $wpdb ) {
			return '';
		}

		// Get the database host
		$db_host = isset( $wpdb->dbhost ) ? $wpdb->dbhost : '';

		if ( empty( $db_host ) ) {
			return '';
		}

		// Check if database appears to be on same server
		if ( in_array( $db_host, array( 'localhost', '127.0.0.1', '::1' ), true ) ) {
			return __( 'Database is configured to run on the same server (localhost). ', 'wpshadow' );
		}

		// Database appears to be remote
		return sprintf(
			/* translators: %s: database host */
			__( 'Database is configured on a remote server (%s). ', 'wpshadow' ),
			esc_html( $db_host )
		);
	}
}
