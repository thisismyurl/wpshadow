<?php
/**
 * AJAX: Load Paginated Diagnostics List
 *
 * Retrieves paginated diagnostic data with filtering and sorting for dashboard.
 * Similar to treatments list - implements pagination for performance.
 *
 * **Performance Features:**
 * - Pagination (50 diagnostics per page) prevents DOM bloat
 * - Lazy loading via AJAX keeps initial page responsive
 * - Filter by family, severity, status
 * - Sort by name, severity, last-run
 *
 * **Philosophy Alignment:**
 * - #7 (Ridiculously Good): Snappy pagination
 * - #8 (Inspire Confidence): Organized view of all checks
 *
 * @since 1.6093.1200
 * @package WPShadow\Admin
 */

declare(strict_types=1);

namespace WPShadow\Admin;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Diagnostics\Diagnostic_Registry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostics List Handler
 *
 * Loads paginated diagnostic list with optional filtering by family,
 * severity, or auto-fixability status.
 *
 * **Parameters:**
 * - `page`: Current page (1-based)
 * - `per_page`: Results per page (default 50)
 * - `family`: Filter by diagnostic family (security, performance, etc)
 * - `severity`: Filter by finding severity (critical, high, medium)
 * - `auto_fixable`: Filter by auto-fixability
 *
 * **Response:** Paginated array of diagnostics with metadata
 */
class AJAX_Diagnostics_List extends AJAX_Handler_Base {
	/**
	 * Handle the AJAX request.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function handle() {
		self::verify_request( 'wpshadow_scan_settings', 'manage_options' );

		$page       = max( 1, absint( self::get_post_param( 'page', 'int', 1 ) ) );
		$per_page   = min( 100, max( 1, absint( self::get_post_param( 'per_page', 'int', 25 ) ) ) );
		$family     = self::get_post_param( 'family', 'text', '' );
		$search     = self::get_post_param( 'search', 'text', '' );
		$get_family = rest_sanitize_boolean( self::get_post_param( 'get_families', 'bool', false ) );

		$all      = Diagnostic_Registry::get_all();
		$items    = array();
		$families = array();

		$disabled = get_option( 'wpshadow_disabled_diagnostic_classes', array() );
		$disabled = is_array( $disabled ) ? $disabled : array();

		foreach ( $all as $class ) {
			if ( ! class_exists( $class ) ) {
				continue;
			}

			$slug        = method_exists( $class, 'get_slug' ) ? $class::get_slug() : '';
			$title       = method_exists( $class, 'get_title' ) ? $class::get_title() : '';
			$description = method_exists( $class, 'get_description' ) ? $class::get_description() : '';
			$family_val  = method_exists( $class, 'get_family' ) ? $class::get_family() : '';

			if ( $get_family && ! empty( $family_val ) ) {
				$families[] = $family_val;
			}

			if ( ! empty( $family ) && $family !== $family_val ) {
				continue;
			}

			if ( ! empty( $search ) ) {
				$hay = strtolower( $slug . ' ' . $title . ' ' . $description . ' ' . $class );
				if ( false === strpos( $hay, strtolower( $search ) ) ) {
					continue;
				}
			}

			$enabled = ! in_array( $class, $disabled, true );
			$enabled = apply_filters( 'wpshadow_diagnostic_enabled', $enabled, $class );

			$items[] = array(
				'class_name'  => $class,
				'slug'        => $slug,
				'title'       => $title,
				'description' => $description,
				'family'      => $family_val,
				'enabled'     => (bool) $enabled,
			);
		}

		$families = array_values( array_unique( $families ) );

		$total = count( $items );
		$start = ( $page - 1 ) * $per_page;
		$paged = array_slice( $items, $start, $per_page );

		self::send_success(
			array(
				'items'    => $paged,
				'total'    => $total,
				'page'     => $page,
				'per_page' => $per_page,
				'families' => $get_family ? $families : array(),
			)
		);
	}
}

// Register AJAX action
\add_action( 'wp_ajax_wpshadow_list_diagnostics', array( '\WPShadow\\Admin\\AJAX_Diagnostics_List', 'handle' ) );
