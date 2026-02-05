<?php
/**
 * Treatment: Tap Target Size Validation
 *
 * Validates that interactive elements meet minimum size requirements (44×44px)
 * for mobile touch interaction as per WCAG 2.5.5 and Apple/Material Design guidelines.
 *
 * Issue: https://github.com/thisismyurl/wpshadow/issues/4026
 *
 * @package    WPShadow
 * @subpackage Treatments\Mobile
 * @since      1.6034.1440
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tap Target Size Treatment
 *
 * Checks if interactive elements are large enough for mobile tapping.
 * Minimum 44×44px per WCAG 2.5.5, Apple HIG. Recommended 48×48px (Material Design).
 *
 * @since 1.6034.1440
 */
class Treatment_Mobile_Tap_Target_Size extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-tap-target-size';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Tap Target Size Validation';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates interactive elements meet minimum 44×44px touch target size (WCAG 2.5.5)';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'mobile';

	/**
	 * Check tap target sizes.
	 *
	 * This treatment checks theme CSS for minimum touch target sizes.
	 * Validates buttons, links, and interactive elements meet WCAG 2.5.5 requirements.
	 *
	 * @since  1.6034.1440
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Get active theme.
		$theme       = wp_get_theme();
		$theme_root  = get_theme_root();
		$theme_path  = $theme_root . '/' . $theme->get_stylesheet();
		$issues      = array();

		// Check if theme has mobile-specific CSS or responsive styles.
		$has_mobile_styles = false;
		$css_files         = array();
		
		if ( file_exists( $theme_path . '/style.css' ) ) {
			$css_files[] = $theme_path . '/style.css';
		}
		
		// Common mobile CSS file names.
		$mobile_files = array( 'mobile.css', 'responsive.css', 'media-queries.css' );
		foreach ( $mobile_files as $file ) {
			if ( file_exists( $theme_path . '/' . $file ) ) {
				$css_files[] = $theme_path . '/' . $file;
			}
		}

		// Analyze CSS for touch target sizing.
		foreach ( $css_files as $css_file ) {
			$content = file_get_contents( $css_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			
			// Check for mobile viewport meta tag in functions.php.
			$functions_file = $theme_path . '/functions.php';
			if ( file_exists( $functions_file ) ) {
				$functions_content = file_get_contents( $functions_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
				if ( strpos( $functions_content, 'viewport' ) !== false ) {
					$has_mobile_styles = true;
				}
			}
			
			// Check for media queries (indication of responsive design).
			if ( preg_match( '/@media.*\(max-width|@media.*\(min-width/i', $content ) ) {
				$has_mobile_styles = true;
			}
			
			// Check for explicit touch target sizing.
			$patterns = array(
				'/\.button.*\{[^}]*min-(width|height):\s*([0-9]+)px/i',
				'/\.btn.*\{[^}]*min-(width|height):\s*([0-9]+)px/i',
				'/a.*\{[^}]*min-(width|height):\s*([0-9]+)px/i',
				'/input\[type=["\']button["\']\].*\{[^}]*min-(width|height):\s*([0-9]+)px/i',
			);
			
			foreach ( $patterns as $pattern ) {
				if ( preg_match_all( $pattern, $content, $matches ) ) {
					// Found some touch target sizing.
					foreach ( $matches[2] as $size ) {
						if ( (int) $size < 44 ) {
							$issues[] = sprintf(
								/* translators: %d: pixel size found */
								__( 'Touch target too small: %dpx (minimum 44px)', 'wpshadow' ),
								(int) $size
							);
						}
					}
				}
			}
		}

		// If no mobile styles detected, this is a concern.
		if ( ! $has_mobile_styles ) {
			$threat_level = 85; // Critical - no responsive design.
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __(
					'Theme lacks responsive/mobile styles. Interactive elements may be too small for touch. WCAG 2.5.5 requires minimum 44×44px touch targets. Consider using a mobile-responsive theme or adding mobile stylesheets.',
					'wpshadow'
				),
				'severity'     => 'critical',
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-tap-target-size',
			);
		}

		// If we found specific undersized targets.
		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: 1: number of issues, 2: list of issues */
					__(
						'Found %1$d touch targets smaller than WCAG 2.5.5 minimum (44×44px): %2$s. Small touch targets cause mis-taps and frustration.',
						'wpshadow'
					),
					count( $issues ),
					implode( ', ', array_slice( $issues, 0, 3 ) )
				),
				'severity'     => 'critical',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-tap-target-size',
			);
		}

		return null;
	}
}
