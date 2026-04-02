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
 * @since 1.6093.1200
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
 * @since 1.6093.1200
 */
class Treatment_Keyboard_Navigation_Support extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $slug = 'keyboard-navigation-support';

	/**
	 * The treatment title
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $title = 'No Keyboard Navigation in Admin';

	/**
	 * The treatment description
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $description = 'Checks if all admin features work without a mouse';

	/**
	 * The family this treatment belongs to
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Keyboard_Navigation_Support' );
	}
}
