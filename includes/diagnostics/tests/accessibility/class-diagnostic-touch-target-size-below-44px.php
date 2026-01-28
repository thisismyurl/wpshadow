<?php
/**
 * Touch Target Size Below 44x44px Diagnostic
 *
 * Identifies interactive elements (buttons, links) smaller than the recommended
 * 44x44px minimum touch target size, violating WCAG 2.1 Level AAA and making
 * mobile interaction difficult.
 *
 * Touch target size is critical for mobile accessibility, affecting users with
 * motor impairments and all mobile users on small screens.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Accessibility
 * @since      1.6028.2053
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Touch_Target_Size_Below_44px Class
 *
 * Scans front-end pages for interactive elements with inadequate touch
 * target sizes. Checks buttons, links, and clickable icons.
 *
 * @since 1.6028.2053
 */
class Diagnostic_Touch_Target_Size_Below_44px extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'touch-target-size-below-44px';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Touch Target Size Below 44x44px';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies interactive elements too small for mobile touch interaction';

	/**
	 * Diagnostic family/category
	 *
	 * @var string
	 */
	protected static $family = 'accessibility-mobile';

	/**
	 * Run the touch target size diagnostic check.
	 *
	 * Analyzes common interactive elements in the active theme for size compliance.
	 * Since we can't render the full DOM, we check CSS and common patterns.
	 *
	 * @since  1.6028.2053
	 * @return array|null Finding array if touch target issues detected, null if compliant.
	 */
	public static function check() {
		$theme_data = self::analyze_theme_touch_targets();

		if ( empty( $theme_data['issues'] ) ) {
			return null; // No obvious touch target issues.
		}

		$total_issues = count( $theme_data['issues'] );
		$severity     = $total_issues > 5 ? 'high' : 'medium';

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of potential issues */
				_n(
					'Found %d potential touch target size issue. Interactive elements should be at least 44x44px for mobile accessibility.',
					'Found %d potential touch target size issues. Interactive elements should be at least 44x44px for mobile accessibility.',
					$total_issues,
					'wpshadow'
				),
				$total_issues
			),
			'severity'     => $severity,
			'threat_level' => $total_issues > 5 ? 65 : 50,
			'auto_fixable' => false,
			'solution'     => array(
				'free'     => array(
					'heading'     => __( 'Increase Touch Target Sizes', 'wpshadow' ),
					'description' => __( 'Add CSS to increase button and link padding to meet 44x44px minimum.', 'wpshadow' ),
					'steps'       => array(
						__( 'Identify all interactive elements (buttons, links, icons)', 'wpshadow' ),
						__( 'Add CSS: button, .btn, a.button { min-height: 44px; min-width: 44px; }', 'wpshadow' ),
						__( 'Add padding: padding: 12px 20px; (or adjust to reach 44px)', 'wpshadow' ),
						__( 'Test close buttons: .close, .modal-close { width: 44px; height: 44px; }', 'wpshadow' ),
						__( 'Add spacing: margin: 8px; (minimum 8px between targets)', 'wpshadow' ),
						__( 'Test on mobile devices (Chrome DevTools mobile view)', 'wpshadow' ),
					),
				),
				'premium'  => array(
					'heading'     => __( 'Theme Touch Target Audit', 'wpshadow' ),
					'description' => __( 'Professional accessibility audit identifying all non-compliant touch targets with specific CSS fixes.', 'wpshadow' ),
				),
				'advanced' => array(
					'heading'     => __( 'Responsive Touch Target System', 'wpshadow' ),
					'description' => __( 'Implement CSS custom properties for consistent touch target sizing across breakpoints.', 'wpshadow' ),
				),
			),
			'details'      => array(
				'issues'           => $theme_data['issues'],
				'wcag_level'       => 'AAA',
				'minimum_size'     => '44x44px',
				'minimum_spacing'  => '8px',
				'affected_users'   => __( 'Mobile users, motor impairments', 'wpshadow' ),
			),
			'resource_links' => array(
				array(
					'title' => __( 'WCAG 2.5.5 Target Size', 'wpshadow' ),
					'url'   => 'https://www.w3.org/WAI/WCAG21/Understanding/target-size.html',
				),
				array(
					'title' => __( 'Apple Touch Target Guidelines', 'wpshadow' ),
					'url'   => 'https://developer.apple.com/design/human-interface-guidelines/layout',
				),
				array(
					'title' => __( 'Material Design Touch Targets', 'wpshadow' ),
					'url'   => 'https://m2.material.io/design/usability/accessibility.html',
				),
			),
			'kb_link'      => 'https://wpshadow.com/kb/touch-target-size-accessibility',
		);
	}

	/**
	 * Analyze active theme for touch target size issues.
	 *
	 * Checks common CSS patterns and theme files for undersized interactive elements.
	 *
	 * @since  1.6028.2053
	 * @return array {
	 *     Touch target analysis results.
	 *
	 *     @type array $issues List of potential issues found.
	 * }
	 */
	private static function analyze_theme_touch_targets() {
		$issues = array();

		// Get active theme.
		$theme = wp_get_theme();

		// Common selectors to check.
		$selectors_to_check = array(
			'button',
			'.btn',
			'.button',
			'a.button',
			'.close',
			'.menu-toggle',
			'.hamburger',
			'nav a',
			'.nav-link',
			'input[type="submit"]',
			'input[type="button"]',
		);

		// Check theme stylesheet for sizing rules.
		$stylesheet = $theme->get_stylesheet_directory() . '/style.css';
		if ( file_exists( $stylesheet ) ) {
			$css_content = file_get_contents( $stylesheet ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

			foreach ( $selectors_to_check as $selector ) {
				// Look for explicit small sizes.
				if ( preg_match( '/' . preg_quote( $selector, '/' ) . '\s*\{[^}]*(height|min-height):\s*([0-9]+)px/i', $css_content, $matches ) ) {
					$size = intval( $matches[2] );
					if ( $size < 44 ) {
						$issues[] = array(
							'selector' => $selector,
							'property' => $matches[1],
							'value'    => $size . 'px',
							'file'     => 'style.css',
						);
					}
				}
			}
		}

		// Check common problem areas.
		$common_issues = array(
			array(
				'selector'    => '.close, .modal-close',
				'description' => __( 'Close buttons often use icon fonts <20px', 'wpshadow' ),
			),
			array(
				'selector'    => '.menu-toggle, .hamburger',
				'description' => __( 'Mobile menu toggles sometimes too small', 'wpshadow' ),
			),
			array(
				'selector'    => 'nav a',
				'description' => __( 'Navigation links may lack adequate padding', 'wpshadow' ),
			),
		);

		// If no CSS issues found but theme exists, add general warnings.
		if ( empty( $issues ) ) {
			// Only report if we detect potential problem selectors in template files.
			$template_dir = $theme->get_template_directory();
			$header_file  = $template_dir . '/header.php';

			if ( file_exists( $header_file ) ) {
				$header_content = file_get_contents( $header_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

				// Check for mobile menu toggle.
				if ( preg_match( '/menu-toggle|hamburger|mobile-menu/i', $header_content ) ) {
					$issues[] = array(
						'selector'    => __( 'Mobile menu toggle', 'wpshadow' ),
						'description' => __( 'Verify mobile menu toggle button is at least 44x44px', 'wpshadow' ),
						'file'        => 'header.php',
					);
				}

				// Check for close buttons.
				if ( preg_match( '/close|×|&times;/i', $header_content ) ) {
					$issues[] = array(
						'selector'    => __( 'Close buttons', 'wpshadow' ),
						'description' => __( 'Verify modal/menu close buttons meet 44x44px minimum', 'wpshadow' ),
						'file'        => 'header.php',
					);
				}
			}
		}

		return array(
			'issues' => $issues,
		);
	}
}
