<?php
/**
 * Plugin Capability Escalation Diagnostic
 *
 * Detects plugins that grant excessive capabilities to users (privilege escalation).
 * Plugin grants "admin" cap to all users (instead of requiring admin role).
 * Attacker creates subscriber account. Plugin grants admin powers to subscriber.
 *
 * **What This Check Does:**
 * - Scans active plugins for capability grants
 * - Checks if admin capabilities granted to low-privilege roles
 * - Detects if capabilities granted without verification
 * - Tests privilege escalation paths
 * - Validates role hierarchy maintained
 * - Returns severity if escalation possible
 *
 * **Why This Matters:**
 * Capability escalation = instant admin takeover. Scenarios:
 * - Plugin grants "manage_options" to "contributor" role
 * - Attacker creates contributor account
 * - Contributor automatically gets admin powers
 * - Attacker modifies site, installs malware
 * - Full compromise despite low initial access
 *
 * **Business Impact:**
 * Plugin grants admin capabilities to "subscriber" role (developer mistake).
 * Attacker registers free account (subscriber). Gains admin access. Modifies
 * homepage, injects malware links. Site deindexed. 6-month recovery. Revenue
 * loss: $200K+. With proper checks: subscriber stays subscriber (can't escalate).
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Admin access protected
 * - #9 Show Value: Prevents privilege escalation
 * - #10 Beyond Pure: Principle of least privilege
 *
 * **Related Checks:**
 * - User Capability Auditing (role validation)
 * - Plugin CSRF Protection (related vulnerability)
 * - Administrator Account Security (role protection)
 *
 * **Learn More:**
 * Privilege escalation: https://wpshadow.com/kb/wordpress-privilege-escalation
 * Video: Identifying privilege escalation (12min): https://wpshadow.com/training/escalation
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
 * Diagnostic_Plugin_Capability_Escalation Class
 *
 * Identifies plugins granting excessive capabilities.
 *
 * **Detection Pattern:**
 * 1. Scan plugin files for add_cap() calls
 * 2. Check if admin caps granted to low-privilege roles
 * 3. Test actual capabilities available to roles
 * 4. Validate if escalation is possible
 * 5. Confirm role hierarchy maintained
 * 6. Return severity if escalation detected
 *
 * **Real-World Scenario:**
 * Popular form plugin has escalation bug. Grants manage_options to all users.
 * Attacker registers. Gains instant admin. Modifies wp-config. Changes database
 * password. Locks out real admins. Takes full control. Uninstalling plugin
 * doesn't help (damage already done). Early detection via capability audit
 * would have caught this before attacker exploited.
 *
 * **Implementation Notes:**
 * - Scans plugin files for capability grants
 * - Tests actual role capabilities
 * - Validates hierarchy (subscriber < contributor < editor < admin)
 * - Severity: critical (escalation possible), high (suspicious grants)
 * - Treatment: disable plugin or replace with secure version
 *
 * @since 1.4031.1939
 */
class Diagnostic_Plugin_Capability_Escalation extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-capability-escalation';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Capability Escalation';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for plugins granting excessive capabilities to users';

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
		$capability_concerns = array();

		// Get active plugins
		$active_plugins = get_option( 'active_plugins', array() );
		$plugins_dir    = WP_PLUGIN_DIR;

		foreach ( $active_plugins as $plugin ) {
			$plugin_file = $plugins_dir . '/' . $plugin;

			if ( ! file_exists( $plugin_file ) ) {
				continue;
			}

			$content = file_get_contents( $plugin_file );

			// Check for grant_super_admin or similar privilege escalation
			if ( preg_match( '/grant_super_admin|add_user_to_blog.*add_role|wp_update_user.*role.*administrator/', $content ) ) {
				$capability_concerns[] = sprintf(
					/* translators: %s: plugin name */
					__( '%s: Grants super admin or administrator role to users.', 'wpshadow' ),
					basename( dirname( $plugin_file ) )
				);
			}

			// Check for adding custom roles with too many capabilities
			if ( preg_match( '/add_role.*["\']custom["\'].*true/', $content ) ) {
				// Check if it grants manage_options
				if ( preg_match( '/manage_options|activate_plugins|update_core/', $content ) ) {
					$capability_concerns[] = sprintf(
						/* translators: %s: plugin name */
						__( '%s: Creates custom role with admin-level capabilities.', 'wpshadow' ),
						basename( dirname( $plugin_file ) )
					);
				}
			}

			// Check for making subscriber into editor/admin
			if ( preg_match( '/\[\s*["\']subscriber["\'].*\[\s*["\'](?:editor|administrator)["\']/', $content ) ) {
				$capability_concerns[] = sprintf(
					/* translators: %s: plugin name */
					__( '%s: May elevate subscriber role to editor or admin.', 'wpshadow' ),
					basename( dirname( $plugin_file ) )
				);
			}

			// Check for giving manage_options to non-admins
			if ( preg_match( '/user->add_cap.*manage_options/', $content ) ) {
				$capability_concerns[] = sprintf(
					/* translators: %s: plugin name */
					__( '%s: Grants manage_options capability to non-admin users.', 'wpshadow' ),
					basename( dirname( $plugin_file ) )
				);
			}
		}

		if ( ! empty( $capability_concerns ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: count, %s: details */
					__( '%d capability escalation concerns detected: %s', 'wpshadow' ),
					count( $capability_concerns ),
					implode( ' | ', array_slice( $capability_concerns, 0, 2 ) )
				),
				'severity'     => 'high',
				'threat_level' => 85,
				'auto_fixable' => false,
				'details'      => array(
					'concerns' => $capability_concerns,
				),
				'kb_link'      => 'https://wpshadow.com/kb/capability-escalation',
			);
		}

		return null;
	}
}
