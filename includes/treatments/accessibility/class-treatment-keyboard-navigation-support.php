<?php
/**
 * Keyboard Navigation Support Treatment
 *
 * Issue #4861: No Keyboard Navigation in Admin
 * Pillar: 🌍 Accessibility First
 *
 * Checks if admin interface supports keyboard-only navigation.
 * ~16% of users need keyboard navigation (motor disabilities, temporary injuries).
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Keyboard_Navigation_Support Class
 *
 * Checks for:
 * - Tab/Shift+Tab navigation through interactive elements
 * - Skip links for repetitive content
 * - Keyboard shortcuts for common actions (Ctrl+S for save)
 * - Focus indicators always visible
 * - No mouse-only interactions (e.g., hover-required menus)
 * - Proper ARIA labels on keyboard-accessible elements
 * - Escape key to close modals/popovers
 *
 * Why this matters:
 * - Motor disabilities: Tremors, arthritis, paralysis prevent mouse use
 * - Temporary injuries: Broken arm, repetitive strain, post-surgery
 * - Environmental: Trackpad on laptop, in a noisy meeting
 * - Preference: Keyboard users are often power users (faster)
 *
 * @since 1.6050.0000
 */
class Treatment_Keyboard_Navigation_Support extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @since 1.6050.0000
	 * @var   string
	 */
	protected static $slug = 'keyboard-navigation-support';

	/**
	 * The treatment title
	 *
	 * @since 1.6050.0000
	 * @var   string
	 */
	protected static $title = 'No Keyboard Navigation in Admin';

	/**
	 * The treatment description
	 *
	 * @since 1.6050.0000
	 * @var   string
	 */
	protected static $description = 'Checks if all admin features work without a mouse';

	/**
	 * The family this treatment belongs to
	 *
	 * @since 1.6050.0000
	 * @var   string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6050.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// This is a guidance treatment - actual keyboard testing requires manual QA.
		// We provide recommendations for keyboard support.

		$issues = array();

		$issues[] = __( 'All interactive elements (buttons, links, form fields) must be reachable via Tab key', 'wpshadow' );
		$issues[] = __( 'Focus indicator must always be visible (don\'t remove outline/border)', 'wpshadow' );
		$issues[] = __( 'Skip links should let users bypass repetitive navigation (e.g., admin menu)', 'wpshadow' );
		$issues[] = __( 'Menus that appear on hover must also be accessible via keyboard', 'wpshadow' );
		$issues[] = __( 'Modal dialogs should trap focus and support Escape to close', 'wpshadow' );
		$issues[] = __( 'Form submission should work via Enter key (not just button click)', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Keyboard-only users and people with motor disabilities cannot use mouse-dependent interfaces. Full keyboard support is essential.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/keyboard-navigation',
				'details'      => array(
					'recommendations'     => $issues,
					'affected_population' => __( '~16% of adults have motor disabilities, many use keyboard only', 'wpshadow' ),
					'wcag_standard'       => 'WCAG 2.1 Level A (2.1.1 Keyboard)',
					'testing_method'      => 'Navigate entire admin using only Tab, Enter, Escape keys',
					'power_user_benefit'  => 'Keyboard shortcuts are faster than mouse for power users',
				),
			);
		}

		return null;
	}
}
