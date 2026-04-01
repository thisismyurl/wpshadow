<?php
/**
 * Keyboard Navigation Diagnostic
 *
 * Checks for keyboard accessibility features including skip links,
 * focus indicators, and keyboard trap prevention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Accessibility
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Keyboard Navigation Diagnostic Class
 *
 * Verifies site is navigable via keyboard alone (no mouse required).
 * WCAG 2.1 Level A Success Criterion 2.1.1 (Keyboard).
 *
 * @since 0.6093.1200
 */
class Diagnostic_Keyboard_Navigation extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'keyboard_navigation';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Keyboard Navigation';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Verifies site is fully navigable via keyboard';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$stats    = array();
		$issues   = array();
		$warnings = array();

		// Check for accessibility plugins that enhance keyboard nav.
		$keyboard_plugins = array(
			'wp-accessibility/wp-accessibility.php'           => 'WP Accessibility',
			'accessibility-checker/accessibility-checker.php' => 'Accessibility Checker',
		);

		$active_keyboard = array();
		foreach ( $keyboard_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_keyboard[] = $plugin_name;
			}
		}

		if ( count( $active_keyboard ) > 0 ) {
			$stats['keyboard_plugins'] = implode( ', ', $active_keyboard );
		}

		// Check current theme.
		$theme                       = wp_get_theme();
		$stats['theme']              = $theme->get( 'Name' );
		$theme_tags                  = $theme->get( 'Tags' );
		$is_a11y_ready               = is_array( $theme_tags ) && in_array( 'accessibility-ready', $theme_tags, true );

		if ( $is_a11y_ready ) {
			$stats['accessibility_ready'] = 'Yes';
		} else {
			$warnings[] = 'Theme not certified as accessibility-ready';
		}

		// Check for skip links in theme.
		$header_path = get_template_directory() . '/header.php';
		$has_skip_link = false;

		if ( file_exists( $header_path ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$header_content = file_get_contents( $header_path );

			// Look for skip link patterns.
			if ( preg_match( '/skip-.*link|skip.*navigation|skip.*content/i', $header_content ) ) {
				$has_skip_link            = true;
				$stats['skip_link_found'] = 'Yes';
			}
		}

		if ( ! $has_skip_link ) {
			$issues[] = 'No skip navigation link detected in theme';
		}

		// Check for focus styles in theme CSS.
		$stylesheet_path = get_stylesheet_directory() . '/style.css';
		$has_focus_styles = false;

		if ( file_exists( $stylesheet_path ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$stylesheet = file_get_contents( $stylesheet_path );

			// Look for focus pseudo-class.
			if ( preg_match( '/:focus\s*{/', $stylesheet ) ) {
				$has_focus_styles          = true;
				$stats['focus_styles']     = 'Defined';
			}

			// Check for outline removal (anti-pattern).
			if ( preg_match( '/outline\s*:\s*(0|none)/i', $stylesheet ) ) {
				$issues[] = 'Theme removes focus outlines (accessibility issue)';
				$stats['removes_outline'] = 'Yes (problematic)';
			}
		}

		if ( ! $has_focus_styles ) {
			$warnings[] = 'No explicit focus styles detected';
		}

		// Return finding if issues detected.
		if ( count( $issues ) > 0 || ( ! $is_a11y_ready && count( $active_keyboard ) === 0 ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your site may not be fully keyboard-accessible. About 16% of users have motor disabilities that make mouse use difficult or impossible—they navigate using Tab, Enter, and arrow keys. Without proper keyboard support (skip links, visible focus indicators, no keyboard traps), these users can\'t access your content. Adding skip links lets users jump to main content, and focus indicators show where they are on the page.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/keyboard-navigation?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'stats'          => $stats,
					'issues'         => $issues,
					'warnings'       => $warnings,
					'wcag_criterion' => 'WCAG 2.1 Level A - 2.1.1 Keyboard',
					'testing_tip'    => 'Try navigating your site using only Tab, Shift+Tab, Enter, and arrow keys. Can you reach everything?',
				),
			);
		}

		return null;
	}
}
