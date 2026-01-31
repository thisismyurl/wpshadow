<?php
/**
 * WCAG Color Contrast Validation Diagnostic
 *
 * Verifies text elements meet WCAG AA/AAA contrast ratio requirements (4.5:1 / 7:1).
 * Fetches homepage and sample pages, extracts colors, calculates contrast ratios.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6028.1740
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WCAG Contrast Validation Diagnostic Class
 *
 * Analyzes page colors and calculates WCAG contrast ratios to ensure
 * accessibility compliance for visually impaired users.
 *
 * @since 1.6028.1740
 */
class Diagnostic_WCAG_Contrast extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.6028.1740
	 * @var   string
	 */
	protected static $slug = 'wcag-color-contrast';

	/**
	 * The diagnostic title
	 *
	 * @since 1.6028.1740
	 * @var   string
	 */
	protected static $title = 'WCAG Color Contrast Validation';

	/**
	 * The diagnostic description
	 *
	 * @since 1.6028.1740
	 * @var   string
	 */
	protected static $description = 'Verifies text contrast meets WCAG AA/AAA accessibility standards';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.6028.1740
	 * @var   string
	 */
	protected static $family = 'design';

	/**
	 * Cache duration in seconds (12 hours)
	 *
	 * @since 1.6028.1740
	 */
	private const CACHE_DURATION = 43200;

	/**
	 * WCAG AA contrast requirements
	 *
	 * @since 1.6028.1740
	 */
	private const WCAG_AA_NORMAL = 4.5;
	private const WCAG_AA_LARGE  = 3.0;
	private const WCAG_AAA_NORMAL = 7.0;
	private const WCAG_AAA_LARGE  = 4.5;

	/**
	 * Run the diagnostic check
	 *
	 * @since  1.6028.1740
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		// Check cache first
		$cache_key = 'wpshadow_diagnostic_wcag_contrast';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			if ( null === $cached ) {
				return null;
			}
			return self::build_finding( $cached );
		}

		// Analyze contrast ratios
		$analysis = self::analyze_contrast_ratios();

		// Cache result
		set_transient( $cache_key, $analysis, self::CACHE_DURATION );

		if ( null === $analysis ) {
			return null;
		}

		return self::build_finding( $analysis );
	}

	/**
	 * Analyze contrast ratios on site pages
	 *
	 * @since  1.6028.1740
	 * @return array|null Analysis results or null if compliant.
	 */
	private static function analyze_contrast_ratios() {
		// Get pages to check
		$pages_to_check = array(
			array(
				'url'  => home_url( '/' ),
				'name' => 'Homepage',
			),
		);

		// Add sample post/page if available
		$sample_post = get_posts( array( 'numberposts' => 1, 'post_type' => 'post' ) );
		if ( ! empty( $sample_post ) ) {
			$pages_to_check[] = array(
				'url'  => get_permalink( $sample_post[0] ),
				'name' => 'Sample Post',
			);
		}

		$all_violations = array();
		$total_elements_checked = 0;

		foreach ( $pages_to_check as $page ) {
			$violations = self::check_page_contrast( $page['url'], $page['name'] );
			
			if ( ! empty( $violations['violations'] ) ) {
				$all_violations = array_merge( $all_violations, $violations['violations'] );
			}
			
			$total_elements_checked += $violations['elements_checked'];
		}

		// No violations found
		if ( empty( $all_violations ) ) {
			return null;
		}

		// Calculate compliance
		$compliance_percentage = $total_elements_checked > 0
			? ( ( $total_elements_checked - count( $all_violations ) ) / $total_elements_checked ) * 100
			: 100;

		return array(
			'total_violations'      => count( $all_violations ),
			'total_elements'        => $total_elements_checked,
			'compliance_percentage' => round( $compliance_percentage, 1 ),
			'violations'            => array_slice( $all_violations, 0, 15 ), // Top 15
		);
	}

	/**
	 * Check contrast ratios on a specific page
	 *
	 * @since  1.6028.1740
	 * @param  string $url  Page URL.
	 * @param  string $name Page name.
	 * @return array Violations and element count.
	 */
	private static function check_page_contrast( string $url, string $name ): array {
		// Fetch page HTML
		$response = wp_remote_get( $url, array(
			'timeout'    => 10,
			'user-agent' => 'WPShadow-Accessibility-Check',
		) );

		if ( is_wp_error( $response ) ) {
			return array(
				'violations'       => array(),
				'elements_checked' => 0,
			);
		}

		$html = wp_remote_retrieve_body( $response );
		if ( empty( $html ) ) {
			return array(
				'violations'       => array(),
				'elements_checked' => 0,
			);
		}

		// Parse HTML for text elements with inline styles
		$violations = array();
		$elements_checked = 0;

		// Common text selectors with color patterns
		$color_patterns = array(
			'/style=["\']([^"\']*color:\s*([^;"\'\s]+)[^"\']*)["\']/i',
			'/style=["\']([^"\']*background(?:-color)?:\s*([^;"\'\s]+)[^"\']*)["\']/i',
		);

		foreach ( $color_patterns as $pattern ) {
			preg_match_all( $pattern, $html, $matches, PREG_SET_ORDER );

			foreach ( $matches as $match ) {
				++$elements_checked;

				// Extract colors from style attribute
				$style = $match[1];
				$colors = self::extract_colors_from_style( $style );

				if ( empty( $colors['foreground'] ) || empty( $colors['background'] ) ) {
					continue;
				}

				// Calculate contrast ratio
				$ratio = self::calculate_contrast_ratio( $colors['foreground'], $colors['background'] );

				// Check if it meets WCAG AA (4.5:1 for normal text)
				if ( $ratio < self::WCAG_AA_NORMAL ) {
					$violations[] = array(
						'page'             => $name,
						'foreground'       => $colors['foreground'],
						'background'       => $colors['background'],
						'contrast_ratio'   => round( $ratio, 2 ),
						'wcag_aa_required' => self::WCAG_AA_NORMAL,
						'wcag_aaa_required' => self::WCAG_AAA_NORMAL,
						'passes_aa'        => false,
						'passes_aaa'       => false,
					);
				}
			}
		}

		return array(
			'violations'       => $violations,
			'elements_checked' => $elements_checked,
		);
	}

	/**
	 * Extract foreground and background colors from style attribute
	 *
	 * @since  1.6028.1740
	 * @param  string $style Style attribute value.
	 * @return array Foreground and background colors.
	 */
	private static function extract_colors_from_style( string $style ): array {
		$colors = array(
			'foreground' => '',
			'background' => '',
		);

		// Extract color (foreground)
		if ( preg_match( '/color:\s*([^;]+)/i', $style, $fg_match ) ) {
			$colors['foreground'] = trim( $fg_match[1] );
		}

		// Extract background-color
		if ( preg_match( '/background(?:-color)?:\s*([^;]+)/i', $style, $bg_match ) ) {
			$colors['background'] = trim( $bg_match[1] );
		}

		return $colors;
	}

	/**
	 * Calculate contrast ratio between two colors
	 *
	 * @since  1.6028.1740
	 * @param  string $fg Foreground color.
	 * @param  string $bg Background color.
	 * @return float Contrast ratio.
	 */
	private static function calculate_contrast_ratio( string $fg, string $bg ): float {
		$l1 = self::get_relative_luminance( $fg );
		$l2 = self::get_relative_luminance( $bg );

		// Lighter color should be L1
		if ( $l2 > $l1 ) {
			list( $l1, $l2 ) = array( $l2, $l1 );
		}

		// Contrast ratio formula: (L1 + 0.05) / (L2 + 0.05)
		return ( $l1 + 0.05 ) / ( $l2 + 0.05 );
	}

	/**
	 * Get relative luminance of a color
	 *
	 * @since  1.6028.1740
	 * @param  string $color Color value (hex, rgb, or named).
	 * @return float Relative luminance (0-1).
	 */
	private static function get_relative_luminance( string $color ): float {
		$rgb = self::parse_color_to_rgb( $color );

		if ( null === $rgb ) {
			return 0.5; // Default medium luminance
		}

		// Convert RGB to relative luminance
		$r = self::linearize_rgb_component( $rgb[0] / 255 );
		$g = self::linearize_rgb_component( $rgb[1] / 255 );
		$b = self::linearize_rgb_component( $rgb[2] / 255 );

		// Relative luminance formula
		return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
	}

	/**
	 * Parse color string to RGB array
	 *
	 * @since  1.6028.1740
	 * @param  string $color Color value.
	 * @return array|null RGB array [r, g, b] or null.
	 */
	private static function parse_color_to_rgb( string $color ) {
		$color = trim( strtolower( $color ) );

		// Hex color (#fff or #ffffff)
		if ( '#' === $color[0] ) {
			$hex = ltrim( $color, '#' );
			
			// Short hex (#fff)
			if ( 3 === strlen( $hex ) ) {
				$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
			}

			if ( 6 === strlen( $hex ) ) {
				return array(
					hexdec( substr( $hex, 0, 2 ) ),
					hexdec( substr( $hex, 2, 2 ) ),
					hexdec( substr( $hex, 4, 2 ) ),
				);
			}
		}

		// RGB color rgb(255, 255, 255)
		if ( preg_match( '/rgb\s*\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*\)/i', $color, $matches ) ) {
			return array(
				(int) $matches[1],
				(int) $matches[2],
				(int) $matches[3],
			);
		}

		// Named colors (simplified set)
		$named_colors = array(
			'white'  => array( 255, 255, 255 ),
			'black'  => array( 0, 0, 0 ),
			'red'    => array( 255, 0, 0 ),
			'green'  => array( 0, 128, 0 ),
			'blue'   => array( 0, 0, 255 ),
			'gray'   => array( 128, 128, 128 ),
			'grey'   => array( 128, 128, 128 ),
		);

		return $named_colors[ $color ] ?? null;
	}

	/**
	 * Linearize RGB component for luminance calculation
	 *
	 * @since  1.6028.1740
	 * @param  float $component RGB component (0-1).
	 * @return float Linearized value.
	 */
	private static function linearize_rgb_component( float $component ): float {
		if ( $component <= 0.03928 ) {
			return $component / 12.92;
		}

		return pow( ( $component + 0.055 ) / 1.055, 2.4 );
	}

	/**
	 * Build finding array
	 *
	 * @since  1.6028.1740
	 * @param  array $analysis Analysis results.
	 * @return array Finding array.
	 */
	private static function build_finding( array $analysis ): array {
		$compliance = $analysis['compliance_percentage'];
		$violations = $analysis['total_violations'];

		// High severity for accessibility issues
		if ( $compliance >= 95 ) {
			$severity = 'medium';
			$threat_level = 50;
		} elseif ( $compliance >= 85 ) {
			$severity = 'high';
			$threat_level = 65;
		} else {
			$severity = 'high';
			$threat_level = 75;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: 1: number of violations, 2: compliance percentage */
				__( 'Found %1$d WCAG contrast violations. Contrast compliance: %2$.1f%%.', 'wpshadow' ),
				$violations,
				$compliance
			),
			'severity'    => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false, // Requires design changes
			'kb_link'     => 'https://wpshadow.com/kb/design-wcag-contrast',
			'family'      => self::$family,
			'meta'        => array(
				'total_violations'      => $analysis['total_violations'],
				'total_elements'        => $analysis['total_elements'],
				'compliance_percentage' => $compliance,
				'wcag_aa_required'      => self::WCAG_AA_NORMAL,
				'wcag_aaa_required'     => self::WCAG_AAA_NORMAL,
			),
			'details'     => array(
				'violations'      => $analysis['violations'],
				'recommendations' => array(
					__( 'Use high-contrast color combinations (4.5:1 minimum for normal text)', 'wpshadow' ),
					__( 'Test color choices with accessibility tools before deployment', 'wpshadow' ),
					__( 'Avoid light gray text on white backgrounds', 'wpshadow' ),
					__( 'Consider color blindness when choosing color schemes', 'wpshadow' ),
					__( 'Use dark text on light backgrounds or vice versa for maximum readability', 'wpshadow' ),
				),
			),
		);
	}
}
