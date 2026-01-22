<?php
/**
 * Diagnostic: Database Query Overhead
 *
 * Detects excessive database queries on admin pages.
 *
 * Philosophy: Show Value (#9) - Measure what matters
 * KB Link: https://wpshadow.com/kb/database-query-overhead
 * Training: https://wpshadow.com/training/database-query-overhead
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Query Overhead diagnostic
 */
class Diagnostic_Database_Query_Overhead extends Diagnostic_Base {

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Diagnostic result or null if no issue
	 */
	public static function check(): ?array {
		// Only check if SAVEQUERIES is enabled
		if ( ! defined( 'SAVEQUERIES' ) || ! SAVEQUERIES ) {
			return [
				'id'                => 'database-query-overhead',
				'title'             => __( 'Database Query Monitoring Not Enabled', 'wpshadow' ),
				'description'       => __( 'SAVEQUERIES is not enabled in wp-config.php. Enable it temporarily to monitor database performance: define(\'SAVEQUERIES\', true);', 'wpshadow' ),
				'severity'          => 'low',
				'category'          => 'performance',
				'impact'            => 'informational',
				'effort'            => 'low',
				'kb_link'           => 'https://wpshadow.com/kb/database-query-overhead',
				'training_link'     => 'https://wpshadow.com/training/database-query-overhead',
				'affected_resource' => 'wp-config.php',
			];
		}

		global $wpdb;

		if ( empty( $wpdb->queries ) ) {
			return null; // No queries logged yet
		}

		$query_count = count( $wpdb->queries );
		$total_time = 0;
		$slow_queries = [];

		foreach ( $wpdb->queries as $query ) {
			$time = $query[1];
			$total_time += $time;

			// Flag slow queries (> 0.05 seconds)
			if ( $time > 0.05 ) {
				$slow_queries[] = [
					'query' => substr( $query[0], 0, 100 ) . '...',
					'time'  => round( $time, 4 ),
				];
			}
		}

		$total_time_ms = round( $total_time * 1000, 2 );

		// Thresholds: < 50 queries good, 50-100 warning, > 100 critical
		if ( $query_count < 50 ) {
			return null;
		}

		$severity = $query_count > 100 ? 'high' : 'medium';

		$description = sprintf(
			__( 'This page executed %d database queries in %s ms. High query counts slow down page loads. WPShadow\'s Option Optimizer reduces queries by batch-loading options.', 'wpshadow' ),
			$query_count,
			$total_time_ms
		);

		if ( ! empty( $slow_queries ) ) {
			$description .= sprintf(
				' ' . __( 'Found %d slow queries (> 50ms).', 'wpshadow' ),
				count( $slow_queries )
			);
		}

		return [
			'id'                => 'database-query-overhead',
			'title'             => __( 'Excessive Database Queries', 'wpshadow' ),
			'description'       => $description,
			'severity'          => $severity,
			'category'          => 'performance',
			'impact'            => 'high',
			'effort'            => 'medium',
			'kb_link'           => 'https://wpshadow.com/kb/database-query-overhead',
			'training_link'     => 'https://wpshadow.com/training/database-query-overhead',
			'affected_resource' => sprintf( '%d queries, %s ms', $query_count, $total_time_ms ),
			'metadata'          => [
				'query_count'      => $query_count,
				'total_time_ms'    => $total_time_ms,
				'slow_query_count' => count( $slow_queries ),
				'slow_queries'     => array_slice( $slow_queries, 0, 5 ),
				'avg_time_ms'      => round( $total_time_ms / $query_count, 2 ),
			],
		];
	}
}
