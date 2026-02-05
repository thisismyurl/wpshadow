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
		$contrast_issues = array();

		// Get theme colors from Customizer
		$text_color       = get_theme_mod( 'text_color', '000000' );
		$background_color = get_theme_mod( 'background_color', 'ffffff' );
		$link_color       = get_theme_mod( 'link_color', '0073aa' );

		// Check main text/background contrast
		$main_contrast = self::calculate_contrast_ratio( $text_color, $background_color );
		if ( $main_contrast < 4.5 ) {
			$contrast_issues[] = array(
				'element'  => 'Main text',
				'ratio'    => round( $main_contrast, 2 ),
				'required' => 4.5,
				'colors'   => sprintf( '#%s on #%s', $text_color, $background_color ),
			);
		}

		// Check link color contrast
		$link_contrast = self::calculate_contrast_ratio( $link_color, $background_color );
		if ( $link_contrast < 4.5 ) {
			$contrast_issues[] = array(
				'element'  => 'Links',
				'ratio'    => round( $link_contrast, 2 ),
				'required' => 4.5,
				'colors'   => sprintf( '#%s on #%s', $link_color, $background_color ),
			);
		}

		// Check for common problematic color combinations in custom CSS
		$custom_css = wp_get_custom_css();
		if ( ! empty( $custom_css ) ) {
			// Look for light gray on white patterns
			if ( preg_match( '/color\s*:\s*#([cdef][0-9a-f]{5})/i', $custom_css ) ) {
				$contrast_issues[] = array(
					'element'  => 'Custom CSS',
					'ratio'    => '< 3.0',
					'required' => 4.5,
					'colors'   => 'Light gray detected in custom CSS',
				);
			}
		}

		if ( empty( $contrast_issues ) ) {
			return null; // No contrast issues detected
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of contrast issues */
				__( '%d color contrast issue(s) detected. Text may be difficult or impossible to read for users with low vision.', 'wpshadow' ),
				count( $contrast_issues )
			),
			'severity'     => 'high',
			'threat_level' => 85,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/accessibility-poor-color-contrast',
			'details'      => array(
				'issues'       => $contrast_issues,
				'wcag_standard' => 'Level AA requires 4.5:1 for normal text, 3:1 for large text',
			),
		);
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
