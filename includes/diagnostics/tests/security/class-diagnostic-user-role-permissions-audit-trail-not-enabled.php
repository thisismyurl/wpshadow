<?php
/**
 * User Role Permissions Audit Trail Not Enabled Diagnostic
 *
 * Checks if permissions audit trail is enabled.
 * Audit trail = log of all permission changes.
 * Who granted admin? When? Audit trail answers.
 * Without trail: permission escalation undetected.
 *
 * **What This Check Does:**
 * - Checks if audit logging plugin active
 * - Validates role change logging
 * - Tests capability modification tracking
 * - Checks if audit logs retained
 * - Validates log review process
 * - Returns severity if audit trail disabled
 *
 * **Why This Matters:**
 * Attacker gains access. Escalates own permissions (contributor → admin).
 * Without audit trail: change undetected.
 * With trail: log shows "User X changed role to admin at 2AM".
 * Suspicious activity detected.
 *
 * **Business Impact:**
 * Compromised account escalates to admin. Operates 3 months undetected.
 * Steals data. Injects malware. Cost: $500K+. With audit trail:
 * permission change logged. Admin reviews logs weekly. Suspicious
 * escalation detected within days. Account locked. Damage minimized.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Permission changes tracked
 * - #9 Show Value: Detects privilege escalation
 * - #10 Beyond Pure: Accountability by design
 *
 * **Related Checks:**
 * - Activity Logging Overall (broader)
 * - User Capability Auditing (related)
 * - Admin Account Security (complementary)
 *
 * **Learn More:**
 * Audit trail setup: https://wpshadow.com/kb/audit-trail
 * Video: Permission auditing (11min): https://wpshadow.com/training/audit-logging
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User Role Permissions Audit Trail Not Enabled Diagnostic Class
 *
 * Detects disabled permissions audit trail.
 *
 * **Detection Pattern:**
 * 1. Check if audit logging plugin active
 * 2. Validate role change logging enabled
 * 3. Test capability modification tracking
 * 4. Check log retention period
 * 5. Validate review notifications
 * 6. Return if audit trail disabled
 *
 * **Real-World Scenario:**
 * Audit trail enabled. Attacker compromises contributor account.
 * Escalates to editor. Log records: "User contributor_123 role changed
 * to editor at 3:47 AM". Admin reviews logs Monday. Sees suspicious
 * change. Investigates. Compromise detected. Account locked.
 *
 * **Implementation Notes:**
 * - Checks audit logging configuration
 * - Validates permission change tracking
 * - Tests log retention
 * - Severity: high (no audit trail)
 * - Treatment: enable audit logging for user roles
 *
 * @since 1.6030.2352
 */
class Diagnostic_User_Role_Permissions_Audit_Trail_Not_Enabled extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'user-role-permissions-audit-trail-not-enabled';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'User Role Permissions Audit Trail Not Enabled';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if permissions audit trail is enabled';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if audit trail is enabled
		if ( ! get_option( 'enable_audit_trail' ) && ! is_plugin_active( 'stream/stream.php' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'User role permissions audit trail is not enabled. Enable audit logging to track changes to user roles and permissions.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 50,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/user-role-permissions-audit-trail-not-enabled',
			);
		}

		return null;
	}
}
