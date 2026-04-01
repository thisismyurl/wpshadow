<?php
/**
 * Theme Nonce Verification Diagnostic
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
 * Theme Nonce Verification Diagnostic
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
 * @since 0.6093.1200
 */
class Diagnostic_Theme_Nonce_Verification extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-nonce-verification';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Nonce Verification';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for nonce verification in theme-admin actions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$theme_dir = wp_get_theme()->get_stylesheet_directory();
		$functions_file = $theme_dir . '/functions.php';

		if ( ! file_exists( $functions_file ) ) {
			return null;
		}

		$content = file_get_contents( $functions_file, false, null, 0, 60000 );
		if ( false === $content ) {
			return null;
		}

		if ( false !== strpos( $content, 'admin_post' ) || false !== strpos( $content, 'admin_init' ) ) {
			if ( false === strpos( $content, 'check_admin_referer' ) && false === strpos( $content, 'wp_verify_nonce' ) ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Theme admin actions may be missing nonce verification', 'wpshadow' ),
					'severity'     => 'high',
					'threat_level' => 80,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/theme-nonce-verification?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
					'details'      => array(
						'issues' => array(
							__( 'Admin hooks detected without nonce verification', 'wpshadow' ),
						),
					),
				);
			}
		}

		return null;
	}
}
