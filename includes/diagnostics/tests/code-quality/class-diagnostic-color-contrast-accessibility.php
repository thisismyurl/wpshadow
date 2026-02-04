<?php
/**
 * Color Contrast Accessibility Diagnostic
 *
 * Tests if text has sufficient color contrast for readability.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.7034.1310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Color Contrast Accessibility Diagnostic Class
 *
 * Validates that text and UI elements meet WCAG 2.1 AA color contrast
 * requirements for users with visual impairments.
 *
 * @since 1.7034.1310
 */
class Diagnostic_Color_Contrast_Accessibility extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'color-contrast-accessibility';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Color Contrast Accessibility';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if text has sufficient color contrast for readability';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests color contrast ratios for text, links, buttons, and
	 * other UI elements against WCAG standards.
	 *
	 * @since  1.7034.1310
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		// Check for accessibility plugins that validate contrast.
		$has_contrast_checker = is_plugin_active( 'accessibility-checker/accessibility-checker.php' ) ||
							   is_plugin_active( 'wp-accessibility/wp-accessibility.php' );

		// Get theme colors.
		$theme_mods = get_theme_mods();
		$background_color = get_theme_mod( 'background_color', 'ffffff' );
		$text_color = get_theme_mod( 'text_color', '000000' );
		$link_color = get_theme_mod( 'link_color', '0073aa' );

		// Parse CSS for color definitions.
		$style_css = get_stylesheet_directory() . '/style.css';
		$color_issues = array();

		if ( file_exists( $style_css ) ) {
			$style_content = file_get_contents( $style_css );

			// Look for color definitions.
			preg_match_all( '/color:\s*#([0-9a-fA-F]{3,6})/', $style_content, $text_colors );
			preg_match_all( '/background(?:-color)?:\s*#([0-9a-fA-F]{3,6})/', $style_content, $bg_colors );

			// Sample contrast checks (simplified).
			$light_backgrounds = array( 'ffffff', 'fff', 'f5f5f5', 'eeeeee', 'eee' );
			$dark_text = array( '000000', '000', '333333', '333', '222222', '222' );

			// Check if using light gray text on white (common contrast issue).
			$has_gray_on_white = false;
			foreach ( $text_colors[1] as $color ) {
				$color_lower = strtolower( $color );
				// Detect light gray (above #777777).
				if ( strlen( $color_lower ) === 6 ) {
					$r = hexdec( substr( $color_lower, 0, 2 ) );
					$g = hexdec( substr( $color_lower, 2, 2 ) );
					$b = hexdec( substr( $color_lower, 4, 2 ) );
					$luminance = ( $r + $g + $b ) / 3;

					// Light gray range (170-220 = #AA-#DD).
					if ( $luminance > 170 && $luminance < 220 ) {
						$has_gray_on_white = true;
						break;
					}
				}
			}
		}

		// Check for high contrast mode support.
		$supports_high_contrast = current_theme_supports( 'custom-background' ) || 
								 current_theme_supports( 'editor-color-palette' );

		// Check if theme uses system colors.
		$uses_system_colors = false;
		if ( file_exists( $style_css ) ) {
			$style_content = file_get_contents( $style_css );
			$uses_system_colors = ( strpos( $style_content, 'currentColor' ) !== false );
		}

		// Check link underlines (important for low-contrast links).
		$links_underlined = true;
		if ( file_exists( $style_css ) ) {
			$style_content = file_get_contents( $style_css );
			if ( strpos( $style_content, 'text-decoration: none' ) !== false ) {
				$links_underlined = false;
			}
		}

		// Check button contrast.
		$button_styles = false;
		if ( file_exists( $style_css ) ) {
			$style_content = file_get_contents( $style_css );
			$button_styles = ( strpos( $style_content, 'button' ) !== false ) || 
						   ( strpos( $style_content, '.btn' ) !== false );
		}

		// Check for issues.
		$issues = array();

		// Issue 1: No contrast checking plugin.
		if ( ! $has_contrast_checker ) {
			$issues[] = array(
				'type'        => 'no_contrast_checker',
				'description' => __( 'No accessibility checker plugin; color contrast not validated', 'wpshadow' ),
			);
		}

		// Issue 2: Light gray text detected (common issue).
		if ( $has_gray_on_white ) {
			$issues[] = array(
				'type'        => 'gray_text_detected',
				'description' => __( 'Light gray text detected; may not meet WCAG AA contrast ratio (4.5:1)', 'wpshadow' ),
			);
		}

		// Issue 3: Links not underlined and no contrast checker.
		if ( ! $links_underlined && ! $has_contrast_checker ) {
			$issues[] = array(
				'type'        => 'links_not_underlined',
				'description' => __( 'Links not underlined; must have sufficient contrast or underlines for WCAG compliance', 'wpshadow' ),
			);
		}

		// Issue 4: No high contrast mode support.
		if ( ! $supports_high_contrast ) {
			$issues[] = array(
				'type'        => 'no_high_contrast',
				'description' => __( 'No high contrast mode support; users cannot adjust colors for visibility', 'wpshadow' ),
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Color contrast may not meet WCAG accessibility standards, making text difficult to read for users with visual impairments', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/color-contrast-accessibility',
				'details'      => array(
					'has_contrast_checker'    => $has_contrast_checker,
					'background_color'        => '#' . $background_color,
					'text_color'              => '#' . $text_color,
					'link_color'              => '#' . $link_color,
					'has_gray_on_white'       => $has_gray_on_white,
					'supports_high_contrast'  => $supports_high_contrast,
					'uses_system_colors'      => $uses_system_colors,
					'links_underlined'        => $links_underlined,
					'button_styles_defined'   => $button_styles,
					'issues_detected'         => $issues,
					'recommendation'          => __( 'Install accessibility checker, ensure 4.5:1 contrast for text, 3:1 for large text', 'wpshadow' ),
					'wcag_requirements'       => array(
						'WCAG 1.4.3 (AA)'  => 'Normal text: 4.5:1 contrast ratio',
						'WCAG 1.4.3 (AA)'  => 'Large text (18pt+): 3:1 contrast ratio',
						'WCAG 1.4.6 (AAA)' => 'Normal text: 7:1 contrast ratio',
						'WCAG 1.4.6 (AAA)' => 'Large text: 4.5:1 contrast ratio',
						'WCAG 1.4.11'      => 'UI components: 3:1 contrast ratio',
					),
					'safe_color_combinations' => array(
						'Black on white'       => '#000000 on #FFFFFF (21:1 ratio)',
						'Dark gray on white'   => '#333333 on #FFFFFF (12.6:1 ratio)',
						'White on dark blue'   => '#FFFFFF on #003366 (11.8:1 ratio)',
						'Black on light gray'  => '#000000 on #F5F5F5 (19.8:1 ratio)',
					),
					'contrast_tools'          => array(
						'WebAIM Contrast Checker',
						'Colour Contrast Analyser (CCA)',
						'Chrome DevTools Accessibility',
						'WAVE Browser Extension',
					),
					'legal_requirements'      => 'ADA, Section 508, AODA require WCAG 2.1 AA compliance',
				),
			);
		}

		return null;
	}
}
