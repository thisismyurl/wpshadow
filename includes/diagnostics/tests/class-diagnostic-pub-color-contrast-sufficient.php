<?php
/**
 * Diagnostic: Pub Color Contrast Sufficient
 *
 * Checks if published content has sufficient color contrast according to
 * WCAG AA standards (4.5:1 for normal text). Analyzes inline styles in
 * published posts and pages to identify accessibility issues.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;


/**
 * Pub Color Contrast Sufficient Diagnostic
 *
 * Validates color contrast ratios in published content to ensure
 * compliance with WCAG AA accessibility standards.
 */
class Diagnostic_Pub_Color_Contrast_Sufficient extends Diagnostic_Base {
	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'pub-color-contrast-sufficient';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Color Contrast Sufficient';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if published content has sufficient color contrast according to WCAG AA standards (4.5:1 ratio).';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * The family label
	 *
	 * @var string
	 */
	protected static $family_label = 'Accessibility';

	/**
	 * Get diagnostic ID
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic identifier.
	 */
	public static function get_id(): string {
		return 'pub-color-contrast-sufficient';
	}

	/**
	 * Get diagnostic name
	 *
	 * @since  1.2601.2148
	 * @return string Human-readable diagnostic name.
	 */
	public static function get_name(): string {
		return __( 'Color Contrast Sufficient', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic description.
	 */
	public static function get_description(): string {
		return __( 'Text has WCAG AA contrast ratio?', 'wpshadow' );
	}

	/**
	 * Get diagnostic category
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic category.
	 */
	public static function get_category(): string {
		return 'content_publishing';
	}

	/**
	 * Get threat level
	 *
	 * @since  1.2601.2148
	 * @return int 0-100 severity level
	 */
	public static function get_threat_level(): int {
		return 45;
	}

	/**
	 * Run diagnostic test
	 *
	 * Legacy method for backward compatibility.
	 * Use check() for actual diagnostic logic.
	 *
	 * @since  1.2601.2148
	 * @return array Diagnostic results
	 */
	public static function run(): array {
		$result = self::check();

		if ( is_null( $result ) ) {
			return array(
				'status'  => 'pass',
				'message' => __( 'All published content has sufficient color contrast', 'wpshadow' ),
				'data'    => array(),
			);
		}

		return array(
			'status'  => 'fail',
			'message' => $result['description'],
			'data'    => $result,
		);
	}

	/**
	 * Get KB article URL
	 *
	 * @since  1.2601.2148
	 * @return string Knowledge base article URL.
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/pub-color-contrast-sufficient';
	}

	/**
	 * Get training video URL
	 *
	 * @since  1.2601.2148
	 * @return string Training video URL.
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/category-content-publishing';
	}

	/**
	 * Get HTML content from POST request (Guardian mode)
	 *
	 * This method is used in Guardian mode where HTML content is
	 * provided via POST for analysis. Nonce verification should be
	 * performed by the calling context before invoking this diagnostic.
	 *
	 * @since  1.2601.2148
	 * @return string HTML content if provided, empty string otherwise.
	 */
	protected static function get_guardian_html(): string {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verification handled by Guardian context
		if ( isset( $_POST['html'] ) && is_string( $_POST['html'] ) ) {
			return sanitize_textarea_field( wp_unslash( $_POST['html'] ) );
		}
		// phpcs:enable WordPress.Security.NonceVerification.Missing
		return '';
	}

	/**
	 * Get HTML content from recent published posts
	 *
	 * Retrieves content from the 5 most recently published posts/pages.
	 *
	 * @since  1.2601.2148
	 * @return string Combined HTML content from recent posts.
	 */
	protected static function get_recent_published_content_html(): string {
		$recent_posts = get_posts(
			array(
				'post_type'      => array( 'post', 'page' ),
				'post_status'    => 'publish',
				'posts_per_page' => 5,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		if ( empty( $recent_posts ) ) {
			return '';
		}

		$html = '';
		foreach ( $recent_posts as $post ) {
			$html .= apply_filters( 'the_content', $post->post_content );
		}

		return $html;
	}

	/**
	 * Analyze HTML for color contrast issues
	 *
	 * Checks inline styles for color/background-color combinations
	 * and validates against WCAG AA standards.
	 *
	 * @since  1.2601.2148
	 * @param  string $html HTML content to analyze.
	 * @return array List of contrast issues found.
	 */
	protected static function analyze_color_contrast( string $html ): array {
		$issues = array();

		if ( empty( $html ) ) {
			return $issues;
		}

		try {
			$dom = new \DOMDocument();
			// Suppress warnings for malformed HTML.
			libxml_use_internal_errors( true );
			$dom->loadHTML( '<?xml encoding="UTF-8">' . $html );
			libxml_clear_errors();

			$xpath = new \DOMXPath( $dom );

			// Find elements with inline styles containing color properties
			$elements_with_style = $xpath->query( '//*[@style]' );

			if ( ! $elements_with_style || 0 === $elements_with_style->length ) {
				// No inline styles found - this is generally good practice
				return $issues;
			}

			$elements_checked = 0;
			foreach ( $elements_with_style as $element ) {
				$style = $element->getAttribute( 'style' );

				// Extract color and background-color values
				$color      = self::extract_color_from_style( $style, 'color' );
				$background = self::extract_color_from_style( $style, 'background-color' );

				// Only check if both colors are specified
				if ( $color && $background ) {
					$ratio = self::calculate_contrast_ratio( $color, $background );

					// WCAG AA requires 4.5:1 for normal text, 3:1 for large text (18pt+)
					// We'll use the stricter 4.5:1 requirement
					if ( $ratio < 4.5 ) {
						$issues[] = sprintf(
							/* translators: 1: foreground color, 2: background color, 3: contrast ratio */
							__( 'Insufficient contrast: %1$s on %2$s (ratio: %3$s:1, requires 4.5:1)', 'wpshadow' ),
							esc_html( $color ),
							esc_html( $background ),
							esc_html( number_format( $ratio, 2 ) )
						);
					}
					++$elements_checked;
				}
			}

			// If we checked elements and found none with issues, that's good
			// If we didn't check any elements with both colors, we can't flag issues
		} catch ( \Exception $e ) {
			// Silently handle parsing errors - not a contrast issue per se
			return array();
		}

		return $issues;
	}

	/**
	 * Extract color value from inline style string
	 *
	 * @since  1.2601.2148
	 * @param  string $style    Inline style attribute value.
	 * @param  string $property CSS property name (color or background-color).
	 * @return string|null Color value if found, null otherwise.
	 */
	protected static function extract_color_from_style( string $style, string $property ): ?string {
		// Match property: value pattern
		$pattern = '/' . preg_quote( $property, '/' ) . '\s*:\s*([^;]+)/i';
		if ( preg_match( $pattern, $style, $matches ) ) {
			return trim( $matches[1] );
		}
		return null;
	}

	/**
	 * Calculate contrast ratio between two colors
	 *
	 * Implements WCAG contrast ratio formula.
	 *
	 * @since  1.2601.2148
	 * @param  string $color1 First color (hex, rgb, or named).
	 * @param  string $color2 Second color (hex, rgb, or named).
	 * @return float Contrast ratio (1-21).
	 */
	protected static function calculate_contrast_ratio( string $color1, string $color2 ): float {
		$luminance1 = self::get_relative_luminance( $color1 );
		$luminance2 = self::get_relative_luminance( $color2 );

		// Ensure lighter color is in numerator
		$lighter = max( $luminance1, $luminance2 );
		$darker  = min( $luminance1, $luminance2 );

		// WCAG contrast ratio formula: (L1 + 0.05) / (L2 + 0.05)
		return ( $lighter + 0.05 ) / ( $darker + 0.05 );
	}

	/**
	 * Get relative luminance of a color
	 *
	 * Converts color to RGB and calculates relative luminance per WCAG formula.
	 *
	 * @since  1.2601.2148
	 * @param  string $color Color value (hex, rgb, or named).
	 * @return float Relative luminance (0-1).
	 */
	protected static function get_relative_luminance( string $color ): float {
		// Convert to RGB array
		$rgb = self::color_to_rgb( $color );

		if ( ! $rgb ) {
			// Default to middle gray if conversion fails
			return 0.5;
		}

		// Convert RGB to sRGB
		$r = $rgb[0] / 255.0;
		$g = $rgb[1] / 255.0;
		$b = $rgb[2] / 255.0;

		// Apply gamma correction
		$r = ( $r <= 0.03928 ) ? $r / 12.92 : pow( ( $r + 0.055 ) / 1.055, 2.4 );
		$g = ( $g <= 0.03928 ) ? $g / 12.92 : pow( ( $g + 0.055 ) / 1.055, 2.4 );
		$b = ( $b <= 0.03928 ) ? $b / 12.92 : pow( ( $b + 0.055 ) / 1.055, 2.4 );

		// Calculate relative luminance
		return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
	}

	/**
	 * Convert color string to RGB array
	 *
	 * Handles hex (#fff, #ffffff), rgb(r,g,b), and named colors.
	 *
	 * @since  1.2601.2148
	 * @param  string $color Color value.
	 * @return array|null RGB array [r, g, b] or null on failure.
	 */
	protected static function color_to_rgb( string $color ): ?array {
		$color = trim( $color );

		// Handle hex colors
		if ( '#' === $color[0] ) {
			$hex = ltrim( $color, '#' );

			// Convert 3-digit hex to 6-digit
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

		// Handle rgb() format
		if ( preg_match( '/rgb\s*\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*\)/i', $color, $matches ) ) {
			return array(
				(int) $matches[1],
				(int) $matches[2],
				(int) $matches[3],
			);
		}

		// Handle common named colors
		$named_colors = array(
			'black'   => array( 0, 0, 0 ),
			'white'   => array( 255, 255, 255 ),
			'red'     => array( 255, 0, 0 ),
			'green'   => array( 0, 128, 0 ),
			'blue'    => array( 0, 0, 255 ),
			'yellow'  => array( 255, 255, 0 ),
			'gray'    => array( 128, 128, 128 ),
			'grey'    => array( 128, 128, 128 ),
			'silver'  => array( 192, 192, 192 ),
			'orange'  => array( 255, 165, 0 ),
			'purple'  => array( 128, 0, 128 ),
			'pink'    => array( 255, 192, 203 ),
			'brown'   => array( 165, 42, 42 ),
			'navy'    => array( 0, 0, 128 ),
			'teal'    => array( 0, 128, 128 ),
			'olive'   => array( 128, 128, 0 ),
			'maroon'  => array( 128, 0, 0 ),
			'lime'    => array( 0, 255, 0 ),
			'aqua'    => array( 0, 255, 255 ),
			'fuchsia' => array( 255, 0, 255 ),
		);

		$color_lower = strtolower( $color );
		if ( isset( $named_colors[ $color_lower ] ) ) {
			return $named_colors[ $color_lower ];
		}

		return null;
	}

	/**
	 * Check color contrast in published content
	 *
	 * Analyzes published posts and pages for WCAG AA color contrast compliance.
	 * Checks text foreground/background color combinations to ensure readability.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if insufficient contrast found, null otherwise.
	 */
	public static function check(): ?array {
		// Get HTML content to analyze from POST or use recent published content
		$html = self::get_guardian_html();

		// If no HTML provided, check recent published posts
		if ( empty( $html ) ) {
			$html = self::get_recent_published_content_html();
		}

		// Skip check if no content available
		if ( empty( $html ) ) {
			return null;
		}

		// Parse HTML and check for color contrast issues
		$issues = self::analyze_color_contrast( $html );

		// Return null if no issues found
		if ( empty( $issues ) ) {
			return null;
		}

		// Build and return finding
		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Published content contains text with insufficient color contrast. This affects readability and accessibility.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => self::get_threat_level(),
			'auto_fixable' => false,
			'kb_link'      => self::get_kb_article(),
			'category'     => self::get_category(),
			'details'      => $issues,
		);
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Pub Color Contrast Sufficient
	 * Slug: pub-color-contrast-sufficient
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Automatically initialized lean diagnostic for Pub Color Contrast Sufficient. Optimized for minimal overhead while surfacing high-value signals.
	 *
	 * @since  1.2601.2148
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_pub_color_contrast_sufficient(): array {
		// Test with good contrast (black on white)
		$good_html = '<html><body><p style="color: #000000; background-color: #ffffff;">High contrast text</p></body></html>';

		// Test with poor contrast (light gray on white)
		$bad_html = '<html><body><p style="color: #cccccc; background-color: #ffffff;">Poor contrast text</p></body></html>';

		// Test good contrast scenario
		$_POST['html'] = $good_html;
		$result_good   = self::check();

		// Test bad contrast scenario
		$_POST['html'] = $bad_html;
		$result_bad    = self::check();

		// Clean up
		unset( $_POST['html'] );

		// Check if test behaves as expected
		$good_passes = is_null( $result_good );
		$bad_fails   = is_array( $result_bad ) && isset( $result_bad['id'] );

		$passed = $good_passes && $bad_fails;

		$message = $passed
			? 'Color contrast diagnostic working correctly: Good contrast passes, poor contrast flagged'
			: sprintf(
				'Color contrast diagnostic not working correctly. Good contrast: %s, Poor contrast: %s',
				$good_passes ? 'passed' : 'failed',
				$bad_fails ? 'flagged' : 'not flagged'
			);

		return array(
			'passed'  => $passed,
			'message' => $message,
		);
	}
}
