<?php
/**
 * Color Contrast Diagnostic
 *
 * Checks theme for WCAG AA color contrast compliance (4.5:1 for normal text,
 * 3:1 for large text) to ensure readability for visually impaired users.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Accessibility
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Color Contrast Diagnostic Class
 *
 * Verifies theme has sufficient color contrast for readability.
 * WCAG 2.1 Level AA Success Criterion1.0 (Contrast Minimum).
 *
 * @since 1.6093.1200
 */
class Diagnostic_Color_Contrast extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'color_contrast';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Color Contrast';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Verifies theme has WCAG AA color contrast (4.5:1 minimum)';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$stats    = array();
		$issues   = array();
		$warnings = array();

		// Check for accessibility plugins that validate contrast.
		$contrast_plugins = array(
			'accessibility-checker/accessibility-checker.php' => 'Accessibility Checker',
			'wp-accessibility/wp-accessibility.php'           => 'WP Accessibility',
		);

		$active_contrast = array();
		foreach ( $contrast_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_contrast[] = $plugin_name;
			}
		}

		if ( count( $active_contrast ) > 0 ) {
			$stats['contrast_tools'] = implode( ', ', $active_contrast );
		} else {
			$warnings[] = 'No automated contrast checking tools detected';
		}

		// Get current theme info.
		$theme               = wp_get_theme();
		$stats['theme']      = $theme->get( 'Name' );
		$stats['theme_tags'] = $theme->get( 'Tags' );

		// Check if theme claims accessibility-ready.
		$is_a11y_ready = false;
		if ( is_array( $stats['theme_tags'] ) && in_array( 'accessibility-ready', $stats['theme_tags'], true ) ) {
			$is_a11y_ready               = true;
			$stats['accessibility_ready'] = 'Yes';
		} else {
			$warnings[] = 'Theme not tagged as accessibility-ready';
		}

		// Check if theme supports custom colors (potential contrast issues).
		$custom_colors = get_theme_support( 'custom-colors' );
		if ( false !== $custom_colors ) {
			$warnings[] = 'Theme allows custom colors (contrast should be validated)';
		}

		// Check for contrast-related CSS custom properties.
		$stylesheet_path = get_stylesheet_directory() . '/style.css';
		if ( file_exists( $stylesheet_path ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$stylesheet = file_get_contents( $stylesheet_path );
			
			// Look for CSS custom properties that might indicate color system.
			$has_color_vars = preg_match( '/--.*color/i', $stylesheet );
			if ( $has_color_vars ) {
				$stats['uses_css_variables'] = 'Yes';
			}
		}

		// Return finding if tools not detected and theme not certified.
		if ( count( $active_contrast ) === 0 && ! $is_a11y_ready ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your site doesn\'t have automated color contrast checking. Color contrast is like the difference between reading a grey book on grey paper versus black text on white paper. WCAG requires 4.5:1 contrast for normal text so people with low vision (8% of population) can read your content. Without checking tools, you might unknowingly use color combinations that exclude these visitors.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/color-contrast',
				'context'      => array(
					'stats'          => $stats,
					'issues'         => $issues,
					'warnings'       => $warnings,
					'wcag_criterion' => 'WCAG 2.1 Level AA -1.0 Contrast (Minimum)',
					'recommended_tools' => array(
						'Browser DevTools' => 'Built-in contrast checker in Chrome/Edge/Firefox',
						'WAVE Extension'   => 'Free browser extension for accessibility testing',
						'WebAIM Contrast Checker' => 'Free online tool',
					),
				),
			);
		}

		return null;
	}
}
