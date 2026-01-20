<?php

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wpshadow_limit_array_size( array $array, int $max = 50 ): array {
	while ( count( $array ) > $max ) {
		array_shift( $array );
	}
	return $array;
}

function wpshadow_array_get( array $array, string $key, $default = null ) {
	return $array[ $key ] ?? $default;
}

function wpshadow_array_get_nested( array $array, string $path, $default = null ) {
	$keys = explode( '.', $path );

	foreach ( $keys as $key ) {
		if ( ! is_array( $array ) || ! isset( $array[ $key ] ) ) {
			return $default;
		}
		$array = $array[ $key ];
	}

	return $array;
}

function wpshadow_array_only( array $array, array $keys ): array {
	return array_intersect_key( $array, array_flip( $keys ) );
}

function wpshadow_array_except( array $array, array $keys ): array {
	return array_diff_key( $array, array_flip( $keys ) );
}
