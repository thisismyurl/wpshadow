<?php
/**
 * Diagnostic: Tap Target Spacing Validation
 *
 * Validates minimum spacing between adjacent interactive elements (8px minimum,
 * 12-16px recommended) to prevent accidental taps as per WCAG 2.5.8.
 *
 * Issue: https://github.com/thisismyurl/wpshadow/issues/4027
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
 * Tap Target Spacing Diagnostic
 *
 * Checks spacing between interactive elements. WCAG 2.5.8 requires minimum 8px,
 * with 12-16px recommended for comfortable mobile interaction.
 *
 * @since 1.6034.1440
 */
class Diagnostic_Mobile_Tap_Target_Spacing extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-tap-target-spacing';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Tap Target Spacing Validation';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates minimum spacing between touch targets (WCAG 2.5.8: 8px minimum)';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'mobile';

	/**
	 * Check tap target spacing.
	 *
	 * Analyzes theme CSS for margin/padding between interactive elements.
	 * Insufficient spacing causes accidental taps.
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

		// Check navigation menu spacing (most common issue).
		$css_files = array();
		
		if ( file_exists( $theme_path . '/style.css' ) ) {
			$css_files[] = $theme_path . '/style.css';
		}

		foreach ( $css_files as $css_file ) {
			$content = file_get_contents( $css_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			
			// Check menu item spacing.
			$patterns = array(
				'/\.menu.*li.*\{[^}]*(margin|padding):\s*([0-9]+)px/i',
				'/\.nav.*li.*\{[^}]*(margin|padding):\s*([0-9]+)px/i',
				'/\.wp-block-navigation.*\{[^}]*(gap|margin):\s*([0-9]+)px/i',
			);
			
			foreach ( $patterns as $pattern ) {
				if ( preg_match_all( $pattern, $content, $matches ) ) {
					foreach ( $matches[2] as $spacing ) {
						if ( (int) $spacing < 8 ) {
							$issues[] = sprintf(
								/* translators: %d: pixel spacing found */
								__( 'Touch target spacing too small: %dpx (minimum 8px)', 'wpshadow' ),
								(int) $spacing
							);
						}
					}
				}
			}
		}

		// Check if navigation menu has registered location.
		$nav_menus = get_registered_nav_menus();
		if ( empty( $nav_menus ) ) {
			// No navigation menus - not applicable.
			return null;
		}

		// If issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: 1: number of issues */
					__(
						'Found %d touch targets with insufficient spacing (<8px). WCAG 2.5.8 requires minimum 8px spacing to prevent accidental taps. Recommended spacing is 12-16px for comfortable mobile use.',
						'wpshadow'
					),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-tap-target-spacing',
			);
		}

		return null;
	}
}
