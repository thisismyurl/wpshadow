<?php
/**
 * ARIA Landmarks Diagnostic
 *
 * Checks for proper ARIA landmark roles (navigation, main, banner, contentinfo)
 * which help screen reader users understand page structure and navigate quickly.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Accessibility
 * @since      1.6035.1700
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ARIA Landmarks Diagnostic Class
 *
 * Verifies proper use of ARIA landmark roles for navigation.
 * WCAG 2.1 Level A Success Criterion 1.3.1 (Info and Relationships).
 *
 * @since 1.6035.1700
 */
class Diagnostic_ARIA_Landmarks extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'aria_landmarks';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'ARIA Landmarks';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Verifies proper ARIA landmark usage for screen readers';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.1700
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$stats    = array();
		$issues   = array();
		$warnings = array();

		// Check for accessibility plugins.
		$a11y_plugins = array(
			'wp-accessibility/wp-accessibility.php'           => 'WP Accessibility',
			'accessibility-checker/accessibility-checker.php' => 'Accessibility Checker',
		);

		$active_a11y = array();
		foreach ( $a11y_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_a11y[] = $plugin_name;
			}
		}

		if ( count( $active_a11y ) > 0 ) {
			$stats['accessibility_plugins'] = implode( ', ', $active_a11y );
		}

		// Check theme.
		$theme                       = wp_get_theme();
		$stats['theme']              = $theme->get( 'Name' );
		$theme_tags                  = $theme->get( 'Tags' );
		$is_a11y_ready               = is_array( $theme_tags ) && in_array( 'accessibility-ready', $theme_tags, true );

		if ( $is_a11y_ready ) {
			$stats['accessibility_ready'] = 'Yes';
		} else {
			$warnings[] = 'Theme not certified as accessibility-ready';
		}

		// Check theme templates for ARIA landmarks.
		$template_files = array(
			get_template_directory() . '/header.php',
			get_template_directory() . '/footer.php',
			get_template_directory() . '/index.php',
			get_stylesheet_directory() . '/header.php',
			get_stylesheet_directory() . '/footer.php',
		);

		$found_landmarks = array();
		$landmark_patterns = array(
			'role="banner"'      => 'banner',
			'role="navigation"'  => 'navigation',
			'role="main"'        => 'main',
			'role="contentinfo"' => 'contentinfo',
			'<nav'               => 'nav element',
			'<main'              => 'main element',
			'<header'            => 'header element',
			'<footer'            => 'footer element',
		);

		foreach ( $template_files as $template_path ) {
			if ( file_exists( $template_path ) ) {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
				$content = file_get_contents( $template_path );
				
				foreach ( $landmark_patterns as $pattern => $landmark_name ) {
					if ( stripos( $content, $pattern ) !== false ) {
						$found_landmarks[ $landmark_name ] = true;
					}
				}
			}
		}

		if ( ! empty( $found_landmarks ) ) {
			$stats['landmarks_found'] = implode( ', ', array_keys( $found_landmarks ) );
		}

		// Check for missing critical landmarks.
		$critical_missing = array();
		if ( ! isset( $found_landmarks['main'] ) && ! isset( $found_landmarks['main element'] ) ) {
			$critical_missing[] = 'main';
		}
		if ( ! isset( $found_landmarks['navigation'] ) && ! isset( $found_landmarks['nav element'] ) ) {
			$critical_missing[] = 'navigation';
		}

		if ( ! empty( $critical_missing ) ) {
			$issues[] = 'Missing critical landmarks: ' . implode( ', ', $critical_missing );
		}

		// Return finding if issues or no a11y-ready theme.
		if ( count( $critical_missing ) > 0 || ! $is_a11y_ready ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your theme doesn\'t appear to use ARIA landmarks. Landmarks are like signposts that tell screen reader users "here\'s the navigation," "here\'s the main content," "here\'s the footer." Without them, blind users must listen to every single element to understand your page structure. It\'s like trying to find a specific room in a building with no signs. Adding landmarks (main, nav, header, footer) helps 2% of blind users navigate 10x faster.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/aria-landmarks',
				'context'      => array(
					'stats'          => $stats,
					'issues'         => $issues,
					'warnings'       => $warnings,
					'wcag_criterion' => 'WCAG 2.1 Level A - 1.3.1 Info and Relationships',
					'key_landmarks'  => array(
						'<header>'     => 'Site header (banner)',
						'<nav>'        => 'Navigation menus',
						'<main>'       => 'Main content area',
						'<footer>'     => 'Site footer (contentinfo)',
						'<aside>'      => 'Sidebar content',
					),
				),
			);
		}

		return null;
	}
}
