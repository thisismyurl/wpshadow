<?php

/**
 * Diagnostic: Keyboard Navigation Support
 *
 * Checks if the site is fully navigable via keyboard alone.
 * All interactive elements should be reachable with Tab/Shift+Tab.
 *
 * Philosophy: Commandment #8 (Inspire Confidence - Accessibility)
 *
 * @package WPShadow
 * @subpackage Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Keyboard Navigation Diagnostic
 *
 * TODO: Implement keyboard navigation testing:
 * - Check for keyboard traps (elements you can't Tab out of)
 * - Verify all interactive elements have tabindex >= 0
 * - Check for proper focus management in modals/dropdowns
 * - Test that Enter/Space activate buttons
 */
class Diagnostic_Keyboard_Navigation extends Diagnostic_Base
{

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Null if no issues, array with details if issues found
	 */
	public static function run(): ?array
	{
		// TODO: Implement automated keyboard navigation checks
		// This requires browser automation to test:
		// - Tab through all interactive elements
		// - Verify no keyboard traps exist
		// - Check modal focus management
		// - Verify dropdown navigation works with arrows

		return array(
			'title'       => __('Keyboard Navigation - Manual Testing Recommended', 'wpshadow'),
			'description' => __('Test if your site can be fully navigated using only the keyboard. This is essential for users with motor disabilities and power users.', 'wpshadow'),
			'severity'    => 'low',
			'category'    => 'accessibility',
			'impact'      => __('Users who can\'t use a mouse (due to disability or preference) may not be able to access all features.', 'wpshadow'),
			'details'     => array(
				'manual_test_steps'   => array(
					'1. Close or disable your mouse',
					'2. Press Tab to move forward through elements',
					'3. Press Shift+Tab to move backward',
					'4. Press Enter to activate buttons/links',
					'5. Press Space to toggle checkboxes',
					'6. Press Esc to close modals/dropdowns',
					'7. Use arrow keys in dropdown menus',
				),
				'common_issues'       => array(
					'Keyboard traps (can\'t Tab out)',
					'Missing skip links',
					'Invisible focus indicators',
					'Modals that trap focus incorrectly',
					'Dropdowns that require mouse hover',
				),
			),
			'kb_link'     => 'https://wpshadow.com/kb/keyboard-navigation',
			'training'    => 'https://wpshadow.com/training/accessibility-keyboard',
		);
	}

	/**
	 * Get diagnostic metadata
	 *
	 * @return array Metadata about this diagnostic
	 */
	public static function get_meta(): array
	{
		return array(
			'id'          => 'keyboard_navigation',
			'title'       => __('Keyboard Navigation', 'wpshadow'),
			'description' => __('Checks if site is fully navigable via keyboard', 'wpshadow'),
			'category'    => 'accessibility',
			'severity'    => 'medium',
		);
	}
}
