<?php
/**
 * Provide AJAX endpoints for dashboard cache maintenance actions.
 *
 * These handlers let the admin UI clear cached dashboard fragments without a
 * full page reload. The class exists so the JavaScript layer can ask for a
 * targeted cache reset while the server side keeps security, messaging, and
 * cache implementation details in one place.
 *
 * @package ThisIsMyURL\Shadow\Admin
 * @since   0.6095
 */

declare(strict_types=1);

namespace ThisIsMyURL\Shadow\Admin;

use ThisIsMyURL\Shadow\Core\AJAX_Handler_Base;
use ThisIsMyURL\Shadow\Core\Dashboard_Cache;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AJAX controller for dashboard cache management.
 *
 * Each public method in this class is wired to a wp_ajax_* action. The
 * methods remain intentionally small so the user-facing behavior is easy to
 * audit: verify the request, perform a cache operation, and return a JSON
 * response that the admin interface can display immediately.
 *
 * @since 0.6095
 */
class AJAX_Dashboard_Cache extends AJAX_Handler_Base {

	/**
	 * Clear the full cached dashboard payload.
	 *
	 * This endpoint is used when the admin needs to force the next dashboard page
	 * load to rebuild its cached data from the current site state instead of
	 * serving a stale snapshot.
	 *
	 * @since  0.6095
	 * @return void Sends a JSON response and terminates execution.
	 */
	public static function invalidate_dashboard_cache() {
		// Verify nonce and capability
		self::verify_request( 'thisismyurl_shadow_cache_action', 'manage_options' );

		// Invalidate cache
		$result = Dashboard_Cache::invalidate_cache();

		if ( $result ) {
			self::send_success( array(
				'message' => __( 'Dashboard cache cleared successfully', 'thisismyurl-shadow' ),
			) );
		} else {
			self::send_error( __( 'Failed to clear dashboard cache', 'thisismyurl-shadow' ) );
		}
	}

	/**
	 * Clear the cache for a single dashboard widget.
	 *
	 * Widget-level invalidation exists so the UI can refresh one panel without
	 * forcing every other dashboard card to rebuild. This keeps the interface
	 * responsive while still letting admins recover from stale data.
	 *
	 * @since  0.6095
	 * @return void Sends a JSON response and terminates execution.
	 */
	public static function invalidate_widget_cache() {
		// Verify nonce and capability
		self::verify_request( 'thisismyurl_shadow_cache_action', 'manage_options' );

		// Get widget ID
		$widget_id = self::get_post_param( 'widget_id', 'text', '', true );

		// Invalidate widget cache
		$result = Dashboard_Cache::invalidate_widget_cache( $widget_id );

		if ( $result ) {
			self::send_success( array(
				'message' => sprintf(
					/* translators: %s: widget ID */
					__( 'Widget %s cache cleared successfully', 'thisismyurl-shadow' ),
					esc_html( $widget_id )
				),
			) );
		} else {
			self::send_error( __( 'Failed to clear widget cache', 'thisismyurl-shadow' ) );
		}
	}

	/**
	 * Return current dashboard cache statistics for the admin UI.
	 *
	 * The response can be used by debugging or monitoring views to explain how
	 * much data is cached and whether cache entries are being regenerated as
	 * expected.
	 *
	 * @since  0.6095
	 * @return void Sends a JSON response and terminates execution.
	 */
	public static function get_cache_stats() {
		// Verify nonce and capability
		self::verify_request( 'thisismyurl_shadow_cache_action', 'manage_options' );

		$stats = Dashboard_Cache::get_cache_stats();

		self::send_success( array(
			'message' => __( 'Cache statistics retrieved', 'thisismyurl-shadow' ),
			'data'    => $stats,
		) );
	}

	/**
	 * Clear every dashboard-related cache entry at once.
	 *
	 * This is the broadest reset action exposed by the cache UI. It is intended
	 * for cases where the admin wants a full clean slate across page-level and
	 * widget-level caches after scans, treatment runs, or debugging work.
	 *
	 * @since  0.6095
	 * @return void Sends a JSON response and terminates execution.
	 */
	public static function invalidate_all_caches() {
		// Verify nonce and capability
		self::verify_request( 'thisismyurl_shadow_cache_action', 'manage_options' );

		// Invalidate all caches
		Dashboard_Cache::invalidate_all_caches();

		self::send_success( array(
			'message' => __( 'All dashboard caches cleared successfully', 'thisismyurl-shadow' ),
		) );
	}
}

// Register AJAX handlers
add_action( 'wp_ajax_thisismyurl_shadow_invalidate_dashboard_cache', array( 'ThisIsMyURL\Shadow\Admin\AJAX_Dashboard_Cache', 'invalidate_dashboard_cache' ) );
add_action( 'wp_ajax_thisismyurl_shadow_invalidate_widget_cache', array( 'ThisIsMyURL\Shadow\Admin\AJAX_Dashboard_Cache', 'invalidate_widget_cache' ) );
add_action( 'wp_ajax_thisismyurl_shadow_get_cache_stats', array( 'ThisIsMyURL\Shadow\Admin\AJAX_Dashboard_Cache', 'get_cache_stats' ) );
add_action( 'wp_ajax_thisismyurl_shadow_invalidate_all_caches', array( 'ThisIsMyURL\Shadow\Admin\AJAX_Dashboard_Cache', 'invalidate_all_caches' ) );
