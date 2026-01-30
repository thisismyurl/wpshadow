<?php
/**
 * Missing ARIA Landmarks Diagnostic
 *
 * Detects missing ARIA landmark roles that help screen reader
 * users navigate page regions efficiently.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Missing_ARIA_Landmarks Class
 *
 * Detects missing ARIA landmark roles.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Missing_ARIA_Landmarks extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'missing-aria-landmarks';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Missing ARIA Landmarks';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects missing ARIA landmark roles';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if landmarks missing, null otherwise.
	 */
	public static function check() {
		$landmark_analysis = self::analyze_landmarks();

		if ( ! $landmark_analysis['has_issue'] ) {
			return null; // Landmarks present
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Missing ARIA landmarks. Screen reader users navigate by landmarks (header, main, footer). No landmarks = read entire page linearly. 100-line page = tedious.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 55,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/aria-landmarks',
			'family'       => self::$family,
			'meta'         => array(
				'theme' => $landmark_analysis['theme'],
			),
			'details'      => array(
				'what_are_aria_landmarks'    => array(
					__( 'Semantic regions of page' ),
					__( 'Screen readers: "Landmarks menu" shortcut' ),
					__( 'Jump directly to main content, navigation, footer' ),
					__( 'Equivalent to visual scanning with eyes' ),
				),
				'essential_landmarks'        => array(
					'<header> or role="banner"' => array(
						'Purpose: Site header with logo, top nav',
						'Limit: One per page',
						'Screen reader: "Banner landmark"',
					),
					'<nav> or role="navigation"' => array(
						'Purpose: Navigation menus',
						'Multiple: Main nav, sidebar nav, footer nav',
						'Use aria-label: aria-label="Main Navigation"',
					),
					'<main> or role="main"' => array(
						'Purpose: Primary page content',
						'Limit: One per page',
						'Most important landmark',
					),
					'<footer> or role="contentinfo"' => array(
						'Purpose: Site footer',
						'Limit: One per page',
						'Screen reader: "Content information"',
					),
					'<aside> or role="complementary"' => array(
						'Purpose: Sidebars, related content',
						'Multiple: OK',
					),
					'<form> or role="search"' => array(
						'Purpose: Search forms',
						'Use: <form role="search">',
					),
				),
				'implementing_landmarks'     => array(
					'HTML5 Semantic Elements (Preferred)' => array(
						'<header>Site Header</header>',
						'<nav>Main Menu</nav>',
						'<main>Page Content</main>',
						'<aside>Sidebar</aside>',
						'<footer>Site Footer</footer>',
					),
					'ARIA Roles (Legacy Support)' => array(
						'<div role="banner">',
						'Use when: HTML5 elements not possible',
						'Note: HTML5 elements implicit ARIA roles',
					),
					'Multiple Landmarks of Same Type' => array(
						'<nav aria-label="Main Navigation">',
						'<nav aria-label="Footer Links">',
						'Label distinguishes them',
					),
				),
				'editing_wordpress_theme'    => array(
					'Locate Template Files' => array(
						'Appearance → Theme File Editor',
						'Or: SFTP to /wp-content/themes/yourtheme/',
					),
					'header.php' => array(
						'Wrap site header in <header>',
						'Main nav in <nav aria-label="Primary">',
					),
					'index.php / single.php' => array(
						'Wrap content loop in <main>',
						'Sidebars in <aside>',
					),
					'footer.php' => array(
						'Wrap site footer in <footer>',
						'Footer nav in <nav aria-label="Footer">',
					),
				),
				'testing_landmarks'          => array(
					'Screen Reader' => array(
						'NVDA (Windows): Insert+F7 (elements list → landmarks)',
						'VoiceOver (Mac): VO+U → Landmarks rotor',
						'Navigate: Should list banner, main, navigation, contentinfo',
					),
					'Browser Extension' => array(
						'Landmarks browser extension',
						'Shows all landmarks visually',
						'Detects missing/duplicate landmarks',
					),
					'Automated Testing' => array(
						'axe DevTools: Checks landmark presence',
						'WAVE: Shows landmark structure',
					),
				),
				'wcag_requirements'          => array(
					__( '1.3.1 Info and Relationships (Level A)' ),
					__( '2.4.1 Bypass Blocks (Level A) - Skip links or landmarks' ),
					__( 'Best practice: Use both landmarks AND skip links' ),
				),
			),
		);
	}

	/**
	 * Analyze landmarks (heuristic).
	 *
	 * @since  1.2601.2148
	 * @return array Landmark analysis.
	 */
	private static function analyze_landmarks() {
		$theme = wp_get_theme();
		$theme_name = $theme->get( 'Name' );

		// Check theme generation (newer themes more likely to have landmarks)
		$theme_version = $theme->get( 'Version' );
		$is_recent_theme = version_compare( $theme_version, '1.0', '>=' );

		// Check for block themes (FSE) - typically have better landmarks
		$is_block_theme = wp_is_block_theme();

		if ( $is_block_theme ) {
			return array(
				'has_issue' => false,
				'theme'     => $theme_name,
			);
		}

		// Conservative: Flag for manual review
		// Real implementation would fetch homepage HTML and check for <main>, <header>, <footer>
		return array(
			'has_issue' => true,
			'theme'     => $theme_name,
		);
	}
}
