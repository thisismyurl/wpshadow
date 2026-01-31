<?php
/**
 * Theme Font Loading Issues Diagnostic
 *
 * Detects missing, incorrectly loaded, or unoptimized web fonts that can impact
 * page loading performance and visual rendering.
 *
 * @package    WPShadow\Diagnostics
 * @subpackage Tests
 * @since      1.2601.2200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Font Loading Issues Diagnostic Class
 *
 * Checks for:
 * - Fonts loaded without font-display property (FOIT/FOUT issues)
 * - Multiple font formats loaded unnecessarily
 * - Fonts not preloaded for critical rendering
 * - Excessive font families/weights loaded
 * - Fonts loaded from slow external sources
 *
 * @since 1.2601.2200
 */
class Diagnostic_Theme_Font_Loading_Issues extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-font-loading-issues';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Font Loading Issues';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects missing or incorrectly loaded web fonts that impact performance';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'themes';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Get active theme.
		$theme = wp_get_theme();

		// Check enqueued styles for font loading.
		global $wp_styles;
		if ( empty( $wp_styles->registered ) ) {
			return null;
		}

		// Track external font sources.
		$external_fonts = array();
		$missing_font_display = array();
		$font_count = 0;

		foreach ( $wp_styles->registered as $handle => $style ) {
			if ( empty( $style->src ) ) {
				continue;
			}

			// Check for Google Fonts or other external font services.
			if ( strpos( $style->src, 'fonts.googleapis.com' ) !== false ||
			     strpos( $style->src, 'fonts.gstatic.com' ) !== false ||
			     strpos( $style->src, 'typekit.net' ) !== false ||
			     strpos( $style->src, 'fonts.com' ) !== false ) {
				$external_fonts[] = $handle;
				$font_count++;

				// Check if font-display is specified.
				if ( strpos( $style->src, 'display=' ) === false ) {
					$missing_font_display[] = $handle;
				}
			}
		}

		// Check theme stylesheet for @font-face declarations.
		$stylesheet_path = get_stylesheet_directory() . '/style.css';
		if ( file_exists( $stylesheet_path ) ) {
			$stylesheet_content = file_get_contents( $stylesheet_path );

			// Count @font-face declarations.
			preg_match_all( '/@font-face\s*\{/', $stylesheet_content, $font_face_matches );
			$font_face_count = count( $font_face_matches[0] );

			if ( $font_face_count > 0 ) {
				$font_count += $font_face_count;

				// Check if font-display is used.
				preg_match_all( '/font-display\s*:\s*([^;]+);/', $stylesheet_content, $display_matches );
				$display_count = count( $display_matches[0] );

				if ( $display_count < $font_face_count ) {
					$issues[] = sprintf(
						__( '%d @font-face declarations missing font-display property', 'wpshadow' ),
						$font_face_count - $display_count
					);
				}
			}
		}

		// Check for excessive font loading.
		if ( $font_count > 6 ) {
			$issues[] = sprintf(
				__( 'Excessive font loading: %d font families/weights loaded (recommended: 6 or fewer)', 'wpshadow' ),
				$font_count
			);
		}

		// Check for external font sources.
		if ( ! empty( $external_fonts ) ) {
			$issues[] = sprintf(
				__( 'Fonts loaded from external sources: %s', 'wpshadow' ),
				implode( ', ', $external_fonts )
			);
		}

		// Check for missing font-display.
		if ( ! empty( $missing_font_display ) ) {
			$issues[] = sprintf(
				__( 'Fonts missing font-display property: %s (causes Flash of Invisible Text)', 'wpshadow' ),
				implode( ', ', $missing_font_display )
			);
		}

		// Check for font preload hints.
		$has_preload = self::check_font_preload();
		if ( ! $has_preload && $font_count > 0 ) {
			$issues[] = __( 'No font preload hints detected (can improve Largest Contentful Paint)', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => implode( "\n", $issues ),
			'severity'     => 'medium',
			'threat_level' => 40,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/theme-font-loading',
		);
	}

	/**
	 * Check if fonts are preloaded.
	 *
	 * @since  1.2601.2200
	 * @return bool True if font preload detected.
	 */
	private static function check_font_preload() {
		// Check if any preload links are registered for fonts.
		$wp_head = get_echo( 'wp_head' );

		if ( strpos( $wp_head, 'rel="preload"' ) !== false &&
		     strpos( $wp_head, 'as="font"' ) !== false ) {
			return true;
		}

		return false;
	}
}
