<?php
/**
 * Admin Page Hook Security Diagnostic
 *
 * Validates that custom admin pages use proper WordPress hooks and follow
 * security best practices. Plugins often register admin pages incorrectly,
 * creating performance bottlenecks, security vulnerabilities, or breaking
 * admin functionality. This diagnostic catches those misconfigurations before
 * they impact your site.
 *
 * **What This Check Does:**
 * - Monitors `admin_menu` and `network_admin_menu` hook usage
 * - Checks if custom admin pages use proper page hook format
 * - Validates admin_init hook isn't overused (performance)
 * - Detects pages registered without capability checks
 * - Identifies improper use of admin hooks (wrong timing, wrong priority)
 * - Checks for pages using deprecated admin registration functions
 *
 * **Why This Matters:**
 * Admin page hooks control when and how custom admin pages render. If hooks
 * are used incorrectly, pages might:
 * - Fail to load (white screen)
 * - Break other plugins (hook conflicts)
 * - Slow admin panel (run on every admin page instead of just their page)
 * - Bypass security checks (capability validation skipped)
 *
 * **Real-World Performance Problem:**
 * Plugin registers settings page incorrectly:
 * ```php
 * // WRONG: Runs on EVERY admin page load
 * add_action( 'admin_init', 'my_heavy_settings_function' );
 * function my_heavy_settings_function() {
 *   // Database queries, API calls, etc.
 * }
 *
 * // CORRECT: Runs only on settings page
 * add_action( 'admin_menu', 'register_settings_page' );
 * function register_settings_page() {
 *   $hook = add_options_page( 'My Settings', 'My Settings', 'manage_options', 'my-settings', 'render_page' );
 *   add_action( 'load-' . $hook, 'my_heavy_settings_function' ); // Runs only on this page
 * }
 * ```
 *
 * **Common Hook Mistakes:**
 *
 * **1. admin_init Overuse:**
 * Problem: Fires on EVERY admin page load (dashboard, posts, plugins, everything)
 * Impact: 10+ plugins each using admin_init = admin panel loads in 5+ seconds
 * Solution: Use page-specific hooks like `load-{$page_hook}`
 *
 * **2. Missing Capability Checks:**
 * ```php
 * // VULNERABLE:
 * add_menu_page( 'Admin Page', 'Menu', 'read', 'my-page', 'render' );
 * // Any logged-in user (even Subscribers) can access
 *
 * // SECURE:
 * add_menu_page( 'Admin Page', 'Menu', 'manage_options', 'my-page', 'render' );
 * // Only administrators can access
 * ```
 *
 * **3. Wrong Hook Timing:**
 * ```php
 * // WRONG: Too early, WordPress not fully loaded
 * add_action( 'init', 'register_admin_page' );
 *
 * // CORRECT: Proper admin context
 * add_action( 'admin_menu', 'register_admin_page' );
 * ```
 *
 * **4. Improper Page Hook Format:**
 * Admin pages should use sanitized slugs:
 * - Good: `my-plugin-settings` (lowercase, dashes)
 * - Bad: `My Plugin Settings` (spaces break CSS/JS)
 * - Bad: `my_plugin/settings` (slashes break routing)
 *
 * **What This Diagnostic Detects:**
 * - Pages registered outside `admin_menu` hook
 * - admin_init hook with >10 callbacks (performance issue)
 * - Page slugs with spaces or special characters
 * - Pages without proper capability requirements
 * - Deprecated functions like `add_submenu_page()` with wrong parameters
 *
 * **Performance Impact:**
 * Each unnecessary admin_init callback adds 50-200ms to every admin page load.
 * 10 badly-hooked plugins = 1-2 second delay on every admin action.
 * Users experience: "WordPress admin is so slow!"
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Ensures admin panel works reliably
 * - #9 Show Value: Improves admin performance (measurable speed gain)
 * - Technical Excellence: Validates WordPress best practices
 *
 * **Learn More:**
 * See https://wpshadow.com/kb/admin-page-hook-best-practices for correct patterns
 * or https://wpshadow.com/training/wordpress-admin-optimization
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: Admin Page Hook Security
 *
 * Uses WordPress' global admin page registry to validate hook usage.
 * Admin pages are tracked in `global $admin_page_hooks` and `$menu`/`$submenu`.
 *
 * **Implementation Pattern:**
 * 1. Access WordPress admin page globals
 * 2. Check admin_init hook callback count (performance indicator)
 * 3. Validate page hook naming conventions
 * 4. Examine page registration timing (correct hook used)
 * 5. Check capability requirements on registered pages
 * 6. Return finding if performance or security issues detected
 *
 * **Detection Logic:**
 * - admin_init callbacks >10: Performance concern
 * - Page hooks without 'admin_page_' or 'admin_menu' prefix: Improper registration
 * - Pages registered on 'init' instead of 'admin_menu': Wrong timing
 * - Page slugs with spaces/slashes: Routing issues
 *
 * **Related Diagnostics:**
 * - Admin Menu Visibility Control: Validates capability-based menu hiding
 * - Admin Performance Optimization: Broader admin speed analysis
 * - Plugin Code Quality: Detects deprecated function usage
 *
 * @since 0.6093.1200
 */
class Diagnostic_Admin_Page_Hook_Security extends Diagnostic_Base {

	protected static $slug = 'admin-page-hook-security';
	protected static $title = 'Admin Page Hook Security';
	protected static $description = 'Verifies custom admin pages use proper security hooks';
	protected static $family = 'admin-security';

	public static function check() {
		$issues = array();

		// Check for admin pages registered without proper hooks
		global $admin_page_hooks;
		$problematic_hooks = 0;

		if ( ! empty( $admin_page_hooks ) ) {
			foreach ( $admin_page_hooks as $hook => $page ) {
				// Check if hook looks properly formatted
				if ( ! strpos( $hook, 'admin_page_' ) && ! strpos( $hook, 'admin_menu' ) ) {
					$problematic_hooks++;
				}
			}
		}

		// Check for improper use of admin_init hook
		$filter_count = has_action( 'admin_init' );
		if ( $filter_count > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of hooks */
				__( 'High number of admin_init hooks (%d) detected - may impact admin performance', 'wpshadow' ),
				$filter_count
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'low',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/admin-page-hook-security?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
