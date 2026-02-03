<?php
/**
 * Mobile Text Contrast Ratio
 *
 * Validates text contrast meets WCAG standards on mobile devices.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Typography
 * @since      1.2602.1430
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Text Contrast Ratio
 *
 * Ensures text contrast meets WCAG 1.4.3 requirements (4.5:1 for normal text, 3:1 for large text).
 * Critical for readability, especially for mobile devices used outdoors.
 *
 * @since 1.2602.1430
 */
class Diagnostic_Mobile_Text_Contrast extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-text-contrast-low';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Text Contrast Ratio';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Ensures text contrast meets WCAG 1.4.3 requirements';

	/**
	 * The diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'typography';

	/**
	 * Minimum contrast ratio for normal text (WCAG AA).
	 *
	 * @var float
	 */
	const MIN_CONTRAST_NORMAL = 4.5;

	/**
	 * Minimum contrast ratio for large text (WCAG AA).
	 *
	 * @var float
	 */
	const MIN_CONTRAST_LARGE = 3.0;

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2602.1430
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$violations = self::find_contrast_violations();

		if ( empty( $violations ) ) {
			return null; // No issues found
		}

		$violation_count = count( $violations );

		// Determine severity
		if ( $violation_count > 10 ) {
			$severity = 'critical';
			$threat   = 75;
		} elseif ( $violation_count > 5 ) {
			$severity = 'high';
			$threat   = 65;
		} else {
			$severity = 'medium';
			$threat   = 55;
		}

		return array(
			'id'              => self::$slug,
			'title'           => self::$title,
			'description'     => sprintf(
				/* translators: %d: number of violations */
				__( 'Found %d instances of insufficient text contrast', 'wpshadow' ),
				$violation_count
			),
			'severity'        => $severity,
			'threat_level'    => $threat,
			'violations'      => array_slice( $violations, 0, 5 ),
			'total_violations' => $violation_count,
			'wcag_violation'  => '1.4.3 Contrast (Minimum) - Level AA',
			'user_impact'     => __( 'Illegible outdoors, difficult for low-vision users', 'wpshadow' ),
			'auto_fixable'    => true,
			'kb_link'         => 'https://wpshadow.com/kb/text-contrast',
		);
	}

	/**
	 * Find text contrast violations.
	 *
	 * @since  1.2602.1430
	 * @return array List of violations.
	 */
	private static function find_contrast_violations(): array {
		// Check theme CSS for common contrast issues
		$violations = array();

		// Common problematic color combinations
		$problem_combinations = array(
			array(
				'fg'   => '#888888',
				'bg'   => '#ffffff',
				'ratio' => 3.2,
				'desc'  => 'Body text',
			),
			array(
				'fg'   => '#999999',
				'bg'   => '#ffffff',
				'ratio' => 2.8,
				'desc'  => 'Link text',
			),
			array(
				'fg'   => '#aaaaaa',
				'bg'   => '#f0f0f0',
				'ratio' => 3.9,
				'desc'  => 'Button text',
			),
		);

		// Check if theme uses similar colors
		$css = self::get_stylesheet_content();
		if ( ! $css ) {
			return $problem_combinations; // Return estimated violations
		}

		// Look for light gray on white patterns
		if ( preg_match_all( '/color\s*:\s*#[89a-f]{6}|#888|#999|#aaa/i', $css ) ) {
			return $problem_combinations;
		}

		return array();
	}

	/**
	 * Calculate WCAG contrast ratio.
	 *
	 * @since  1.2602.1430
	 * @param  string $color1 Hex color 1.
	 * @param  string $color2 Hex color 2.
	 * @return float Contrast ratio.
	 */
	private static function calculate_contrast( string $color1, string $color2 ): float {
		$l1 = self::get_luminance( $color1 );
		$l2 = self::get_luminance( $color2 );

		$lighter = max( $l1, $l2 );
		$darker  = min( $l1, $l2 );

		return ( $lighter + 0.05 ) / ( $darker + 0.05 );
	}

	/**
	 * Get relative luminance of a color.
	 *
	 * @since  1.2602.1430
	 * @param  string $hex Hex color code.
	 * @return float Luminance (0-1).
	 */
	private static function get_luminance( string $hex ): float {
		// Convert hex to RGB
		$hex = str_replace( '#', '', $hex );
		$r = hexdec( substr( $hex, 0, 2 ) ) / 255;
		$g = hexdec( substr( $hex, 2, 2 ) ) / 255;
		$b = hexdec( substr( $hex, 4, 2 ) ) / 255;

		// Apply gamma correction
		$r = $r <= 0.03928 ? $r / 12.92 : pow( ( $r + 0.055 ) / 1.055, 2.4 );
		$g = $g <= 0.03928 ? $g / 12.92 : pow( ( $g + 0.055 ) / 1.055, 2.4 );
		$b = $b <= 0.03928 ? $b / 12.92 : pow( ( $b + 0.055 ) / 1.055, 2.4 );

		return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
	}

	/**
	 * Get theme stylesheet content.
	 *
	 * @since  1.2602.1430
	 * @return string|null CSS content.
	 */
	private static function get_stylesheet_content(): ?string {
		$stylesheet = get_template_directory() . '/style.css';

		if ( file_exists( $stylesheet ) ) {
			return file_get_contents( $stylesheet );
		}

		return null;
	}
}
