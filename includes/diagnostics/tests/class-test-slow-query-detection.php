<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Slow Query Detection
 *
 * Detects database queries that are performing slowly.
 * Slow queries indicate missing indexes or inefficient code.
 *
 * @since 1.2.0
 */
class Test_Slow_Query_Detection extends Diagnostic_Base {


	private const SLOW_QUERY_THRESHOLD_MS = 1000; // 1 second

	/**
	 * Check for slow queries
	 *
	 * @return array|null Diagnostic array if issues found, null if all good
	 */
	public static function check(): ?array {
		if ( ! defined( 'SAVEQUERIES' ) || ! SAVEQUERIES ) {
			return null; // Query logging not enabled
		}

		$slow_queries = self::detect_slow_queries();

		if ( empty( $slow_queries ) ) {
			return null;
		}

		$threat = min( 50, count( $slow_queries ) * 5 );

		return array(
			'threat_level'  => $threat,
			'threat_color'  => 'yellow',
			'passed'        => false,
			'issue'         => sprintf(
				'Found %d slow database queries (>%dms)',
				count( $slow_queries ),
				self::SLOW_QUERY_THRESHOLD_MS
			),
			'metadata'      => array(
				'slow_query_count' => count( $slow_queries ),
				'sample_queries'   => array_slice( $slow_queries, 0, 5 ),
			),
			'kb_link'       => 'https://wpshadow.com/kb/database-query-optimization/',
			'training_link' => 'https://wpshadow.com/training/wordpress-database-performance/',
		);
	}

	/**
	 * Guardian Sub-Test: Query logging status
	 *
	 * @return array Test result
	 */
	public static function test_query_logging(): array {
		$logging_enabled = defined( 'SAVEQUERIES' ) && SAVEQUERIES;

		return array(
			'test_name'       => 'Query Logging Status',
			'logging_enabled' => $logging_enabled,
			'passed'          => $logging_enabled,
			'description'     => $logging_enabled ? 'Query logging is enabled' : 'Query logging disabled (production recommended)',
		);
	}

	/**
	 * Guardian Sub-Test: Slow query count
	 *
	 * @return array Test result
	 */
	public static function test_slow_query_count(): array {
		if ( ! defined( 'SAVEQUERIES' ) || ! SAVEQUERIES ) {
			return array(
				'test_name'       => 'Slow Query Count',
				'logging_enabled' => false,
				'description'     => 'Query logging not enabled',
			);
		}

		$slow_queries = self::detect_slow_queries();

		return array(
			'test_name'    => 'Slow Query Count',
			'slow_queries' => count( $slow_queries ),
			'passed'       => count( $slow_queries ) < 5,
			'description'  => sprintf( 'Found %d slow queries', count( $slow_queries ) ),
		);
	}

	/**
	 * Guardian Sub-Test: Slowest queries
	 *
	 * @return array Test result
	 */
	public static function test_slowest_queries(): array {
		if ( ! defined( 'SAVEQUERIES' ) || ! SAVEQUERIES ) {
			return array(
				'test_name'   => 'Slowest Queries',
				'description' => 'Query logging not enabled',
			);
		}

		$slow_queries = self::detect_slow_queries();
		usort( $slow_queries, fn( $a, $b ) => $b['duration'] <=> $a['duration'] );

		return array(
			'test_name'     => 'Slowest Queries',
			'slowest_count' => count( $slow_queries ),
			'slowest'       => array_slice( $slow_queries, 0, 3 ),
			'description'   => sprintf( 'Slowest query: %sms', round( ( $slow_queries[0]['duration'] ?? 0 ) * 1000, 2 ) ),
		);
	}

	/**
	 * Guardian Sub-Test: Missing indexes detection
	 *
	 * @return array Test result
	 */
	public static function test_missing_indexes(): array {
		$missing_indexes = self::detect_missing_indexes();

		return array(
			'test_name'           => 'Missing Indexes',
			'potentially_missing' => $missing_indexes,
			'count'               => count( $missing_indexes ),
			'description'         => count( $missing_indexes ) > 0 ? sprintf( '%d queries may need indexes', count( $missing_indexes ) ) : 'No obvious missing indexes',
		);
	}

	/**
	 * Detect slow queries
	 *
	 * @return array List of slow queries
	 */
	private static function detect_slow_queries(): array {
		global $wpdb;

		if ( ! isset( $wpdb->queries ) ) {
			return array();
		}

		$slow = array();

		foreach ( $wpdb->queries as $query ) {
			$duration = $query[1] * 1000; // Convert to milliseconds

			if ( $duration > self::SLOW_QUERY_THRESHOLD_MS ) {
				$slow[] = array(
					'query'       => substr( $query[0], 0, 200 ) . '...',
					'duration'    => $duration / 1000, // Back to seconds
					'duration_ms' => $duration,
				);
			}
		}

		return $slow;
	}

	/**
	 * Detect potentially missing indexes
	 *
	 * @return array Queries that may need indexes
	 */
	private static function detect_missing_indexes(): array {
		global $wpdb;

		if ( ! isset( $wpdb->queries ) ) {
			return array();
		}

		$missing = array();

		foreach ( $wpdb->queries as $query ) {
			$query_text = strtolower( $query[0] );

			// Look for full table scans or inefficient patterns
			if ( preg_match( '/SELECT.*FROM.*WHERE.*LIKE|SELECT.*JOIN.*WHERE.*LIKE/i', $query[0] ) ) {
				$missing[] = array(
					'type'  => 'LIKE clause without index',
					'query' => substr( $query[0], 0, 150 ) . '...',
				);
			}

			// Check for missing JOIN conditions
			if ( substr_count( $query_text, 'join' ) > 3 && ( $query[1] * 1000 ) > 500 ) {
				$missing[] = array(
					'type'  => 'Complex JOIN (>500ms)',
					'query' => substr( $query[0], 0, 150 ) . '...',
				);
			}
		}

		return $missing;
	}

	/**
	 * Get diagnostic name
	 *
	 * @return string
	 */
	public static function get_name(): string {
		return 'Slow Query Detection';
	}

	/**
	 * Get diagnostic description
	 *
	 * @return string
	 */
	public static function get_description(): string {
		return 'Identifies slow database queries that impact performance';
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
