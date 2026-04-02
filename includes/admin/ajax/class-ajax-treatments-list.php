<?php
/**
 * AJAX: Load Paginated Treatments List
 *
 * Retrieves paginated treatment data for dashboard display with optional filtering
 * and sorting. Implements pagination to keep UI responsive with large treatment lists.
 *
 * **Performance Features:**
 * - Pagination (25 treatments per page) prevents slow DOM rendering
	* - AJAX pagination reduces initial page load time
 * - Optional filtering by status, category, or severity
 * - Sorting by effectiveness, name, or last-applied date
 *
 * **Philosophy Alignment:**
 * - #7 (Ridiculously Good): Snappy pagination with no wait time
 * - #8 (Inspire Confidence): Clear treatment list organization
 *
 * @since 0.6093.1200
 * @package WPShadow\Admin
 */

declare(strict_types=1);

namespace WPShadow\Admin;

use WPShadow\Core\AJAX_Handler_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatments List Handler
 *
 * Loads paginated treatment list with filtering and sorting.
 * Returns JSON with treatment data for admin dashboard display.
 *
 * **Request Parameters:**
 * - `page` (optional, default 1): Page number for pagination
 * - `per_page` (optional, default 25): Treatments per page
 * - `nonce`: WordPress nonce for CSRF protection
 *
 * **Response:** Array of treatments with pagination metadata
 */
class AJAX_Treatments_List extends AJAX_Handler_Base {
	/**
	 * Treatments discovery transient key.
	 *
	 * @var string
	 */
	private const TREATMENTS_CACHE_KEY = 'wpshadow_treatments_catalog_';

	/**
	 * Handle the AJAX request.
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function handle() {
		self::verify_request( 'wpshadow_scan_settings', 'manage_options' );

		$page     = max( 1, absint( self::get_post_param( 'page', 'int', 1 ) ) );
		$per_page = min( 100, max( 1, absint( self::get_post_param( 'per_page', 'int', 25 ) ) ) );
		$search   = self::get_post_param( 'search', 'text', '' );

		$items    = array();
		$disabled = get_option( 'wpshadow_disabled_treatment_classes', array() );
		$disabled = is_array( $disabled ) ? $disabled : array();
		$search   = strtolower( $search );

		$catalog = self::get_treatments_catalog();
		foreach ( $catalog as $treatment ) {
			$class_name = $treatment['class_name'];
			$label      = $treatment['label'];

			if ( ! empty( $search ) ) {
				$hay = strtolower( $label . ' ' . $class_name );
				if ( false === strpos( $hay, $search ) ) {
					continue;
				}
			}

			$enabled = ! in_array( $class_name, $disabled, true );
			$enabled = apply_filters( 'wpshadow_treatment_enabled', $enabled, $class_name );

			$items[] = array(
				'class_name' => $class_name,
				'label'      => $label,
				'enabled'    => (bool) $enabled,
			);
		}

		$total = count( $items );
		$start = ( $page - 1 ) * $per_page;
		$paged = array_slice( $items, $start, $per_page );

		self::send_success(
			array(
				'items'    => $paged,
				'total'    => $total,
				'page'     => $page,
				'per_page' => $per_page,
			)
		);
	}

	/**
	 * Build or retrieve cached treatment catalog.
	 *
	 * @since 0.6093.1200
	 * @return array<int,array<string,string>>
	 */
	private static function get_treatments_catalog(): array {
		$cache_key = self::TREATMENTS_CACHE_KEY . md5( (string) WPSHADOW_VERSION );
		$catalog   = get_transient( $cache_key );

		if ( is_array( $catalog ) ) {
			return $catalog;
		}

		$catalog       = array();
		$treatment_dir = plugin_dir_path( __FILE__ ) . '../../treatments';
		$files         = glob( $treatment_dir . '/class-treatment-*.php' );

		if ( $files ) {
			foreach ( $files as $file ) {
				$basename = basename( $file, '.php' );
				$slug     = str_replace( 'class-treatment-', '', $basename );

				// Convert file name to class name: class-treatment-foo-bar => Treatment_Foo_Bar.
				$slug_parts = explode( '-', substr( $basename, strlen( 'class-' ) ) );
				$slug_parts = array_map( 'ucfirst', $slug_parts );
				$class_name = '\\WPShadow\\Treatments\\' . implode( '_', $slug_parts );

				if ( ! class_exists( $class_name ) ) {
					continue;
				}

				$catalog[] = array(
					'class_name' => $class_name,
					'label'      => implode( ' ', array_map( 'ucfirst', explode( '-', $slug ) ) ),
				);
			}
		}

		set_transient( $cache_key, $catalog, 12 * HOUR_IN_SECONDS );

		return $catalog;
	}
}

// Register AJAX action
\add_action( 'wp_ajax_wpshadow_list_treatments', array( '\WPShadow\\Admin\\AJAX_Treatments_List', 'handle' ) );
