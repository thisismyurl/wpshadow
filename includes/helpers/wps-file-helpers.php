<?php
/**
 * WPShadow File Helper Functions
 *
 * Consolidated file operations to reduce code duplication
 * and provide safe file access patterns.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Safely check if file exists and is readable
 *
 * Consolidates repeated pattern: if ( file_exists( $file ) && is_readable( $file ) )
 *
 * @param string $path File path to check.
 * @return bool True if file exists and is readable, false otherwise.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wpshadow_file_readable( string $path ): bool {
	return is_string( $path ) && file_exists( $path ) && is_readable( $path );
}

/**
 * Safely read file contents with fallback
 *
 * Replaces: if ( file_exists ) { file_get_contents() }
 *
 * @param string $path    File path to read.
 * @param string $default Default value if file cannot be read.
 * @return string File contents or default value.
 */
function wpshadow_safe_file_get_contents( string $path, string $default = '' ): string {
	if ( ! wpshadow_file_readable( $path ) ) {
		return $default;
	}

	// Reading local bundled files, not remote content - documented exception.
	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$content = @file_get_contents( $path );

	return is_string( $content ) ? $content : $default;
}

/**
 * Safely scan directory
 *
 * Replaces: if ( is_dir( $path ) ) { scandir( $path ) }
 *
 * @param string $path          Directory path to scan.
 * @param int    $sorting_order Sort order (SCANDIR_SORT_ASCENDING, etc).
 * @return array List of directory contents, or empty array on error.
 */
function wpshadow_safe_scandir( string $path, int $sorting_order = SCANDIR_SORT_ASCENDING ): array {
	if ( ! is_string( $path ) || ! is_dir( $path ) || ! is_readable( $path ) ) {
		return array();
	}

	$result = @scandir( $path, $sorting_order );
	return is_array( $result ) ? $result : array();
}

/**
 * Get file size safely
 *
 * @param string $path File path.
 * @return int File size in bytes, or 0 if cannot be determined.
 */
function wpshadow_get_file_size( string $path ): int {
	if ( ! wpshadow_file_readable( $path ) ) {
		return 0;
	}

	$size = @filesize( $path );
	return is_int( $size ) ? $size : 0;
}

/**
 * Get last modified time safely
 *
 * @param string $path File path.
 * @return int Unix timestamp, or 0 if cannot be determined.
 */
function wpshadow_get_file_mtime( string $path ): int {
	if ( ! wpshadow_file_readable( $path ) ) {
		return 0;
	}

	$mtime = @filemtime( $path );
	return is_int( $mtime ) ? $mtime : 0;
}

/**
 * Check if path is valid and exists
 *
 * @param string $path Path to validate.
 * @return bool True if path is valid and exists.
 */
function wpshadow_path_exists( string $path ): bool {
	if ( ! is_string( $path ) || empty( $path ) ) {
		return false;
	}

	return file_exists( $path );
}

/**
 * Safely get JSON file contents and decode
 *
 * @param string $path    JSON file path.
 * @param mixed  $default Default value if cannot be read.
 * @return mixed Decoded JSON or default value.
 */
function wpshadow_get_json_file( string $path, mixed $default = null ): mixed {
	$content = wpshadow_safe_file_get_contents( $path );

	if ( empty( $content ) ) {
		return $default;
	}

	$decoded = json_decode( $content, true );
	return is_array( $decoded ) ? $decoded : $default;
}

/**
 * Get list of installed plugins with caching
 *
 * Phase 3 Optimization: Cache expensive get_plugins() call
 * (requires file system scan). Cached for 1 hour by default.
 *
 * @param int $cache_ttl Cache TTL in seconds (default 1 hour).
 * @return array List of installed plugins.
 */
function wpshadow_get_cached_plugins_list( int $cache_ttl = 3600 ): array {
	$cache_key = 'wpshadow_plugins_list_cache';
	
	// Try to get from transient cache
	$plugins = get_transient( $cache_key );
	if ( is_array( $plugins ) ) {
		return $plugins;
	}

	// Load get_plugins if not available
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	// Get fresh list
	$plugins = get_plugins();
	
	// Cache result
	set_transient( $cache_key, $plugins, $cache_ttl );

	return is_array( $plugins ) ? $plugins : array();
}

/**
 * Clear cached plugins list
 *
 * Call this when plugins are activated/deactivated.
 *
 * @return bool True if cache was cleared.
 */
function wpshadow_clear_plugins_cache(): bool {
	return delete_transient( 'wpshadow_plugins_list_cache' );
}
