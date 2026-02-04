<?php
/**
 * Slow Query Log Diagnostic
 *
 * Monitors and logs slow database queries affecting performance.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.2055
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Slow Query Log Diagnostic Class
 *
 * Hooks into $wpdb to detect queries taking >1 second.
 * Slow queries can dramatically impact page load times.
 *
 * @since 1.6033.2055
 */
class Diagnostic_Slow_Query_Log extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'slow-query-log';

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
	protected static $description = 'Monitors for slow database queries (>1 second)';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks for slow queries logged during request.
	 * Queries >1 second indicate database optimization needed.
	 *
	 * @since  1.6033.2055
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;
		
		// Check if SAVEQUERIES is enabled
		if ( ! defined( 'SAVEQUERIES' ) || ! SAVEQUERIES ) {
			// Can't check queries without SAVEQUERIES
			// Suggest enabling it for monitoring
			return array(
				'id'           => 'savequeries-disabled',
				'title'        => __( 'Query Monitoring Disabled', 'wpshadow' ),
				'description'  => __( 'SAVEQUERIES constant is not enabled. Enable it in wp-config.php to monitor database query performance and identify slow queries.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/enable-query-monitoring',
				'meta'         => array(
					'savequeries_enabled' => false,
					'recommendation'      => "Add 'define( \"SAVEQUERIES\", true );' to wp-config.php",
					'note'                => 'Only enable on development/staging, not production',
				),
			);
		}
		
		// Check saved queries
		if ( empty( $wpdb->queries ) ) {
			return null; // No queries recorded yet
		}
		
		$slow_queries = array();
		$total_time   = 0;
		
		foreach ( $wpdb->queries as $query ) {
			$time = $query[1] ?? 0;
			$sql  = $query[0] ?? '';
			
			$total_time += $time;
			
			// Log queries taking >1 second
			if ( $time > 1.0 ) {
				$slow_queries[] = array(
					'time' => $time,
					'sql'  => substr( $sql, 0, 200 ),
				);
			}
		}
		
		// If slow queries found
		if ( ! empty( $slow_queries ) ) {
			$severity     = 'high';
			$threat_level = 70;
			
			if ( count( $slow_queries ) > 3 ) {
				$severity     = 'critical';
				$threat_level = 90;
			}
			
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: number of slow queries, 2: total time */
					__( 'Found %1$d slow database queries (>1 second each). Total query time: %2$ss. Slow queries indicate need for database optimization, indexing, or caching.', 'wpshadow' ),
					count( $slow_queries ),
					number_format( $total_time, 3 )
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/optimize-slow-queries',
				'meta'         => array(
					'slow_query_count' => count( $slow_queries ),
					'total_queries'    => count( $wpdb->queries ),
					'total_time'       => round( $total_time, 3 ),
					'slow_queries'     => array_slice( $slow_queries, 0, 5 ), // First 5
					'threshold'        => '1.0s',
					'savequeries'      => true,
				),
			);
		}
		
		// Check for excessive total query time (>2s)
		if ( $total_time > 2.0 ) {
			return array(
				'id'           => 'excessive-query-time',
				'title'        => __( 'Excessive Database Query Time', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %s: total query time */
					__( 'Total database query time is %ss (should be <1s). While no individual slow queries, the cumulative time indicates need for query optimization or caching.', 'wpshadow' ),
					number_format( $total_time, 3 )
				),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/reduce-query-time',
				'meta'         => array(
					'total_queries' => count( $wpdb->queries ),
					'total_time'    => round( $total_time, 3 ),
					'avg_time'      => round( $total_time / count( $wpdb->queries ), 4 ),
					'threshold'     => '2.0s',
				),
			);
		}
		
		return null;
	}
}
