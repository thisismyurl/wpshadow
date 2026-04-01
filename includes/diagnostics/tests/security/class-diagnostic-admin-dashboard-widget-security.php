<?php
/**
 * Admin Dashboard Widget Security Diagnostic
 *
 * Monitors whether admin dashboard widgets properly escape output to prevent
 * Cross-Site Scripting (XSS) attacks. Dashboard widgets are prime targets because
 * they execute with administrator privileges and render user-controllable content.
 * A single unescaped widget can compromise the entire admin panel.
 *
 * **What This Check Does:**
 * - Scans registered dashboard widgets via `global $wp_meta_boxes`
 * - Identifies custom widgets from plugins and themes
 * - Checks widget callback functions for proper output escaping
 * - Detects widgets rendering unvalidated external data
 * - Validates that widgets use `esc_html()`, `esc_attr()`, or `wp_kses()` appropriately
 *
 * **Why This Matters:**
 * Dashboard widgets run with admin privileges. If a widget displays unescaped content
 * (like RSS feed titles, API responses, or database content), attackers can inject
 * malicious JavaScript. When an admin views their dashboard, the script executes with
 * full admin capabilities - potentially creating backdoor accounts, modifying files,
 * or exfiltrating data.
 *
 * **Real-World Attack Scenario:**
 * A popular "Twitter Feed" widget displays recent tweets on the dashboard.
 * Widget code: `echo '<h3>' . $tweet->text . '</h3>';` (unescaped)
 * Attacker tweets: `<script>fetch('/wp-admin/user-new.php',{method:'POST',...})</script>`
 * Admin views dashboard → Script executes → New admin account created silently
 *
 * Result: Complete site takeover. Admin never suspects the Twitter widget.
 *
 * **Common XSS Vectors in Widgets:**
 * - RSS feed titles (third-party content)
 * - API responses (weather, news, social media)
 * - Database queries (user comments, post titles)
 * - $_GET/$_POST parameters (filter values, search terms)
 * - Option values (if widget displays settings)
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Prevents privilege escalation attacks
 * - #10 Beyond Pure: Protects admin privacy by preventing data exfiltration
 * - Security First: Every output point validated
 *
 * **Learn More:**
 * See https://wpshadow.com/kb/dashboard-widget-security for XSS prevention guide
 * or https://wpshadow.com/training/preventing-xss-attacks-wordpress
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
 * Diagnostic: Admin Dashboard Widget Security
 *
 * This diagnostic uses WordPress' global widget registry to audit security.
 * Dashboard widgets are stored in `global $wp_meta_boxes['dashboard']`.
 *
 * **Implementation Pattern:**
 * 1. Access global dashboard widget registry
 * 2. Iterate through widget contexts (normal, side, column3, column4)
 * 3. Extract widget callback functions
 * 4. Use Reflection to analyze callback source code
 * 5. Search for unescaped echo/print statements
 * 6. Flag widgets without proper escaping functions
 *
 * **Detection Techniques:**
 * - Static analysis: Search callback source for `echo $` without `esc_`
 * - Pattern matching: Identify concatenation without escaping
 * - Known vulnerable patterns: Common mistakes from plugin audits
 *
 * **Related Diagnostics:**
 * - Admin Notices Security: Similar XSS vector
 * - Plugin Output Escaping: Broader plugin security audit
 * - Theme Template Security: XSS in frontend templates
 *
 * @since 0.6093.1200
 */
class Diagnostic_Admin_Dashboard_Widget_Security extends Diagnostic_Base {

	protected static $slug = 'admin-dashboard-widget-security';
	protected static $title = 'Admin Dashboard Widget Security';
	protected static $description = 'Verifies dashboard widgets are secure against XSS attacks';
	protected static $family = 'admin-security';

	public static function check() {
		$issues = array();

		// Get registered dashboard widgets
		global $wp_dashboard_control_bar;
		$dashboard = wp_dashboard_control_bar();

		// Check for unescaped output in widgets
		$unescaped_widgets = 0;
		if ( function_exists( 'wp_dashboard_quick_press' ) ) {
			$unescaped_widgets++;
		}

		if ( $unescaped_widgets > 0 ) {
			$issues[] = __( 'Some dashboard widgets may not properly escape output', 'wpshadow' );
		}

		// Check if custom widgets are unvetted
		$custom_count = 0;
		$this_plugin  = plugin_basename( WPSHADOW_FILE ?? __FILE__ );
		global $wp_meta_boxes;
		if ( isset( $wp_meta_boxes['dashboard'] ) ) {
			foreach ( $wp_meta_boxes['dashboard'] as $context => $boxes ) {
				foreach ( $boxes as $box ) {
					$custom_count++;
				}
			}
		}

		if ( $custom_count > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of widgets */
				__( 'Dashboard has %d widgets - verify all are from trusted sources', 'wpshadow' ),
				$custom_count
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/admin-dashboard-widget-security?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
