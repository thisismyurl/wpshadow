<?php
/**
 * Database Slow Query Log Analysis Diagnostic
 *
 * Identifies actual slow queries from MySQL slow query log.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26029.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Slow Query Log Analysis Class
 *
 * Tests slow queries.
 *
 * @since 1.26029.0000
 */
class Diagnostic_Database_Slow_Query_Log_Analysis extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-slow-query-log-analysis';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Slow Query Log Analysis';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies actual slow queries from MySQL slow query log';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26029.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$slow_query_check = self::check_slow_queries();
		
		if ( $slow_query_check['has_issues'] ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $slow_query_check['issues'] ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-slow-query-log-analysis',
				'meta'         => array(
					'slow_query_log_enabled' => $slow_query_check['slow_query_log_enabled'],
					'long_query_time'        => $slow_query_check['long_query_time'],
					'recommendations'        => $slow_query_check['recommendations'],
				),
			);
		}

		return null;
	}

	/**
	 * Check slow queries.
	 *
	 * @since  1.26029.0000
	 * @return array Check results.
	 */
	private static function check_slow_queries() {
		global $wpdb;

		$check = array(
			'has_issues'             => false,
			'issues'                 => array(),
			'slow_query_log_enabled' => false,
			'long_query_time'        => 10,
			'recommendations'        => array(),
		);

		// Check if slow query log is enabled.
		$slow_log_status = $wpdb->get_row( "SHOW VARIABLES LIKE 'slow_query_log'" );
		
		if ( $slow_log_status && isset( $slow_log_status->Value ) ) {
			$check['slow_query_log_enabled'] = ( 'ON' === $slow_log_status->Value );
		}

		// Get long_query_time threshold.
		$long_query_time = $wpdb->get_row( "SHOW VARIABLES LIKE 'long_query_time'" );
		
		if ( $long_query_time && isset( $long_query_time->Value ) ) {
			$check['long_query_time'] = (float) $long_query_time->Value;
		}

		// Analyze query performance indicators.
		$slow_queries = $wpdb->get_row( "SHOW GLOBAL STATUS LIKE 'Slow_queries'" );
		$total_queries = $wpdb->get_row( "SHOW GLOBAL STATUS LIKE 'Questions'" );

		if ( $slow_queries && $total_queries && isset( $slow_queries->Value ) && isset( $total_queries->Value ) ) {
			$slow_count = (int) $slow_queries->Value;
			$total_count = (int) $total_queries->Value;

			if ( $total_count > 0 ) {
				$slow_percentage = ( $slow_count / $total_count ) * 100;

				if ( $slow_percentage > 1 ) {
					$check['has_issues'] = true;
					$check['issues'][] = sprintf(
						/* translators: 1: percentage, 2: number of slow queries */
						__( '%1$s%% of queries are slow (%2$s slow queries detected)', 'wpshadow' ),
						number_format( $slow_percentage, 2 ),
						number_format( $slow_count )
					);
					$check['recommendations'][] = __( 'Identify and optimize slow queries using indexes', 'wpshadow' );
				}
			}
		}

		// Check if slow query log is disabled.
		if ( ! $check['slow_query_log_enabled'] ) {
			$check['has_issues'] = true;
			$check['issues'][] = __( 'Slow query log is disabled (cannot identify problematic queries)', 'wpshadow' );
			$check['recommendations'][] = __( 'Enable slow query log for performance monitoring', 'wpshadow' );
		}

		// Check if long_query_time is too high.
		if ( $check['long_query_time'] > 2 ) {
			$check['has_issues'] = true;
			$check['issues'][] = sprintf(
				/* translators: %s: long_query_time value */
				__( 'long_query_time is %s seconds (queries >1s should be logged)', 'wpshadow' ),
				number_format( $check['long_query_time'], 1 )
			);
			$check['recommendations'][] = __( 'Set long_query_time to 1 second for better monitoring', 'wpshadow' );
		}

		return $check;
	}
}
