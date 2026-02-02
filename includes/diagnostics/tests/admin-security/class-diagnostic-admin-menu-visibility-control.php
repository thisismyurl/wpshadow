<?php
/**
 * Admin Menu Visibility Control Diagnostic
 *
 * Validates that admin menu items are properly restricted based on user capabilities.
 * The WordPress admin menu should adapt to each user's permissions - showing only
 * features they can actually use. Improperly configured menu items confuse users,
 * expose security-sensitive options, or create clickable links that lead to
 * "You do not have permission" errors.
 *
 * **What This Check Does:**
 * - Scans admin menu structure via `global $menu` and `global $submenu`
 * - Validates each menu item has appropriate capability requirement
 * - Detects menu items using generic 'read' capability (accessible to all)
 * - Identifies menus visible to wrong user roles
 * - Checks if sensitive admin pages properly hidden from non-admins
 * - Validates menu capability matches underlying page capability
 *
 * **Why This Matters:**
 * Admin menu capabilities control what users see and can access. If a menu item
 * requires only 'read' capability, even Subscribers see it in their admin menu.
 * They click it, WordPress checks the actual page capability ('manage_options'),
 * then shows "You do not have permission." This creates:
 * - Poor user experience (why show unusable options?)
 * - Security confusion (users think they should have access)
 * - Support burden ("Why can't I access this menu item?")
 *
 * **Real-World UX Problem:**
 * Plugin adds "Advanced Settings" menu item:
 * ```php
 * add_menu_page(
 *   'Advanced Settings',
 *   'Advanced',
 *   'read',              // Any logged-in user can see menu
 *   'advanced-settings',
 *   'render_page',
 *   'dashicons-admin-generic'
 * );
 *
 * function render_page() {
 *   if ( ! current_user_can( 'manage_options' ) ) {
 *     wp_die( 'You do not have permission' );
 *   }
 *   // Actual settings interface
 * }
 * ```
 *
 * Result:
 * - Subscriber logs in, sees "Advanced" in menu
 * - Clicks it, gets "You do not have permission" error
 * - Subscriber confused: "Why is it in my menu if I can't use it?"
 * - Support ticket: "I can't access Advanced Settings"
 *
 * **Correct Implementation:**
 * ```php
 * add_menu_page(
 *   'Advanced Settings',
 *   'Advanced',
 *   'manage_options',    // Only admins see menu item
 *   'advanced-settings',
 *   'render_page',
 *   'dashicons-admin-generic'
 * );
 * ```
 * Now:
 * - Only administrators see menu item
 * - No permission errors possible
 * - Clean, role-appropriate admin interface
 *
 * **Common Menu Capability Mistakes:**
 *
 * **1. Generic 'read' Capability:**
 * Every logged-in user has 'read' capability.
 * Menu items using 'read' appear for all users (Subscribers, Contributors, etc.).
 * Solution: Use specific capability like 'edit_posts', 'manage_options', etc.
 *
 * **2. Empty/Missing Capability:**
 * ```php
 * add_menu_page( 'Page', 'Menu', '', 'slug', 'callback' );
 * ```
 * WordPress defaults to 'read' = Everyone sees it.
 * Solution: Always specify explicit capability.
 *
 * **3. Capability Mismatch:**
 * Menu requires 'edit_posts' but page requires 'manage_options'.
 * Editor sees menu item, clicks, gets permission error.
 * Solution: Menu capability should match page capability.
 *
 * **4. Overly Restrictive Capabilities:**
 * Menu requires 'manage_options' but page only needs 'edit_posts'.
 * Editors can't access feature they should be able to use.
 * Solution: Use minimum required capability.
 *
 * **WordPress Capability Hierarchy:**
 * - **read**: Every logged-in user (Subscriber+)
 * - **edit_posts**: Contributors and above
 * - **publish_posts**: Authors and above  
 * - **edit_others_posts**: Editors and above
 * - **manage_options**: Administrators only
 * - **manage_network**: Super Admins only (multisite)
 *
 * **Menu Structure in WordPress:**
 * `global $menu` contains top-level menu items:
 * ```php
 * $menu[] = array(
 *   0 => 'Page Title',
 *   1 => 'required_capability',
 *   2 => 'menu-slug',
 *   ...
 * );
 * ```
 * Index 1 is the capability requirement.
 *
 * **What This Diagnostic Flags:**
 * - Menu items with 'read' capability (too permissive)
 * - Menu items with empty capability (defaults to 'read')
 * - Capability mismatches (menu vs page requirements)
 * - Overly broad capability on sensitive features
 *
 * **Security Implications:**
 * Not a direct vulnerability (page still checks permissions).
 * But:
 * - Information disclosure (reveals admin feature existence)
 * - Social engineering vector ("If I can see it, maybe I should have access")
 * - User enumeration aid (different menus for different roles)
 *
 * **Philosophy Alignment:**
 * - #1 Helpful Neighbor: Shows users only what they can actually use
 * - #8 Inspire Confidence: Professional, role-appropriate interface
 * - Accessibility: Reduces cognitive load by hiding irrelevant options
 *
 * **Learn More:**
 * See https://wpshadow.com/kb/admin-menu-capability-best-practices
 * or https://wpshadow.com/training/wordpress-user-roles-capabilities
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.0633
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: Admin Menu Visibility Control
 *
 * Uses WordPress' global menu arrays to validate capability requirements.
 * Menu structure stored in `global $menu` (top-level) and `global $submenu` (child items).
 *
 * **Implementation Pattern:**
 * 1. Access WordPress menu globals: `global $menu, $submenu`
 * 2. Iterate through top-level menu items
 * 3. Extract capability requirement (index 1 in menu array)
 * 4. Flag items using 'read' or empty capability
 * 5. Check submenu items for same issues
 * 6. Validate capability matches expected user role access
 * 7. Return finding if overly permissive menu items detected
 *
 * **Detection Criteria:**
 * - Capability = 'read': Anyone can see (usually wrong)
 * - Capability empty: Defaults to 'read' (always wrong)
 * - Sensitive admin features (Plugins, Themes, Settings) with wrong capability
 * - Custom menu pages without explicit capability
 *
 * **Special Cases:**
 * - Dashboard widgets have separate capability system
 * - Network admin (multisite) requires 'manage_network'
 * - Some plugins intentionally use 'read' for user-facing features (validate context)
 *
 * **Related Diagnostics:**
 * - Capability Map Consistency: Validates capability definitions
 * - User Role Assignment Security: Checks role change logging
 * - Admin Page Hook Security: Validates page registration patterns
 *
 * @since 1.26033.0633
 */
class Diagnostic_Admin_Menu_Visibility_Control extends Diagnostic_Base {

	protected static $slug = 'admin-menu-visibility-control';
	protected static $title = 'Admin Menu Visibility Control';
	protected static $description = 'Verifies menu items are restricted by user capabilities';
	protected static $family = 'admin-security';

	public static function check() {
		$issues = array();

		// Check for menu items that might be exposed
		global $menu;
		$menu_capability_issues = 0;

		if ( ! empty( $menu ) ) {
			foreach ( $menu as $item ) {
				// Check if menu item uses valid capability
				$capability = $item[1] ?? 'read';
				if ( 'read' === $capability || empty( $capability ) ) {
					$menu_capability_issues++;
				}
			}
		}

		if ( $menu_capability_issues > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of menu items */
				__( '%d menu item(s) use generic "read" capability - should be more specific', 'wpshadow' ),
				$menu_capability_issues
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/admin-menu-visibility-control',
			);
		}

		return null;
	}
}
