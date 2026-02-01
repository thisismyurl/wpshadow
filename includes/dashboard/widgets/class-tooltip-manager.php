<?php
/**
 * Tooltip Manager for WPShadow
 *
 * Handles tooltip catalog loading and caching.
 *
 * @package WPShadow
 * @subpackage Admin
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get tooltip catalog for Tips & Guidance.
 *
 * @param string|null $category Optional category filter.
 * @return array Tooltip data.
 */
function wpshadow_get_tooltip_catalog( $category = null ) {
	// Use transient caching for persistent, cross-request caching (24 hour TTL)

	// If no category specified, load all categories
	if ( null === $category ) {
		$cache_key = 'wpshadow_tooltips_all';
		$cached    = \WPShadow\Core\Cache_Manager::get(
			$cache_key,
			'wpshadow_tooltips'
		);

		if ( false !== $cached ) {
			return $cached;
		}

		// Load all category files
		$all_tooltips = array();
		$categories   = array( 'navigation', 'content', 'design', 'extensions', 'maintenance', 'people', 'settings' );

		foreach ( $categories as $cat ) {
			$category_tooltips = wpshadow_get_tooltip_catalog( $cat );
			$all_tooltips      = array_merge( $all_tooltips, $category_tooltips );
		}

		// Cache all tooltips for 24 hours
		\WPShadow\Core\Cache_Manager::set(
			$cache_key,
			$all_tooltips,
			24 * HOUR_IN_SECONDS,
			'wpshadow_tooltips'
		);
		return $all_tooltips;
	}

	// Check transient cache for category
	$cache_key = 'wpshadow_tooltips_' . sanitize_key( $category );
	$cached    = \WPShadow\Core\Cache_Manager::get(
		$cache_key,
		'wpshadow_tooltips'
	);

	if ( false !== $cached ) {
		return $cached;
	}

	// Path to category-specific PHP file
	$php_file = plugin_dir_path( __FILE__ ) . '../data/tooltips-' . sanitize_file_name( $category ) . '.php';

	// Check if file exists
	if ( ! file_exists( $php_file ) ) {
		// Log error only for debugging
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Legitimate error logging for missing files
			error_log( 'WPShadow: tooltips-' . $category . '.php file not found at ' . $php_file );
		}
		return array();
	}

	// Load PHP file (returns array with translations already applied)
	$data = require $php_file;

	// Check if valid array
	if ( ! is_array( $data ) ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'WPShadow: Invalid data structure in tooltips-' . $category . '.php' );
		}
		return array();
	}

	// Cache the result for 24 hours
	\WPShadow\Core\Cache_Manager::set(
		$cache_key,
		$data,
		24 * HOUR_IN_SECONDS,
		'wpshadow_tooltips'
		);

	return $data;
}

/**
 * Get tip categories.
 *
 * @return array Categories.
 */
function wpshadow_get_tip_categories() {
	return array(
		'navigation'  => __( 'Navigation', 'wpshadow' ),
		'content'     => __( 'Content', 'wpshadow' ),
		'design'      => __( 'Design & Appearance', 'wpshadow' ),
		'extensions'  => __( 'Plugins & Extensions', 'wpshadow' ),
		'people'      => __( 'Users & Roles', 'wpshadow' ),
		'settings'    => __( 'Settings', 'wpshadow' ),
		'maintenance' => __( 'Maintenance', 'wpshadow' ),
	);
}

/**
 * Get user tip preferences.
 *
 * @param int $user_id User ID.
 * @return array User preferences.
 */
function wpshadow_get_user_tip_prefs( $user_id ) {
	$prefs = get_user_meta( $user_id, 'wpshadow_tip_prefs', true );
	if ( ! is_array( $prefs ) ) {
		$prefs = array();
	}
	$defaults = array(
		'disabled_categories' => array(),
		'dismissed_tips'      => array(),
	);

	return wp_parse_args( $prefs, $defaults );
}

/**
 * Save user tip preferences.
 *
 * @param int   $user_id User ID.
 * @param array $prefs User preferences.
 * @return void
 */
function wpshadow_save_user_tip_prefs( $user_id, $prefs ) {
	if ( ! is_array( $prefs ) ) {
		return;
	}
	$clean = array(
		'disabled_categories' => array_map( 'sanitize_key', $prefs['disabled_categories'] ?? array() ),
		'dismissed_tips'      => array_map( 'sanitize_key', $prefs['dismissed_tips'] ?? array() ),
	);
	update_user_meta( $user_id, 'wpshadow_tip_prefs', $clean );
}
