<?php

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wpshadow_hex_to_rgb( string $hex ): ?array {
	$hex = ltrim( $hex, '#' );

	if ( strlen( $hex ) === 3 ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}

	if ( strlen( $hex ) !== 6 ) {
		return null;
	}

	if ( ! ctype_xdigit( $hex ) ) {
		return null;
	}

	$r = hexdec( substr( $hex, 0, 2 ) );
	$g = hexdec( substr( $hex, 2, 2 ) );
	$b = hexdec( substr( $hex, 4, 2 ) );

	return array(
		'r' => $r,
		'g' => $g,
		'b' => $b,
	);
}

function wpshadow_calculate_luminance( int $r, int $g, int $b ): float {

	$r_srgb = $r / 255.0;
	$g_srgb = $g / 255.0;
	$b_srgb = $b / 255.0;

	$r_linear = ( $r_srgb <= 0.03928 ) ? $r_srgb / 12.92 : pow( ( $r_srgb + 0.055 ) / 1.055, 2.4 );
	$g_linear = ( $g_srgb <= 0.03928 ) ? $g_srgb / 12.92 : pow( ( $g_srgb + 0.055 ) / 1.055, 2.4 );
	$b_linear = ( $b_srgb <= 0.03928 ) ? $b_srgb / 12.92 : pow( ( $b_srgb + 0.055 ) / 1.055, 2.4 );

	return 0.2126 * $r_linear + 0.7152 * $g_linear + 0.0722 * $b_linear;
}

function wpshadow_calculate_contrast_ratio( string $color1, string $color2 ): ?float {
	$rgb1 = WPSHADOW_hex_to_rgb( $color1 );
	$rgb2 = WPSHADOW_hex_to_rgb( $color2 );

	if ( ! $rgb1 || ! $rgb2 ) {
		return null;
	}

	$l1 = WPSHADOW_calculate_luminance( $rgb1['r'], $rgb1['g'], $rgb1['b'] );
	$l2 = WPSHADOW_calculate_luminance( $rgb2['r'], $rgb2['g'], $rgb2['b'] );

	if ( $l1 < $l2 ) {
		$temp = $l1;
		$l1   = $l2;
		$l2   = $temp;
	}

	return ( $l1 + 0.05 ) / ( $l2 + 0.05 );
}

function wpshadow_check_wcag_aa( string $text_color, string $background_color, bool $is_large_text = false ): array {
	$required_ratio = $is_large_text ? 3.0 : 4.5;
	$ratio          = WPSHADOW_calculate_contrast_ratio( $text_color, $background_color );

	return array(
		'passes'   => $ratio !== null && $ratio >= $required_ratio,
		'ratio'    => $ratio,
		'required' => $required_ratio,
	);
}

function wpshadow_check_wcag_aaa( string $text_color, string $background_color, bool $is_large_text = false ): array {
	$required_ratio = $is_large_text ? 4.5 : 7.0;
	$ratio          = WPSHADOW_calculate_contrast_ratio( $text_color, $background_color );

	return array(
		'passes'   => $ratio !== null && $ratio >= $required_ratio,
		'ratio'    => $ratio,
		'required' => $required_ratio,
	);
}

function wpshadow_check_contrast( string $text_color, string $background_color, bool $is_large_text = false ): array {
	$ratio = WPSHADOW_calculate_contrast_ratio( $text_color, $background_color );
	$aa    = WPSHADOW_check_wcag_aa( $text_color, $background_color, $is_large_text );
	$aaa   = WPSHADOW_check_wcag_aaa( $text_color, $background_color, $is_large_text );

	return array(
		'ratio'           => $ratio,
		'aa'              => $aa,
		'aaa'             => $aaa,
		'formatted_ratio' => $ratio !== null ? number_format( $ratio, 2 ) . ':1' : 'N/A',
	);
}
