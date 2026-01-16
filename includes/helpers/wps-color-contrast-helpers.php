<?php
/**
 * Color Contrast Helpers
 *
 * Functions to check color contrast ratios for WCAG accessibility compliance.
 * Ensures text and background colors meet accessibility standards.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.75001
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Convert hex color to RGB array.
 *
 * @param string $hex Hex color code (with or without #).
 * @return array{r: int, g: int, b: int}|null RGB values array or null on failure.
 */
function WPSHADOW_hex_to_rgb( string $hex ): ?array {
	$hex = ltrim( $hex, '#' );

	// Support both 3 and 6 character hex codes.
	if ( strlen( $hex ) === 3 ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}

	if ( strlen( $hex ) !== 6 ) {
		return null;
	}

	$r = hexdec( substr( $hex, 0, 2 ) );
	$g = hexdec( substr( $hex, 2, 2 ) );
	$b = hexdec( substr( $hex, 4, 2 ) );

	if ( $r === false || $g === false || $b === false ) {
		return null;
	}

	return array(
		'r' => $r,
		'g' => $g,
		'b' => $b,
	);
}

/**
 * Calculate relative luminance of a color.
 *
 * Based on WCAG 2.1 specification:
 * https://www.w3.org/TR/WCAG21/#dfn-relative-luminance
 *
 * @param int $r Red component (0-255).
 * @param int $g Green component (0-255).
 * @param int $b Blue component (0-255).
 * @return float Relative luminance (0-1).
 */
function WPSHADOW_calculate_luminance( int $r, int $g, int $b ): float {
	// Convert to 0-1 range.
	$r_srgb = $r / 255.0;
	$g_srgb = $g / 255.0;
	$b_srgb = $b / 255.0;

	// Apply gamma correction.
	$r_linear = ( $r_srgb <= 0.03928 ) ? $r_srgb / 12.92 : pow( ( $r_srgb + 0.055 ) / 1.055, 2.4 );
	$g_linear = ( $g_srgb <= 0.03928 ) ? $g_srgb / 12.92 : pow( ( $g_srgb + 0.055 ) / 1.055, 2.4 );
	$b_linear = ( $b_srgb <= 0.03928 ) ? $b_srgb / 12.92 : pow( ( $b_srgb + 0.055 ) / 1.055, 2.4 );

	// Calculate relative luminance.
	return 0.2126 * $r_linear + 0.7152 * $g_linear + 0.0722 * $b_linear;
}

/**
 * Calculate contrast ratio between two colors.
 *
 * Based on WCAG 2.1 specification:
 * https://www.w3.org/TR/WCAG21/#dfn-contrast-ratio
 *
 * @param string $color1 First color (hex).
 * @param string $color2 Second color (hex).
 * @return float|null Contrast ratio (1-21) or null on failure.
 */
function WPSHADOW_calculate_contrast_ratio( string $color1, string $color2 ): ?float {
	$rgb1 = WPSHADOW_hex_to_rgb( $color1 );
	$rgb2 = WPSHADOW_hex_to_rgb( $color2 );

	if ( ! $rgb1 || ! $rgb2 ) {
		return null;
	}

	$l1 = WPSHADOW_calculate_luminance( $rgb1['r'], $rgb1['g'], $rgb1['b'] );
	$l2 = WPSHADOW_calculate_luminance( $rgb2['r'], $rgb2['g'], $rgb2['b'] );

	// Ensure l1 is the lighter color.
	if ( $l1 < $l2 ) {
		$temp = $l1;
		$l1   = $l2;
		$l2   = $temp;
	}

	// Calculate contrast ratio: (L1 + 0.05) / (L2 + 0.05).
	return ( $l1 + 0.05 ) / ( $l2 + 0.05 );
}

/**
 * Check if colors meet WCAG AA standard.
 *
 * WCAG AA requires:
 * - 4.5:1 for normal text
 * - 3:1 for large text (18pt+ or 14pt+ bold)
 *
 * @param string $text_color       Text color (hex).
 * @param string $background_color Background color (hex).
 * @param bool   $is_large_text    Whether text is large (18pt+ or 14pt+ bold).
 * @return array{passes: bool, ratio: float|null, required: float} Result array.
 */
function WPSHADOW_check_wcag_aa( string $text_color, string $background_color, bool $is_large_text = false ): array {
	$required_ratio = $is_large_text ? 3.0 : 4.5;
	$ratio          = WPSHADOW_calculate_contrast_ratio( $text_color, $background_color );

	return array(
		'passes'   => $ratio !== null && $ratio >= $required_ratio,
		'ratio'    => $ratio,
		'required' => $required_ratio,
	);
}

/**
 * Check if colors meet WCAG AAA standard.
 *
 * WCAG AAA requires:
 * - 7:1 for normal text
 * - 4.5:1 for large text (18pt+ or 14pt+ bold)
 *
 * @param string $text_color       Text color (hex).
 * @param string $background_color Background color (hex).
 * @param bool   $is_large_text    Whether text is large (18pt+ or 14pt+ bold).
 * @return array{passes: bool, ratio: float|null, required: float} Result array.
 */
function WPSHADOW_check_wcag_aaa( string $text_color, string $background_color, bool $is_large_text = false ): array {
	$required_ratio = $is_large_text ? 4.5 : 7.0;
	$ratio          = WPSHADOW_calculate_contrast_ratio( $text_color, $background_color );

	return array(
		'passes'   => $ratio !== null && $ratio >= $required_ratio,
		'ratio'    => $ratio,
		'required' => $required_ratio,
	);
}

/**
 * Get comprehensive contrast check results.
 *
 * @param string $text_color       Text color (hex).
 * @param string $background_color Background color (hex).
 * @param bool   $is_large_text    Whether text is large (18pt+ or 14pt+ bold).
 * @return array{ratio: float|null, aa: array{passes: bool, ratio: float|null, required: float}, aaa: array{passes: bool, ratio: float|null, required: float}, formatted_ratio: string} Comprehensive results.
 */
function WPSHADOW_check_contrast( string $text_color, string $background_color, bool $is_large_text = false ): array {
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
