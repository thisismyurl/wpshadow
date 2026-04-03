<?php
/**
 * AJAX: Load Paginated Diagnostics List
 *
 * Retrieves paginated diagnostic data with filtering and sorting for dashboard.
 * Similar to treatments list - implements pagination for performance.
 *
 * **Performance Features:**
 * - Pagination (50 diagnostics per page) prevents DOM bloat
	* - AJAX pagination keeps initial page responsive
 * - Filter by family, severity, status
 * - Sort by name, severity, last-run
 *
 * **Philosophy Alignment:**
 * - #7 (Ridiculously Good): Snappy pagination
 * - #8 (Inspire Confidence): Organized view of all checks
 *
 * @since 0.6093.1200
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
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function handle() {
		self::verify_manage_options_request( 'wpshadow_scan_settings' );

		$pagination = self::get_pagination_params( 25, 100 );
		$page       = $pagination['page'];
		$per_page   = $pagination['per_page'];
		$family     = self::get_post_param( 'family', 'text', '' );
		$search     = self::get_post_param( 'search', 'text', '' );
		$get_family = rest_sanitize_boolean( self::get_post_param( 'get_families', 'bool', false ) );

		$definitions = Diagnostic_Registry::get_diagnostic_definitions();
		$items       = array();
		$families    = array();

		foreach ( $definitions as $definition ) {
			if ( ! is_array( $definition ) ) {
				continue;
			}

			$class       = isset( $definition['class'] ) ? (string) $definition['class'] : '';
			$slug        = isset( $definition['run_key'] ) ? (string) $definition['run_key'] : '';
			$title       = isset( $definition['title'] ) ? (string) $definition['title'] : '';
			$description = isset( $definition['description'] ) ? (string) $definition['description'] : '';
			$family_val  = isset( $definition['family'] ) ? (string) $definition['family'] : '';
			$enabled     = ! empty( $definition['enabled'] );

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
		$paged = self::paginate_items( $items, $pagination['start'], $per_page );

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
