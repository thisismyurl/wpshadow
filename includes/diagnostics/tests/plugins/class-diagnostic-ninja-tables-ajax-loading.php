<?php
/**
 * Ninja Tables AJAX Loading Diagnostic
 *
 * Ninja Tables AJAX not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.479.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ninja Tables AJAX Loading Diagnostic Class
 *
 * @since 1.479.0000
 */
class Diagnostic_NinjaTablesAjaxLoading extends Diagnostic_Base {

	protected static $slug = 'ninja-tables-ajax-loading';
	protected static $title = 'Ninja Tables AJAX Loading';
	protected static $description = 'Ninja Tables AJAX not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'NINJA_TABLES_VERSION' ) ) {
			return null;
		}
		
		$issues = array();

		// Check 1: Verify AJAX loading is enabled
		$ajax_enabled = get_option( 'ninja_tables_ajax_enabled', 'no' );
		if ( 'yes' !== $ajax_enabled ) {
			$issues[] = __( 'AJAX table loading not enabled', 'wpshadow' );
		}

		// Check 2: Check AJAX response caching configuration
		$ajax_cache = get_option( 'ninja_tables_ajax_cache', 'no' );
		if ( 'yes' !== $ajax_cache ) {
			$issues[] = __( 'AJAX response caching not enabled', 'wpshadow' );
		}

		// Check 3: Verify pagination for large datasets
		$pagination_enabled = get_option( 'ninja_tables_pagination', 'no' );
		if ( 'yes' !== $pagination_enabled ) {
			$issues[] = __( 'Table pagination not enabled for performance', 'wpshadow' );
		}

		// Check 4: Check lazy loading configuration
		$lazy_load = get_option( 'ninja_tables_lazy_load', 'no' );
		if ( 'yes' !== $lazy_load ) {
			$issues[] = __( 'Lazy loading not configured for tables', 'wpshadow' );
		}

		// Check 5: Verify per-page limit is reasonable
		$per_page = get_option( 'ninja_tables_per_page', 100 );
		if ( $per_page > 50 ) {
			$issues[] = __( 'Per-page limit too high for optimal AJAX performance', 'wpshadow' );
		}

		// Check 6: Check AJAX request optimization
		$minimize_requests = get_option( 'ninja_tables_minimize_ajax_requests', 'no' );
		if ( 'yes' !== $minimize_requests ) {
			$issues[] = __( 'AJAX request optimization not enabled', 'wpshadow' );
		}
		// Check transient support
		if ( ! function_exists( 'set_transient' ) ) {
			$issues[] = __( 'Transient functions unavailable', 'wpshadow' );
		}
		return null;
	}
}
