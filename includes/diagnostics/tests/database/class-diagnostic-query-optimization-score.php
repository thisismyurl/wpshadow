<?php
/**
 * Query Optimization Score
 *
 * Provides an overall rating (0-100) of database query efficiency
 * based on various performance metrics and optimization opportunities.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Database
 * @since      1.6029.1105
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Query Optimization Score Diagnostic Class
 *
 * Calculates a comprehensive query optimization score.
 *
 * @since 1.6029.1105
 */
class Diagnostic_Query_Optimization_Score extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'query-optimization-score';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Query Optimization Score';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Overall rating of database query efficiency (0-100)';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'database';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6029.1105
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_query_optimization_score';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$score_data = self::calculate_optimization_score();

		if ( $score_data['score'] >= 70 ) {
			set_transient( $cache_key, null, 12 * HOUR_IN_SECONDS );
			return null;
		}

		$severity = self::calculate_severity( $score_data['score'] );

		$finding = array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: optimization score */
				__( 'Query optimization score is %d/100, indicating performance opportunities.', 'wpshadow' ),
				$score_data['score']
			),
			'severity'     => $severity,
			'threat_level' => 100 - $score_data['score'],
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/query-optimization',
			'meta'         => array(
				'score'       => $score_data['score'],
				'factors'     => $score_data['factors'],
				'suggestions' => $score_data['suggestions'],
			),
			'details'      => $score_data['details'],
			'recommendation' => __( 'Review and optimize database queries, add indexes, and enable object caching.', 'wpshadow' ),
		);

		set_transient( $cache_key, $finding, 12 * HOUR_IN_SECONDS );
		return $finding;
	}

	/**
	 * Calculate optimization score.
	 *
	 * @since  1.6029.1105
	 * @return array Score data with factors and suggestions.
	 */
	private static function calculate_optimization_score() {
		global $wpdb;

		$factors      = array();
		$details      = array();
		$suggestions  = array();
		$total_score  = 0;
		$factor_count = 0;

		// Factor 1: Table indexing (25 points).
		$index_score = self::check_table_indexes();
		$factors['indexing'] = $index_score;
		$total_score += $index_score;
		$factor_count++;

		if ( $index_score < 20 ) {
			$details[] = __( 'Database tables lack proper indexes', 'wpshadow' );
			$suggestions[] = __( 'Add indexes to frequently queried columns', 'wpshadow' );
		}

		// Factor 2: Object caching (25 points).
		$cache_score = self::check_object_caching();
		$factors['object_cache'] = $cache_score;
		$total_score += $cache_score;
		$factor_count++;

		if ( $cache_score < 20 ) {
			$details[] = __( 'Object caching not enabled', 'wpshadow' );
			$suggestions[] = __( 'Enable persistent object caching (Redis/Memcached)', 'wpshadow' );
		}

		// Factor 3: Query count (25 points).
		$query_count_score = self::check_query_count();
		$factors['query_count'] = $query_count_score;
		$total_score += $query_count_score;
		$factor_count++;

		if ( $query_count_score < 20 ) {
			$details[] = __( 'High number of queries per page', 'wpshadow' );
			$suggestions[] = __( 'Reduce unnecessary queries and combine where possible', 'wpshadow' );
		}

		// Factor 4: Slow query detection (25 points).
		$slow_query_score = self::check_slow_queries();
		$factors['slow_queries'] = $slow_query_score;
		$total_score += $slow_query_score;
		$factor_count++;

		if ( $slow_query_score < 20 ) {
			$details[] = __( 'Slow queries detected', 'wpshadow' );
			$suggestions[] = __( 'Optimize slow queries with better indexes', 'wpshadow' );
		}

		$average_score = $factor_count > 0 ? (int) round( $total_score / $factor_count ) : 0;

		return array(
			'score'       => $average_score,
			'factors'     => $factors,
			'details'     => $details,
			'suggestions' => $suggestions,
		);
	}

	/**
	 * Check table indexes.
	 *
	 * @since  1.6029.1105
	 * @return int Score (0-25).
	 */
	private static function check_table_indexes() {
		global $wpdb;

		$tables = $wpdb->get_col( 'SHOW TABLES' );
		$indexed_count = 0;

		foreach ( $tables as $table ) {
			$indexes = $wpdb->get_results( "SHOW INDEX FROM `{$table}`" );
			if ( count( $indexes ) > 1 ) { // More than just PRIMARY.
				$indexed_count++;
			}
		}

		$percentage = count( $tables ) > 0 ? ( $indexed_count / count( $tables ) ) * 100 : 0;
		return (int) min( 25, round( $percentage / 4 ) );
	}

	/**
	 * Check object caching.
	 *
	 * @since  1.6029.1105
	 * @return int Score (0-25).
	 */
	private static function check_object_caching() {
		global $wp_object_cache;

		if ( wp_using_ext_object_cache() ) {
			return 25; // Full points for persistent cache.
		}

		return 10; // Partial points for default cache.
	}

	/**
	 * Check query count.
	 *
	 * @since  1.6029.1105
	 * @return int Score (0-25).
	 */
	private static function check_query_count() {
		global $wpdb;

		if ( ! defined( 'SAVEQUERIES' ) || ! SAVEQUERIES ) {
			return 15; // Neutral score if can't measure.
		}

		$query_count = count( $wpdb->queries );

		if ( $query_count < 20 ) {
			return 25;
		} elseif ( $query_count < 50 ) {
			return 20;
		} elseif ( $query_count < 100 ) {
			return 15;
		}

		return 5;
	}

	/**
	 * Check for slow queries.
	 *
	 * @since  1.6029.1105
	 * @return int Score (0-25).
	 */
	private static function check_slow_queries() {
		global $wpdb;

		if ( ! defined( 'SAVEQUERIES' ) || ! SAVEQUERIES || empty( $wpdb->queries ) ) {
			return 15; // Neutral score if can't measure.
		}

		$slow_count = 0;
		foreach ( $wpdb->queries as $query ) {
			if ( (float) $query[1] > 0.05 ) { // 50ms threshold.
				$slow_count++;
			}
		}

		if ( $slow_count === 0 ) {
			return 25;
		} elseif ( $slow_count < 5 ) {
			return 20;
		} elseif ( $slow_count < 10 ) {
			return 15;
		}

		return 5;
	}

	/**
	 * Calculate severity based on score.
	 *
	 * @since  1.6029.1105
	 * @param  int $score Optimization score.
	 * @return string Severity level.
	 */
	private static function calculate_severity( $score ) {
		if ( $score < 40 ) {
			return 'high';
		} elseif ( $score < 70 ) {
			return 'medium';
		}
		return 'low';
	}
}
