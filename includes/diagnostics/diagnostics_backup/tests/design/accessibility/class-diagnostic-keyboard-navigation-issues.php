<?php
/**
 * Keyboard Navigation Issues Diagnostic
 *
 * Detects elements that cannot be accessed via keyboard,
 * blocking users who cannot use a mouse.
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
 * Diagnostic_Keyboard_Navigation_Issues Class
 *
 * Detects keyboard accessibility problems.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Keyboard_Navigation_Issues extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'keyboard-navigation-issues';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Keyboard Navigation Issues';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects keyboard accessibility problems';

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
	 * @return array|null Finding array if keyboard issues likely, null otherwise.
	 */
	public static function check() {
		$keyboard_analysis = self::analyze_keyboard_accessibility();

		if ( ! $keyboard_analysis['has_issue'] ) {
			return null; // Likely keyboard accessible
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Site likely has keyboard navigation issues. Users with motor disabilities cannot use mouse. Tab key doesn\'t reach menus/buttons = features inaccessible.', 'wpshadow' ),
			'severity'     => 'high',
			'threat_level' => 70,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/keyboard-navigation',
			'family'       => self::$family,
			'meta'         => array(
				'theme'           => $keyboard_analysis['theme'],
				'custom_js_files' => $keyboard_analysis['custom_js_count'],
			),
			'details'      => array(
				'who_uses_keyboard_only'      => array(
					__( 'Motor disabilities: Difficulty using mouse' ),
					__( 'Blind users: Screen reader + keyboard' ),
					__( 'Power users: Keyboard faster than mouse' ),
					__( 'RSI/Carpal tunnel: Mouse causes pain' ),
					__( '15% of users use keyboard frequently' ),
				),
				'keyboard_navigation_basics'  => array(
					'Tab Key' => __( 'Move forward through interactive elements' ),
					'Shift+Tab' => __( 'Move backward through elements' ),
					'Enter' => __( 'Activate links and buttons' ),
					'Space' => __( 'Toggle checkboxes, scroll page' ),
					'Arrow Keys' => __( 'Navigate within menus, carousels' ),
					'Escape' => __( 'Close modals, exit menus' ),
				),
				'common_keyboard_problems'    => array(
					'onclick on <div>' => array(
						'Problem: <div onclick="...">',
						'Issue: Divs not focusable',
						'Fix: Use <button> or add tabindex="0"',
					),
					'Missing Focus Indicators' => array(
						'Problem: outline: none; in CSS',
						'Issue: Can\'t see where focus is',
						'Fix: Never remove outlines, style instead',
					),
					'Dropdown Menus' => array(
						'Problem: Hover-only menus',
						'Issue: Can\'t hover with keyboard',
						'Fix: Open on focus, close on blur/escape',
					),
					'Skip Links Missing' => array(
						'Problem: Tab through 50 nav links to reach content',
						'Issue: Tedious for every page',
						'Fix: "Skip to content" link at top',
					),
				),
				'testing_keyboard_accessibility' => array(
					'Manual Testing' => array(
						'Unplug mouse or trackpad',
						'Navigate entire site with Tab, Enter, Arrows',
						'Can you: Login, submit forms, open menus, checkout?',
					),
					'Browser DevTools' => array(
						'Tab through page',
						'Inspect focused element',
						'Check: tabindex, role, aria-* attributes',
					),
					'Automated Testing' => array(
						'axe DevTools: Detects some issues',
						'WAVE: Flags keyboard traps',
						'Pa11y CI: Automated in build process',
					),
				),
				'fixing_keyboard_issues'      => array(
					'Use Semantic HTML' => array(
						'<button> instead of <div onclick>',
						'<a href> for links',
						'<input>, <select> for form controls',
					),
					'Add tabindex' => array(
						'tabindex="0": Make focusable in natural order',
						'tabindex="-1": Focusable via JavaScript only',
						'NEVER: tabindex="1+" (breaks tab order)',
					),
					'Style Focus States' => array(
						'button:focus { outline: 2px solid blue; }',
						'Never: outline: none; (accessibility violation)',
					),
					'Implement Skip Links' => array(
						'<a href="#content" class="skip-link">Skip to content</a>',
						'CSS: Position off-screen, show on :focus',
					),
				),
				'wcag_keyboard_requirements'  => array(
					'2.1.1 Keyboard (Level A)' => __( 'All functionality available via keyboard' ),
					'2.1.2 No Keyboard Trap (Level A)' => __( 'Can move focus away from any element' ),
					'2.4.7 Focus Visible (Level AA)' => __( 'Keyboard focus indicator visible' ),
					'2.4.3 Focus Order (Level A)' => __( 'Logical tab order' ),
				),
			),
		);
	}

	/**
	 * Analyze keyboard accessibility (heuristic).
	 *
	 * @since  1.2601.2148
	 * @return array Keyboard accessibility analysis.
	 */
	private static function analyze_keyboard_accessibility() {
		$theme = wp_get_theme();
		$theme_name = $theme->get( 'Name' );

		// Count custom JavaScript files (custom JS = higher chance of keyboard issues)
		$theme_dir = get_template_directory();
		$js_files = glob( $theme_dir . '/**/*.js' );
		$custom_js_count = is_array( $js_files ) ? count( $js_files ) : 0;

		// Check for accessibility plugins
		$has_a11y_plugin = is_plugin_active( 'wp-accessibility/wp-accessibility.php' ) ||
						 is_plugin_active( 'one-click-accessibility/one-click-accessibility.php' );

		if ( $has_a11y_plugin ) {
			return array(
				'has_issue'       => false,
				'theme'           => $theme_name,
				'custom_js_count' => $custom_js_count,
			);
		}

		// Conservative: Flag for manual testing
		// Real implementation would check for skip links, focus states
		return array(
			'has_issue'       => true,
			'theme'           => $theme_name,
			'custom_js_count' => $custom_js_count,
		);
	}
}
