<?php
/**
 * Color Utilities
 *
 * Centralized color operations including conversion and contrast calculations.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Color Utility Functions
 *
 * Provides centralized color operations including hex-to-RGB conversion,
 * WCAG contrast ratio calculation, and accessibility verification.
 */
class Color_Utils {
	/**
	 * Convert hex color to RGB array.
	 *
	 * Handles both 3-digit (#abc) and 6-digit (#aabbcc) hex formats.
	 * Returns null for invalid input.
	 *
	 * @param string $hex Hex color code (with or without #).
	 * @return array|null RGB array with r, g, b keys, or null if invalid.
	 */
	public static function hex_to_rgb( $hex ) {
		$normalized = ltrim( trim( $hex ), '#' );

		// Expand 3-digit to 6-digit format
		if ( strlen( $normalized ) === 3 ) {
			$normalized = $normalized[0] . $normalized[0] . $normalized[1] . $normalized[1] . $normalized[2] . $normalized[2];
		}

		// Validate hex format
		if ( strlen( $normalized ) !== 6 ) {
			return null;
		}

		// Convert to RGB
		$int = hexdec( $normalized );
		return array(
			'r' => ( $int >> 16 ) & 255,
			'g' => ( $int >> 8 ) & 255,
			'b' => $int & 255,
		);
	}

	/**
	 * Calculate relative luminance of RGB color.
	 *
	 * Used for WCAG contrast ratio calculation.
	 * Formula: https://www.w3.org/TR/WCAG20/#relativeluminancedef
	 *
	 * @param array $rgb RGB array with r, g, b keys.
	 * @return float Relative luminance (0-1).
	 */
	private static function get_luminance( $rgb ) {
		// Normalize to 0-1 range
		$r = $rgb['r'] / 255;
		$g = $rgb['g'] / 255;
		$b = $rgb['b'] / 255;

		// Apply gamma correction
		$r = $r <= 0.03928 ? $r / 12.92 : pow( ( $r + 0.055 ) / 1.055, 2.4 );
		$g = $g <= 0.03928 ? $g / 12.92 : pow( ( $g + 0.055 ) / 1.055, 2.4 );
		$b = $b <= 0.03928 ? $b / 12.92 : pow( ( $b + 0.055 ) / 1.055, 2.4 );

		// Calculate relative luminance
		return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
	}

	/**
	 * Calculate WCAG contrast ratio between two hex colors.
	 *
	 * Formula: https://www.w3.org/TR/WCAG20/#contrast-ratiodef
	 * Returns 0 if either color is invalid.
	 *
	 * @param string $fg_hex Foreground color (hex).
	 * @param string $bg_hex Background color (hex).
	 * @return float Contrast ratio (1-21).
	 */
	public static function contrast_ratio( $fg_hex, $bg_hex ) {
		$fg = self::hex_to_rgb( $fg_hex );
		$bg = self::hex_to_rgb( $bg_hex );

		if ( ! $fg || ! $bg ) {
			return 0;
		}

		$fg_luminance = self::get_luminance( $fg );
		$bg_luminance = self::get_luminance( $bg );

		// Lighter color / darker color
		$lighter = max( $fg_luminance, $bg_luminance );
		$darker  = min( $fg_luminance, $bg_luminance );

		return ( $lighter + 0.05 ) / ( $darker + 0.05 );
	}

	/**
	 * Check if contrast between two colors meets accessibility standard.
	 *
	 * Levels:
	 * - 'AA' (default): Requires 4.5:1 for normal text, 3:1 for large text
	 * - 'AAA': Requires 7:1 for normal text, 4.5:1 for large text
	 *
	 * @param string $fg_hex Foreground color (hex).
	 * @param string $bg_hex Background color (hex).
	 * @param string $level   Accessibility level ('AA' or 'AAA').
	 * @return bool True if contrast meets standard.
	 */
	public static function is_accessible_contrast( $fg_hex, $bg_hex, $level = 'AA' ) {
		$ratio = self::contrast_ratio( $fg_hex, $bg_hex );

		if ( $level === 'AAA' ) {
			return $ratio >= 7;
		}

		// AA level (default)
		return $ratio >= 4.5;
	}
}
