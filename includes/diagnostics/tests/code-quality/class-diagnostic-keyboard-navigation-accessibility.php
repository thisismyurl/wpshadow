<?php
/**
 * Keyboard Navigation Accessibility Diagnostic
 *
 * Tests if site is fully navigable via keyboard for accessibility.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.7034.1300
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Keyboard Navigation Accessibility Diagnostic Class
 *
 * Validates that the site is fully keyboard accessible per WCAG 2.1
 * guidelines for users who cannot use a mouse.
 *
 * @since 1.7034.1300
 */
class Diagnostic_Keyboard_Navigation_Accessibility extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'keyboard-navigation-accessibility';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Keyboard Navigation Accessibility';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if site is fully navigable via keyboard for accessibility';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests keyboard navigation including skip links, focus indicators,
	 * and accessible dropdown menus.
	 *
	 * @since  1.7034.1300
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		// Check theme support for accessibility.
		$theme_supports = array(
			'skip-link'           => current_theme_supports( 'skip-link' ),
			'keyboard-navigation' => current_theme_supports( 'keyboard-navigation' ),
		);

		// Check for accessibility plugins.
		$has_accessibility_plugin = is_plugin_active( 'wp-accessibility/wp-accessibility.php' ) ||
									is_plugin_active( 'accessibility-checker/accessibility-checker.php' );

		// Check active theme.
		$theme      = wp_get_theme();
		$theme_name = $theme->get( 'Name' );

		// Known accessible themes.
		$accessible_themes   = array( 'Twenty Twenty-One', 'Twenty Twenty-Two', 'Twenty Twenty-Three', 'Kadence', 'GeneratePress' );
		$is_accessible_theme = in_array( $theme_name, $accessible_themes, true );

		// Check navigation menus.
		$nav_menus     = wp_get_nav_menus();
		$has_nav_menus = ! empty( $nav_menus );

		// Check for ARIA landmarks.
		$header_file        = get_template_directory() . '/header.php';
		$has_aria_landmarks = false;

		if ( file_exists( $header_file ) ) {
			$header_content     = file_get_contents( $header_file );
			$has_aria_landmarks = ( strpos( $header_content, 'role="navigation"' ) !== false ) ||
								( strpos( $header_content, 'role="main"' ) !== false );
		}

		// Check for skip link.
		$has_skip_link = false;
		if ( file_exists( $header_file ) ) {
			$header_content = file_get_contents( $header_file );
			$has_skip_link  = ( strpos( $header_content, 'skip-link' ) !== false ) ||
							( strpos( $header_content, 'skip-to-content' ) !== false );
		}

		// Check CSS for focus indicators.
		$style_css        = get_stylesheet_directory() . '/style.css';
		$has_focus_styles = false;

		if ( file_exists( $style_css ) ) {
			$style_content    = file_get_contents( $style_css );
			$has_focus_styles = ( strpos( $style_content, ':focus' ) !== false );
		}

		// Check for tabindex misuse.
		$tabindex_issues = false;
		if ( file_exists( $header_file ) ) {
			$header_content = file_get_contents( $header_file );
			// Check for positive tabindex values (antipattern).
			preg_match_all( '/tabindex=["\']([1-9]\d*)["\']/', $header_content, $matches );
			$tabindex_issues = ! empty( $matches[1] );
		}

		// Check menu depth (deep menus are keyboard-unfriendly).
		$menu_depth = 0;
		if ( ! empty( $nav_menus ) ) {
			foreach ( $nav_menus as $menu ) {
				$menu_items = wp_get_nav_menu_items( $menu->term_id );
				if ( is_array( $menu_items ) ) {
					foreach ( $menu_items as $item ) {
						if ( absint( $item->menu_item_parent ) > 0 ) {
							$depth  = 1;
							$parent = $item->menu_item_parent;
							while ( $parent > 0 ) {
								++$depth;
								foreach ( $menu_items as $potential_parent ) {
									if ( absint( $potential_parent->ID ) === absint( $parent ) ) {
										$parent = absint( $potential_parent->menu_item_parent );
										break;
									}
								}
								if ( $depth > 5 ) {
									break;
								}
							}
							$menu_depth = max( $menu_depth, $depth );
						}
					}
				}
			}
		}

		// Check for issues.
		$issues = array();

		// Issue 1: No skip link for keyboard users.
		if ( ! $has_skip_link ) {
			$issues[] = array(
				'type'        => 'no_skip_link',
				'description' => __( 'No skip link found; keyboard users must tab through entire header to reach content', 'wpshadow' ),
			);
		}

		// Issue 2: No focus indicators in CSS.
		if ( ! $has_focus_styles ) {
			$issues[] = array(
				'type'        => 'no_focus_styles',
				'description' => __( 'No :focus styles in CSS; keyboard users cannot see which element is focused', 'wpshadow' ),
			);
		}

		// Issue 3: Positive tabindex values (antipattern).
		if ( $tabindex_issues ) {
			$issues[] = array(
				'type'        => 'tabindex_antipattern',
				'description' => __( 'Positive tabindex values detected; disrupts natural keyboard navigation order', 'wpshadow' ),
			);
		}

		// Issue 4: No ARIA landmarks.
		if ( ! $has_aria_landmarks ) {
			$issues[] = array(
				'type'        => 'no_aria_landmarks',
				'description' => __( 'No ARIA landmarks (role="navigation", role="main"); screen readers cannot navigate page structure', 'wpshadow' ),
			);
		}

		// Issue 5: Deep menu nesting (keyboard unfriendly).
		if ( $menu_depth > 3 ) {
			$issues[] = array(
				'type'        => 'deep_menu_nesting',
				'description' => sprintf(
					/* translators: %d: menu depth level */
					__( 'Navigation menu is %d levels deep; difficult for keyboard navigation', 'wpshadow' ),
					$menu_depth
				),
			);
		}

		// Issue 6: Theme not known for accessibility.
		if ( ! $is_accessible_theme && ! $has_accessibility_plugin ) {
			$issues[] = array(
				'type'        => 'theme_not_accessible',
				'description' => __( 'Theme not known for accessibility and no accessibility plugin active', 'wpshadow' ),
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Site is not fully keyboard accessible, which prevents users with mobility impairments from navigating effectively', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/keyboard-navigation-accessibility',
				'details'      => array(
					'theme_name'               => $theme_name,
					'is_accessible_theme'      => $is_accessible_theme,
					'theme_skip_link_support'  => $theme_supports['skip-link'],
					'has_accessibility_plugin' => $has_accessibility_plugin,
					'has_skip_link'            => $has_skip_link,
					'has_focus_styles'         => $has_focus_styles,
					'has_aria_landmarks'       => $has_aria_landmarks,
					'tabindex_issues'          => $tabindex_issues,
					'menu_depth'               => $menu_depth,
					'has_nav_menus'            => $has_nav_menus,
					'issues_detected'          => $issues,
					'recommendation'           => __( 'Add skip link, style :focus states, use ARIA landmarks, simplify menu structure', 'wpshadow' ),
					'wcag_compliance'          => array(
						'WCAG 2.1.1' => 'Keyboard - All functionality available via keyboard',
						'WCAG 2.4.1' => 'Bypass Blocks - Skip link to main content',
						'WCAG 2.4.7' => 'Focus Visible - Keyboard focus indicator visible',
						'WCAG 1.3.1' => 'Info and Relationships - Use ARIA landmarks',
					),
					'skip_link_code'           => '<a href="#main-content" class="skip-link screen-reader-text">Skip to content</a>',
					'focus_css_example'        => 'a:focus, button:focus { outline: 2px solid #0073aa; outline-offset: 2px; }',
					'aria_landmark_examples'   => array(
						'<header role="banner">',
						'<nav role="navigation">',
						'<main role="main">',
						'<aside role="complementary">',
						'<footer role="contentinfo">',
					),
				),
			);
		}

		return null;
	}
}
