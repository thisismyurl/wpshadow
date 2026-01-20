<?php

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wpshadow_get_post_text( string $key, string $default = '' ): string {
	return isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : $default;
}

function wpshadow_get_post_email( string $key, string $default = '' ): string {
	return isset( $_POST[ $key ] ) ? sanitize_email( wp_unslash( $_POST[ $key ] ) ) : $default;
}

function wpshadow_get_post_url( string $key, string $default = '' ): string {
	return isset( $_POST[ $key ] ) ? esc_url_raw( wp_unslash( $_POST[ $key ] ) ) : $default;
}

function wpshadow_get_post_int( string $key, int $default = 0 ): int {
	return isset( $_POST[ $key ] ) ? absint( $_POST[ $key ] ) : $default;
}

function wpshadow_get_post_key( string $key, string $default = '' ): string {
	return isset( $_POST[ $key ] ) ? sanitize_key( wp_unslash( $_POST[ $key ] ) ) : $default;
}

function wpshadow_get_post_bool( string $key, bool $default = false ): bool {
	return isset( $_POST[ $key ] ) ? (bool) $_POST[ $key ] : $default;
}

function wpshadow_get_post_textarea( string $key, string $default = '' ): string {
	return isset( $_POST[ $key ] ) ? sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) : $default;
}

function wpshadow_get_query_text( string $key, string $default = '' ): string {
	return isset( $_GET[ $key ] ) ? sanitize_text_field( wp_unslash( $_GET[ $key ] ) ) : $default;
}

function wpshadow_get_query_key( string $key, string $default = '' ): string {
	return isset( $_GET[ $key ] ) ? sanitize_key( wp_unslash( $_GET[ $key ] ) ) : $default;
}

function wpshadow_get_query_int( string $key, int $default = 0 ): int {
	return isset( $_GET[ $key ] ) ? absint( $_GET[ $key ] ) : $default;
}

function wpshadow_get_query_url( string $key, string $default = '' ): string {
	return isset( $_GET[ $key ] ) ? esc_url_raw( wp_unslash( $_GET[ $key ] ) ) : $default;
}

function wpshadow_get_query_bool( string $key, bool $default = false ): bool {
	return isset( $_GET[ $key ] ) ? (bool) $_GET[ $key ] : $default;
}

function wpshadow_get_post_array( string $key, array $default = array() ): array {
	if ( ! isset( $_POST[ $key ] ) || ! is_array( $_POST[ $key ] ) ) {
		return $default;
	}

	return array_map(
		function ( $value ) {
			return is_string( $value ) ? sanitize_text_field( wp_unslash( $value ) ) : $value;
		},
		$_POST[ $key ]
	);
}

function wpshadow_get_post_key_array( string $key, array $default = array() ): array {
	if ( ! isset( $_POST[ $key ] ) || ! is_array( $_POST[ $key ] ) ) {
		return $default;
	}

	return array_map( 'sanitize_key', array_map( 'wp_unslash', $_POST[ $key ] ) );
}
