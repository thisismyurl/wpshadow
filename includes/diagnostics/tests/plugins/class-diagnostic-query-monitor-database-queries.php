<?php
/**
 * Query Monitor Database Queries Diagnostic
 *
 * Query Monitor Database Queries not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.930.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Query Monitor Database Queries Diagnostic Class
 *
 * @since 1.930.0000
 */
class Diagnostic_QueryMonitorDatabaseQueries extends Diagnostic_Base {

	protected static $slug = 'query-monitor-database-queries';
	protected static $title = 'Query Monitor Database Queries';
	protected static $description = 'Query Monitor Database Queries not optimized';
	protected static $family = 'performance';

	public static function check() {
		// Check for Query Monitor
		if ( ! class_exists( 'QueryMonitor' ) && ! defined( 'QM_VERSION' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Query Monitor activated
		if ( ! defined( 'SAVEQUERIES' ) || ! SAVEQUERIES ) {
			$issues[] = __( 'SAVEQUERIES not enabled (limited query monitoring)', 'wpshadow' );
		}
		
		// Check 2: Current page query count
		if ( ! empty( $wpdb->queries ) ) {
			$query_count = count( $wpdb->queries );
			
			if ( $query_count > 100 ) {
				$issues[] = sprintf( __( 'Current page: %d database queries (optimization needed)', 'wpshadow' ), $query_count );
			}
			
			// Check 3: Slow queries
			$slow_queries = 0;
			foreach ( $wpdb->queries as $query ) {
				if ( isset( $query[1] ) && $query[1] > 0.05 ) { // 50ms
					$slow_queries++;
				}
			}
			
			if ( $slow_queries > 5 ) {
				$issues[] = sprintf( __( '%d slow queries (>50ms each)', 'wpshadow' ), $slow_queries );
			}
			
			// Check 4: Duplicate queries
			$query_texts = array_column( $wpdb->queries, 0 );
			$unique_queries = array_unique( $query_texts );
			$duplicate_count = count( $query_texts ) - count( $unique_queries );
			
			if ( $duplicate_count > 10 ) {
				$issues[] = sprintf( __( '%d duplicate queries (caching opportunity)', 'wpshadow' ), $duplicate_count );
			}
		}
		
		// Check 5: Query Monitor output enabled on frontend
		$show_frontend = get_option( 'qm_enable_for_non_admins', false );
		if ( $show_frontend && ! is_admin() ) {
			$issues[] = __( 'Query Monitor visible on frontend (information exposure)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 45;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 58;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 52;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of query monitoring issues */
				__( 'Query Monitor database analysis shows %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/query-monitor-database-queries',
		);
	}
}
