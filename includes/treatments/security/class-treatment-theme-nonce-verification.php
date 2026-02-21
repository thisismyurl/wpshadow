<?php
/**
 * Theme Nonce Verification Treatment
 *
 * Checks for nonce verification in theme-admin actions.
 * Admin action without nonce = CSRF vulnerability (Cross-Site Request Forgery).
 * Attacker tricks admin into performing unintended action.
 *
 * **What This Check Does:**
 * - Scans theme files for add_action on admin hooks
 * - Detects callback functions without nonce checks
 * - Tests for wp_verify_nonce usage
 * - Validates check_admin_referer implementation
 * - Checks nonce creation in forms
 * - Returns severity for each unprotected action
 *
 * **Why This Matters:**
 * Theme admin action doesn't verify nonce.
 * Attacker sends admin to malicious page.
 * Page includes: <img src="/wp-admin/?action=delete_all_posts">
 * Admin's browser auto-loads. Request executed (authenticated, no CSRF check).
 * All posts deleted. Site destroyed.
 *
 * **Business Impact:**
 * Theme settings page missing nonce verification.
 * Attacker targets site admin. Sends malicious email:
 * "Check your new dashboard feature!"
 * Link includes hidden request: ?action=add_admin_user&user=hacker
 * Admin clicks. New admin account created (attacker can now login).
 * With nonce: request fails (no CSRF token). Attack blocked.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Admin actions protected
 * - #9 Show Value: Prevents CSRF attacks
 * - #10 Beyond Pure: Cross-site attack prevention
 *
 * **Related Checks:**
 * - Plugin CSRF Protection (similar for plugins)
 * - Theme Capability Checks (complementary)
 * - CSRF Protection Overall (broader)
 *
 * **Learn More:**
 * CSRF protection guide: https://wpshadow.com/kb/theme-nonce-verification
 * Video: Implementing CSRF protection (11min): https://wpshadow.com/training/csrf
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6030.2240
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Nonce Verification Treatment
 *
 * Ensures admin actions registered by themes verify nonces.
 *
 * **Detection Pattern:**
 * 1. Find all theme PHP files
 * 2. Search for add_action with admin hooks
 * 3. Find callback function definition
 * 4. Check for wp_verify_nonce or check_admin_referer
 * 5. Validate nonce action/nonce field
 * 6. Return each unprotected action
 *
 * **Real-World Scenario:**
 * Theme has custom admin page:
 * ```
 * add_action('admin_init', 'save_theme_settings');
 * function save_theme_settings() {
 *     if (!current_user_can('manage_options')) return;
 *     // MISSING: wp_verify_nonce check!
 *     update_option('theme_settings', \$_POST['settings']);
 * }
 * ```
 * Attacker sends admin malicious link. Settings changed. With nonce:
 * function checks wp_verify_nonce(\$_POST['nonce'], 'theme_settings').
 * Attacker's request fails (no valid nonce). Attack blocked.
 *
 * **Implementation Notes:**
 * - Scans active theme files
 * - Detects admin actions without nonce checks
 * - Validates nonce verification methods
 * - Severity: critical (no nonce), high (weak nonce handling)
 * - Treatment: add wp_verify_nonce or check_admin_referer calls
 *
 * @since 1.6030.2240
 */
class Treatment_Theme_Nonce_Verification extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-nonce-verification';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Nonce Verification';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for nonce verification in theme-admin actions';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.2240
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Theme_Nonce_Verification' );
	}
}
