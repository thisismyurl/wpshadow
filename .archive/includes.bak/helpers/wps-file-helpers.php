<?php

declare(strict_types=1);

namespace WPShadow\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wpshadow_file_readable( string $path ): bool {
	return is_string( $path ) && file_exists( $path ) && is_readable( $path );
}

function wpshadow_safe_file_get_contents( string $path, string $default = '' ): string {
	if ( ! wpshadow_file_readable( $path ) ) {
		return $default;
	}

	$content = @file_get_contents( $path );

	return is_string( $content ) ? $content : $default;
}

function wpshadow_safe_scandir( string $path, int $sorting_order = SCANDIR_SORT_ASCENDING ): array {
	if ( ! is_string( $path ) || ! is_dir( $path ) || ! is_readable( $path ) ) {
		return array();
	}

	$result = @scandir( $path, $sorting_order );
	return is_array( $result ) ? $result : array();
}

function wpshadow_get_file_size( string $path ): int {
	if ( ! wpshadow_file_readable( $path ) ) {
		return 0;
	}

	$size = @filesize( $path );
	return is_int( $size ) ? $size : 0;
}

function wpshadow_get_file_mtime( string $path ): int {
	if ( ! wpshadow_file_readable( $path ) ) {
		return 0;
	}

	$mtime = @filemtime( $path );
	return is_int( $mtime ) ? $mtime : 0;
}

function wpshadow_path_exists( string $path ): bool {
	if ( ! is_string( $path ) || empty( $path ) ) {
		return false;
	}

	return file_exists( $path );
}

function wpshadow_get_json_file( string $path, mixed $default = null ): mixed {
	$content = wpshadow_safe_file_get_contents( $path );

	if ( empty( $content ) ) {
		return $default;
	}

	$decoded = json_decode( $content, true );
	return is_array( $decoded ) ? $decoded : $default;
}

function wpshadow_get_cached_plugins_list( int $cache_ttl = 3600 ): array {
	$cache_key = 'wpshadow_plugins_list_cache';

	$plugins = get_transient( $cache_key );
	if ( is_array( $plugins ) ) {
		return $plugins;
	}

	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	$plugins = get_plugins();

	set_transient( $cache_key, $plugins, $cache_ttl );

	return is_array( $plugins ) ? $plugins : array();
}

function wpshadow_clear_plugins_cache(): bool {
	return delete_transient( 'wpshadow_plugins_list_cache' );
}
