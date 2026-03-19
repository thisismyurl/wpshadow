<?php
/**
 * Focus Management Diagnostic
 *
 * Issue #4761: Visible Focus Moves During Keyboard Navigation
 * Pillar: 🌍 Accessibility First
 * Commandment: #8 (Inspire Confidence)
 *
 * Checks if focus is properly managed during interactions.
 * Focus should never move unexpectedly during keyboard navigation.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Focus_Management Class
 *
 * Checks for proper focus management patterns.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Focus_Management extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'focus-management';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Visible Focus Moves During Keyboard Navigation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if focus changes are predictable and never jump unexpectedly';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		$issues[] = __( 'Never move focus automatically while user is typing', 'wpshadow' );
		$issues[] = __( 'When closing a modal, return focus to trigger element', 'wpshadow' );
		$issues[] = __( 'Don\'t trap focus in component (provide escape)', 'wpshadow' );
		$issues[] = __( 'Focus should follow logical reading order (top to bottom)', 'wpshadow' );
		$issues[] = __( 'Deletions should move focus to next logical element', 'wpshadow' );
		$issues[] = __( 'Dynamic content shouldn\'t steal focus from user', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your site might move keyboard focus unexpectedly during user interactions. Imagine typing an email when suddenly your cursor jumps to a different field mid-sentence—that\'s how keyboard users feel when focus moves unpredictably. Common problems: Auto-complete fields that move focus after selection, closing modals that leave focus nowhere, deleting items that abandon focus, dynamic content that steals focus from what the user was doing. Good focus management means: focus never moves unless the user triggered it, when something closes/deletes focus returns somewhere logical, and users can always tell where they are. This affects blind users using screen readers, motor-disabled users navigating by keyboard, and power users who prefer keyboard shortcuts.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/focus-management',
				'details'      => array(
					'recommendations'       => $issues,
					'wcag_requirement'      => 'WCAG 2.1 2.4.3 Focus Order, 3.2.1 On Focus',
					'affected_users'        => 'Screen reader users, keyboard-only users, power users',
					'modal_pattern'         => 'Open modal → trap focus inside → close → return focus to trigger',
					'delete_pattern'        => 'Delete item → move focus to next item (or previous if last)',
					'never_steal'           => 'Don\'t move focus due to timers, AJAX, or auto-updates',
				),
			);
		}

		return null;
	}
}
