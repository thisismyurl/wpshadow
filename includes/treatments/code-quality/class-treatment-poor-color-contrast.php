<?php
/**
 * Poor Color Contrast Treatment
 *
 * Detects insufficient color contrast ratios between text and backgrounds
 * that fail WCAG 2.1 Level AA requirements (4.5:1 for normal text, 3:1 for large text).
 *
 * @package    WPShadow
 * @subpackage Treatments\Accessibility
 * @since      1.6034.2145
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Poor Color Contrast Treatment Class
 *
 * Analyzes theme colors and common color combinations to detect contrast
 * ratio failures that make content difficult or impossible to read for
 * users with low vision or color blindness.
 *
 * **Why This Matters:**
 * - WCAG 2.1 Level AA compliance (SC 1.4.3 Contrast Minimum)
 * - Legal requirement for ADA/Section 508 compliance
 * - 8% of men have color blindness
 * - Poor contrast = 53% user abandonment
 *
 * **WCAG Standards:**
 * - Normal text: 4.5:1 minimum contrast ratio
 * - Large text (18pt+): 3:1 minimum contrast ratio
 * - UI components: 3:1 minimum contrast ratio
 *
 * @since 1.6034.2145
 */
class Treatment_Poor_Color_Contrast extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'poor-color-contrast';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Poor Color Contrast Detected';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies insufficient color contrast ratios that fail WCAG 2.1 Level AA accessibility standards';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check
	 *
	 * Analyzes theme colors and common design elements for contrast issues:
	 * - Text color vs background color
	 * - Link colors vs background
	 * - Button colors and hover states
	 * - Custom CSS color declarations
	 *
	 * @since  1.6034.2145
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Poor_Color_Contrast' );
	}

	/**
	 * Calculate WCAG contrast ratio between two colors
	 *
	 * Formula: (L1 + 0.05) / (L2 + 0.05) where L is relative luminance.
	 *
	 * @since  1.6034.2145
	 * @param  string $color1 Hex color code (with or without #).
	 * @param  string $color2 Hex color code (with or without #).
	 * @return float Contrast ratio.
	 */
	private static function calculate_contrast_ratio( $color1, $color2 ) {
		$lum1 = self::get_relative_luminance( $color1 );
		$lum2 = self::get_relative_luminance( $color2 );

		$lighter = max( $lum1, $lum2 );
		$darker  = min( $lum1, $lum2 );

		return ( $lighter + 0.05 ) / ( $darker + 0.05 );
	}

	/**
	 * Calculate relative luminance for a color
	 *
	 * @since  1.6034.2145
	 * @param  string $hex Hex color code.
	 * @return float Relative luminance (0-1).
	 */
	private static function get_relative_luminance( $hex ) {
		// Remove # if present
		$hex = ltrim( $hex, '#' );

		// Convert to RGB
		$r = hexdec( substr( $hex, 0, 2 ) ) / 255;
		$g = hexdec( substr( $hex, 2, 2 ) ) / 255;
		$b = hexdec( substr( $hex, 4, 2 ) ) / 255;

		// Apply gamma correction
		$r = ( $r <= 0.03928 ) ? $r / 12.92 : pow( ( $r + 0.055 ) / 1.055, 2.4 );
		$g = ( $g <= 0.03928 ) ? $g / 12.92 : pow( ( $g + 0.055 ) / 1.055, 2.4 );
		$b = ( $b <= 0.03928 ) ? $b / 12.92 : pow( ( $b + 0.055 ) / 1.055, 2.4 );

		// Calculate luminance
		return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
	}
}
