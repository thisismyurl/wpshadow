<?php
/**
 * Database Query Cache Effectiveness Diagnostic
 *
 * Measures MySQL query cache hit rate and effectiveness.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26028.1905
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Query Cache Effectiveness Class
 *
 * Tests query cache.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Database_Query_Cache_Effectiveness extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-query-cache-effectiveness';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Query Cache Effectiveness';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Measures MySQL query cache hit rate and effectiveness';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$cache_check = self::check_query_cache();
		
		if ( $cache_check['has_issues'] ) {
			$issues = array();
			
			if ( ! $cache_check['is_enabled'] ) {
				$issues[] = __( 'Query cache disabled (missing 80-90% performance gain)', 'wpshadow' );
			}

			if ( $cache_check['is_enabled'] && $cache_check['hit_rate'] < 0.40 ) {
				$issues[] = sprintf(
					/* translators: %d: hit rate percentage */
					__( 'Query cache hit rate at %d%% (should be >80%%)', 'wpshadow' ),
					(int) ( $cache_check['hit_rate'] * 100 )
				);
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-query-cache-effectiveness',
				'meta'         => array(
					'is_enabled' => $cache_check['is_enabled'],
					'hit_rate'   => $cache_check['hit_rate'],
					'cache_size' => $cache_check['cache_size'],
				),
			);
		}

		return null;
	}

	/**
	 * Check query cache.
	 *
	 * @since  1.26028.1905
	 * @return array Check results.
	 */
	private static function check_query_cache() {
		global $wpdb;

		$check = array(
			'has_issues' => false,
			'is_enabled' => false,
			'hit_rate'   => 0,
			'cache_size' => 0,
		);

		// Check query cache type.
		$cache_type = $wpdb->get_var( "SHOW VARIABLES WHERE Variable_name = 'query_cache_type'" );
		
		if ( $cache_type && '1' === $cache_type ) {
			$check['is_enabled'] = true;

			// Get cache statistics.
			$stats = $wpdb->get_results( "SHOW STATUS LIKE 'Qcache%'", OBJECT_K );
			
			if ( isset( $stats['Qcache_hits'] ) && isset( $stats['Qcache_inserts'] ) ) {
				$hits = (int) $stats['Qcache_hits']->Value;
				$inserts = (int) $stats['Qcache_inserts']->Value;

				if ( ( $hits + $inserts ) > 0 ) {
					$check['hit_rate'] = $hits / ( $hits + $inserts );
				}

				if ( $check['hit_rate'] < 0.40 ) {
					$check['has_issues'] = true;
				}
			}

			// Get cache size.
			if ( isset( $stats['Qcache_free_memory'] ) ) {
				$check['cache_size'] = (int) $stats['Qcache_free_memory']->Value;
			}
		} else {
			$check['has_issues'] = true;
		}

		return $check;
	}
}
