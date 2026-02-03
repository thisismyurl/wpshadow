<?php
/**
 * Diagnostic: Mobile Font Size - Body Text
 *
 * Validates minimum font size for body text (16px) to ensure readability
 * and prevent iOS auto-zoom.
 *
 * Issue: https://github.com/thisismyurl/wpshadow/issues/4029
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Mobile
 * @since      1.6034.1440
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Font Size Diagnostic
 *
 * Checks body text font size. Minimum 16px prevents iOS auto-zoom and ensures
 * readable text without manual zooming.
 *
 * @since 1.6034.1440
 */
class Diagnostic_Mobile_Font_Size extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-font-size';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Font Size - Body Text';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates minimum 16px body text font size to prevent iOS auto-zoom';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'mobile';

	/**
	 * Check mobile font sizes.
	 *
	 * Analyzes theme CSS for body text font sizes. iOS auto-zooms on input
	 * fields with <16px font size, creating poor UX.
	 *
	 * @since  1.6034.1440
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Get active theme.
		$theme      = wp_get_theme();
		$theme_root = get_theme_root();
		$theme_path = $theme_root . '/' . $theme->get_stylesheet();
		$issues     = array();

		// Check CSS files.
		$css_files = array();
		
		if ( file_exists( $theme_path . '/style.css' ) ) {
			$css_files[] = $theme_path . '/style.css';
		}

		$min_font_size = 999;
		$has_font_size = false;
		
		foreach ( $css_files as $css_file ) {
			$content = file_get_contents( $css_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			
			// Check body font size.
			$patterns = array(
				'/body\s*\{[^}]*font-size:\s*([0-9.]+)(px|rem|em)/i',
				'/html\s*\{[^}]*font-size:\s*([0-9.]+)(px|rem|em)/i',
				'/\.site-content\s*\{[^}]*font-size:\s*([0-9.]+)(px|rem|em)/i',
			);
			
			foreach ( $patterns as $pattern ) {
				if ( preg_match( $pattern, $content, $matches ) ) {
					$has_font_size = true;
					$size          = (float) $matches[1];
					$unit          = $matches[2];
					
					// Convert rem/em to approximate px (assuming 16px base).
					if ( 'rem' === $unit || 'em' === $unit ) {
						$size = $size * 16;
					}
					
					$min_font_size = min( $min_font_size, $size );
				}
			}
			
			// Check input field font sizes specifically.
			if ( preg_match( '/input.*\{[^}]*font-size:\s*([0-9.]+)px/i', $content, $matches ) ) {
				$input_size = (float) $matches[1];
				if ( $input_size < 16 ) {
					$issues[] = sprintf(
						/* translators: %s: pixel size */
						__( 'Input font size %spx triggers iOS auto-zoom', 'wpshadow' ),
						$input_size
					);
				}
			}
		}

		if ( ! $has_font_size ) {
			// No explicit font size - using browser default (typically 16px).
			return null;
		}

		// Check if body font size is too small.
		if ( $min_font_size < 16 ) {
			$threat_level = 70; // High severity.
			
			if ( $min_font_size < 14 ) {
				$threat_level = 80; // Very small text.
			}
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: minimum font size found */
					__(
						'Body text font size is too small (%.1fpx). Minimum recommended is 16px to prevent iOS auto-zoom on input focus and ensure mobile readability. Small text forces users to pinch-zoom.',
						'wpshadow'
					),
					$min_font_size
				),
				'severity'     => 'high',
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-font-size',
			);
		}

		// If we found input field issues.
		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: list of issues */
					__(
						'Input field font size issues detected: %s. Font sizes below 16px cause iOS to auto-zoom, disrupting mobile UX.',
						'wpshadow'
					),
					implode( '; ', $issues )
				),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-font-size',
			);
		}

		return null;
	}
}
