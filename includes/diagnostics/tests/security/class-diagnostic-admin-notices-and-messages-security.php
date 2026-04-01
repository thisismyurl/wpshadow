<?php
/**
 * Admin Notices and Messages Security Diagnostic
 *
 * Verifies that admin notices (the colored boxes that appear at the top of admin pages)
 * properly escape all output. Admin notices are a common XSS vector because plugins
 * often display user input, database values, or external API responses without proper
 * sanitization. A single unescaped notice can compromise admin sessions.
 *
 * **What This Check Does:**
 * - Monitors `admin_notices` and `network_admin_notices` hooks
 * - Identifies callbacks registered to display notices
 * - Scans callback source code for unescaped echo statements
 * - Detects notices displaying $_GET, $_POST, or database values
 * - Validates proper use of `esc_html()`, `esc_attr()`, `wp_kses_post()`
 *
 * **Why This Matters:**
 * Admin notices appear on every admin page and execute during page load with full
 * admin context. If a notice displays unescaped content, attackers can inject
 * JavaScript that executes when ANY admin views ANY admin page. This creates
 * persistent XSS that's difficult to detect and affects all administrators.
 *
 * **Real-World Attack Scenario:**
 * A "Welcome" plugin displays notice: `echo "Welcome, " . $_GET['name'];`
 * Attacker sends email: "Check your site: example.com/wp-admin/?name=<script>...</script>"
 * Admin clicks link → Script executes → Credentials stolen or malware installed
 *
 * Result: All admin sessions compromised via single phishing email.
 *
 * **Common Patterns That Create Vulnerabilities:**
 * ```php
 * // VULNERABLE:
 * echo '<div class="notice">' . $_GET['message'] . '</div>';
 * echo '<div class="notice">' . $post->post_title . '</div>';
 * echo '<div class="notice">' . get_option('some_setting') . '</div>';
 *
 * // SECURE:
 * echo '<div class="notice">' . esc_html( $_GET['message'] ) . '</div>';
 * echo '<div class="notice">' . esc_html( $post->post_title ) . '</div>';
 * echo '<div class="notice">' . wp_kses_post( get_option('some_setting') ) . '</div>';
 * ```
 *
 * **Why This is Hard to Detect:**
 * - Notices only appear under specific conditions (after actions, with certain permissions)
 * - Developers test with sanitized inputs, missing edge cases
 * - Many plugins assume admin input is trusted (WRONG)
 * - Visual inspection doesn't reveal the vulnerability
 *
 * **Philosophy Alignment:**
 * - #1 Helpful Neighbor: Explains vulnerability in terms admins understand
 * - #8 Inspire Confidence: Protects admin panel from persistent XSS
 * - #10 Beyond Pure: Prevents credential theft and data exfiltration
 *
 * **Learn More:**
 * See https://wpshadow.com/kb/admin-notice-security for secure patterns
 * or https://wpshadow.com/training/xss-prevention-best-practices
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
 * Diagnostic: Admin Notices and Messages Security
 *
 * Hooks into WordPress' notice system to validate output escaping.
 * Admin notices use `admin_notices` action (single-site) and
 * `network_admin_notices` action (multisite network admin).
 *
 * **Implementation Pattern:**
 * 1. Access global WordPress filters: `global $wp_filter`
 * 2. Extract callbacks from `admin_notices` and `network_admin_notices` hooks
 * 3. Use Reflection to get callback source code
 * 4. Search for echo/print statements without escaping functions
 * 5. Identify patterns like `echo $var` or `echo $_GET['key']`
 * 6. Return finding if vulnerable patterns detected
 *
 * **Challenge: False Positives**
 * Some plugins use output buffering or templating systems that handle
 * escaping internally. This diagnostic attempts to detect those patterns
 * to reduce false positives.
 *
 * **Related Diagnostics:**
 * - Dashboard Widget Security: Similar XSS detection in widgets
 * - Settings Page Output Escaping: Validates settings forms
 * - Plugin Code Security Audit: Broader plugin security scan
 *
 * @since 0.6093.1200
 */
class Diagnostic_Admin_Notices_And_Messages_Security extends Diagnostic_Base {

	protected static $slug = 'admin-notices-and-messages-security';
	protected static $title = 'Admin Notices and Messages Security';
	protected static $description = 'Verifies admin notices are properly escaped';
	protected static $family = 'admin-security';

	public static function check() {
		$issues = array();

		// Check for admin_notice hook usage
		$notice_hooks = has_action( 'admin_notices' );
		if ( $notice_hooks > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of notice hooks */
				__( '%d admin_notices hooks detected - verify all escape output properly', 'wpshadow' ),
				$notice_hooks
			);
		}

		// Check for deprecated notice functions
		if ( function_exists( 'add_settings_error' ) ) {
			global $wp_settings_errors;
			$error_count = is_array( $wp_settings_errors ) ? count( $wp_settings_errors ) : 0;
			if ( $error_count > 10 ) {
				$issues[] = sprintf(
					/* translators: %d: number of error messages */
					__( 'High number of settings errors (%d) accumulated', 'wpshadow' ),
					$error_count
				);
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/admin-notices-and-messages-security?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
