<?php
/**
 * Directory Search Performance Diagnostic
 *
 * Directory search queries slow.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.564.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Directory Search Performance Diagnostic Class
 *
 * @since 1.564.0000
 */
class Diagnostic_DirectorySearchPerformance extends Diagnostic_Base {

	protected static $slug = 'directory-search-performance';
	protected static $title = 'Directory Search Performance';
	protected static $description = 'Directory search queries slow';
	protected static $family = 'performance';

	public static function check() {
		if ( ! function_exists( 'wpbdp' ) ) {
			return null;
		}
		
		$issues = array();

		// Check 1: Verify search indexing is enabled
		$search_indexing = get_option( 'wpbdp_search_indexing_enabled', false );
		if ( ! $search_indexing ) {
			$issues[] = __( 'Directory search indexing not enabled', 'wpshadow' );
		}

		// Check 2: Check search query caching
		$query_cache = get_option( 'wpbdp_search_query_cache', false );
		if ( ! $query_cache ) {
			$issues[] = __( 'Search query caching not enabled', 'wpshadow' );
		}

		// Check 3: Verify search result pagination
		$per_page = get_option( 'wpbdp_search_results_per_page', 0 );
		if ( $per_page > 50 || $per_page === 0 ) {
			$issues[] = __( 'Search results per page not optimally configured', 'wpshadow' );
		}

		// Check 4: Check relevance sorting configuration
		$relevance_sorting = get_option( 'wpbdp_search_relevance_sorting', false );
		if ( ! $relevance_sorting ) {
			$issues[] = __( 'Search relevance sorting not enabled', 'wpshadow' );
		}

		// Check 5: Verify search filter caching
		$filter_cache = get_option( 'wpbdp_search_filter_cache', false );
		if ( ! $filter_cache ) {
			$issues[] = __( 'Search filter caching not enabled', 'wpshadow' );
		}

		// Check 6: Check maximum search results limit
		$max_results = get_option( 'wpbdp_search_max_results', 0 );
		if ( $max_results > 1000 || $max_results === 0 ) {
			$issues[] = __( 'Maximum search results limit too high or unlimited', 'wpshadow' );
		}
		// Check transient support
		if ( ! function_exists( 'set_transient' ) ) {
			$issues[] = __( 'Transient functions unavailable', 'wpshadow' );
		}
		return null;
	}
}
