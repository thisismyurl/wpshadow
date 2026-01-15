<?php
/**
 * Array Utility Helpers
 *
 * Common array manipulation utilities to eliminate duplicate patterns.
 *
 * @package WPS\CoreSupport
 * @since 1.2601.73003
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Limit array size by removing oldest entries.
 *
 * Removes entries from the beginning of the array until it's within max size.
 *
 * @param array $array Array to limit.
 * @param int   $max   Maximum number of entries (default: 50).
 * @return array Limited array.
 */
function wps_limit_array_size( array $array, int $max = 50 ): array {
	while ( count( $array ) > $max ) {
		array_shift( $array );
	}
	return $array;
}

/**
 * Get array value with default fallback.
 *
 * @param array  $array   Array to search.
 * @param string $key     Key to retrieve.
 * @param mixed  $default Default value if key doesn't exist.
 * @return mixed Value from array or default.
 */
function wps_array_get( array $array, string $key, $default = null ) {
	return $array[ $key ] ?? $default;
}

/**
 * Safely get nested array value using dot notation.
 *
 * @param array  $array   Array to search.
 * @param string $path    Dot-separated path (e.g., 'settings.debug.enabled').
 * @param mixed  $default Default value if path doesn't exist.
 * @return mixed Value from array or default.
 */
function wps_array_get_nested( array $array, string $path, $default = null ) {
	$keys = explode( '.', $path );

	foreach ( $keys as $key ) {
		if ( ! is_array( $array ) || ! isset( $array[ $key ] ) ) {
			return $default;
		}
		$array = $array[ $key ];
	}

	return $array;
}

/**
 * Filter array to only include specified keys.
 *
 * @param array $array Array to filter.
 * @param array $keys  Keys to keep.
 * @return array Filtered array.
 */
function wps_array_only( array $array, array $keys ): array {
	return array_intersect_key( $array, array_flip( $keys ) );
}

/**
 * Filter array to exclude specified keys.
 *
 * @param array $array Array to filter.
 * @param array $keys  Keys to remove.
 * @return array Filtered array.
 */
function wps_array_except( array $array, array $keys ): array {
	return array_diff_key( $array, array_flip( $keys ) );
}
