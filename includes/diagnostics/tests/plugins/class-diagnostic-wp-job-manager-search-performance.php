<?php
/**
 * WP Job Manager Search Performance Diagnostic
 *
 * Job search queries not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.247.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP Job Manager Search Performance Diagnostic Class
 *
 * @since 1.247.0000
 */
class Diagnostic_WpJobManagerSearchPerformance extends Diagnostic_Base {

	protected static $slug = 'wp-job-manager-search-performance';
	protected static $title = 'WP Job Manager Search Performance';
	protected static $description = 'Job search queries not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'WP_Job_Manager' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Search optimization enabled
		$search = get_option( 'job_manager_search_optimization_enabled', 0 );
		if ( ! $search ) {
			$issues[] = 'Search optimization not enabled';
		}

		// Check 2: Query optimization
		$query = get_option( 'job_manager_query_optimization_enabled', 0 );
		if ( ! $query ) {
			$issues[] = 'Query optimization not enabled';
		}

		// Check 3: Indexing
		$indexing = get_option( 'job_manager_search_indexing_enabled', 0 );
		if ( ! $indexing ) {
			$issues[] = 'Search indexing not enabled';
		}

		// Check 4: Results caching
		$cache = get_option( 'job_manager_search_results_caching_enabled', 0 );
		if ( ! $cache ) {
			$issues[] = 'Search results caching not enabled';
		}

		// Check 5: Autocomplete
		$autocomplete = get_option( 'job_manager_search_autocomplete_enabled', 0 );
		if ( ! $autocomplete ) {
			$issues[] = 'Search autocomplete not enabled';
		}

		// Check 6: Filters optimization
		$filters = get_option( 'job_manager_search_filters_optimized', 0 );
		if ( ! $filters ) {
			$issues[] = 'Search filters not optimized';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 30;
			$threat_multiplier = 6;
			$max_threat = 60;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d job search performance issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wp-job-manager-search-performance',
			);
		}

		return null;
	}
}
