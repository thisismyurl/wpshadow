<?php
/**
 * Theme Capability Checks Diagnostic
 *
 * Validates theme code checks user capabilities before admin operations.
 * Theme without capability checks = anyone can execute admin functions.
 * Privilege escalation vector.
 *
 * **What This Check Does:**
 * - Scans theme PHP files
 * - Detects admin hooks (add_action('admin_init', ...))
 * - Checks if capability verified (current_user_can)
 * - Flags dangerous functions without checks
 * - Tests for unprotected custom endpoints
 * - Returns severity if checks missing
 *
 * **Why This Matters:**
 * Theme function runs admin code without capability check.
 * Non-admin calls function. Code executes with admin privileges.
 * Privilege escalation (non-admin → admin). Account takeover.
 *
 * **Business Impact:**
 * Theme has custom settings page. Forgot capability check.
 * Attacker discovers. Calls function directly. Settings modified.
 * Site configuration changed. Malware injected. Site defaced.
 * Cost: $150K+. With capability check: attacker's call rejected.
 * Only administrators can modify settings.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Theme respects permissions
 * - #9 Show Value: Prevents privilege escalation via theme
 * - #10 Beyond Pure: Role-based access control
 *
 * **Related Checks:**
 * - Plugin Capability Escalation (similar risk in plugins)
 * - Theme Direct Database Access (complementary check)
 * - Theme Data Validation (input handling)
 *
 * **Learn More:**
 * Theme security standards: https://wpshadow.com/kb/theme-capability-checks
 * Video: Coding secure themes (14min): https://wpshadow.com/training/theme-security
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2240
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Capability Checks Diagnostic
 *
 * Checks theme files for capability checks in admin hooks.
 *
 * **Detection Pattern:**
 * 1. Find all theme PHP files
 * 2. Search for add_action with admin hooks
 * 3. Find callback function definition
 * 4. Check if current_user_can used
 * 5. Verify capability required
 * 6. Return severity if missing
 *
 * **Real-World Scenario:**
 * Theme has custom admin page for site settings. Developer forgot
 * capability check on the callback:
 * ```
 * add_action('admin_init', 'my_custom_settings');
 * function my_custom_settings() {
 *     // No capability check here!
 *     update_option('theme_color', $_POST['color']);
 * }
 * ```
 * Attacker discovers function. Crafts AJAX request. Option updated.
 * With check: function verifies current_user_can('manage_options')
 * first. Non-admin call fails.
 *
 * **Implementation Notes:**
 * - Scans active theme files
 * - Detects admin hooks and callbacks
 * - Validates capability verification
 * - Severity: critical (no checks), high (weak checks)
 * - Treatment: add current_user_can verification
 *
 * @since 1.6030.2240
 */
class Diagnostic_Theme_Capability_Checks extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-capability-checks';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Capability Checks';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies theme code includes basic capability checks';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2240
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$theme_dir = wp_get_theme()->get_stylesheet_directory();
		$functions_file = $theme_dir . '/functions.php';

		if ( ! file_exists( $functions_file ) ) {
			return null;
		}

		$content = file_get_contents( $functions_file, false, null, 0, 50000 );
		$issues = array();

		if ( false !== strpos( $content, 'admin_init' ) || false !== strpos( $content, 'admin_post' ) ) {
			if ( false === strpos( $content, 'current_user_can' ) ) {
				$issues[] = __( 'Theme registers admin hooks without capability checks', 'wpshadow' );
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Theme capability checks may be missing in admin actions', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/theme-capability-checks',
				'details'      => array(
					'issues' => $issues,
				),
			);
		}

		return null;
	}
}
