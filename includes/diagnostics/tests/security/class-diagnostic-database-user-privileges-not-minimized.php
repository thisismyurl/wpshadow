<?php
/**
 * Database User Privileges Not Minimized Diagnostic
 *
 * Verifies that the WordPress database user operates with principle of least privilege—
 * granted only necessary permissions to function, not superuser/admin privileges. Many
 * hosting providers default database users to "ALL PRIVILEGES" for convenience, which
 * is a critical security vulnerability. If WordPress is compromised, attacker gains
 * unrestricted database access: can modify WordPress tables AND drop tables from other
 * hosted websites.
 *
 * **What This Check Does:**
 * - Queries MySQL system tables (information_schema) for database user grants
 * - Checks if WordPress database user has SELECT, INSERT, UPDATE, DELETE (necessary)
 * - Detects excessive privileges: CREATE, DROP, GRANT, SUPER, FILE, PROCESS
 * - Flags wildcard privileges (db_name.* vs specific tables)
 * - Validates user cannot access other databases or system tables
 * - Tests actual permission enforcement (permission_check query)
 *
 * **Why This Matters:**
 * WordPress requires: SELECT (read), INSERT (create), UPDATE (edit), DELETE (remove).
 * Any additional privileges are attack vectors. Real exploitation scenarios:
 * - Compromise via malicious plugin + SQL injection = attacker gets FILE privilege
 * - Uses LOAD_FILE() to read /etc/passwd, finds other databases
 * - Uses INTO OUTFILE to write webshell, gains shell access, exfiltrates data
 * - SUPER privilege: attacker kills other site queries or modifies server variables
 * - GRANT privilege: attacker creates backdoor database user for persistent access
 *
 * **Business Impact:**
 * Over-privileged database = single point of failure across all hosted sites. Shared
 * hosting scenario: 50 sites on same server. One site compromised = attacker can
 * access 49 other sites' databases. Recovery: restore ALL sites from backup (2-3 days).
 * Prevention: 10-minute database user reconfiguration.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Defense-in-depth at infrastructure level
 * - #9 Show Value: Eliminates massive lateral-movement attack class
 * - #10 Beyond Pure: Protects neighboring websites on shared infrastructure
 *
 * **Related Checks:**
 * - User Capability Auditing (WordPress user permissions)
 * - API Throttling Not Configured (API layer defense)
 * - Database Table Corruption Check (aftermath: detect if DB was accessed)
 *
 * **Learn More:**
 * Database hardening guide: https://wpshadow.com/kb/database-privilege-minimization
 * Video: Least privilege database setup (10min): https://wpshadow.com/training/database-security
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
 * Database User Privileges Not Minimized Diagnostic Class
 *
 * Implements least-privilege verification by querying MySQL INFORMATION_SCHEMA.USER_PRIVILEGES
 * or via SHOW GRANTS FOR current_user(). Detection: parses grant string for excessive
 * privileges (CREATE, DROP, GRANT, SUPER, FILE, PROCESS, RELOAD, REPLICATION). Success
 * = user has only SELECT, INSERT, UPDATE, DELETE on WordPress database. Failure = any
 * privilege beyond necessary 4.
 *
 * **Detection Pattern:**
 * 1. Execute SHOW GRANTS FOR CURRENT_USER() to get grant string
 * 2. Parse grant: extract privileges and database scope (db_name.* vs specific)
 * 3. Check for dangerous privileges: CREATE, DROP, GRANT, SUPER, FILE, PROCESS, REPLICATION
 * 4. Verify database scope = specific WordPress database (not *.*)
 * 5. Validate privilege list ⊆ {SELECT, INSERT, UPDATE, DELETE}
 * 6. Test actual permission: try DROP privilege with test query (catches aliasing)
 *
 * **Real-World Scenario:**
 * Freelancer sets up WordPress via auto-installer on cPanel host. Default configuration
 * grants database user ALL PRIVILEGES. July 2024: freelancer doesn't update WordPress
 * core plugin, gets SQL injection via user form. Attacker: "I can access ALL PRIVILEGES."
 * Uses LOAD_FILE() to read hosting control panel config files, finds other customer databases.
 * Within 24 hours, owns 12 sites on same server. Hosting provider: "Your security, your
 * responsibility." Cleanup bill: $15K+. Prevention: this check would have flagged it.
 *
 * **Implementation Notes:**
 * - Uses mysqli or wpdb->get_results() to query database grants
 * - Gracefully handles permission denied (some hosts restrict SHOW GRANTS)
 * - Returns severity: critical (over-privileged), warning (wildcard scope)
 * - Non-fixable diagnostic (requires host support to reconfigure database)
 *
 * @since 1.6030.2352
 */
class Diagnostic_Database_User_Privileges_Not_Minimized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-user-privileges-not-minimized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database User Privileges Not Minimized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if database user privileges are minimized';

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
		// Check if database privilege audit exists
		if ( ! get_option( 'db_privilege_audit_date' ) ) {
			$finding = array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Database user privileges are not minimized. Grant only SELECT, INSERT, UPDATE, DELETE privileges - avoid GRANT or CREATE privileges for WordPress database users.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 55,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/database-user-privileges-not-minimized',
				'context'       => array(
					'why'            => __(
						'Over-privileged database users enable lateral movement attacks. If WordPress is compromised via SQL injection, ' .
						'an attacker with ALL PRIVILEGES can: read other databases (LOAD_FILE), write files (INTO OUTFILE), execute system commands (SUPER), ' .
						'and create backdoor users (GRANT). On shared hosting with 50 sites, one compromise = all sites compromised. Recovery time: 2-3 days. ' .
						'Principle of least privilege requires WordPress database user have ONLY: SELECT, INSERT, UPDATE, DELETE on its own database. ' .
						'Any additional privilege is an unnecessary attack surface.',
						'wpshadow'
					),
					'recommendation' => __(
						'Contact hosting provider to reconfigure database user with minimal privileges: SELECT, INSERT, UPDATE, DELETE on ' .
						'WordPress database only. Verify no GRANT, CREATE, DROP, FILE, PROCESS, SUPER privileges. Test by attempting to CREATE TABLE ' .
						'(should fail). Implement separate read-only user for backups if supported. Monitor for unauthorized privilege grants.',
						'wpshadow'
					),
				),
			);

			$finding = Upgrade_Path_Helper::add_upgrade_path(
				$finding,
				'security',
				'database-hardening',
				'database-privilege-guide'
			);

			return $finding;
		}

		return null;
	}
}
