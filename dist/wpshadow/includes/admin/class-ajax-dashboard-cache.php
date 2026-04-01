<?php
/**
 * AJAX Handler for Dashboard Cache Operations
 *
 * Handles AJAX requests for invalidating dashboard cache
 * and clearing widget-specific caches from the frontend.
 *
 * @since 0.6093.1200
 * @package WPShadow\Admin
 */

declare(strict_types=1);

namespace WPShadow\Admin;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Core\Dashboard_Cache;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AJAX_Dashboard_Cache Class
 *
 * Provides AJAX endpoints for managing dashboard cache operations.
 *
 * @since 0.6093.1200
 */
class AJAX_Dashboard_Cache extends AJAX_Handler_Base {

	/**
	 * Invalidate dashboard cache
	 *
	 * AJAX endpoint: wp_ajax_wpshadow_invalidate_dashboard_cache
	 * Clears the entire dashboard page cache.
	 *
	 * @since 0.6093.1200
	 * @return void Dies after sending JSON response.
	 */
	public static function invalidate_dashboard_cache() {
		// Verify nonce and capability
		self::verify_request( 'wpshadow_cache_action', 'manage_options' );

		// Invalidate cache
		$result = Dashboard_Cache::invalidate_cache();

		if ( $result ) {
			self::send_success( array(
				'message' => __( 'Dashboard cache cleared successfully', 'wpshadow' ),
			) );
		} else {
			self::send_error( __( 'Failed to clear dashboard cache', 'wpshadow' ) );
		}
	}

	/**
	 * Invalidate widget cache
	 *
	 * AJAX endpoint: wp_ajax_wpshadow_invalidate_widget_cache
	 * Clears cache for a specific dashboard widget.
	 *
	 * @since 0.6093.1200
	 * @return void Dies after sending JSON response.
	 */
	public static function invalidate_widget_cache() {
		// Verify nonce and capability
		self::verify_request( 'wpshadow_cache_action', 'manage_options' );

		// Get widget ID
		$widget_id = self::get_post_param( 'widget_id', 'text', '', true );

		// Invalidate widget cache
		$result = Dashboard_Cache::invalidate_widget_cache( $widget_id );

		if ( $result ) {
			self::send_success( array(
				'message' => sprintf(
					/* translators: %s: widget ID */
					__( 'Widget %s cache cleared successfully', 'wpshadow' ),
					esc_html( $widget_id )
				),
			) );
		} else {
			self::send_error( __( 'Failed to clear widget cache', 'wpshadow' ) );
		}
	}

	/**
	 * Get cache statistics
	 *
	 * AJAX endpoint: wp_ajax_wpshadow_get_cache_stats
	 * Returns dashboard cache statistics for monitoring.
	 *
	 * @since 0.6093.1200
	 * @return void Dies after sending JSON response.
	 */
	public static function get_cache_stats() {
		// Verify nonce and capability
		self::verify_request( 'wpshadow_cache_action', 'manage_options' );

		$stats = Dashboard_Cache::get_cache_stats();

		self::send_success( array(
			'message' => __( 'Cache statistics retrieved', 'wpshadow' ),
			'data'    => $stats,
		) );
	}

	/**
	 * Invalidate all dashboard caches
	 *
	 * AJAX endpoint: wp_ajax_wpshadow_invalidate_all_caches
	 * Clears dashboard page cache and all widget caches.
	 *
	 * @since 0.6093.1200
	 * @return void Dies after sending JSON response.
	 */
	public static function invalidate_all_caches() {
		// Verify nonce and capability
		self::verify_request( 'wpshadow_cache_action', 'manage_options' );

		// Invalidate all caches
		Dashboard_Cache::invalidate_all_caches();

		self::send_success( array(
			'message' => __( 'All dashboard caches cleared successfully', 'wpshadow' ),
		) );
	}
}

// Register AJAX handlers
add_action( 'wp_ajax_wpshadow_invalidate_dashboard_cache', array( 'WPShadow\Admin\AJAX_Dashboard_Cache', 'invalidate_dashboard_cache' ) );
add_action( 'wp_ajax_wpshadow_invalidate_widget_cache', array( 'WPShadow\Admin\AJAX_Dashboard_Cache', 'invalidate_widget_cache' ) );
add_action( 'wp_ajax_wpshadow_get_cache_stats', array( 'WPShadow\Admin\AJAX_Dashboard_Cache', 'get_cache_stats' ) );
add_action( 'wp_ajax_wpshadow_invalidate_all_caches', array( 'WPShadow\Admin\AJAX_Dashboard_Cache', 'invalidate_all_caches' ) );
