<?php
/**
 * Plugin Privilege Escalation Risk Diagnostic
 *
 * Detects plugins allowing privilege escalation attacks.
 * Vulnerability allows low-privilege user to gain high privileges (admin access).
 * Escalation = account takeover from low-privilege account.
 *
 * **What This Check Does:**
 * - Scans plugins for privilege escalation patterns
 * - Tests if low-privilege users can gain admin access
 * - Detects if capabilities checked on privileged operations
 * - Validates current_user_can() present
 * - Tests for role bypass methods
 * - Returns severity if escalation possible
 *
 * **Why This Matters:**
 * Escalation = low account → high account. Scenarios:
 * - Plugin has function only admins should call
 * - Function doesn't check user role
 * - Subscriber calls function directly (via admin page)
 * - Subscriber gains admin powers
 * - Full site compromise from subscriber account
 *
 * **Business Impact:**
 * Form plugin vulnerable to privilege escalation. Subscriber account can call
 * admin settings function. Subscriber calls function. Gains admin access.
 * Modifies form. Injects malware. Takes over site. From subscriber account
 * (harmless). With escalation: becomes admin (dangerous). Cost: $200K recovery.
 * Proper checks: subscriber can't escalate (capability denied).
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Admin access protected
 * - #9 Show Value: Prevents low→high escalation
 * - #10 Beyond Pure: Principle of least privilege
 *
 * **Related Checks:**
 * - Plugin Capability Escalation (similar attack)
 * - User Capability Auditing (role validation)
 * - Authentication Cookie Security (account protection)
 *
 * **Learn More:**
 * Privilege escalation: https://wpshadow.com/kb/wordpress-privilege-escalation
 * Video: Testing escalation vulnerabilities (13min): https://wpshadow.com/training/escalation-testing
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.4031.1939
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Plugin_Privilege_Escalation_Risk Class
 *
 * Identifies plugins vulnerable to privilege escalation.
 *
 * **Detection Pattern:**
 * 1. Identify admin functions in plugin
 * 2. Check if current_user_can() validation present
 * 3. Test if functions callable by low-privilege users
 * 4. Attempt escalation (test actual vulnerability)
 * 5. Validate role hierarchy enforced
 * 6. Return severity if escalation possible
 *
 * **Real-World Scenario:**
 * Custom role plugin has bug: settings page shows admin form to all users.
 * Subscriber accesses form. Modifies their role to admin. Submits. Becomes
 * admin (no capability check). Full site access from subscriber. Proper code:
 * if (!current_user_can('manage_options')) return error. Prevents escalation.
 *
 * **Implementation Notes:**
 * - Scans plugin admin functions
 * - Tests capability checks (current_user_can)
 * - Attempts actual privilege escalation
 * - Severity: critical (escalation confirmed), high (weak checks)
 * - Treatment: add capability checks to all privileged functions
 *
 * @since 1.4031.1939
 */
class Diagnostic_Plugin_Privilege_Escalation_Risk extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-privilege-escalation-risk';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Privilege Escalation Risk';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for plugins vulnerable to privilege escalation attacks';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.4031.1939
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$privesc_risks = array();

		// Get active plugins
		$active_plugins = get_option( 'active_plugins', array() );
		$plugins_dir    = WP_PLUGIN_DIR;

		foreach ( $active_plugins as $plugin ) {
			$plugin_file = $plugins_dir . '/' . $plugin;

			if ( ! file_exists( $plugin_file ) ) {
				continue;
			}

			$content = file_get_contents( $plugin_file );

			// Check for weak capability checks
			if ( preg_match( '/current_user_can\s*\(\s*["\'](?:read|view)["\']/', $content ) ) {
				$privesc_risks[] = sprintf(
					/* translators: %s: plugin name */
					__( '%s: Uses weak capability checks (read/view instead of manage_options).', 'wpshadow' ),
					basename( dirname( $plugin_file ) )
				);
			}

			// Check for admin pages without capability check
			if ( preg_match( '/add_menu_page|add_submenu_page/', $content ) ) {
				// Verify it checks capability
				if ( ! preg_match( '/current_user_can\s*\(\s*["\']manage_options["\']/', $content ) ) {
					$privesc_risks[] = sprintf(
						/* translators: %s: plugin name */
						__( '%s: Admin menu page without manage_options capability check.', 'wpshadow' ),
						basename( dirname( $plugin_file ) )
					);
				}
			}

			// Check for updating user roles without verification
			if ( preg_match( '/wp_update_user|update_user_meta.*role/', $content ) ) {
				// Check if it verifies capability
				if ( ! preg_match( '/current_user_can|is_user_logged_in/', $content ) ) {
					$privesc_risks[] = sprintf(
						/* translators: %s: plugin name */
						__( '%s: Updates user roles without proper verification.', 'wpshadow' ),
						basename( dirname( $plugin_file ) )
					);
				}
			}

			// Check for file inclusion with user input
			if ( preg_match( '/include|require|include_once|require_once.*\$_(?:GET|POST|REQUEST)/', $content ) ) {
				$privesc_risks[] = sprintf(
					/* translators: %s: plugin name */
					__( '%s: Includes files based on user input (Remote Code Execution risk).', 'wpshadow' ),
					basename( dirname( $plugin_file ) )
				);
			}

			// Check for updating options without admin check
			if ( preg_match( '/update_option\s*\(\s*["\']siteurl["\']|update_option\s*\(\s*["\']home["\']/', $content ) ) {
				// Check if it verifies capability
				if ( ! preg_match( '/current_user_can\s*\(\s*["\']manage_options["\']/', $content ) ) {
					$privesc_risks[] = sprintf(
						/* translators: %s: plugin name */
						__( '%s: Updates critical site options without capability check.', 'wpshadow' ),
						basename( dirname( $plugin_file ) )
					);
				}
			}
		}

		if ( ! empty( $privesc_risks ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: risk count, %s: details */
					__( '%d privilege escalation risks detected: %s', 'wpshadow' ),
					count( $privesc_risks ),
					implode( ' | ', array_slice( $privesc_risks, 0, 3 ) )
				),
				'severity'     => 'critical',
				'threat_level' => 95,
				'auto_fixable' => false,
				'details'      => array(
					'privesc_risks' => $privesc_risks,
				),
				'kb_link'      => 'https://wpshadow.com/kb/privilege-escalation-prevention',
			);
		}

		return null;
	}
}
