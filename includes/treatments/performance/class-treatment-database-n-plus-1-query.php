<?php
/**
 * Database N+1 Query Problem Treatment
 *
 * Detects N+1 query patterns causing performance issues.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6033.2056
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database N+1 Query Problem Treatment Class
 *
 * Detects N+1 query patterns where loops trigger repeated
 * similar queries instead of batch loading.
 *
 * @since 1.6033.2056
 */
class Treatment_Database_N_Plus_1_Query extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-n-plus-1-query';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Database N+1 Query Problem';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects N+1 query patterns causing excessive database calls';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * Analyzes query patterns to detect N+1 problems.
	 * Common in post loops fetching meta/terms repeatedly.
	 *
	 * @since  1.6033.2056
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;
		
		// Requires SAVEQUERIES
		if ( ! defined( 'SAVEQUERIES' ) || ! SAVEQUERIES ) {
			return null; // Can't detect without query logging
		}
		
		if ( empty( $wpdb->queries ) ) {
			return null;
		}
		
		// Track query patterns
		$query_patterns = array();
		
		foreach ( $wpdb->queries as $query ) {
			$sql = $query[0] ?? '';
			
			// Normalize query (remove specific IDs/values)
			$normalized = preg_replace( '/\d+/', 'N', $sql );
			$normalized = preg_replace( '/\'[^\']*\'/', "'X'", $normalized );
			
			if ( ! isset( $query_patterns[ $normalized ] ) ) {
				$query_patterns[ $normalized ] = 0;
			}
			$query_patterns[ $normalized ]++;
		}
		
		// Find patterns repeated many times
		$n_plus_1_patterns = array();
		foreach ( $query_patterns as $pattern => $count ) {
			if ( $count > 10 ) { // Repeated >10 times
				// Check if it's a meta or term query (common N+1 culprits)
				if ( strpos( $pattern, 'postmeta' ) !== false ||
				     strpos( $pattern, 'term_relationships' ) !== false ||
				     strpos( $pattern, 'usermeta' ) !== false ) {
					$n_plus_1_patterns[] = array(
						'pattern' => substr( $pattern, 0, 150 ),
						'count'   => $count,
					);
				}
			}
		}
		
		// If N+1 patterns detected
		if ( ! empty( $n_plus_1_patterns ) ) {
			$total_repeated = array_sum( array_column( $n_plus_1_patterns, 'count' ) );
			
			$severity     = 'medium';
			$threat_level = 50;
			
			if ( $total_repeated > 50 ) {
				$severity     = 'high';
				$threat_level = 75;
			}
			if ( $total_repeated > 100 ) {
				$severity     = 'critical';
				$threat_level = 90;
			}
			
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: number of repeated queries, 2: number of patterns */
					__( 'Detected N+1 query pattern: %1$d repeated queries across %2$d patterns. N+1 queries occur when loops fetch data repeatedly instead of batch loading, severely impacting performance. Use get_posts() with meta queries or WP_Query with tax_query instead of loops with get_post_meta() or has_term().', 'wpshadow' ),
					$total_repeated,
					count( $n_plus_1_patterns )
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/fix-n-plus-1-queries',
				'meta'         => array(
					'total_repeated_queries' => $total_repeated,
					'pattern_count'          => count( $n_plus_1_patterns ),
					'patterns'               => array_slice( $n_plus_1_patterns, 0, 3 ), // Top 3
					'total_queries'          => count( $wpdb->queries ),
					'recommendation'         => 'Use update_post_meta_cache() or update_term_meta_cache()',
				),
			);
		}
		
		// Check for excessive query count even without N+1
		$total_queries = count( $wpdb->queries );
		if ( $total_queries > 100 ) {
			return array(
				'id'           => 'excessive-query-count',
				'title'        => __( 'Excessive Database Query Count', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %d: number of queries */
					__( 'Page generated %d database queries (should be <50). Excessive queries indicate need for caching or query optimization.', 'wpshadow' ),
					$total_queries
				),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/reduce-query-count',
				'meta'         => array(
					'total_queries'    => $total_queries,
					'threshold'        => 50,
					'recommendation'   => 'Enable object caching or page caching',
				),
			);
		}
		
		return null;
	}
}
